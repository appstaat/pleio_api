<?php
$site_guid = get_config ( "site_id" );
$dataroot = get_config ( "dataroot", $site_guid );
if ($contents = file_get_contents ( $dataroot . "pleio_api/mobile_logos/logo_" . $site_guid )) {
	header ( "Content-type: image/jpeg" );
	header ( 'Expires: ' . date ( 'r', time () + 864000 ) );
	header ( "Pragma: public" );
	header ( "Cache-Control: public" );
	header ( "Content-Length: " . strlen ( $contents ) );
	$splitString = str_split ( $contents, 1024 );
	foreach ( $splitString as $chunk ) {
		echo $chunk;
	}
}
?>