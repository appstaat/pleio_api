<?php

$plugin = $vars["entity"];

$html = elgg_view('input/text', array('name' => 'params[swordfish_api_shared_key]', 'value' => $plugin -> swordfish_api_shared_key));

echo elgg_view_module("inline", elgg_echo('pleio_api:settings:swordfish_api_shared_key'), $html); 
?>