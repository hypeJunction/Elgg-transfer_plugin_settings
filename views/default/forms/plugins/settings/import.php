<?php

$options = [
	'active' => elgg_echo('admin:plugin_settings_transfer:import:active'),
	'priority' => elgg_echo('admin:plugin_settings_transfer:import:priority'),
	'settings' => elgg_echo('admin:plugin_settings_transfer:import:settings'),
	'settings_unset' => elgg_echo('admin:plugin_settings_transfer:import:settings_unset'),
];

echo elgg_view_field([
	'#type' => 'file',
	'#label' => elgg_echo('admin:plugin_settings_transfer:import:file'),
	'name' => 'json',
	'accept' => 'application/json',
	'required' => true,
]);

echo elgg_view_field([
	'#type' => 'checkboxes',
	'#label' => elgg_echo('admin:plugin_settings_transfer:import:options'),
	'name' => 'options',
	'value' => array_keys($options),
	'options' => array_flip($options),
	'default' => false,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('admin:plugin_settings_transfer:import'),
]);

elgg_set_form_footer($footer);