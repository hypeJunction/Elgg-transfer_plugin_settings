<?php

namespace Ambercal\SettingsTransfer;

use Elgg\HooksRegistrationService\Hook;

class SetupAdminMenu {
	public function __invoke(Hook $hook) {
		$menu = $hook->getValue();
		/* @var $menu \Elgg\Collections\Collection */

		$menu->add(\ElggMenuItem::factory([
			'name' => 'settings_transfer',
			'text' => elgg_echo('admin:plugin_settings_transfer'),
			'section' => 'develop',
			'context' => ['admin'],
		]));

		$menu->add(\ElggMenuItem::factory([
			'name' => 'settings_transfer:import',
			'text' => elgg_echo('admin:plugin_settings_transfer:import'),
			'href' => 'admin/plugin_settings_transfer/import',
			'section' => 'develop',
			'context' => ['admin'],
			'parent_name' => 'settings_transfer',
		]));

		$menu->add(\ElggMenuItem::factory([
			'name' => 'settings_transfer:export',
			'text' => elgg_echo('admin:plugin_settings_transfer:export'),
			'href' => 'admin/plugin_settings_transfer/export',
			'section' => 'develop',
			'context' => ['admin'],
			'parent_name' => 'settings_transfer',
		]));

		return $menu;
	}
}