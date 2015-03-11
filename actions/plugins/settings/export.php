<?php

namespace Ambercal\SettingsTransfer;

$options = get_input('options', array());

$export_options = array();
foreach ($options as $option) {
	$export_options[$option] = true;
}

$export = Util::export($export_options);
$json = json_encode($export);

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="settings.json"');
header('Content-Length: ' . strlen($json));

echo $json;
exit;
