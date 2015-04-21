<?php

$options = array(
	'unserialize' => elgg_echo('admin:plugin_settings_transfer:export:unserialize'),
		)
?>
<div>
	<label><?php echo elgg_echo('admin:plugin_settings_transfer:export:options') ?></label>
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
		'value' => elgg_echo('admin:plugin_settings_transfer:export')
	));
	?>
</div>