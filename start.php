<?php

/**
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 */

elgg_register_classes(__DIR__ . '/classes/');

elgg_register_event_handler('pagesetup', 'system',  'ambercal_settings_transfer_pagesetup');
elgg_register_event_handler('init', 'system', 'ambercal_settings_transfer_init');

/**
 * Initialize the plugin on system init
 * @return void
 */
function ambercal_settings_transfer_init() {

	elgg_register_action('plugins/settings/export', __DIR__ . '/actions/plugins/settings/export.php', 'admin');
	elgg_register_action('plugins/settings/import', __DIR__ . '/actions/plugins/settings/import.php', 'admin');

}

/**
 * Setup menus
 */
function ambercal_settings_transfer_pagesetup() {

	elgg_register_admin_menu_item('develop', 'import', 'plugin_settings_transfer');
	elgg_register_admin_menu_item('develop', 'export', 'plugin_settings_transfer');
}
