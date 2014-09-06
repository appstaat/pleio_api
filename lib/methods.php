<?php

function pleio_api_use_api_key($hook, $type, $returnvalue, $params) {
	$site = elgg_get_site_entity ();
	if ($site && $site->guid != 1) {
		$license_key = elgg_get_plugin_setting ( "license_key", "pleio_api" );
		$last_check = intval ( elgg_get_plugin_setting ( "last_license_check", "pleio_api" ) );
		$hash = hash_hmac ( "SHA256", $site->url, $site->guid );
		if (! $license_key || $hash != $license_key || ! $last_check || $last_check < time () - 86400) {
			elgg_set_plugin_setting ( "last_license_check", time (), "pleio_api" );
			if (! empty ( $params ) && is_string ( $params )) {
				$api_user = get_api_user ( $site->getGUID (), $params );
				if ($api_user) {
					$app = ws_pack_get_application_from_api_user_id ( $api_user->id );
					if ($app) {
						if ($app->application_id == "pleio_app") {
							$data = array ("id" => $site->guid, "name" => $site->name, "url" => $site->url, "email" => $site->email, "members" => $site->member_count );
							$url = "http://appstaat.funil.nl/overheidsplein-app/license.php?" . http_build_query ( $data );
							try {
								$response = file_get_contents ( $url );
								if ($response) {
									$response = json_decode ( $response );
									$license_key = $response->key;
									elgg_set_plugin_setting ( "license_key", $license_key, "pleio_api" );
								}
							} catch ( Exception $ex ) {
							}
						}
					}
				}
			}
		}
		if (! $license_key) {
			return false;
		}
	}
}

function pleio_api_get_login() {
	$user = elgg_get_logged_in_user_entity ();
	$login = pleio_api_format_user ( $user );
	$login ["email"] = $user ["email"];
	return $login;
}

function pleio_api_update_device_token($device_token, $device) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if ($user_id && $device_token) {
		set_private_setting ( $user_id, "device", $device );
		set_private_setting ( $user_id, "device_token", $device_token );
		set_private_setting ( $user_id, "device_token_updated", time () );
		return new SuccessResult ( elgg_echo ( "device_token:saved" ) );
	}
	return new ErrorResult ( elgg_echo ( "device_token:failed" ) );
}

function pleio_api_get_button_names() {
	$user = elgg_get_logged_in_user_entity ();
	$lang = $user && $user->lanugage ? $user->language : 'nl';
	$keys = array ("messages:inbox" );
	foreach ( $keys as $key ) {
		$list [$key] = elgg_echo ( $key, array (), $lang );
	}
	return $list;
}

function pleio_api_get_all_subsites($search = null, $subsite_id = 0, $locked_filter = 0, $order_by = 0, $offset = 0, $wheres = array (), $joins = array()) {
	$list = array ();
	$total = 0;
	$joins [] = sprintf ( " INNER JOIN %ssites_entity s USING (guid) ", get_config ( "dbprefix" ) );
	$offset = intval ( $offset );
	if ($search) {
		$search = sanitise_string ( $search );
		$wheres [] = " (s.name LIKE '%$search%' OR s.url LIKE '%$search%' OR s.description LIKE '%$search%') ";
	}
	if ($locked_filter == 1) {
		$joins [] = sprintf ( " INNER JOIN %sprivate_settings ps ON ps.entity_guid = guid ", get_config ( "dbprefix" ) );
		$wheres [] = " ps.name = 'membership' AND ps.value = 'open' ";
	} elseif ($locked_filter == 2) {
		$joins [] = sprintf ( " INNER JOIN %sprivate_settings ps ON ps.entity_guid = guid ", get_config ( "dbprefix" ) );
		$wheres [] = " ps.name = 'membership' AND ps.value != 'open' ";
	}
	$options = array ('type' => 'site', 'limit' => 20, 'offset' => $offset, 'count' => true, "wheres" => $wheres, "joins" => $joins );
	if ($subsite_id) {
		$options ["guids"] = $subsite_id;
	}
	$total = elgg_get_entities ( $options );
	if ($total > 0) {
		$options ['count'] = false;
		if ($order_by == 1) {
			$options ['order_by'] = ' s.name ';
		} elseif ($order_by == 2) {
			$options ['order_by'] = ' s.name DESC ';
		}
		$sites = elgg_get_entities ( $options );
		/* @var $site Subsite */
		foreach ( $sites as $site ) {
			$e = pleio_api_export ( $site, explode ( ",", "guid,name,url" ) );
			$membership = get_private_setting ( $site->guid, "membership" );
			$e ["o"] = (! $membership || $membership == "open") ? 1 : 0;
			$is_member = ($site instanceof Subsite && $site->isUser ()) || (! ($site instanceof Subsite) && $site instanceof ElggSite) ? 1 : 0;
			$e ["m"] = $is_member;
			$e ["i"] = ! $is_member && $site instanceof Subsite && $site->hasInvitation () ? 1 : 0;
			$e ["p"] = ! $is_member && $site instanceof Subsite && $site->pendingMembershipRequest () ? 1 : 0;
			$e ["l"] = pleio_api_get_mobile_logo ( $e ['guid'], $e ["url"] );
			$colors = pleio_api_get_site_colors ( $e ['guid'] );
			$e ["c"] = $colors [0];
			$e ["fc"] = $colors [1];
			$count_result = get_data_row ( 
					sprintf ( "select count(*) as c from %sentity_relationships where guid_two = %d and relationship = 'member_of_site' ", get_config ( "dbprefix" ), 
							$e ['guid'] ) );
			$e ["mt"] = $count_result->c;
			$e ["e"] = $site->email;
			$list [] = $e;
		}
	}
	return array ("total" => $total, "list" => $list, "offset" => $offset );
}

function pleio_api_get_my_subsites($search = null, $locked_filter = 0, $order_by = null, $offset = 0) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$wheres = array (sprintf ( " guid_one = %d and relationship = 'member_of_site' ", $user_id ) );
	$joins = array (sprintf ( " INNER JOIN %sentity_relationships ON guid_two = guid ", get_config ( "dbprefix" ) ) );
	return pleio_api_get_all_subsites ( $search, 0, $locked_filter, $order_by, $offset, $wheres, $joins );
}

function pleio_api_join_subsite($reason = "") {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$subsite_id = get_config ( "site_id" );
	/* @var $subsite Subsite */
	$subsite = elgg_get_site_entity ( $subsite_id );
	if ($user && $subsite && $subsite instanceof Subsite) {
		if ($subsite->isUser ( $user->guid )) {
			return new ErrorResult ( "{$user->name} was al aangemeld op {$subsite->name}" );
		} elseif ($subsite->canJoin ( $user->guid )) {
			if ($subsite->addUser ( $user->guid )) {
				return new SuccessResult ( elgg_echo ( "subsite_manager:action:subsites:add_user:success", array ($user->name, $subsite->name ) ) );
			} else {
				return new ErrorResult ( elgg_echo ( "subsite_manager:action:subsites:add_user:error:add", array ($user->name, $subsite->name ) ) );
			}
		} else {
			switch ($subsite->getMembership ()) {
				case Subsite::MEMBERSHIP_APPROVAL :
					if ($reason && $subsite->requestMembership ( $reason )) {
						return new SuccessResult ( elgg_echo ( "subsite_manager:actions:subsites:join:request_approval:success" ) );
					} else {
						return new ErrorResult ( "Goedkeuring vereist, geef een reden" );
					}
					break;
				case Subsite::MEMBERSHIP_DOMAIN_APPROVAL :
				case Subsite::MEMBERSHIP_DOMAIN :
					return new ErrorResult ( "Kan niet aanmelden op dit type subsite, raadpleeg de website" );
					break;
			}
		}
	}
	return new ErrorResult ( "Aanmelden mislukt" );
}

function pleio_api_get_all_groups($search = null, $offset = 0, $group_id = 0) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if ($user) {
		$total = 0;
		$offset = intval ( $offset );
		$wheres = array ();
		$joins = array ();
		if ($search) {
			$search = sanitise_string ( $search );
			$wheres [] = " (s.name LIKE '%$search%' OR s.description LIKE '%$search%') ";
			$joins [] = sprintf ( " INNER JOIN %sgroups_entity s USING (guid) ", get_config ( "dbprefix" ) );
		}
		$options = array ('type' => 'group', 'limit' => 20, 'offset' => $offset, 'count' => true, "wheres" => $wheres, "joins" => $joins );
		if ($group_id) {
			$options ["guids"] = $group_id;
		}
		$total = elgg_get_entities ( $options );
		$options ['count'] = false;
		$groups = elgg_get_entities ( $options );
		return pleio_api_format_groups ( $groups, $total, $offset, $user_id );
	}
	return array ("total" => 0, "list" => array (), "offset" => $offset );
}

