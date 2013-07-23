<?php
require_once (dirname ( __FILE__ ) . "/lib/helpers.php");
require_once (dirname ( __FILE__ ) . "/lib/methods.php");
// register events & hooks
elgg_register_event_handler ( 'init', 'system', 'pleio_api_expose_functions' );
elgg_register_event_handler ( 'create', 'object', 'pleio_api_create_object_handler' );
elgg_register_event_handler ( 'create', 'friendrequest', 'pleio_api_create_object_handler' );
elgg_register_event_handler ( 'created', 'river', 'pleio_api_create_object_handler' );
elgg_register_plugin_hook_handler ( 'cron', 'minute', 'pleio_api_handle_push_queue' );
?>
