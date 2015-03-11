<?php

/**
 * @package Ambercal
 * @subpackage SettingsTransfer
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 */

namespace Ambercal\SettingsTransfer;

require_once __DIR__ . '/vendor/autoload.php';

elgg_register_event_handler('pagesetup', 'system', __NAMESPACE__ . '\\pagesetup');
elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

/**
 * Initialize the plugin on system init
 * @return void
 */
function init() {

	elgg_register_action('plugins/settings/export', __DIR__ . '/actions/plugins/settings/export.php', 'admin');
	elgg_register_action('plugins/settings/import', __DIR__ . '/actions/plugins/settings/import.php', 'admin');

}

/**
 * Setup menus
 */
function pagesetup() {

	elgg_register_admin_menu_item('develop', 'import', 'plugin_settings_transfer');
	elgg_register_admin_menu_item('develop', 'export', 'plugin_settings_transfer');
}