function pleio_api_get_my_groups($search = null, $offset = 0) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if ($user) {
		$offset = intval ( $offset );
		$wheres = array ();
		$joins = array ();
		if ($search) {
			$search = sanitise_string ( $search );
			$wheres [] = " (s.name LIKE '%$search%' OR s.description LIKE '%$search%') ";
			$joins [] = sprintf ( " INNER JOIN %sgroups_entity s USING (guid) ", get_config ( "dbprefix" ) );
		}
		$options = array (
				'type' => 'group', 
				'relationship' => 'member', 
				'relationship_guid' => $user->guid, 
				'limit' => 20, 
				'offset' => $offset, 
				'count' => true, 
				"wheres" => $wheres, 
				"joins" => $joins );
		$total = elgg_get_entities_from_relationship ( $options );
		$options ['count'] = false;
		$groups = elgg_get_entities_from_relationship ( $options );
		return pleio_api_format_groups ( $groups, $total, $offset, $user_id );
	}
	return array ("total" => 0, "list" => array (), "offset" => $offset );
}

function pleio_api_join_group($group_id) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	// access bypass for getting invisible group
	$ia = elgg_set_ignore_access ( true );
	$group = get_entity ( $group_id );
	elgg_set_ignore_access ( $ia );
	if (($user instanceof ElggUser) && ($group instanceof ElggGroup)) {
		// join or request
		$join = false;
		if ($group->isPublicMembership () || $group->canEdit ( $user->guid )) {
			// anyone can join public groups and admins can join any group
			$join = true;
		} else {
			if (check_entity_relationship ( $group->guid, 'invited', $user->guid )) {
				// user has invite to closed group
				$join = true;
			}
		}
		if ($join) {
			if (groups_join_group ( $group, $user )) {
				return new SuccessResult ( elgg_echo ( "groups:joined" ) );
			} else {
				return new ErrorResult ( elgg_echo ( "groups:cantjoin" ) );
			}
		} else {
			add_entity_relationship ( $user->guid, 'membership_request', $group->guid );
			// Notify group owner
			$url = "{$CONFIG->url}groups/requests/$group->guid";
			$subject = elgg_echo ( 'groups:request:subject', array ($user->name, $group->name ) );
			$body = elgg_echo ( 'groups:request:body', array ($group->getOwnerEntity ()->name, $user->name, $group->name, $user->getURL (), $url ) );
			if (notify_user ( $group->owner_guid, $user->getGUID (), $subject, $body )) {
				return new SuccessResult ( elgg_echo ( "groups:joinrequestmade" ) );
			} else {
				return new ErrorResult ( elgg_echo ( "groups:joinrequestnotmade" ) );
			}
		}
	} else {
		return new ErrorResult ( elgg_echo ( "groups:cantjoin" ) );
	}
}

function pleio_api_get_group($group_id = 0, $offset = 0) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if ($user) {
		$group_id = intval ( $group_id );
		$options = array (
				'site_guid' => ELGG_ENTITIES_ANY_VALUE, 
				'type' => 'group', 
				'relationship' => 'member', 
				'relationship_guid' => $user->guid, 
				'count' => false, 
				"wheres" => array (" guid = $group_id " ) );
		$groups = elgg_get_entities_from_relationship ( $options );
		if (sizeof ( $groups )) {
			$group = pleio_api_format_group ( $groups [0], $user_id );
			$list = array ();
			$offset = intval ( $offset );
			foreach ( get_group_members ( $group_id, 20, $offset ) as $member ) {
				$list [] = pleio_api_format_user ( $member );
			}
			$group ["offset"] = $offset;
			$group ["members"] = $list;
			return $group;
		}
	}
	return new ErrorResult ( "Groep niet gevonden of geen lid" );
}

function pleio_api_get_group_icon($group_id = 0) {
	$user = elgg_get_logged_in_user_entity ();
	if ($user) {
		$size = "large";
		$group_id = intval ( $group_id );
		$group = get_entity ( $group_id );
		$filehandler = new ElggFile ( );
		$filehandler->owner_guid = $group->owner_guid;
		$filehandler->setFilename ( "groups/" . $group->guid . $size . ".jpg" );
		$success = false;
		if ($filehandler->open ( "read" )) {
			if ($contents = $filehandler->grabFile ()) {
				$success = true;
			}
		}
		if (! $success) {
			$location = elgg_get_plugins_path () . "groups/graphics/default{$size}.gif";
			$contents = @file_get_contents ( $location );
		}
		return base64_encode ( $contents );
	}
	return new ErrorResult ( "Groep niet gevonden of geen lid" );
}

function pleio_api_get_tweios($group_id = 0, $user_id = 0, $filter = 0, $search = null, $offset = 0, $wheres = array (), $joins = array()) {
	$user = elgg_get_logged_in_user_entity ();
	$list = array ();
	$total = 0;
	$offset = intval ( $offset );
	if ($user) {
		if ($search) {
			$search = sanitise_string ( $search );
			$wheres [] = " (o.description LIKE '%$search%') ";
			$joins [] = sprintf ( " INNER JOIN %sobjects_entity o USING (guid) ", get_config ( "dbprefix" ) );
		}
		$options = array ('type' => 'object', 'subtype' => 'thewire', 'limit' => 20, 'offset' => $offset, 'count' => true, "wheres" => $wheres, "joins" => $joins );
		if ($group_id) {
			$options ['container_guids'] = $group_id;
		}
		if ($user_id) {
			$options ['owner_guids'] = $user_id;
		}
		switch ($filter) {
			case 1 :
				$options ['owner_guids'] = $user->guid;
				break;
			case 2 :
				$friends = get_user_friends ( $user->guid, "", 999999, 0 );
				if (sizeof ( $friends )) {
					$options ['owner_guids'] = array_map ( create_function ( '$user', 'return $user->guid;' ), $friends );
				} else {
					return array ("total" => 0, "list" => array (), "offset" => $offset );
				}
				break;
		}
		$total = elgg_get_entities ( $options );
		if ($total) {
			$options ['count'] = false;
			$items = elgg_get_entities ( $options );
			foreach ( $items as $item ) {
				$e = pleio_api_export ( $item, explode ( ",", "guid,time_created,owner_guid,container_guid,site_guid,description" ) );
				$parent = get_data_row ( 
						sprintf ( "select guid_two as guid from %sentity_relationships where relationship = 'parent' and guid_one = %d", get_config ( "dbprefix" ), 
								$e ["guid"] ) );
				$e ["parent_guid"] = $parent ? intval ( $parent->guid ) : 0;
				$u = pleio_api_format_user ( get_user ( $item->owner_guid ) );
				$e ["name"] = $u ["name"];
				$e ["avatar"] = $u ["avatar"];
				$e ["likes_count"] = pleio_api_fetch_likes($item->guid);
				$e ["liked"] = 0;
				if ($e ["likes_count"]) {
					//$options = array ('guid' => $item->guid, 'annotation_name' => "likes", 'count' => 1, 'annotation_owner_guid' =>  $user->guid);
					//$anno = elgg_get_annotations ( $options );
					$e ["liked"] = pleio_api_fetch_likes ( $item->guid, 1, 0, 0, $user->guid ) > 0 ? 1 : 0;
				}				
				$list [] = $e;
			}
		}
	}
	return array ("total" => $total, "list" => $list, "offset" => $offset );
}

