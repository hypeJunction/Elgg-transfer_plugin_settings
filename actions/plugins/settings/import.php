<?php

use Ambercal\SettingsTransfer\Util;

if (!isset($_FILES['json']['name']) || $_FILES['json']['error'] != UPLOAD_ERR_OK) {
	$error = elgg_get_friendly_upload_error($_FILES['json']['error']);
} else {
	$contents = @file_get_contents($_FILES['json']['tmp_name']);	
	$json = @json_decode($contents, true);
}

if (empty($json)) {
	if (!$error) {
		$error = elgg_echo('admin:plugin_settings_transfer:upload:invalid_json');
	}
	register_error($error);
	forward(REFERER);
}

$plugins = elgg_get_plugins('all');

$options = get_input('options', array());
$import_options = array();
foreach ($options as $option) {
	$import_options[$option] = true;
}

$errors = Util::import($json, $import_options);

if ($errors) {
	system_message(elgg_echo('admin:plugin_settings_transfer:import:error', array($errors)));
} else {
	system_message(elgg_echo('admin:plugin_settings_transfer:import:success'));
}

elgg_invalidate_simplecache();
elgg_reset_system_cache();

forward(REFERER);