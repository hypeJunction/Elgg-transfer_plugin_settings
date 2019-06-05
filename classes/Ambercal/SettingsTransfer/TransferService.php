<?php

namespace Ambercal\SettingsTransfer;

use Elgg\Cli\PluginsHelper;
use ElggPlugin;

class TransferService {

	use PluginsHelper;

	/**
	 * Prepares an array of all plugins with their status, priority, and settings
	 *
	 * @param array $options Export options
	 *
	 * @uses bool $options['unserialize'] Unserialize json/php serialized values
	 * @return array
	 */
	public function export(array $options = []) {
		$unserialize = (bool) elgg_extract('unserialize', $options, false);
		$export = [];

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
						$settings[$key] = [
							//'serialized_value' => $value,
							'value' => $unserialized ? : $json,
							'serialization' => $unserialized ? 'serialize' : 'json_encode'
						];
					}
				}
			}

			$export[$id] = [
				'active' => $active,
				'priority' => $priority,
				'settings' => $settings,
			];
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
	 * @throws \DatabaseException
	 */
	public function import(array $data, array $options = []) {
		$modify_active = (bool) elgg_extract('active', $options, false);
		$modify_priority = (bool) elgg_extract('priority', $options, false);
		$modify_settings = (bool) elgg_extract('settings', $options, false);
		$unset_settings = (bool) elgg_extract('settings_unset', $options, false);

		$plugins = elgg_get_plugins('all');

		$error = 0;

		foreach ($plugins as $plugin) {
			/* @var $plugin ElggPlugin */

			$id = $plugin->getID();

			if (!isset($data[$id])) {
				if ($modify_active && $id !== 'ambercal_settings_transfer') {
					try {
						$this->deactivate($id, true);
					} catch (\Exception $ex) {
						register_error($id . ' (deactivate): ' . $ex->getMessage());
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
					try {
						$this->activate($id, true);
					} catch (\Exception $ex) {
						register_error($id . ' (activate): ' . $ex->getMessage());
					}
				} else if (!$data[$id]['active']) {
					try {
						$this->deactivate($id, true);
					} catch (\Exception $ex) {
						register_error($id . ' (deactivate): ' . $ex->getMessage());
					}
				}
			}
		}

		return $error;
	}
}