function pleio_api_get_access_list() {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$list = array ();
	$list [ACCESS_PRIVATE] = elgg_echo ( "PRIVATE" );
	$list [ACCESS_FRIENDS] = elgg_echo ( "access:friends:label" );
	$list [ACCESS_LOGGED_IN] = elgg_echo ( "LOGGED_IN" );
	$list [ACCESS_PUBLIC] = elgg_echo ( "PUBLIC" );
	foreach ( get_data ( 
			sprintf ( 
					"select a.id, s.name from %sentity_relationships r
		inner join %ssites_entity s on s.guid = r.guid_two
		inner join %sprivate_settings p on r.guid_two = p.entity_guid and p.name = 'subsite_acl' 
		inner join %saccess_collections a on a.id = p.value		
		where guid_one = $user_id and relationship = 'member_of_site' ", get_config ( "dbprefix" ), get_config ( "dbprefix" ), get_config ( "dbprefix" ), 
					get_config ( "dbprefix" ) ) ) as $subsite ) {
		$list [$subsite->id] = "Deelsite: " . $subsite->name;
	}
	foreach ( get_data ( 
			sprintf ( 
					"select a.id, g.name from %sentity_relationships r 
		inner join %saccess_collections a on a.owner_guid = r.guid_two
		inner join %sgroups_entity g on a.owner_guid = g.guid
		where guid_one = $user_id and relationship = 'member' ", get_config ( "dbprefix" ), get_config ( "dbprefix" ), get_config ( "dbprefix" ) ) ) as $group ) {
		$list [$group->id] = "Groep: " . $group->name;
	}
	return $list;
}

function pleio_api_send_tweio($message, $access_id = "", $reply = 0, $group_id = 0) {
	$access_ids = explode ( ",", $access_id );
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$method = "api";
	$parent_guid = $reply;
	$guid = false;
	$group_id = intval ( $group_id );
	foreach ( $access_ids as $access_id ) {
		$access = get_access_collection ( $access_id );
		if ($access || $access_id < 3) {
			$site_guid = false;
			if ($access && strpos ( $access->name, "subsite_acl_" ) === 0) {
				$site_guid = intval ( substr ( $access->name, 12 ) );
			}
			$guid = thewire_save_post ( $message, $user_id, $access_id, $parent_guid, $method );
			if ($site_guid) {
				$site_guid = $site_guid;
				;
				// site_guid wordt niet gezet door thewire_save_post
				update_data ( sprintf ( "update %sentities set site_guid = %d where guid = %d", get_config ( "dbprefix" ), $site_guid, $guid ) );
				update_data ( sprintf ( "update %sriver set site_guid = %d where object_guid = %d", get_config ( "dbprefix" ), $site_guid, $guid ) );
			}
			if ($group_id) {
				// container_guid wordt niet gezet door thewire_save_post
				update_data ( sprintf ( "update %sentities set container_guid = %d where guid = %d", get_config ( "dbprefix" ), $group_id, $guid ) );
			}
		}
	}
	if ($guid) {
		return new SuccessResult ( elgg_echo ( "thewire:posted" ) );
	} else {
		return new ErrorResult ( elgg_echo ( "thewire:error" ) );
	}
}

function pleio_api_get_folders($group_id = 0, $folder_id = 0, $offset = 0) {
	$total = 0;
	$list = array ();
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if ($user) {
		$swordfish_group = $group_id ? pleio_api_swordfish_group ( $group_id ) : false;
		if ($swordfish_group) {
			$folder_id = sanitise_string ( $folder_id );
			return pleio_api_get_swordfish_files ( $user, $group_id, $swordfish_group, $folder_id, true );
		}
		$options = array ("type" => "object", "subtype" => "folder", "limit" => 20 );
		// limit to folders in a group
		if (( int ) $group_id) {
			$options ["container_guid"] = $group_id;
		}
		// limit to folders with the given parent folder
		if (( int ) $folder_id) {
			$options ["metadata_name_values_pairs"] = array ("name" => "parent_guid", "value" => ( int ) $folder_id );
		}
		// change offset
		if (( int ) $offset) {
			$options ["offset"] = $offset;
		}
		// get count of folders
		$options ["count"] = true;
		$total = elgg_get_entities_from_metadata ( $options );
		if ($total) {
			// fetch all folders
			$options ["count"] = false;
			$entities = elgg_get_entities_from_metadata ( $options );
			foreach ( $entities as $entity ) {
				$export = pleio_api_export ( $entity, array ("guid", "owner_guid", "container_guid", "site_guid", "title" ) );
				$order = intval ( $entity->order );
				while ( array_key_exists ( $order, $list ) ) {
					$order ++;
				}
				$list [$order] = $export;
			}
			ksort ( $list );
			$list = array_values ( $list );
		}
	}
	return array ("total" => $total, "list" => $list, "offset" => $offset );
}

function pleio_api_get_swordfish_files($user, $group_id, $swordfish_group, $folder_id, $folder_only = false) {
	if ($folder_id) {
		$swordfish_group = $folder_id;
	}
	$group = get_entity ( $group_id );
	$url = pleio_api_swordfish_baseurl ( $group->site_guid ) . "get-folder-contents?id=" . $swordfish_group;
	$swordfish_name = pleio_api_swordfish_username ( $user->username );
	$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "GET" );
	if ($result->ok) {
		if (strpos ( $result->headers ["CONTENT-TYPE"], "json" )) {
			foreach ( json_decode ( $result->response ) as $f ) {
				$export = array (
						"guid" => $f->id, 
						"type" => $f->isFolder ? "folder" : "file", 
						"url" => $f->url, 
						"name" => $f->filename, 
						"title" => $f->name, 
						"container_guid" => $group->guid, 
						"site_guid" => $group->site_guid, 
						"mimetype" => $f->contentType, 
						"size" => $f->size, 
						"can_edit" => $f->canDelete ? 1 : 0 );
				$date = strtotime ( $f->date );
				if ($date) {
					$export ["time_created"] = $date;
				}
				if (! $export ["name"]) {
					$export ["name"] = $export ["title"];
				}
				if (! $folder_only || $export ["type"] == "folder")
					$list [] = $export;
			}
		} else {
			return new ErrorResult ( $result->headers ["BOBO-EXCEPTION-VALUE"] );
		}
	} else {
		return new ErrorResult ( $result->headers ["BOBO-EXCEPTION-VALUE"] );
	}
	return array ("total" => sizeof ( $list ), "list" => $list, "offset" => 0 );
}

function pleio_api_get_files($group_id = 0, $folder_id = 0, $user_id = 0, $file_id = 0, $offset = 0, $search = null, $filter = 0) {
	$group_id = intval ( $group_id );
	$user_id = intval ( $user_id );
	$file_id = intval ( $file_id );
	$offset = intval ( $offset );
	$total = 0;
	$list = array ();
	$user = elgg_get_logged_in_user_entity ();
	if ($filter == 1) {
		$user_id = $user->guid;
	}
	$swordfish_group = $group_id ? pleio_api_swordfish_group ( $group_id ) : false;
	if ($swordfish_group) {
		$folder_id = sanitise_string ( $folder_id );
		return pleio_api_get_swordfish_files ( $user, $group_id, $swordfish_group, $folder_id );
	} else {
		$folder_id = intval ( $folder_id );
		$wheres = array ();
		$joins = array ();
		$site = null;
		if ($group_id) {
			$wheres [] = sprintf ( "e.container_guid = %d", $group_id );
		}
		//		if ($folder_id) {
		//			$more = elgg_get_entity_relationship_where_sql ( 'e.guid', 'folder_of', $folder_id ? $folder_id : null );
		//			$wheres = array_merge ( $wheres, $more ["wheres"] );
		//			$joins = array_merge ( $joins, $more ["joins"] );
		//		}
		// if we don't look in a folder (so in the root folder), we don't want files that are in a folder to show up here too 
		$joins [] = sprintf ( 
				"LEFT JOIN %sentity_relationships r on r.guid_two = e.guid and relationship = 'folder_of'", get_config ( "dbprefix" ) );
		$wheres [] = 'guid_one ' . ($folder_id ? ' = ' . $folder_id : 'IS NULL');
		if ($file_id) {
			$wheres [] = sprintf ( "e.guid = %d", $file_id );
		} elseif ($user_id) {
			$wheres [] = sprintf ( "e.owner_guid = %d", $user_id );
		} elseif ($filter == 2) {
			// friends
			$friends = get_user_friends ( $user->guid, "", 999999, 0 );
			if (sizeof ( $friends )) {
				$wheres [] = " e.owner_guid IN (" . implode ( ",", array_map ( create_function ( '$user', 'return $user->guid;' ), $friends ) ) . ") ";
			} else {
				return array ("total" => 0, "list" => array (), "offset" => $offset );
			}
		}
		if (!$file_id && $search) {
			$search = sanitise_string ( $search );
			$wheres [] = " (o.description LIKE '%%$search%%' OR o.title LIKE '%%$search%%') ";
			$joins [] = sprintf ( "INNER JOIN %sobjects_entity o on e.guid = o.guid", get_config ( "dbprefix" ) );
		}
		$options = array ('type' => 'object', 'subtypes' => array ('file', 'folder' ), 'limit' => 20, 'offset' => $offset, 'count' => true, "wheres" => $wheres, "joins" => $joins );
		$total = elgg_get_entities ( $options );
		if ($total) {
			$options ["count"] = false;
			$options ["order_by"] = "e.time_created DESC";
			$data = elgg_get_entities ( $options );
			foreach ( $data as $item ) {
				if (! $site) {
					$site = get_entity ( $item->site_guid );
				}
				$export = pleio_api_export ( $item, array ("guid", "time_created", "owner_guid", "container_guid", "title", "description", "site_guid" ) );
				$export ["type"] = $item->getSubType ();
				if ($export ["type"] == "file" && $item instanceof ElggFile) {
					$export = pleio_api_get_metadata ( $item->guid, $export );
					unset ( $export ["originalfilename"] );
					//unset ( $export ["simpletype"] );
					unset ( $export ["filestore::dir_root"] );
					unset ( $export ["filestore::filestore"] );
					$export ["name"] = basename ( $export ["filename"] );
					unset ( $export ["filename"] );
					$export ["size"] = is_readable ( $item->getFilenameOnFilestore () ) ? filesize ( $item->getFilenameOnFilestore () ) : 0;
					unset ( $export ["folder_guid"] );
					unset ( $export ["smallthumb"] );
					unset ( $export ["largethumb"] );
					unset ( $export ["thumbnail"] );
					if ($site) {
						$export ["url"] = $site->url . "file/download/" . $export ["guid"];
					}
					$export ["can_edit"] = $item->canEdit ( $user->guid ) ? 1 : 0;
				}
				$export ["likes_count"] = pleio_api_fetch_likes($item->guid);
				$export ["liked"] = 0;
				if ($export ["likes_count"]) {
					$export ["liked"] = pleio_api_fetch_likes ( $item->guid, 1, 0, 0, $user->guid ) > 0 ? 1 : 0;
				}			
				$list [] = $export;
			}
		}
	}
	return array ("total" => $total, "list" => $list, "offset" => $offset );
}

function pleio_api_save_file($data = "", $file_name = "", $title = "", $description = "", $tags = "", $file_id = null, $folder_id = 0, $group_id = 0, $access_id = "", $wiki_id = "", $mimetype = "") {
	$file_id = $file_id ? $file_id : null;
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if (! $data && ! $file_id) {
		return new ErrorResult ( elgg_echo ( "file:uploadfailed" ) );
	}
	$swordfish_group = $group_id ? pleio_api_swordfish_group ( $group_id ) : false;
	if ($swordfish_group) {
		$group = get_entity ( $group_id );
		$url = pleio_api_swordfish_baseurl ( $group->site_guid ) . "post-file";
		$swordfish_name = pleio_api_swordfish_username ( $user->username );
		$params = array ("data" => $data, "title" => $title );
		if ($file_id) {
			$params ["fileId"] = $file_id;
		} elseif ($folder_id) {
			$params ["folderId"] = $folder_id;
		} elseif ($group_id) {
			$params ["groupId"] = $swordfish_group;
		} else {
			return new ErrorResult ( "Vul minimaal een bestand, folder of groep in" );
		}
		if ($wiki_id) {
			$params ["wikiId"] = $wiki_id;
		}
		if ($access_id != ACCESS_PRIVATE) {
			$params ["visibility"] = "internally_published";
		} else {
			$params ["visibility"] = "private";
		}
		$params ["filename"] = $file_name;
		$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "POST", $params );
		if ($result->ok) {
			if (strpos ( $result->headers ["CONTENT-TYPE"], "json" )) {
				$response = json_decode ( $result->response );
				return new SaveSuccessResult ( elgg_echo ( "file:saved" ), $response->id );
			} else {
				return new ErrorResult ( $result->headers ["BOBO-EXCEPTION-VALUE"] );
			}
		} else {
			return new ErrorResult ( $result->headers ["BOBO-EXCEPTION-VALUE"] );
		}
	} else {
		if ($file_id) {
			$file = get_entity ( $file_id );
		}
		if (!$file) {
			$file = new ElggFile ( );
			$file->owner_guid = $user_id;
		}
		if ($title) {
			$file->title = $title;
		}
		if ($description) {
			$file->setDescription ( $description );
		}
		if ($tags) {
			$file->setMetaData ( "tags", $tags );
		}
		if ($group_id) {
			$file->setContainerGUID ( $group_id );
		}
		if ($access_id) {
			$file->access_id = $access_id;
		}
		if ($data) {
			$file->setFilename ( basename ( $file_name ) );
			$data = base64_decode ( $data );
			$fh = $file->open ( "write" );
			if ($fh) {
				$file->write ( $data );
				$file->close ();
			}
			if (! $mimetype) {
				$mimetype = $file->detectMimeType ( $file->getFilenameOnFilestore () );
			}
			$file->setMimeType ( $mimetype );
			$file->simpletype = file_get_simple_type ( $mimetype );
		}
		if (! $file->save ()) {
			return new ErrorResult ( elgg_echo ( "file:uploadfailed" ) );
		}
		if ($folder_id) {
			remove_entity_relationships ( $file->guid, "folder_of", 1 );
			add_entity_relationship ( $folder_id, "folder_of", $file->guid );
		}
		if (! $file_id)
			add_to_river ( 'river/object/file/create', 'create', $user_id, $file->guid );
		return new SaveSuccessResult ( elgg_echo ( "file:saved" ), $file->guid );
	}
	return new ErrorResult ( elgg_echo ( "file:uploadfailed" ) );
}

