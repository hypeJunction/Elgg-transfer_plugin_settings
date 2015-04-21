<?php

$options = array(
	'active' => elgg_echo('admin:plugin_settings_transfer:import:active'),
	'priority' => elgg_echo('admin:plugin_settings_transfer:import:priority'),
	'settings' => elgg_echo('admin:plugin_settings_transfer:import:settings'),
	'settings_unset' => elgg_echo('admin:plugin_settings_transfer:import:settings_unset'),
		);
?>
<div>
	<label><?php echo elgg_echo('admin:plugin_settings_transfer:import:file') ?></label>
	<?php
	echo elgg_view('input/file', array(
		'name' => 'json',
		'accept' => 'application/json',
		'required' => true,
	));
	?>
</div>
<div>
	<label><?php echo elgg_echo('admin:plugin_settings_transfer:import:options') ?></label>
	<?php
	echo elgg_view('input/checkboxes', array(
		'name' => 'options',
		'value' => array_keys($options),
		'options' => array_flip($options),
		'default' => false,
	));
	?>
</div>
<div class="elgg-foot">
	<?php
	echo elgg_view('input/submit', array(
		'value' => elgg_echo('admin:plugin_settings_transfer:import')
	));
	?>
</div>