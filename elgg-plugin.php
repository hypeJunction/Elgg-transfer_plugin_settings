<?php

return [
	'bootstrap' => \Ambercal\SettingsTransfer\Bootstrap::class,

	'actions' => [
		'plugins/settings/export' => [
			'controller' => \Ambercal\SettingsTransfer\ExportSettings::class,
			'access' => 'admin',
		],
		'plugins/settings/import' => [
			'controller' => \Ambercal\SettingsTransfer\ImportSettings::class,
			'access' => 'admin',
		],
	]
];