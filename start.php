<?php
require_once (dirname ( __FILE__ ) . "/lib/helpers.php");
require_once (dirname ( __FILE__ ) . "/lib/methods.php");
//
// register init functions
elgg_register_event_handler ( 'init', 'system', 'pleio_api_expose_functions' );
elgg_register_event_handler ( 'init', 'system', 'pleio_api_init' );

function pleio_api_init() {
	//
	// register events & hooks
	elgg_register_event_handler ( 'create', 'object', 'pleio_api_create_object_handler' );
	elgg_register_event_handler ( 'create', 'friendrequest', 'pleio_api_create_object_handler' );
	elgg_register_event_handler ( 'created', 'river', 'pleio_api_create_object_handler' );
	elgg_register_plugin_hook_handler ( 'cron', 'minute', 'pleio_api_handle_push_queue' );
	elgg_register_plugin_hook_handler ( "api_key", "use", "pleio_api_use_api_key" );
	//
	// register settings action and menu item
	elgg_register_action ( "pleio_api/settings", dirname ( __FILE__ ) . "/actions/settings.php", "admin" );
	elgg_register_admin_menu_item ( "configure", "pleio_api", "settings" );
	//
	// register page handler
	elgg_register_page_handler ( "pleio_api", "pleio_api_page_handler" );
}

function pleio_api_page_handler($page) {
	switch ($page [0]) {
		case "mobile_logo" :
			include (dirname ( __FILE__ ) . "/pages/mobile_logo.php");
			break;
		default :
			return false;
	}
}