<?php

function pleio_api_methods() {
	return array (
			'add_comment' => array ('method' => 'POST', 'params' => array ('guid' => array ('type' => 'string' ), 'comment' => array ('type' => 'string' ) ) ), 
			'add_contact' => array ('method' => 'POST', 'params' => array ('contact_id' => array ('type' => 'int' ) ) ), 
			'add_contacts_by_email' => array ('method' => 'POST', 'params' => array ('emails' => array ('type' => 'string' ) ) ), 
			'change_setting' => array (
					'method' => 'POST', 
					'params' => array (
							'name' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'password' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'language' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'email' => array ('type' => 'string', 'required' => false, 'default' => '' ) ) ), 
			'contact_request_respond' => array ('method' => 'POST', 'params' => array ('contact_id' => array ('type' => 'int' ), 'accept' => array ('type' => 'int' ) ) ), 
			'delete_comment' => array ('method' => 'POST', 'params' => array ('comment_id' => array ('type' => 'int' ) ) ), 
			'delete_contact' => array ('method' => 'POST', 'params' => array ('contact_id' => array ('type' => 'int' ) ) ), 
			'delete_file' => array ('method' => 'POST', 'params' => array ('file_id' => array ('type' => 'string' ) ) ), 
			'delete_message' => array ('method' => 'POST', 'params' => array ('message_id' => array ('type' => 'int' ) ) ), 
			'delete_wiki' => array ('method' => 'POST', 'params' => array ('wiki_id' => array ('type' => 'string' ) ) ), 
			'get_access_list' => array (), 
			'get_activity' => array (
					'params' => array (
							'group_id' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'get_all_groups' => array (
					'params' => array (
							'search' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'offset' => array ('type' => 'int', 'required' => false, 'default' => 0 ), 
							'group_id' => array ('type' => 'int', 'required' => false, 'default' => 0 ) ) ), 
			'get_all_subsites' => array (
					'params' => array (
							'search' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'subsite_id' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'locked_filter' => array ('type' => 'int', 'required' => false, 'description' => '0 = alles, 1 = open, 2 = gesloten', 'default' => '' ), 
							'order_by' => array ('type' => 'int', 'required' => false, 'description' => '0 = geen, 1 = az, 2 = za', 'default' => '' ), 
							'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'get_button_names' => array ('params' => array () ), 
			'get_comments' => array ('params' => array ('guid' => array ('type' => 'int' ), 'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'get_contact' => array ('params' => array ('contact_id' => array ('type' => 'int' ) ) ), 
			'get_contact_requests' => array (
					'params' => array (
							'sent' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'search' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'get_contacts' => array (
					'params' => array (
							'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'search' => array ('type' => 'string', 'required' => false, 'default' => '' ) ) ), 
			'get_file' => array ('method' => 'GET', 'params' => array ('file_id' => array ('type' => 'string' ) ) ), 
			'get_files' => array (
					'params' => array (
							'group_id' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'folder_id' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'user_id' => array ('type' => 'int', 'required' => false, 'default' => '' ),
							'file_id' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'offset' => array ('type' => 'int', 'required' => false, 'default' => 0 ), 
							'search' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'filter' => array ('type' => 'int', 'required' => false, 'default' => 0, 'description' => '0 = none, 1 = mine, 2 = friends' ) ) ), 
			'get_folders' => array (
					'params' => array (
							'group_id' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'folder_id' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'get_group' => array (
					'params' => array ('group_id' => array ('type' => 'int' ), 'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'get_group_icon' => array ('params' => array ('group_id' => array ('type' => 'int' ) ) ), 
			'get_login' => array ('method' => 'GET' ), 
			'get_messages' => array (
					'params' => array (
							'sent' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'search' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'get_my_groups' => array (
					'params' => array (
							'search' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'offset' => array ('type' => 'int', 'required' => false, 'default' => 0 ) ) ), 
			'get_my_subsites' => array (
					'params' => array (
							'search' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'locked_filter' => array ('type' => 'int', 'required' => false, 'description' => '0 = alles, 1 = open, 2 = gesloten', 'default' => '' ), 
							'order_by' => array ('type' => 'int', 'required' => false, 'description' => '0 = geen, 1 = az, 2 = za', 'default' => '' ), 
							'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'get_online_users' => array (), 
			'get_tweios' => array (
					'params' => array (
							'group_id' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'user_id' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'filter' => array ('type' => 'int', 'required' => false, 'description' => '0 = none, 1 = mine, 2 = friends', 'default' => '' ), 
							'search' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ),
							'parent_id' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'get_user' => array ('params' => array ('user_id' => array ('type' => 'int' ) ) ), 
			'get_wiki' => array (
					'params' => array ('wiki_id' => array ('type' => 'string' ), 'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'get_wikis' => array (
					'params' => array (
							'group_id' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'parent_id' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'user_id' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'offset' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'search' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'filter' => array ('type' => 'int', 'required' => false, 'description' => '0 = none, 1 = mine, 2 = friends', 'default' => '' ) ) ), 
			'join_group' => array ('params' => array ('group_id' => array ('type' => 'int' ) ) ), 
			'join_subsite' => array ('params' => array ('reason' => array ('type' => 'string', 'required' => false, 'default' => '' ) ) ), 
			'like_entity' => array ('method' => 'POST', 'params' => array ('guid' => array ('type' => 'string' ) ) ),			 
			'mark_message' => array ('method' => 'POST', 'params' => array ('message_id' => array ('type' => 'int' ), 'read' => array ('type' => 'int' ) ) ), 
			'report_contact' => array (
					'method' => 'POST', 
					'params' => array ('contact_id' => array ('type' => 'int' ), 'report_content' => array ('type' => 'string' ) ) ), 
			'save_file' => array (
					'method' => 'POST', 
					'params' => array (
							'data' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'file_name' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'title' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'description' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'tags' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'file_id' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'folder_id' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'group_id' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'access_id' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'wiki_id' => array ('type' => 'string', 'required' => false, 'default' => '' ),
							'mimetype' => array ('type' => 'string', 'required' => false, 'default' => '' ) ) ), 
			'save_wiki' => array (
					'method' => 'POST', 
					'params' => array (
							'content' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'title' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'wiki_id' => array ('type' => 'string', 'required' => false, 'default' => '' ), 
							'parent_id' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'group_id' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'access_id' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'write_access_id' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'send_message' => array (
					'method' => 'POST', 
					'params' => array (
							'contact_id' => array ('type' => 'int' ), 
							'message_title' => array ('type' => 'string' ), 
							'message_content' => array ('type' => 'string' ), 
							'reply' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'send_tweio' => array (
					'method' => 'POST', 
					'params' => array (
							'message' => array ('type' => 'string' ), 
							'access_id' => array ('type' => 'int' ), 
							'reply' => array ('type' => 'int', 'required' => false, 'default' => '' ), 
							'group_id' => array ('type' => 'int', 'required' => false, 'default' => '' ) ) ), 
			'swordfish_group_connect' => array ('params' => array ('group_id' => array ('type' => 'int' ), 'swordfish_group_id' => array ('type' => 'string' ) ) ), 
			'swordfish_notify' => array (
					'method' => 'POST', 
					"login_required" => false, 
					'params' => array (
							'username' => array ('type' => 'string', 'default' => '' ), 
							'type' => array ('type' => 'string', 'default' => '' ), 
							'event' => array ('type' => 'string', 'default' => '' ), 
							'group_id' => array ('type' => 'string', 'default' => '' ), 
							'subject_id' => array ('type' => 'string', 'default' => '' ) ) ), 
			'swordfish_site_connect' => array ('params' => array ('subsite_id' => array ('type' => 'int' ), 'swordfish_api_url' => array ('type' => 'string' ) ) ),
			'unlike_entity' => array ('method' => 'POST', 'params' => array ('guid' => array ('type' => 'string' ) ) ), 
			'update_device_token' => array (
					'method' => 'POST', 
					'params' => array ('device_token' => array ('type' => 'string', 'default' => '' ), 'device' => array ('type' => 'string', 'default' => '' ) ) ) );
}

function pleio_api_expose_functions() {
	foreach ( pleio_api_methods () as $method => $info ) {
		$info ["params"] = isset ( $info ["params"] ) ? $info ["params"] : array ();
		$info ["description"] = isset ( $info ["description"] ) ? $info ["description"] : "Pleio API method " . $method;
		$info ["method"] = isset ( $info ["method"] ) ? $info ["method"] : "GET";
		$info ["login_required"] = isset ( $info ["login_required"] ) ? $info ["login_required"] : true;
		$public_method = isset ( $info ["public_method"] ) ? $info ["public_method"] : "pleio." . $method;
		$private_function = isset ( $info ["private_function"] ) ? $info ["private_function"] : "pleio_api_" . $method;
		expose_function ( $public_method, $private_function, $info ["params"], $info ["description"], $info ["method"], true, $info ["login_required"] );
	}
}

function pleio_api_export($data, $exportable = null, $include = false) {
	$exportable = isset ( $exportable ) ? $exportable : $data->getExportableValues ();
	$x = array ();
	foreach ( $data as $n => $v ) {
		if (in_array ( $n, $exportable )) {
			$x [$n] = $v;
		} elseif ($include) {
			$x ["__hidden"] [$n] = $v;
		}
	}
	return $x;
}

function pleio_api_get_mobile_logo($site_guid = 1, $wwwroot) {
	$dataroot = get_config ( "dataroot", $site_guid );
	$result = "";
	if (file_get_contents ( $dataroot . "pleio_api/mobile_logos/logo_" . $site_guid )) {
		$result = $wwwroot . "pleio_api/mobile_logo/logo.jpg";
	}
	return $result;
}

function pleio_api_get_ios_push_certificate($site_guid = 1) {
	$dataroot = get_config ( "dataroot", $site_guid );
	return $dataroot . "pleio_api/ios_push_certificate.pem";
}

function pleio_api_get_site_colors($site_guid = 1) {
	$settings = get_all_private_settings ( $site_guid );
	$colorset = array_key_exists ( "colorset", $settings ) ? $settings ["colorset"] : false;
	$colors = array ("01689B", "CCE0F1", "E5F0F9", "154273", "0162CD" ); //default color set
	switch ($colorset) {
		case "custom" :
			for($i = 0; $i < count ( $colors ); $i ++) {
				$custom_color = "custom_color_" . ($i + 1);
				if (array_key_exists ( $custom_color, $settings )) {
					$colors [$i] = $settings [$custom_color];
				}
			}
			break;
		case "mint" :
			$colors [0] = "6ED9AD";
			$colors [1] = "CBE6DB";
			$colors [2] = "E5F2ED";
			break;
		case "magenta" :
			$colors [0] = "900079";
			$colors [1] = "E3B2DA";
			$colors [2] = "F1D9ED";
			break;
		case "yellow" :
			$colors [0] = "F9E11E";
			$colors [1] = "FDF6BB";
			$colors [2] = "FEFBDD";
			break;
		case "purple" :
			$colors [0] = "42145F";
			$colors [1] = "C6B8CF";
			$colors [2] = "E3DCE7";
			break;
		case "violet" :
			$colors [0] = "A90061";
			$colors [1] = "E5B2CF";
			$colors [2] = "F2D9E7";
			break;
		case "pink" :
			$colors [0] = "F092CD";
			$colors [1] = "FADEF0";
			$colors [2] = "FDEFF8";
			break;
		case "navy" :
			$colors [0] = "01689B";
			$colors [1] = "CCE0F1";
			$colors [2] = "E5F0F9";
			$colors [3] = "154273";
			$colors [4] = "0162CD";
			break;
		case "orange" :
			$colors [0] = "E17000";
			$colors [1] = "F6D4B2";
			$colors [2] = "FBEAD9";
			break;
		case "blue" :
			$colors [0] = "007BC7";
			$colors [1] = "B2D7EE";
			$colors [2] = "D9EBF7";
			break;
		case "sand" :
			$colors [0] = "F9B249";
			$colors [1] = "FDE4BE";
			$colors [2] = "FEF2DF";
			break;
		case "green" :
			$colors [0] = "4E9625";
			$colors [1] = "CBE1BD";
			$colors [2] = "E1FECF";
			break;
		default :
			break;
	}
	return $colors;
}

function pleio_api_get_metadata($guid, $data = array()) {
	$metas = elgg_get_metadata ( 
			array ('guids' => $guid, // using guids io guid to prevent subsite_manager hook from messing up query 
'site_guids' => ELGG_ENTITIES_ANY_VALUE, 'limit' => false ) );
	foreach ( $metas as $meta ) {
		$data [$meta->name] = $meta->value;
	}
	return $data;
}

function pleio_api_swordfish_group($group_guid = 0) {
	$metadata = pleio_api_get_metadata ( $group_guid );
	return $metadata ["swordfish_group"];
}

function pleio_api_swordfish_username($pleio_username) {
	return strtolower ( preg_replace ( "/[^a-zA-Z0-9]/", "_", trim ( $pleio_username ) ) );
}

function pleio_api_swordfish_baseurl() {
	$url = false;
	$site_guid = get_config ( "site_id" );
	if ($site_guid)
		$url = get_private_setting ( $site_guid, "swordfish_api_url" );
	return $url;
}

function pleio_api_format_group(ElggGroup $group, $user_guid) {
	$g = pleio_api_export ( $group, explode ( ",", "guid,owner_guid,site_guid,name,description" ) );
	$metadata = pleio_api_get_metadata ( $group->guid );
	if ($metadata ["swordfish_group"]) {
		$g ["swordfish"] = $metadata ["swordfish_group"];
		$g ["name"] .= " [SwordFish]";
	}
	$g ["avatar"] = $metadata ["icontime"] ? "1" : "0";
	$g ["owner_name"] = get_entity ( $group->owner_guid )->name;
	$g ["public"] = $group->membership == ACCESS_PUBLIC ? 1 : 0;
	$g ["member"] = is_group_member ( $g ["guid"], $user_guid ) ? 1 : 0;
	$g ["description"] = trim ( strip_tags ( $g ["description"] ) );
	$g ["member_total"] = get_group_members ( $g ["guid"], 0, 0, 0, true );
	$g ["has_invitation"] = 0;
	$g ["has_pending_membership_request"] = 0;
	if (! $g ["member"]) {
		$g ["has_invitation"] = check_entity_relationship ( $group->guid, 'invited', $user_guid ) ? 1 : 0;
		$g ["has_pending_membership_request"] = check_entity_relationship ( $user_guid, 'membership_request', $group->guid ) ? 1 : 0;
	}
	return $g;
}

function pleio_api_format_groups($groups, $total, $offset, $user_id) {
	$list = array ();
	$total = intval ( $total );
	$offset = intval ( $offset );
	foreach ( $groups as $group ) {
		$list [] = pleio_api_format_group ( $group, $user_id );
	}
	return array ("total" => $total, "list" => $list, "offset" => $offset );
}

function pleio_api_format_user(ElggUser $user, $token = null) {
	$export = pleio_api_export ( $user, explode ( ",", "guid,name,username,language,time_created,last_login" ) );
	$metadata = array (); //pleio_api_get_metadata ( $user->guid );
	$meta = elgg_get_metadata ( array ('guids' => $user->guid, 'site_guids' => ELGG_ENTITIES_ANY_VALUE ) );
	foreach ( $meta as $m ) {
		if ($m->site_guid != 1) {
			$custom = elgg_get_entities ( 
					array ("type" => "object", "subtype" => CUSTOM_PROFILE_FIELDS_PROFILE_SUBTYPE, "limit" => false, "site_guid" => $m->site_guid ) );
			foreach ( $custom as $f ) {
				if ($f->metadata_name == $m->name) {
					if ($f->metadata_type == "email" && $f->admin_only == 'no') {
						$export ["email"] = $m->value;
					}
				}
			}
		}
		$metadata [$m->name] = $m->value;
	}
	$export ["function"] = array_key_exists ( "Ambtenaar", $metadata ) ? $metadata ["Ambtenaar"] : "";
	$export ["location"] = array_key_exists ( "Werklokatie", $metadata ) ? $metadata ["Werklokatie"] : "";
	$export ["website"] = array_key_exists ( "website", $metadata ) ? $metadata ["website"] : "";
	if (isset ( $metadata ["icontime"] )) {
		$path = get_config ( "dataroot" ) . date ( 'Y/m/d/', $user->time_created ) . $user->guid;
		if (file_exists ( $path )) {
			$export ["avatar"] = get_config ( "wwwroot" ) . sprintf ( "mod/profile/icondirect.php?lastcache=%d&joindate=%d&guid=%d&size=medium", $metadata ["icontime"], 
					$user->time_created, $user->guid );
		}
	}
	$export ["friend"] = 0;
	$export ["friendrequest"] = 0;
	$me = elgg_get_logged_in_user_entity ();
	if ($me) {
		$my_user_id = $me !== false ? $me->guid : 0;
		$export ["friend"] = user_is_friend ( $user->guid, $my_user_id ) ? 1 : 0;
		$export ["friendrequest"] = check_entity_relationship ( $my_user_id, "friendrequest", $user->guid ) ? 1 : 0;
	}
	if ($token) {
		$export ["token"] = $token;
	}
	$export["device"] = get_private_setting ( $user->guid, "device" );
	$export["device_token"] = get_private_setting ( $user->guid, "device_token" );
	return $export;
}

function pleio_api_fetch_comments($guid = 0, $user_guid = 0, $offset = 0, $count = false, $limit = 20) {
	$options = array ('guid' => $guid, 'annotation_name' => "generic_comment", 'limit' => $limit, 'offset' => $offset, 'count' => 1 );
	if ($count) {
		return elgg_get_annotations ( $options );
	} else {
		$list = array ();
		$options ['count'] = 0;
		$options ['reverse_order_by'] = true;
		foreach ( elgg_get_annotations ( $options ) as $a ) {
			$list [] = pleio_api_format_comment ( $a, $user_guid );
		}
		return $list;
	}
}

function pleio_api_format_comment(ElggAnnotation $a, $user_guid = 0) {
	$u = get_entity ( $a->owner_guid );
	$u = pleio_api_format_user ( $u );
	$edit = can_edit_extender ( $a->id, "annotation", $user_guid );
	return array (
			"guid" => ( int ) $a->id, 
			"owner_guid" => $a->owner_guid, 
			"description" => trim ( $a->value ), 
			"time_created" => $a->time_created, 
			"avatar" => $u ["avatar"], 
			"name" => $u ["name"], 
			"can_edit" => $edit ? 1 : 0 );
}

function pleio_api_format_activity($item) {
	$subject = $item->getSubjectEntity ();
	$object = $item->getObjectEntity ();
	if ($subject && $object) {
		$subject_link = $subject->name;
		$object_link = $object->title ? $object->title : $object->name;
		$action = $item->action_type;
		$type = $item->type;
		$subtype = $item->subtype ? $item->subtype : 'default';
		$m = "river:$action:$type:$subtype";
		$message = elgg_echo ( $m, array ($subject_link, $object_link ), 'nl' );
		$type = $item->subtype ? $item->subtype : $item->type;
		$avatar = "";
		if ($subject instanceof ElggUser) {
			$u = pleio_api_format_user ( $subject );
			$avatar = $u ["avatar"];
		}
		$description = strip_tags ( $object->description );
		if ($description == $object_link) {
			$description = "";
		}
		$description = str_replace ( array ("&nbsp;", "\n", "\r" ), array (" ", " ", " " ), $description );
		$message = str_replace ( array ($subject_link, $object_link ), array ("", "" ), $message );
		if (strlen ( $description ) >= 80) {
			$description = substr ( $description, 0, 78 ) . "..";
		}
		switch ($type . ":" . $action) {
			case "thewire:create" :
				$message = "plaatste";
				$object_link = "een tweio";
				break;
			case "user:update" :
				$message = "heeft zijn/haar gegevens bijgewerkt";
				break;
		}
		if (strpos ( $message, ":" ) !== false) {
			if (strpos ( $message, "create" ) !== false) {
				$message = "maakte";
			}
		}
		return array (
				"id" => $item->id, 
				"s" => array ("id" => $item->subject_guid, "name" => $subject_link ), 
				"o" => array ("id" => $item->object_guid, "name" => $object_link ), 
				"a" => $action, 
				"type" => $type, 
				"m" => trim ( $message ), 
				"t" => $item->posted, 
				"avatar" => $avatar, 
				"d" => $description,
		        "can_edit" => $object->canEdit() ? 1 : 0
		);
	}
}

function pleio_api_swordfish_login($user_id, $ip, $expiry_time = 0) {
	$shared_key = elgg_get_plugin_setting ( "swordfish_api_shared_key", "pleio_api" );
	//	var_dump($shared_key);
	$expiry_time = $expiry_time ? $expiry_time : time () + 3600;
	$hash = hash_hmac ( "SHA256", $user_id . $expiry_time . $ip, $shared_key );
	return "__pleio_ac=" . $user_id . "&" . $expiry_time . "&" . $ip . "&" . $hash;
}

function pleio_api_call_swordfish_api($sw_user, $url, $method = "GET", $data = array()) {
	$headers = array ();
	$login = pleio_api_swordfish_login ( $sw_user, $_SERVER ['SERVER_ADDR'] );
	$headers = array ("Cookie: " . $login );
	$http_opts = array ('method' => $method );
	$postdata = http_build_query ( $data );
	if ($method == 'POST') {
		$headers [] = "Content-Length: " . strlen ( $postdata );
		$http_opts ['content'] = $postdata;
	} elseif ($postdata) {
		$url .= "?" . $postdata;
	}
	//	var_dump($url, $postdata, $headers);
	$http_opts ["header"] = implode ( "\r\n", $headers );
	$opts = array ('http' => $http_opts );
	$context = stream_context_create ( $opts );
	$http_response_header = array ();
	$result = new stdClass ( );
	$result->opts = $opts;
	$result->url = $url;
	$result->response = file_get_contents ( $url, false, $context );
	foreach ( $http_response_header as $header ) {
		if (strpos ( $header, "HTTP/1.1" ) !== false) {
			$n = "STATUS";
			$v = str_replace ( "HTTP/1.1 ", "", $header );
		} else {
			list ( $n, $v ) = explode ( ":", $header );
			$n = strtoupper ( $n );
		}
		$v = trim ( $v );
		$result->headers [$n] = $v;
	}
	$result->ok = $result->headers ["STATUS"] == "200 OK";
	return $result;
}

function pleio_api_add_contact_by_user($user, $contact) {
	if (! $user || ! $contact)
		return new ErrorResult ( elgg_echo ( "friend_request:add:failure" ) );
	if ($contact->guid == $user->guid)
		return new ErrorResult ( elgg_echo ( "friend_request:add:self" ) );
	if (check_entity_relationship ( $user->guid, "friendrequest", $contact->guid )) {
		return new ErrorResult ( elgg_echo ( "friendrequest:add:exists", array ($contact->name ) ) );
	}
	if (check_entity_relationship ( $user->guid, "friend", $contact->guid )) {
		return new ErrorResult ( elgg_echo ( "friends:add:exists", array ($contact->name ) ) );
	}
	// is friend, but not reciprocal
	if (check_entity_relationship ( $contact->guid, "friend", $user->guid )) {
		$user->addFriend ( $contact->guid );
		return new SuccessResult ( elgg_echo ( "friends:add:successful", array ($contact->name ) ) );
	}
	// has incoming friend request
	if (check_entity_relationship ( $contact->guid, "friendrequest", $user->guid )) {
		if (remove_entity_relationship ( $contact->guid, 'friendrequest', $user->guid )) {
			$user->addFriend ( $contact->guid );
			$contact->addFriend ( $user->guid );
			return new SuccessResult ( elgg_echo ( "friends:add:successful", array ($contact->name ) ) );
		}
	}
	if (add_entity_relationship ( $user->guid, "friendrequest", $contact->guid )) {
		return new SuccessResult ( elgg_echo ( "friend_request:add:successful", array ($contact->name ) ) );
	} else {
		return new ErrorResult ( elgg_echo ( "friend_request:add:failure", array ($contact->name ) ) );
	}
}

function pleio_api_fetch_likes($guid = 0, $count = 1, $offset = 0, $limit = 50, $owner_guid = 0) {
	$options = array ('guid' => $guid, 'annotation_name' => "likes", 'limit' => $limit, 'offset' => $offset, 'count' => $count );
	if ($owner_guid) {
		$options['annotation_owner_guid'] = $owner_guid;
	}
	return elgg_get_annotations ( $options );
}

function pleio_api_users_with_access_device_token($river) {
	$list = array ();
	$subject = get_entity ( $river->subject_guid );
	$object = get_entity ( $river->object_guid );
	$wheres = array ();
	$joins = array ();
	// don't return same object or subject
	$wheres [] = sprintf ( "e.guid NOT IN (%d, %d, %d, %d)", $object->guid, $object->owner_guid, $subject->guid, $subject->owner_guid );
	$site_guid = $object->site_guid;
	// override site_guid if object is site_guid
	if ($object instanceof Subsite || $object instanceof ElggSite) {
		$site_guid = $object->guid;
	}
	// add access_id specific wheres to determine possible receivers
	$more = false;
	switch ($object->access_id) {
		case ACCESS_DEFAULT :
		case ACCESS_LOGGED_IN :
		case ACCESS_PUBLIC :
			if ($object->type == "user") {
				//only friends $object->guid
				$more = elgg_get_entity_relationship_where_sql ( 'e.guid', 'friend', $object->guid, 1 );				
			} elseif ($object && $object->container_guid && $object->container_guid != $object->owner_guid && $container = get_entity ( $object->container_guid ) && $container instanceof ElggGroup) {
				// only group $object->container_guid
				$more = elgg_get_entity_relationship_where_sql ( 'e.guid', 'member', $object->container_guid, 1 );
			} elseif ($site_guid > 1) {
				//only subsite $site_guid
				$more = elgg_get_entity_relationship_where_sql ( 'e.guid', 'member_of_site', $site_guid, 1 );
			} else {
				// pleio has too many users to update everyone, so only friends $object->owner_guid
				$more = elgg_get_entity_relationship_where_sql ( 'e.guid', 'friend', $object->owner_guid, 1 );
			}
			break;
		case ACCESS_FRIENDS :
			//only friends $object->owner_guid
			$more = elgg_get_entity_relationship_where_sql ( 'e.guid', 'friend', $object->owner_guid, 1 );
			break;
		case ACCESS_PRIVATE :
			return array ();
		default :
			$users = get_members_of_access_collection ( $object->access_id, 1 );
			$wheres [] = "e.guid IN (" . implode ( ", ", $users ) . ")";
	}
	if ($more) {
		$wheres = array_merge ( $wheres, $more ["wheres"] );
		$joins = array_merge ( $joins, $more ["joins"] );
	}
	// only select users that have a device_token
	$more = elgg_get_entity_private_settings_where_sql ( 'e', array (), array (), 
			array (
					array ('name' => 'device_token', 'value' => 1, /* need this because function combines name & value and skips if value is unset */
							'operand' => 'IS NOT NULL AND 1 = ' ) ) );
	$wheres = array_merge ( $wheres, $more ["wheres"] );
	$joins = array_merge ( $joins, $more ["joins"] );
	$options = array ('type' => 'user', 'limit' => 100, 'wheres' => $wheres, 'joins' => $joins, 'site_guid' => ELGG_ENTITIES_ANY_VALUE );
	$users = elgg_get_entities ( $options );
	foreach ( $users as $user ) {
		$list [$user->guid] = array ("device_token" => $user->getPrivateSetting ( 'device_token' ), "device" => $user->getPrivateSetting ( 'device' ) );
	}
	return $list;
}

function pleio_api_create_apns_message($txt, $device_token, $type, $object = null) {
	$info = pleio_api_create_push_message ( $type, $object );
	$m = array ("aps" => array ('alert' => $txt, 'sound' => 'default' ) );
	$m ['info'] = $info;
	$payload = json_encode ( $m );
	$apnsMessage = chr ( 0 ) . chr ( 0 ) . chr ( 32 ) . pack ( 'H*', str_replace ( ' ', '', $device_token ) ) . chr ( 0 ) . chr ( strlen ( $payload ) ) . $payload;
	return $apnsMessage;
}

function pleio_api_create_push_message($type, $object = null) {
	$m = array ();
	$m ['t'] = $type;
	if ($object) {
		if ($object->container_guid == $object->guid) {
			unset ( $object->container_guid );
		}
		$m ['id'] = $object->guid;
		if ($object->subtype) {
			$m ['t'] = get_subtype_from_id ( $object->subtype );
		}
		if ($object->site_guid) {
			$m ['s'] = $object->site_guid;
		}
		if ($object->container_guid) {
			$m ['c'] = $object->container_guid;
		}
	}
	return $m;
}

function pleio_api_create_gcm_message($txt, $type, $object = null) {
	$info = pleio_api_create_push_message ( $type, $object );
	$info ["message"] = $txt;
	return $info;
}

function pleio_api_push_handle_river_id($river_id, $apnsMessages = array(), $gcmMessages = array()) {
	list ( $river ) = elgg_get_river ( array ("id" => $river_id, 'site_guid' => ELGG_ENTITIES_ANY_VALUE ) );
	if ($river) {
		$users = pleio_api_users_with_access_device_token ( $river );
		if (sizeof ( $users )) {			
			$subject = $river->getSubjectEntity ();
			$object = $river->getObjectEntity ();
			$subject_link = $subject->name;
			$object_link = $object->title ? $object->title : $object->name;
			$action = $river->action_type;
			$type = $river->type;
			$subtype = $river->subtype ? $river->subtype : 'default';
			$m = "river:$action:$type:$subtype";
			$item = pleio_api_format_activity ( $river );
			if ($item ["s"] ["name"] != $item ["s"] ["name"]) {
				$txt = trim ( sprintf ( "%s %s %s", $item ["s"] ["name"], $item ["m"], $item ["o"] ["name"] ) );
			} else {
				$txt = trim ( sprintf ( "%s %s  ", $item ["s"] ["name"], $item ["m"] ) );
			}
			//$txt = elgg_echo ( $m, array ( $subject_link, $object_link ), 'nl' );
			if ($object && $object->container_guid == $subject->guid) {
				unset ( $object->container_guid );
			}
			$gcmMessage = pleio_api_create_gcm_message ( $txt, $river->type, $object );
			$gcmRegistrationIds = array ();
			$gcmUserGuids = array (); // store user_guid to unregister token if needed						
			foreach ( $users as $to_guid => $info ) {
				if (strpos ( $info ["device"], "Android" ) !== false) {
					$gcmRegistrationIds [] = $info ["device_token"];
					$gcmUserGuids [] = $to_guid;
				} else {
					$apnsMessages [$to_guid] = pleio_api_create_apns_message ( $txt, $info ["device_token"], $river->type, $object );
				}
			}
			if (sizeof ( $gcmRegistrationIds )) {
				$gcmMessages [] = array ('registration_ids' => $gcmRegistrationIds, 'data' => $gcmMessage, 'user_guids' => $gcmUserGuids );
			}
		}
	}
	return array ($apnsMessages, $gcmMessages );
}

function pleio_api_push_handle_message_to($message, $apnsMessages = array(), $gcmMessages = array()) {
	$device_token = get_private_setting ( $message->to_guid, "device_token" );
	if ($device_token) {
		$device = get_private_setting ( $message->to_guid, "device" );
		$txt = $message->description;
		$object = get_entity ( $message->object_guid );
		if ($object && $object->container_guid == $message->to_guid) {
			unset ( $object->container_guid );
		}
		if (strpos ( $info ["device"], "Android" ) !== false) {
			$gcmMessage = pleio_api_create_gcm_message ( $txt, $message->message_type, $object );
			$gcmMessages [] = array ('registration_ids' => $device_token, 'data' => $gcmMessage, 'user_guids' => $message->to_guid );
		} else {
			$apnsMessages [$message->to_guid] = pleio_api_create_apns_message ( $txt, $device_token, $message->message_type, $object );
		}
	}
	return array ($apnsMessages, $gcmMessages );
}

function pleio_api_push_apns_messages($apnsMessages = array()) {
	if (! sizeof ( $apnsMessages ))
		return;
	$error = 0;
	$errorString = "";
	$dataroot = get_config ( "dataroot" );
	$apnsCert = $dataroot . "pleio_api/ios_push_certificate.pem";
	if (! file_exists ( $apnsCert ))
		return;
		//$apnsCert = elgg_get_plugins_path () . "pleio_api/apns.pem";
	$apnsHost = 'gateway.push.apple.com:2195';
	$streamContext = stream_context_create ();
	stream_context_set_option ( $streamContext, 'ssl', 'local_cert', $apnsCert );
	//	set_time_limit ( 5 );
	$apns = stream_socket_client ( 'ssl://' . $apnsHost, $error, $errorString, 5, STREAM_CLIENT_CONNECT, $streamContext );
	if ($apns) {
		foreach ( $apnsMessages as $apnsMessage ) {
			fwrite ( $apns, $apnsMessage );
		}
	}
	if ($error) {
		return array ($error, $errorString );
	} else {
		return true;
	}
}

function pleio_api_push_gcm_messages($gcmMessages = array()) {
	if (! sizeof ( $gcmMessages ))
		return;
	$apiKey = elgg_get_plugin_setting ( "gcm_api_key", "pleio_api" );
	$url = 'https://android.googleapis.com/gcm/send';
	$headers = array ('Authorization: key=' . $apiKey, 'Content-Type: application/json' );
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_POST, true );
	curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	foreach ( $gcmMessages as $m ) {
		//$m ["dry_run"] = true; //TEST SERVER		
		$user_guids = $m ["user_guids"];
		unset ( $m ["user_guids"] );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode ( $m ) );
		$result = curl_exec ( $ch );
		$results = json_decode ( $result );
		for($i = 0; $i < sizeof ( $results->results ); $i ++) {
			$result = $results->results [$i];
			if (isset ( $result->error )) {
				switch ($result->error) {
					case "InvalidRegistration" :
					case "NotRegistered" :
						remove_private_setting ( $user_guids [$i], "device_token" );
						break;
				}
			} elseif (isset ( $result->registration_id )) {
				set_private_setting ( $user_guids [$i], "device_token", $result->registration_id );
			}
		}
	}
	curl_close ( $ch );
	return true;
}

function pleio_api_create_object_handler($event, $object_type, $object) {
	if (is_object ( $object )) {
		switch ($object->getSubType ()) {
			case "messages" :
				if ($object->owner_guid == $object->toId) {
					pleio_api_queue_push_message ( $object->toId, $object->fromId, "messages:email:subject", $object_type, $object->guid );
				}
				break;
			case "friendrequest" :
				pleio_api_queue_push_message ( $object->guid_two, $object->guid_one, "friend_request:newfriend:subject", $object_type, $object->guid_one );
				break;
			default :
				break;
		}
	}
	if ($object_type == "river") {
		pleio_api_queue_push_message_for_river ( $object );
	}
}

function pleio_api_queue_push_message_for_river($river) {
	if ($river instanceof ElggRiverItem) {
		$object = $river->getObjectEntity ();
		if ($object) {
			if ($object->site_guid == "1" && $object->container_guid) {
				$container = $object->getContainerEntity ();
				if (! ($container instanceof ElggGroup)) {
					return false;
				}
			}
			$valid_types = array ("user", "group" );
			$valid_subtypes = array ("file", "page", "page_top", "subsite", "thewire", "plugin" );
			if (in_array ( $object->getType (), $valid_types ) || in_array ( $object->getSubtype (), $valid_subtypes )) {
				$message = new ElggObject ( );
				try {	 
					$message->subtype = 'push_message_queue';
					$message->river_id = $river->id;
					return $message->save ();					
				} catch (Exception $ex) { 
					error_log("pleio_api_queue_push_message_for_river failed for river $river->id: " . $ex->getMessage());					
				}
			}
		}
	}
	return false;
}

function pleio_api_queue_push_message($to_guid, $from_guid, $message, $object_type, $object_guid = 0, $site_guid = 0, $container_guid = 0) {
	$to = get_user ( $to_guid );
	if ($to) {
		$device_token = get_private_setting ( $to->guid, "device_token" );
		if ($device_token) {
			if ($from_guid) {
				$from = get_user ( $from_guid );
				if ($from) {
					$push = elgg_echo ( $message, array ($from->name ), 'nl' );
				}
			}
			$message = new ElggObject ( );
			$message->subtype = 'push_message_queue';
			// $message -> access_id = ACCESS_PUBLIC;
			$message->description = $push;
			$message->to_guid = $to->guid;
			$message->object_guid = $object_guid;
			$message->message_type = $object_type;
			$message->site_guid = $site_guid;
			$message->container_guid = $container_guid;
			$message->save ();
		}
	}
}

function pleio_api_handle_push_queue() {
	$site_guid = get_config ( "site_id" );
	if ($site_guid == 1) { // pleio.nl only, handles all subsites
		$max_messages = 300;				
		set_time_limit ( 59 );
		elgg_set_ignore_access ( 1 );
		$messages = elgg_get_entities ( 
				array (
						'types' => 'object', 
						'subtypes' => 'push_message_queue', 
						'site_guid' => ELGG_ENTITIES_ANY_VALUE, 
						'order_by' => 'e.time_created asc' /*send older messages first*/, 'limit' => $max_messages ) );
		$apnsMessages = array ();
		$gcmMessages = array ();
		if ($messages) {
			foreach ( $messages as $message ) {
				if ($message->river_id) {
					list ( $apnsMessages, $gcmMessages ) = pleio_api_push_handle_river_id ( $message->river_id, $apnsMessages, $gcmMessages );
				} else {
					list ( $apnsMessages, $gcmMessages ) = pleio_api_push_handle_message_to ( $message, $apnsMessages, $gcmMessages );
				}
				system_log ( $message, 'handled apns:' . sizeof ( $apnsMessages ) . ' gcm:' . sizeof ( $gcmMessages ) );
				$message->delete ();
			}
		}
		if (sizeof ( $apnsMessages )) {
			pleio_api_push_apns_messages ( $apnsMessages );
		}
		if (sizeof ( $gcmMessages )) {
			pleio_api_push_gcm_messages ( $gcmMessages );
		}
		elgg_set_ignore_access ( 0 );
		return sizeof ( $apnsMessages ) + sizeof ( $gcmMessages );
	}
}

function pleio_api_save_mobile_logo($logo_contents) {
	$dataroot = get_config ( "dataroot" );
	$site_guid = get_config ( "site_id" );
	if (! empty ( $logo_contents )) {
		$path = $dataroot . "pleio_api/mobile_logos/";
		if (! is_dir ( $path )) {
			mkdir ( $path, 0755, true );
		}
		return file_put_contents ( $path . "logo_" . $site_guid, $logo_contents );
	}
	return false;
}

function pleio_api_save_ios_push_certificate($contents) {
	$dataroot = get_config ( "dataroot" );
	$site_guid = get_config ( "site_id" );
	if (! empty ( $contents )) {
		$path = $dataroot . "pleio_api";
		if (! is_dir ( $path )) {
			mkdir ( $path, 0755, true );
		}
		return file_put_contents ( $path . "/ios_push_certificate.pem", $contents );
	}
	return false;
}

function pleio_api_format_tweio($item) {
	$e = pleio_api_export ( $item, explode ( ",", "guid,time_created,owner_guid,container_guid,site_guid,description,parent_guid,childs" ) );
	//				$parent = get_data_row ( 
	//						sprintf ( "select guid_two as guid from %sentity_relationships where relationship = 'parent' and guid_one = %d", get_config ( "dbprefix" ), 
	//								$e ["guid"] ) );
	//				$e ["parent_guid"] = $parent ? intval ( $parent->guid ) : 0;
	$e ["parent_guid"] = $e ["parent_guid"] ? intval ( $e ["parent_guid"] ) : 0;
	$e ["wire_thread"] = $item->wire_thread;
	$e ["reply"] = $item->reply;
	$e ["thread_id"] = $item->wire_thread && ($item->reply || ($item->wire_thread == $e["guid"] && $e["childs"])) ? (string)$item->wire_thread : "0";
	$u = pleio_api_format_user ( get_user ( $item->owner_guid ) );
	$e ["name"] = $u ["name"];
	$e ["avatar"] = $u ["avatar"];
	$e ["likes_count"] = pleio_api_fetch_likes ( $item->guid );
	$e ["liked"] = 0;
	if ($e ["likes_count"]) {
		//$options = array ('guid' => $item->guid, 'annotation_name' => "likes", 'count' => 1, 'annotation_owner_guid' =>  $user->guid);
		//$anno = elgg_get_annotations ( $options );
		$e ["liked"] = pleio_api_fetch_likes ( $item->guid, 1, 0, 0, $user->guid ) > 0 ? 1 : 0;
	}
	return $e;
}