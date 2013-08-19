<?php
$params = get_input ( "params" );
//var_dump ( $params, $_FILES );
//$logo_contents = get_uploaded_file ( "mobile_logo" );
//print "<img src='data:image/jpg;base64,";
//print base64_encode ( $logo_contents );
//print "' alt='logo'>";
//exit ();
if (! empty ( $params ) && is_array ( $params )) {
	$error_count = 0;
	foreach ( $params as $setting => $value ) {
		elgg_set_plugin_setting ( $setting, $value, "pleio_api" );
	}
	if ($logo_contents = get_uploaded_file ( "mobile_logo" )) {
		if (pleio_api_save_mobile_logo ( $logo_contents )) {
			// reset cache
			elgg_regenerate_simplecache (); // update sc timestamps
			elgg_invalidate_simplecache (); // remove files 
			elgg_filepath_cache_reset ();
		} else {
			$error_count ++;
			register_error ( 
					elgg_echo ( "plugins:settings:save:fail", array (elgg_echo ( "admin:settings:pleio_api" ) ) ) );
		}
	}
	if (! $error_count) {
		system_message ( elgg_echo ( "plugins:settings:save:ok", array (elgg_echo ( "admin:settings:pleio_api" ) ) ) );
	} else {
		register_error ( elgg_echo ( "plugins:settings:save:fail", array (elgg_echo ( "admin:settings:pleio_api" ) ) ) );
	}
} else {
	register_error ( elgg_echo ( "plugins:settings:save:fail", array (elgg_echo ( "admin:settings:pleio_api" ) ) ) );
}
//forward ( REFERER );
var_dump ( $error_count );