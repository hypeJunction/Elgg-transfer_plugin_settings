<?php
/**
 *
 */

namespace Ambercal\SettingsTransfer;


use Elgg\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportSettings {

	public function __invoke(Request $request) {
		$file = elgg_get_uploaded_file('json');

		if ($file instanceof UploadedFile && $file->isValid()) {
			$contents = @file_get_contents($file->getPathname());
			$json = @json_decode($contents, true);
		}

		if (empty($json)) {
			$error = elgg_echo('admin:plugin_settings_transfer:upload:invalid_json');

			return elgg_error_response($error);
		}

		$options = $request->getParam('options', []);

		$import_options = [];

		foreach ($options as $option) {
			$import_options[$option] = true;
		}

		$svc = new TransferService();
		$errors = $svc->import($json, $import_options);

		if ($errors) {
			return elgg_error_response(elgg_echo('admin:plugin_settings_transfer:import:error', [$errors]));
		}

		elgg_flush_caches();

		return elgg_ok_response('', elgg_echo('admin:plugin_settings_transfer:import:success'));
	}
}