function pleio_api_get_file($file_id) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if ($file_id && $user_id) {
		if (strlen ( $file_id ) != 32) {
			$file = get_entity ( $file_id );
			if ($file) {
				/* @var $file ElggFile */
				return base64_encode ( $file->grabFile () );
			}
		} else {
			$url = pleio_api_swordfish_baseurl () . "get-file?id=" . $file_id;
			$swordfish_name = pleio_api_swordfish_username ( $user->username );
			$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "GET" );
			if ($result->ok) {
				$response = json_decode ( $result->response );
				return $response->content;
			}
		}
	}
	return new ErrorResult ( "File not found" );
}

function pleio_api_delete_file($file_id) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if ($file_id && $user_id) {
		if (strlen ( $file_id ) != 32) {
			$file = new ElggFile ( $file_id );
			if ($file && $file->guid && $file->canEdit ( $user_id ) && $file->delete ()) {
				return new SuccessResult ( elgg_echo ( "file:deleted" ) );
			}
		} else {
			$url = pleio_api_swordfish_baseurl () . "delete-file?id=" . $file_id;
			$swordfish_name = pleio_api_swordfish_username ( $user->username );
			$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "POST" );
			if ($result->ok) {
				return new SuccessResult ( elgg_echo ( "file:deleted" ) );
			}
		}
	}
	return new ErrorResult ( elgg_echo ( "file:deletefailed" ) );
}

function pleio_api_like_entity($guid) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$guid = ( int ) $guid;
	//check to see if the user has already liked the item
	if (elgg_annotation_exists ( $guid, 'likes' )) {
		return new ErrorResult ( elgg_echo ( "likes:alreadyliked" ) );
	}
	// Let's see if we can get an entity with the specified GUID
	$entity = get_entity ( $guid );
	if (! $entity) {
		return new ErrorResult ( elgg_echo ( "likes:notfound" ) );
	}
	// limit likes through a plugin hook (to prevent liking your own content for example)
	if (! $entity->canAnnotate ( $user_id, 'likes' )) {
		return new ErrorResult ( elgg_echo ( "likes:failure" ) );
	}
	$annotation = create_annotation ( $entity->guid, 'likes', "likes", "", $user_id, $entity->access_id );
	// tell user annotation didn't work if that is the case
	if (! $annotation) {
		return new ErrorResult ( elgg_echo ( "likes:failure" ) );
	}
	// notify if poster wasn't owner
	if ($entity->owner_guid != $user->guid) {
		likes_notify_user ( $entity->getOwnerEntity (), $user, $entity );
	}
	return new SuccessResult ( elgg_echo ( "likes:likes" ) );
}

function pleio_api_unlike_entity($guid) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$guid = ( int ) $guid;
	
	$entity = get_entity ( $guid );	
	if (! $entity) {
		return new ErrorResult ( elgg_echo ( "likes:notdeleted" ) );
	}
	// limit likes through a plugin hook (to prevent liking your own content for example)
	if (! $entity->canAnnotate ( $user_id, 'likes' )) {
		return new ErrorResult ( elgg_echo ( "likes:notdeleted" ) );
	}
	
	$options = array ('guid' => $guid, 'annotation_name' => "likes", 'annotation_owner_guid' => $user_id );
	$annotations = elgg_get_annotations ( $options );
	
	// see if the user has liked the item
	if (!sizeof($annotations)) {
		return new ErrorResult ( "sizeof ". elgg_echo ( "likes:notdeleted" ) );
	}
	
	//delete like(s)
	foreach ($annotations as $annotation) {
		elgg_delete_annotation_by_id($annotation->id);
	}

	return new SuccessResult ( elgg_echo ( "likes:deleted" ) );
}

function pleio_api_get_wikis($group_id = 0, $parent_id = 0, $user_id = 0, $offset = 0, $search = null, $filter = 0) {
	return pleio_api_get_sub_wikis ( $group_id, $parent_id, $user_id, $offset, $search, $filter );
}

function pleio_api_get_swordfish_wikis($user, $group_id, $swordfish_group, $parent_id) {
	$group = get_entity ( $group_id );
	$swordfish_name = pleio_api_swordfish_username ( $user->username );
	if ($parent_id) {
		$url = pleio_api_swordfish_baseurl ( $group->site_guid ) . "get-page?id=" . $swordfish_group;
		$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "GET" );
		$wiki = pleio_api_swordfish_parse_get_page ( $result );
		if ($wiki) {
			foreach ( $wiki ["sub_wikis"] as $id ) {
				$url = pleio_api_swordfish_baseurl ( $group->site_guid ) . "get-page?id=" . $id;
				$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "GET" );
				$subwiki = pleio_api_swordfish_parse_get_page ( $result );
				$title = $subwiki ["title"];
				$list [] = array ("guid" => $id, "title" => $title, "container_guid" => $group->guid, "site_guid" => $group->site_guid, "parent_guid" => $parent_id );
			}
		}
	} else {
		$url = pleio_api_swordfish_baseurl ( $group->site_guid ) . "get-pages?gid=" . $swordfish_group;
		$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "GET" );
		if ($result->ok) {
			if (strpos ( $result->headers ["CONTENT-TYPE"], "json" )) {
				foreach ( json_decode ( $result->response ) as $f ) {
					$list [] = array ("guid" => $f->id, "url" => $f->url, "title" => $f->name, "container_guid" => $group->guid, "site_guid" => $group->site_guid );
				}
			} else {
				return new ErrorResult ( $result->headers ["BOBO-EXCEPTION-VALUE"] );
			}
		} else {
			return new ErrorResult ( $result->headers ["BOBO-EXCEPTION-VALUE"] );
		}
	}
	return array ("total" => sizeof ( $list ), "list" => $list, "offset" => 0 );
}

