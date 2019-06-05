<?php

$options = [
	'unserialize' => elgg_echo('admin:plugin_settings_transfer:export:unserialize'),
];

echo elgg_view_field([
	'#type' => 'checkboxes',
	'#label' => elgg_echo('admin:plugin_settings_transfer:export:options'),
	'name' => 'options',
	'value' => array_keys($options),
	'options' => array_flip($options),
	'default' => false,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('admin:plugin_settings_transfer:export'),
]);

elgg_set_form_footer($footer);