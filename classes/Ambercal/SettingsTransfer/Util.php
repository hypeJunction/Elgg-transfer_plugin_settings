<?php

namespace Ambercal\SettingsTransfer;

use ElggPlugin;

class Util {

	/**
	 * Prepares an array of all plugins with their status, priority, and settings
	 *
	 * @param array $options Export options
	 * @uses bool $options['unserialize'] Unserialize json/php serialized values
	 * @return array
	 */
	public static function export(array $options = array()) {

		$unserialize = (isset($options['unserialize'])) ? (bool) $options['unserialize'] : true;
		$export = array();

		$plugins = elgg_get_plugins('all');

		foreach ($plugins as $plugin) {
			/* @var $plugin ElggPlugin */

			$id = $plugin->getID();
			$priority = (int) $plugin->getPriority();
			$active = (bool) $plugin->isActive();
			$settings = $plugin->getAllSettings();

			if (!empty($settings) && $unserialize) {
				foreach ($settings as $key => $value) {
					$unserialized = @unserialize($value);
					$json = @json_decode('value', true);

					if ($unserialized || $json) {
						$settings[$key] = array(
							//'serialized_value' => $value,
							'value' => ($unserialized) ? : $json,
							'serialization' => ($unserialized) ? 'serialize' : 'json_encode'
						);
					}
				}
			}

			$export[$id] = array(
				'active' => $active,
				'priority' => $priority,
				'settings' => $settings,
			);
		}

		return $export;
	}

	/**
	 * Imports plugin settings from an array
	 *
	 * @param array $data    Import data
	 * @param array $options Import options
	 *
	 * @uses bool $options['active']         Import active status
	 * @uses bool $options['priority']       Import priority
	 * @uses bool $options['settings']       Import settings
	 * @uses bool $options['settings_unset'] Unset plugin settings before import
	 *
	 * @return int Number of errors
	 */
	public static function import(array $data, array $options = array()) {

		$modify_active = (isset($options['active'])) ? (bool) $options['active'] : true;
		$modify_priority = (isset($options['priority'])) ? (bool) $options['priority'] : true;
		$modify_settings = (isset($options['settings'])) ? (bool) $options['settings'] : true;
		$unset_settings = (isset($options['settings_unset'])) ? (bool) $options['settings_unset'] : true;

		$plugins = elgg_get_plugins('all');

		$error = 0;

		foreach ($plugins as $plugin) {
			/* @var $plugin ElggPlugin */

			$id = $plugin->getID();
			if (!isset($data[$id])) {
				if ($modify_active && $id !== 'ambercal_settings_transfer') {
					if ($plugin->isActive()) {
						if (!$plugin->deactivate()) {
							register_error($plugin->getID() . ' (deactivate): ' . $plugin->getError());
							$error++;
						}
					}
				}
				if ($modify_priority) {
					if (!$plugin->setPriority('first')) {
						register_error($plugin->getID() . ' (set priority): ' . $plugin->getError());
						$error++;
					}
				}
				continue;
			}

			if ($modify_priority && isset($data[$id]['priority']) && is_int($data[$id]['prioririty'])) {
				if (!$plugin->setPriority($data[$id]['priority'])) {
					register_error($plugin->getID() . ' (set priority): ' . $plugin->getError());
					$error++;
				}
			}

			if ($modify_settings && $unset_settings) {
				if (count($plugin->getAllSettings()) && !$plugin->unsetAllSettings()) {
					register_error($plugin->getID() . ' (unset all settings): ' . $plugin->getError());
					$error++;
				}
			}

			if ($modify_settings && !empty($data[$id]['settings'])) {

				foreach ($data[$id]['settings'] as $key => $value) {
					$new_value = null;
					if (is_scalar($value) || is_bool($value)) {
						$new_value = $value;
					} else if (is_array($value) && isset($value['value'])) {
						if (is_scalar($value['value']) || is_bool($value['value'])) {
							$new_value = $value['value'];
						} else if (is_array($value['value'])) {
							$serialization = elgg_extract('serialization', $value, 'serialize');
							$new_value = $serialization($value['value']);
						}
					}
					error_log(print_r($value, true));
					error_log(print_r($new_value, true));
					if (is_null($new_value)) {
						register_error($plugin->getID() . ' (set setting): Can not parse new value for "' . $key . '"');
						$error++;
						continue;
					}
					if (!$plugin->setSetting($key, $new_value)) {
						register_error($plugin->getID() . ' (set setting): Unable to set new value for "' . $key . '"');
						$error++;
					}
				}
			}

			if ($modify_active && isset($data[$id]['active'])) {
				if ($data[$id]['active'] && !$plugin->isActive()) {
					if (!$plugin->activate()) {
						register_error($plugin->getID() . ' (activate): ' . $plugin->getError());
						$error++;
					}
				} else if (!$data[$id]['active'] && $plugin->isActive()) {
					if (!$plugin->deactivate()) {
						register_error($plugin->getID() . ' (deactivate): ' . $plugin->getError());
						$error++;
					}
				}
			}
		}

		return $error;
	}

}
