<?php

use Ambercal\SettingsTransfer\Util;

$options = get_input('options', array());

$export_options = array();
foreach ($options as $option) {
	$export_options[$option] = true;
}

$url = elgg_get_site_url();
$dt = date('Y-m-d');
$filename = implode('-', array('settings', $url, $dt)) . '.json';

$export = Util::export($export_options);
$json = json_encode($export);

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($json));

echo $json;
exit;