function pleio_api_get_sub_wikis($group_id = 0, $parent_id = 0, $user_id = 0, $offset = 0, $search = null, $filter = 0) {
	$total = 0;
	$offset = intval ( $offset );
	$list = array ();
	$user = elgg_get_logged_in_user_entity ();
	if ($filter == 1) {
		$user_id = $user->guid;
	}
	if (strlen ( $parent_id ) == 32) {
		$swordfish_group = $parent_id;
	} else {
		$swordfish_group = $group_id ? pleio_api_swordfish_group ( $group_id ) : false;
	}
	$url = false;
	if ($swordfish_group) {
		return pleio_api_get_swordfish_wikis ( $user, $group_id, $swordfish_group, $parent_id );
	} else {
		$wheres = array ();
		$joins = array ();
		if ($group_id) {
			$wheres [] = sprintf ( "e.container_guid = %d", $group_id );
		}
		if ($parent_id) {
			$more = elgg_get_entity_metadata_where_sql ( 'e', 'metadata', null, null, array ("name" => "parent_guid", "value" => ( int ) $parent_id ) );
			$wheres = array_merge ( $wheres, $more ["wheres"] );
			$joins = array_merge ( $joins, $more ["joins"] );
		}
		if ($user_id) {
			$wheres [] = sprintf ( "e.owner_guid = %d", $user_id );
		} elseif ($filter == 2) {
			// friends
			$friends = get_user_friends ( $user->guid, "", 999999, 0 );
			if (sizeof ( $friends )) {
				$wheres [] = " e.owner_guid IN (" . implode ( ",", array_map ( create_function ( '$user', 'return $user->guid;' ), $friends ) ) . ") ";
			} else {
				return array ("total" => 0, "list" => array (), "offset" => $offset );
			}
		}
		if ($search) {
			$search = sanitise_string ( $search );
			$wheres [] = " (o.description LIKE '%%$search%%' OR o.title LIKE '%%$search%%') ";
			$joins [] = sprintf ( "INNER JOIN %sobjects_entity o on e.guid = o.guid", get_config ( "dbprefix" ) );
		}
		$options = array (
				'type' => 'object', 
				'subtypes' => $parent_id ? array ('page_top', 'page' ) : 'page_top', 
				'limit' => 20, 
				'offset' => $offset, 
				'count' => true, 
				"wheres" => $wheres, 
				"joins" => $joins );
		$total = elgg_get_entities ( $options );
		if ($total) {
			$options ["count"] = false;
			$options ["order_by"] = "e.time_created DESC";
			$data = elgg_get_entities ( $options );
			foreach ( $data as $item ) {
				$export = pleio_api_export ( $item, array ("guid", "time_created", "owner_guid", "container_guid", "title", "site_guid" ) );
				$export ["likes_count"] = pleio_api_fetch_likes ( $item->guid );
				$export ["liked"] = 0;
				if ($export ["likes_count"]) {
					$export ["liked"] = pleio_api_fetch_likes ( $item->guid, 1, 0, 0, $user->guid ) > 0 ? 1 : 0;
				}								
				$list [] = $export;
			}
		}
	}
	return array ("total" => $total, "list" => $list, "offset" => $offset );
}

function pleio_api_get_wiki($wiki_id, $offset = 0) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if (strlen ( $wiki_id ) != 32) {
		$wiki = get_entity ( $wiki_id );
		if ($wiki) {
			$access_list = pleio_api_get_access_list ();
			$export = array ();
			$export = pleio_api_export ( $wiki );
			$export ["site_guid"] = $wiki->site_guid;
			$export ["parent_guid"] = $wiki->parent_guid;
			$export ["can_edit"] = $wiki->canEdit ( $user_id ) ? 1 : 0;
			$export ["likes_count"] = pleio_api_fetch_likes ( $wiki_id );
			$export ["liked"] = 0;
			$export ["likes"] = array ();
			if ($export ["likes_count"]) {
				$export ["liked"] = pleio_api_fetch_likes ( $item->guid, 1, 0, 0, $user->guid ) > 0 ? 1 : 0;
				foreach ( pleio_api_fetch_likes ( $wiki_id, 0, $offset ) as $a ) {
					$export ["likes"] [] = $a ["owner_guid"];
				}
			}			
			$export ["comments_count"] = pleio_api_fetch_comments ( $wiki_id, $user_id, $offset, 1 );
			$subwikis = pleio_api_get_sub_wikis ( $wiki->container_guid, $wiki_id, 0, $offset );
			$export ["sub_wikis_count"] = $subwikis ["total"];
			$export ["description"] = preg_replace ( "/^\s*\/\/\s*/ism", "", $export ["description"] );
			$author = pleio_api_format_user ( get_entity ( $export ["owner_guid"] ) );
			$export ["name"] = $author ["name"];
			$export ["avatar"] = $author ["avatar"];
			$export ["access_id"] = intval ( $wiki->access_id );
			$export ["write_access_id"] = intval ( $wiki->write_access_id );
			$export ["access_name"] = $access_list [$wiki->access_id];
			$export ["write_access_name"] = $access_list [$wiki->write_access_id];
			$export ["allow_comments"] = "yes";
			return pleio_api_extract_wiki_files ( $export, $wiki );
		}
	}
	$url = pleio_api_swordfish_baseurl () . "get-page?id=" . $wiki_id;
	$swordfish_name = pleio_api_swordfish_username ( $user->username );
	$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "GET" );
	$export = pleio_api_swordfish_parse_get_page ( $result, $swordfish_name );
	if ($export) {
		return $export;
	}
	return new ErrorResult ( elgg_echo ( "pages:error:notfound" ) );
}

function pleio_api_extract_wiki_files($export, $wiki) {
	$files = array ();
	if (preg_match_all ( "/<a href.*?\/file\/view\/(\d+)\/.*?\/a>/ism", $export ["description"], $m, PREG_SET_ORDER )) {
		foreach ( $m as $f ) {
			$export ["description"] = str_replace ( $f [0], "", $export ["description"] );
			$file_id = $f [1];
			if (! array_key_exists ( $file_id, $files )) {
				$file = get_entity ( $file_id );
				$meta = pleio_api_get_metadata ( $file_id );
				$file = pleio_api_export ( $file, array ("title" ) );
				$file ["name"] = basename ( $meta ["filename"] );
				$file ["guid"] = $file_id;
				$files [$file_id] = $file;
			}
		}
	}
	$export ["sub_files"] = array_values ( $files );
	return $export;
}

function pleio_api_swordfish_parse_get_page($result, $swordfish_name) {
	if ($result->ok && strpos ( $result->headers ["CONTENT-TYPE"], "json" )) {
		$response = json_decode ( $result->response );
		$export = array ();
		$export ["guid"] = $response->id;
		$export ["title"] = $response->name;
		$export ["url"] = $response->url;
		$export ["sub_wikis_count"] = sizeof ( $response->subWikiIds );
		$export ["sub_wikis"] = $response->subWikiIds;
		$export ["sub_files"] = array ();
		if ($swordfish_name) {
			foreach ( $response->fileIds as $file_id ) {
				$url = pleio_api_swordfish_baseurl () . "get-file?id=" . $file_id;
				$file = pleio_api_call_swordfish_api ( $swordfish_name, $url, "GET" );
				$name = "";
				$title = "";
				if ($file->ok && strpos ( $file->headers ["CONTENT-TYPE"], "json" )) {
					$file = json_decode ( $file->response );
					$name = $file->filename;
					$title = $file->name;
					$export ["sub_files"] [] = array ("guid" => $file_id, "name" => $name, "title" => $title );
				}
			}
		}
		$export ["name"] = "";
		$export ["access_id"] = $response->visibility == "private" ? 0 : 2;
		$export ["access_name"] = $response->visibility == "private" ? "Privé" : "Publiek";
		$export ["write_access_name"] = "";
		$export ["allow_comments"] = $response->commentAllowed ? "yes" : "no";
		$export ["can_edit"] = 1;
		// $export ["site_guid"] = $subsite_id;
		return pleio_api_swordfish_extract_comments ( $response->content, $export );
	}
}

function pleio_api_swordfish_extract_comments($content, $export) {
	$content = preg_replace ( '/<div class="discreet">Commentaar is uitgeschakeld<\/div>\s*<\/div>\s*<\/div>/ism', "", $content );
	if (preg_match ( "/<div id=\"content\">(.*?)<\/div>\s*<div id=\"comments\">/ism", $content, $m )) {
		$export ["description"] = $m [1];
		$content = str_replace ( $m [1], "", $content );
	}
	$comments = preg_split ( '/<div class="comment .*?"/ism', $content );
	array_shift ( $comments );
	foreach ( $comments as $c ) {
		$comment = array ();
		$c = trim ( preg_replace ( '/<div class="commentActions">(.*?)<\/div>/ism', "", $c ) );
		$c = trim ( preg_replace ( '/\s*<\/div>\s*/ism', "", $c ) );
		$parts = preg_split ( '/<div.*?>/ism', $c );
		$first = array_shift ( $parts );
		if (preg_match ( "/id=\"(\d+)\"/ism", $content, $m )) {
			$comment ["guid"] = $m [1];
		}
		array_shift ( $parts );
		$comment ["name"] = trim ( str_replace ( " zegt:", "", strip_tags ( array_shift ( $parts ) ) ) );
		$comment ["time_created"] = strtotime ( trim ( strip_tags ( array_shift ( $parts ) ) ) );
		$comment ["description"] = trim ( array_shift ( $parts ) );
		$comment ["can_edit"] = 0;
		if ($comment ["guid"]) {
			$export ["comments"] [] = $comment;
		}
	}
	$export ["comments_count"] = sizeof ( $export ["comments"] );
	return $export;
}

