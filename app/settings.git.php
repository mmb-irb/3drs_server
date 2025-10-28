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
			'password' => 'XXX',
			'port'     => 'XXX'
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
		'trajPath' => 'XXXX',
		//'pdbapi' => 'http://mmb.irbbarcelona.org/api/pdb/%s'
		//'pdbapi' => 'https://files.rcsb.org/download/%s.pdb'
		'pdbapi' => 'http://mdb-login.bsc.es/api/pdb/%s',
		'filetypes' => [
			'structures' => [ 'pdb', 'gro' ],
			'trajectories' => [ 'dcd', 'xtc', 'trr', 'binpos', 'netcdf', 'nc' ]
		],
		'expiration' => 20
	],

];
