<?php

return [

	'settings' => [
		'displayErrorDetails' => true,
		'logger' => [
			'name' => '3dRS',
			'path' => __DIR__ . '/../logs/app.log',
			'level' => \Monolog\Logger::DEBUG,
		],
		'db' => [
			'host'     => 'XXX',
			'database' => 'XXX',
			'username' => 'XXX',
			'password' => 'XXX'
		],
		'dbPDB' => [
			'host'     => 'XXX',
			'database' => 'XXX',
			'username' => 'XXX',
			'password' => 'XXX'
		]
	],

	'globals' => [
		'shortProjectName' => '3dRS',
		'longProjectName' => '3-dimensional structure Representation Sharing',
		'pdbapi' => 'http://mmb.irbbarcelona.org/api/pdb/%s'
	],

];