function pleio_api_save_wiki($content = "", $title = "", $wiki_id = null, $parent_id = null, $group_id = 0, $access_id = 0, $write_access_id = 0) {
	$wiki_id = $wiki_id ? $wiki_id : null;
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if (! $content && ! $wiki_id || ! $user_id) {
		return new ErrorResult ( elgg_echo ( "pages:error:no_save" ) );
	}
	$parent = false;
	$swordfish_group = $group_id ? pleio_api_swordfish_group ( $group_id ) : false;
	if ($swordfish_group) {
		$group = get_entity ( $group_id );
		$url = pleio_api_swordfish_baseurl ( $group->site_guid ) . "post-page";
		$swordfish_name = pleio_api_swordfish_username ( $user->username );
		$params = array ("title" => $title, "content" => $content );
		if (! $wiki_id) {
			$params ["commentAllowed"] = 1;
		}
		if ($wiki_id) {
			$params ["wikiId"] = $wiki_id;
		} elseif ($parent_id) {
			$params ["parentId"] = $parent_id;
		} elseif ($group_id) {
			$params ["groupId"] = $swordfish_group;
		} else {
			return new ErrorResult ( "Selecteer minimaal een groep of bovenliggende wiki" );
		}
		if ($access_id != ACCESS_PRIVATE) {
			$params ["visibility"] = "internally_published";
		} else {
			$params ["visibility"] = "private";
		}
		$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "POST", $params );
		if ($result->ok) {
			if (strpos ( $result->headers ["CONTENT-TYPE"], "json" )) {
				$response = json_decode ( $result->response );
				return new SaveSuccessResult ( elgg_echo ( "pages:saved" ), $response->id );
			} else {
				return new ErrorResult ( $result->headers ["BOBO-EXCEPTION-VALUE"] );
			}
		} else {
			return new ErrorResult ( $result->headers ["BOBO-EXCEPTION-VALUE"] );
		}
	} else {
		if ($wiki_id) {
			$wiki = get_entity ( $wiki_id );
		} else {
			$wiki = new ElggObject ( );
			$wiki->owner_guid = $user_id;
			$wiki->subtype = "page_top";
			$wiki->parent_guid = 0;
		}
		if ($group_id) {
			$wiki->container_guid = $group_id;
		}
		if ($access_id !== "") {
			$wiki->access_id = $access_id;
		}
		if ($write_access_id !== "") {
			$wiki->write_access_id = $write_access_id;
		}
		if (isset ( $parent_id )) {
			$parent = get_entity ( $parent_id );
			if ($parent) {
				$wiki->subtype = "page";
				$wiki->parent_guid = $parent_id;
				$wiki->access_id = $parent->access_id;
				$wiki->container_guid = $parent->container_guid;
				$wiki->site_guid = $parent->site_guid;
			}
		}
		if ($title) {
			$wiki->title = $title;
		}
		if ($content) {
			$wiki->description = $content;
		}
		if (! $wiki->save ()) {
			return new ErrorResult ( elgg_echo ( "pages:error:no_save" ) );
		}
		$wiki->annotate ( 'page', $wiki->description, $wiki->access_id );
		if (! $wiki_id)
			add_to_river ( 'river/object/page/create', 'create', $user_id, $wiki->guid );
		return new SaveSuccessResult ( elgg_echo ( "pages:saved" ), $wiki->guid );
	}
}

function pleio_api_delete_wiki($wiki_id) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if ($wiki_id && $user_id) {
		if (strlen ( $wiki_id ) != 32) {
			$page = get_entity ( $wiki_id );
			if ($page) {
				if ($page->canEdit ( $user_id )) {
					$container = get_entity ( $page->container_guid );
					// Bring all child elements forward
					$parent = $page->parent_guid;
					$children = elgg_get_entities_from_metadata ( array ('metadata_name' => 'parent_guid', 'metadata_value' => $page->getGUID () ) );
					if ($children) {
						foreach ( $children as $child ) {
							$child->parent_guid = $parent;
						}
					}
					if ($page->delete ()) {
						return new SuccessResult ( elgg_echo ( 'pages:delete:success' ) );
					}
				}
			}
		} else {
			$url = pleio_api_swordfish_baseurl () . "delete-page?id=" . $wiki_id;
			$swordfish_name = pleio_api_swordfish_username ( $user->username );
			$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "POST" );
			if ($result->ok) {
				return new SuccessResult ( elgg_echo ( 'pages:delete:success' ) );
			}
		}
	}
	return new ErrorResult ( elgg_echo ( 'pages:delete:failure' ) );
}

function pleio_api_add_comment($guid, $comment) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	if (! $user_id) {
		return new ErrorResult ( elgg_echo ( "generic_comment:failure" ) );
	}
	if (empty ( $comment )) {
		return new ErrorResult ( elgg_echo ( "generic_comment:blank" ) );
	}
	$entity = get_entity ( ( int ) $guid );
	if ($entity) {
		$annotation = create_annotation ( $entity->guid, 'generic_comment', $comment, "", $user_id, $entity->access_id );
		if (! $annotation) {
			return new ErrorResult ( elgg_echo ( "generic_comment:failure" ) );
		}
		if ($entity->owner_guid != $user_id) {
			notify_user ( $entity->owner_guid, $user->guid, elgg_echo ( 'generic_comment:email:subject' ), 
					elgg_echo ( 'generic_comment:email:body', array ($entity->title, $user->name, $comment, $entity->getURL (), $user->name, $user->getURL () ) ) );
		}
		add_to_river ( 'river/annotation/generic_comment/create', 'comment', $user->guid, $entity->guid, "", 0, $annotation );
		return new SuccessResult ( elgg_echo ( "generic_comment:posted" ) );
	} else {
		$swordfish_name = pleio_api_swordfish_username ( $user->username );
		$url = pleio_api_swordfish_baseurl () . "get-page?id=" . $guid;
		$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "GET" );
		$wiki = pleio_api_swordfish_parse_get_page ( $result );
		if ($wiki) {
			$params = array ("comment" => $comment, "wikiId" => $guid );
			$url = pleio_api_swordfish_baseurl () . "post-comment";
			$result = pleio_api_call_swordfish_api ( $swordfish_name, $url, "POST", $params );
			if ($result->ok) {
				return new SuccessResult ( elgg_echo ( "generic_comment:posted" ) );
			} else {
				return new ErrorResult ( elgg_echo ( "generic_comment:failure" ) );
			}
		}
		return $result;
		return new ErrorResult ( elgg_echo ( "swordfish_comment:notfound:swordfish" ) );
	}
	return new ErrorResult ( elgg_echo ( "generic_comment:notfound" ) );
}

function pleio_api_get_activity($group_id = 0, $offset = 0) {
	$offset = intval ( $offset );
	$group_id = intval ( $group_id );
	$joins = array ();
	$wheres = array ();
	if ($group_id) {
		$joins [] = sprintf ( " JOIN %sentities e ON e.guid = rv.object_guid ", get_config ( "dbprefix" ) );
	}
	if ($group_id) {
		$wheres [] = " (e.container_guid = $group_id OR e.guid = $group_id) ";
	}
	$total = elgg_get_river ( array ("count" => true, "joins" => $joins, "wheres" => $wheres ) );
	$rivers = elgg_get_river ( array ("offset" => $offset, "joins" => $joins, "wheres" => $wheres ) );
	$list = array ();
	foreach ( $rivers as $item ) {
		$river = pleio_api_format_activity ( $item );
		if ($river) {
			$list [] = $river;
		}
	}
	return array ("total" => $total, "list" => $list, "offset" => $offset );
}

function pleio_api_get_user($user_id) {
	$user_id = intval ( $user_id );
	$user = get_user ( $user_id );
	return $user ? pleio_api_format_user ( $user ) : null;
}

