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
					if (!self::deactivatePlugin($id)) {
						register_error($id . ' (deactivate): ' . $plugin->getError());
						$error++;
					}
				}

				if ($modify_priority) {
					if (!$plugin->setPriority('first')) {
						register_error($id . ' (set priority): ' . $plugin->getError());
						$error++;
					}
				}

				continue;
			}

			if ($modify_priority && isset($data[$id]['priority']) && is_int($data[$id]['priority'])) {
				if (!$plugin->setPriority($data[$id]['priority'])) {
					register_error($id . ' (set priority): ' . $plugin->getError());
					$error++;
				}
			}

			if ($modify_settings && $unset_settings) {
				if (count($plugin->getAllSettings()) && !$plugin->unsetAllSettings()) {
					register_error($id . ' (unset all settings): ' . $plugin->getError());
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

					if (is_null($new_value)) {
						register_error($id . ' (set setting): Can not parse new value for "' . $key . '"');
						$error++;
						continue;
					}
					if (!$plugin->setSetting($key, $new_value)) {
						register_error($id . ' (set setting): Unable to set new value for "' . $key . '"');
						$error++;
					}
				}
			}

			if ($modify_active && isset($data[$id]['active'])) {
				if ($data[$id]['active']) {
					if (!self::activatePlugin($id)) {
						register_error($id . ' (activate): ' . $plugin->getError());
						$error++;
					}
				} else if (!$data[$id]['active']) {
					if (!self::deactivatePlugin($id)) {
						register_error($id . ' (deactivate): ' . $plugin->getError());
						$error++;
					}
				}
			}
		}

		return $error;
	}

	/**
	 * Deactivate the plugin as well as any other plugin depending on it
	 *
	 * @param string $id Plugin ID
	 * @return boolean
	 */
	public static function deactivatePlugin($id) {

		$plugin = elgg_get_plugin_from_id($id);
		if (!$plugin) {
			return false;
		}

		if (!$plugin->isActive()) {
			return true;
		}

		$result = true;
		$dependants = array();

		$plugins = elgg_get_plugins('active');
		foreach ($plugins as $p) {
			/** @var $dependent ElggPlugin */
			$requires = $p->getManifest()->getRequires();
			if (!empty($requires)) {
				foreach ($requires as $require) {
					if ($require['type'] == 'plugin' && $require['name'] == $id && !in_array($id, $dependants)) {
						$dependants[] = $p->getID();
					}
				}
			}
		}
		foreach ($dependants as $dependant) {
			$result = self::deactivatePlugin($dependant);
			if (!$result) {
				break;
			}
		}

		if (!$result) {
			return false;
		}

		return $plugin->deactivate();
	}

	/**
	 * Activate the plugin as well as all plugins required by it
	 *
	 * @param string $id Plugin ID
	 * @return boolean
	 */
	public static function activatePlugin($id) {

		if (!$id) {
			return false;
		}

		$plugin = elgg_get_plugin_from_id($id);
		if (!$plugin) {
			$plugins = elgg_get_plugins('inactive');
			foreach ($plugins as $p) {
				/* @var $p ElggPlugin */

				$manifest = $p->getManifest();
				if (!$manifest) {
					continue;
				}

				$provides = $manifest->getProvides();
				if (!empty($provides)) {
					foreach ($provides as $provide) {
						if ($provide['type'] == 'plugin' && $provide['name'] == $id) {
							$plugin = $p;
							break;
						}
					}
				}
			}
			if (!$plugin) {
				return false;
			}
		}

		if ($plugin->isActive()) {
			return true;
		}

		$result = true;

		$manifest = $plugin->getManifest();
		if (!$manifest) {
			return false;
		}

		$requires = $manifest->getRequires();

		if (!empty($requires)) {
			foreach ($requires as $require) {
				if ($require['type'] == 'plugin') {
					$result = self::activatePlugin($require['name']);
					if (!$result) {
						break;
					}
				}
			}
		}

		if (!$result) {
			return false;
		}

		return $plugin->activate();
	}

}
