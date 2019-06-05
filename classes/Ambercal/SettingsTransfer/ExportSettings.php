<?php

namespace Ambercal\SettingsTransfer;

use Elgg\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportSettings {

	public function __invoke(Request $request) {
		$options = $request->getParam('options', []);

		$export_options = [];
		foreach ($options as $option) {
			$export_options[$option] = true;
		}

		$dt = new \DateTime('now');
		$filename = implode('-', ['settings', $dt->format('Y-m-d-H-i')]);

		$svc = new TransferService();
		$export = $svc->export($export_options);
		$json = json_encode($export);

		$dir = elgg_get_data_path() . 'settings_transfer/';
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		$filepath = "{$dir}${filename}.json";

		$fh = fopen($filepath, 'w');
		fwrite($fh, $json);
		fclose($fh);

		$response = BinaryFileResponse::create($filepath, 200, [
			'Content-Type' => 'application/json; charset=UTF-8',
		], true, 'attachment');

		$response->send();

		exit;
	}
}