function pleio_api_get_messages($sent = 0, $search = "", $offset = 0) {
	$total = 0;
	$list = array ();
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$searchSql = "";
	$searchJoin = "";
	$subtype_id = get_subtype_id ( 'object', 'messages' );
	if (!$user) {
		return new ErrorResult ( $fail );
	}
	try {
		if ($search) {
			$search = sanitise_string ( $search );
			$searchSql = " AND (description LIKE '%%$search%%' OR title LIKE '%%$search%%' ";
			$sql = "select guid from " . get_config ( "dbprefix" ) . "users_entity where name like '%$search%' ";
			$users = get_data ( $sql );
			if (sizeof ( $users )) {
				$users = array_map(create_function ( '$user', 'return $user->guid;' ), $users);
				$users = implode ( ",", $users );
				$searchSql .= " OR (msn2.string = '" . ($sent ? "toId" : "fromId") . "' AND msv2.string in ($users)) ";
				$searchJoin = " INNER JOIN " . get_config ( "dbprefix" ) . "metadata n_table2 on e.guid = n_table2.entity_guid  
										INNER JOIN " . get_config ( "dbprefix" ) . "metastrings msn2 on n_table2.name_id = msn2.id  
										INNER JOIN " . get_config ( "dbprefix" ) . "metastrings msv2 on n_table2.value_id = msv2.id ";
			}
			$searchSql .= ")";
		}
		$offset = intval ( $offset );
		$sent_sql = $sent ? "fromId" : "toId";
		$sql = "SELECT %s 
			FROM " . get_config ( "dbprefix" ) . "entities e
			INNER JOIN " . get_config ( "dbprefix" ) . "objects_entity o on e.guid = o.guid   
			INNER JOIN " . get_config ( "dbprefix" ) . "metadata n_table on e.guid = n_table.entity_guid  
			INNER JOIN " . get_config ( "dbprefix" ) . "metastrings msn on n_table.name_id = msn.id  
			INNER JOIN " . get_config ( "dbprefix" ) . "metastrings msv on n_table.value_id = msv.id
			$searchJoin	
			WHERE e.owner_guid = %d
			$searchSql 
			AND e.type = 'object' AND e.subtype = $subtype_id
			AND msn.string ='$sent_sql' AND msv.string = %d 
			AND e.enabled = 'yes' ";
		$total = get_data_row ( sprintf ( $sql, "COUNT(e.guid) AS cnt", $user_id, $user_id ) );
		$total = $total->cnt;
		$data = get_data ( sprintf ( $sql, "DISTINCT e.*", $user_id, $user_id ) . sprintf ( "ORDER BY e.time_created DESC LIMIT %d, 20", $offset ) );
		foreach ( $data as $row ) {
			$message = entity_row_to_elggstar ( $row );
			$export = pleio_api_export ( $message, array ("time_created", "guid", "owner_guid", "container_guid", "site_guid", "title", "description" ) );
			$export = pleio_api_get_metadata ( $message->guid, $export );
			unset ( $export ["msg"] );
			// filter links
			$export ["description"] = strip_tags ( trim ( preg_replace ( "/\w+:\/\/[\w\d\.\-_\?=&;:#\/]+/ism", "", $export ["description"] ), ": " ) );
			$u = false;
			if ($export ["fromId"] == $user_id) {
				$u = get_entity ( $export ["toId"] );
			} elseif ($export ["toId"] == $user_id) {
				$u = get_entity ( $export ["fromId"] );
			}
			$export ["name"] = $u->name;
			if ($u instanceof ElggUser) {
				$u = pleio_api_format_user ( $u );
				$export ["avatar"] = $u ["avatar"];
			}
			$sender_and_reciever = false;
			if ($export ["fromId"] == $export ["toId"] && $export ["fromId"] == $user_id) {
				foreach ( $list as $e ) {
					if ($e ["guid"] == $export ["guid"] + 1 || $e ["guid"] == $export ["guid"] - 1) {
						$sender_and_reciever = true;
						$total --;
					}
				}
			}
			if (! $sender_and_reciever) {
				$list [] = $export;
			}
		}
	} catch (Exception $ex) {
		return new ErrorResult ( elgg_echo ( "error:default" ) );
	}
	return array ("total" => $total, "list" => $list, "offset" => $offset );
}

function pleio_api_send_message($contact_id = 0, $message_title, $message_content, $reply = 0) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$subject = strip_tags ( $message_title );
	if (! $contact_id) {
		return new ErrorResult ( elgg_echo ( "messages:user:blank" ) );
	}
	if ($contact_id == $user_id) {
		return new ErrorResult ( elgg_echo ( "messages:error" ) );
	}
	$user = get_user ( $contact_id );
	if (! $user) {
		return new ErrorResult ( elgg_echo ( "messages:user:nonexist" ) );
	}
	if (! $message_content || ! $subject) {
		return new ErrorResult ( elgg_echo ( "messages:blank" ) );
	}
	$result = messages_send ( $subject, $message_content, $contact_id, $user_id, $reply, null, 1 );
	if (! $result) {
		return new ErrorResult ( elgg_echo ( "messages:error" ) );
	}
	return new SuccessResult ( elgg_echo ( "messages:posted" ) );
}

function pleio_api_delete_message($message_id) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$message_id = intval ( $message_id );
	$message = get_entity ( $message_id );
	if ($message && $message->canEdit ( $user_id ) && $message->delete ()) {
		return new SuccessResult ( elgg_echo ( 'messages:success:delete:single' ) );
	}
	return new ErrorResult ( elgg_echo ( 'messages:error:delete:single' ) );
}

function pleio_api_mark_message($message_id = 0, $read = 0) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$message_id = intval ( $message_id );
	$read = intval ( $read ) == 1;
	$message = get_entity ( $message_id );
	if ($message && $message->canEdit ( $user_id )) {
		$message->readYet = $read;
		if ($message->save ()) {
			return new SuccessResult ( elgg_echo ( 'messages:success:mark', $message->readYet ) );
		}
	}
	return new ErrorResult ( elgg_echo ( 'messages:error:mark' ) );
}

function pleio_api_delete_comment($comment_id) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$comment_id = intval ( $comment_id );
	$comment = elgg_get_annotation_from_id ( $comment_id );
	if ($comment && $comment->canEdit ( $user_id ) && $comment->delete ()) {
		return new SuccessResult ( elgg_echo ( 'generic_comment:deleted' ) );
	}
	return new ErrorResult ( elgg_echo ( 'generic_comment:notdeleted' ) );
}

function pleio_api_swordfish_site_connect($subsite_id = 0, $swordfish_api_url = "") {
	$subsite_id = intval ( $subsite_id );
	$swordfish_api_url = sanitise_string ( $swordfish_api_url );
	$user = elgg_get_logged_in_user_entity ();
	$subsite = get_entity ( $subsite_id );
	$parsed = parse_url ( $swordfish_api_url );
	if (! $subsite)
		return new ErrorResult ( elgg_echo ( 'pleio_api:swordfish_api_url:subsite_not_found' ) );
	if (! $parsed ["scheme"] || ! $parsed ["host"] || ! $parsed ["path"])
		return new ErrorResult ( elgg_echo ( 'pleio_api:swordfish_api_url:invalid_url' ) );
	if (set_private_setting ( $subsite_id, "swordfish_api_url", $swordfish_api_url ))
		return new SuccessResult ( elgg_echo ( 'pleio_api:swordfish_api_url:success' ) );
	return new ErrorResult ( elgg_echo ( 'pleio_api:swordfish_api_url:fail' ) );
}

function pleio_api_swordfish_notify($username, $type, $event, $group_id, $subject_id) {
	$username = sanitise_string ( $username );
	$type = sanitise_string ( $type );
	$event = sanitise_string ( $event );
	$group_id = sanitise_string ( $group_id );
	$subject_id = sanitise_string ( $subject_id );
	$group = elgg_get_entities_from_metadata ( array ('metadata_name' => "swordfish_group", 'metadata_value' => $group_id, 'site_guids' => ELGG_ENTITIES_ANY_VALUE ) );
	//var_dump ( $site, $group );
	if (sizeof ( $group )) {
		$group = array_pop ( $group );
	}
	if (! $group)
		return new ErrorResult ( elgg_echo ( 'pleio_api:swordfish_notify:unknown_group' ) );
	$site = get_entity ( $group->site_guid );
	if (! $site)
		return new ErrorResult ( elgg_echo ( 'pleio_api:swordfish_notify:unknown_site' ) );
	$user = get_user_by_username ( $username );
	if (! $user)
		return new ErrorResult ( elgg_echo ( 'pleio_api:swordfish_notify:unknown_user' ) );
	if (! $username || ! in_array ( $type, array ("wiki", "file" ) ) || ! in_array ( $event, array ("create", "update", "comment" ) ) || ! $group_id || ! $subject_id)
		return new ErrorResult ( elgg_echo ( 'pleio_api:swordfish_notify:invalid' ) );
	$type = $type == "wiki" ? "page" : "file";
	//$river_id = add_to_river('river/object/' . $type . '/' . $event, $event, $group->guid, $group->guid );
	//	$river_item = elgg_get_river(array('id' => $river_id));
	//	if (sizeof($river_item)) {
	//		$river_item = array_pop($river_item);
	////		$river_item->site_guid = $site->guid;
	////		$river_item->type = "object";
	////		$river_item->subtype = $type;
	////		$river_item->save(); 
	//	}
	//	return array($username, $type, $event, $group_id, $subject_id, pleio_api_export($site), pleio_api_export($group), pleio_api_export($user));
	return new SuccessResult ( elgg_echo ( 'pleio_api:swordfish_notify:success' ) );
}

function pleio_api_swordfish_group_connect($group_id = 0, $swordfish_group_id = "") {
	$group_id = intval ( $group_id );
	$swordfish_group_id = sanitise_string ( $swordfish_group_id );
	$user = elgg_get_logged_in_user_entity ();
	$group = get_entity ( $group_id );
	$result = "connect:" . ($group->setMetaData ( "swordfish_group", $swordfish_group_id ) ? "Y" : "N");
	foreach ( get_group_members ( $group_id, 10, 0 ) as $member ) {
		$url = pleio_api_swordfish_baseurl ( $group->site_guid ) . "add-user-to-group";
		$swordfish_name = "pleio";
		$params = array ("gid" => $swordfish_group_id, "userid" => pleio_api_swordfish_username ( $member->username ), "fullname" => $member->name, "email" => $member->email );
		$result .= "," . $params ["userid"] . ":";
		$r = pleio_api_call_swordfish_api ( $swordfish_name, $url, "POST", $params );
		$result .= $r->ok ? "Y" : "N";
	}
	return $result;
}

