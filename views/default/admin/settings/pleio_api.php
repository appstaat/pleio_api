<?php 
$swordfish_api_shared_key = elgg_get_plugin_setting("swordfish_api_shared_key", "pleio_api");	
$gcm_api_key = elgg_get_plugin_setting("gcm_api_key", "pleio_api");	
$logo_url = pleio_api_get_mobile_logo(get_config ( "site_id" ));
?>
<form action="<?php echo $vars["url"]; ?>action/pleio_api/settings" method="post" enctype="multipart/form-data">
	<?php echo elgg_view("input/securitytoken"); ?>
	
	<?php 
	$html = elgg_view('input/text', array('name' => 'params[swordfish_api_shared_key]', 'value' => $swordfish_api_shared_key));
	echo elgg_view_module("inline", elgg_echo('pleio_api:settings:swordfish_api_shared_key'), $html); 

	$html = elgg_view('input/text', array('name' => 'params[gcm_api_key]', 'value' => $gcm_api_key));
	echo elgg_view_module("inline", elgg_echo('pleio_api:settings:gcm_api_key'), $html);
	?>
	
	<div class="elgg-module elgg-module-inline">
		<div class="elgg-head">
			<h3><?php echo elgg_echo("pleio_api:settings:mobile_logo"); ?></h3>
		</div>
		<div class="elgg-body">
			<input type="file" name="mobile_logo" />	
			<br />
			<img src="/<?php echo $logo_url; ?>" style="max-width: 500px; padding: 0; margin: 10px 0 0 2px" class="elgg-plugin" />			
		</div>
	</div>
	
	<?php echo elgg_view("input/submit", array("value" => elgg_echo("save"))); ?>
</form>