function pleio_api_get_contacts($offset = 0, $search = "") {
	$offset = intval ( $offset );
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$options = array (
			'limit' => 100, 
			'offset' => $offset, 
			'relationship' => 'friend', 
			'relationship_guid' => $user_id, 
			'types' => 'user', 
			'subtypes' => ELGG_ENTITIES_ANY_VALUE, 
			'count' => 1 );
	if ($search) {
		$search = sanitise_string ( $search );
		$options ['wheres'] = " name LIKE '%$search%' ";
		$options ['joins'] = " inner join " . get_config ( "dbprefix" ) . "users_entity using (guid) ";
	}
	$total = elgg_get_entities_from_relationship ( $options );
	$options ['count'] = 0;
	$list = array ();
	foreach ( elgg_get_entities_from_relationship ( $options ) as $friend ) {
		$list [] = pleio_api_format_user ( $friend );
	}
	return array ("total" => $total, "list" => $list, "offset" => $offset );
}

function pleio_api_get_contact_requests($sent = 0, $search = "", $offset = 0) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$sent = intval ( $sent );
	$search = sanitise_string ( $search );
	$options = array ("type" => "user", "limit" => false, "relationship" => "friendrequest", "relationship_guid" => $user_id, "count" => 1 );
	$options ["inverse_relationship"] = $sent == 0; //voor ontvangen verzoeken inverse relation ophalen
	$total = elgg_get_entities_from_relationship ( $options );
	$options ['count'] = 0;
	$options ['limit'] = 20;
	$options ['offset'] = intval ( $offset );
	$list = array ();
	foreach ( elgg_get_entities_from_relationship ( $options ) as $friend ) {
		$list [] = pleio_api_format_user ( $friend );
	}
	return array ("total" => $total, "list" => $list, "offset" => 0 );
}

function pleio_api_get_contact($contact_id) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$contact_id = intval ( $contact_id );
	if (user_is_friend ( $contact_id, $user_id )) {
		return pleio_api_get_user ( $contact_id );
	}
	return new ErrorResult ( "Contactpersoon niet gevonden" );
}

function pleio_api_delete_contact($contact_id) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$contact_id = intval ( $contact_id );
	$contact = get_user ( $contact_id );
	if (user_remove_friend ( $user_id, $contact_id )) {
		try { //V1.1 - Old relationships might not have the 2 as friends...	
			user_remove_friend ( $contact_id, $user_id );
		} catch ( Exception $e ) {
			//ignore
		}
		return new SuccessResult ( elgg_echo ( "friends:remove:successful", array ($contact->name ) ) );
	} else {
		return new ErrorResult ( elgg_echo ( "friends:remove:failure", array ($contact->name ) ) );
	}
}

function pleio_api_contact_request_respond($contact_id, $accept) {
	$user = elgg_get_logged_in_user_entity ();
	$contact_id = intval ( $contact_id );
	$contact = get_user ( $contact_id );
	$removed = false;
	if ($accept == 2) {
		$removed = remove_entity_relationship ( $user->guid, 'friendrequest', $contact_id );
	} else {
		$removed = remove_entity_relationship ( $contact_id, 'friendrequest', $user->guid );
	}
	if ($user && $contact && $removed) {
		if ($accept == 1) {
			if (user_add_friend ( $user->guid, $contact_id ) && user_add_friend ( $contact_id, $user->guid )) {
				return new SuccessResult ( elgg_echo ( "friend_request:approve:successful", array ($contact->name ) ) );
			} else {
				return new SuccessResult ( elgg_echo ( "friend_request:approve:fail", array ($contact->name ) ) );
			}
		} elseif ($accept == 2) {
			return new SuccessResult ( elgg_echo ( "friend_request:revoke:success" ) );
		} else {
			$subject = elgg_echo ( "friend_request:decline:subject", array ($user->name ) );
			$message = elgg_echo ( "friend_request:decline:message", array ($contact->name, $user->name ) );
			notify_user ( $contact_id, $user->guid, $subject, $message );
			return new SuccessResult ( elgg_echo ( "friend_request:decline:success" ) );
		}
	}
	switch ($accept) {
		case 0 :
			return new ErrorResult ( elgg_echo ( "friend_request:decline:fail", array ($contact->name ) ) );
		case 1 :
			return new ErrorResult ( elgg_echo ( "friend_request:approve:fail", array ($contact->name ) ) );
		case 2 :
			return new ErrorResult ( elgg_echo ( "friend_request:revoke:fail", array ($contact->name ) ) );
	}
}

function pleio_api_add_contact($contact_id) {
	$user = elgg_get_logged_in_user_entity ();
	$user_id = $user !== false ? $user->guid : 0;
	$contact_id = intval ( $contact_id );
	$contact = get_user ( $contact_id );
	return pleio_api_add_contact_by_user ( $user, $contact );
}

function pleio_api_add_contacts_by_email($emails = "") {
	$results = array ();
	$user = elgg_get_logged_in_user_entity ();
	if (! $user) {
		return new ErrorResult ( "no_user" );
	}
	foreach ( explode ( "\n", $emails ) as $email ) {
		$email = trim ( $email );
		if ($email) {
			$contact = get_user_by_email ( $email );
			$status = ErrorResult::$RESULT_FAIL;
			$message = elgg_echo ( "notfound" );
			if (sizeof ( $contact )) {
				$contact = $contact [0];
				$result = pleio_api_add_contact_by_user ( $user, $contact );
				if ($result) {
					$result = $result->export ();
					$status = $result->status;
					$message = $result->message . $result->result;
				}
			}
			$results [] = array ("email" => $email, "status" => $status, "message" => $message );
		}
	}
	if (sizeof ( $results )) {
		return $results;
	}
	return new ErrorResult ( "unknown" );
}

function pleio_api_report_contact($contact_id, $report_content) {
	$contact_id = intval ( $contact_id );
	$contact = get_user ( $contact_id );
	if ($contact) {
		$title = $contact->name;
		$description = $report_content;
		$address = elgg_get_site_entity ()->url . "profile/" . $contact->username;
		$access = ACCESS_PRIVATE;
		$report = new ElggObject ( );
		$report->subtype = "reported_content";
		$report->owner_guid = elgg_get_logged_in_user_guid ();
		$report->title = $title;
		$report->address = $address;
		$report->description = $description;
		$report->access_id = $access;
		if ($report->save ()) {
			return new SuccessResult ( elgg_echo ( 'reportedcontent:success' ) );
		}
	}
	return new ErrorResult ( elgg_echo ( 'reportedcontent:failed' ) );
}

function pleio_api_change_setting($name = "", $password = "", $language = "", $email = "") {
	$fail = false;
	$dirty = false;
	$user = elgg_get_logged_in_user_entity ();
	if ($language && $language != $user->language && array_key_exists ( $language, get_installed_translations () )) {
		$user->language = $language;
		$dirty = true;
	}
	if ($email && $email != $user->email) {
		if (! is_email_address ( $email )) {
			$fail = elgg_echo ( 'email:save:fail' );
		} else {
			if (! get_user_by_email ( $email )) {
				$user->email = $email;
				$dirty = true;
			} else {
				$fail = elgg_echo ( 'registration:dupeemail' );
			}
		}
	}
	if ($name && $name != $user->name) {
		$name = strip_tags ( $name );
		if (elgg_strlen ( $name ) > 50) {
			$fail = elgg_echo ( 'user:name:fail' );
		} else {
			$user->name = $name;
			$dirty = true;
		}
	}
	if ($password) {
		try {
			$result = validate_password ( $password );
			if ($result) {
				$user->salt = generate_random_cleartext_password ();
				$user->password = generate_user_password ( $user, $password );
				$dirty = true;
			} else {
				$fail = elgg_echo ( 'user:password:fail' );
			}
		} catch ( RegistrationException $e ) {
			$fail = $e->getMessage ();
		}
	}
	if ($fail) {
		return new ErrorResult ( $fail );
	} else {
		if ($dirty) {
			if ($user->canEdit () && $user->save ()) {
				return new SuccessResult ( "Instellingen opgeslagen" );
			} else {
				return new ErrorResult ( "Opslaan mislukt" );
			}
		} else {
			return new SuccessResult ( "Instellingen niet gewijzigd" );
		}
	}
	return new ErrorResult ( "Niets gewijzigd" );
}

function pleio_api_get_online_users() {
	$query = sprintf ( "SELECT * FROM %susers_apisessions ORDER BY expires LIMIT 10", get_config ( "dbprefix" ) );
	$result = array ();
	foreach ( get_data ( $query ) as $u ) {
		if ($u->expires > time ()) {
			$o = get_user_entity_as_row ( $u->user_guid );
			$o->expires = $u->expires;
			$o->expiresDate = date ( "d-m-Y H:i:s", $u->expires );
			$result [$u->user_guid] = $o;
		}
	}
	return $result;
}

function pleio_api_get_comments($guid = 0, $offset = 0) {
	$user = elgg_get_logged_in_user_entity ();
	$user_guid = $user !== false ? $user->guid : 0;
	return array ("total" => pleio_api_fetch_comments ( $guid, $user_guid, $offset, 1 ), "list" => pleio_api_fetch_comments ( $guid, $user_guid, $offset ), "offset" => $offset );
}
?>