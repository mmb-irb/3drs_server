<?php

// Get the container
$container = $app->getContainer();

//Override the default Not Found Handler
$container['notFoundHandler'] = function ($c) {
	$code = 404;
    $errMsg = "Requested page not found;";
	throw new \Exception($errMsg, $code);
};

// monolog
$container['logger'] = function ($c) {
	$settings = $c->get('settings')['logger'];
	$logger = new Monolog\Logger($settings['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	//$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
	$logger->pushHandler(new Monolog\Handler\RotatingFileHandler($settings['path'], 30, $settings['level']));
	return $logger;
};

// DB dependency
$container['db'] = function ($c) {
	$db = $c->get('settings')['db'];
	$mng = new \MongoDB\Driver\Manager("mongodb://".$db['username'].":".$db['password']."@".$db['host']);
	return new \App\Models\DB($mng, $db['database']);
};

// DB PDB dependency
$container['dbPDB'] = function ($c) {
	$db = $c->get('settings')['dbPDB'];
	$mng = new \MongoDB\Driver\Manager("mongodb://".$db['username'].":".$db['password']."@".$db['host']);
	return new \App\Models\DB($mng, $db['database']);
};

// MODELS
$container['utils'] = function($c) {
	return new \App\Classes\Utilities($c);
};

// CONTROLLERS
$container['apiController'] = function($c) {
	return new \App\Controllers\APIController($c);
};

$container['reprController'] = function($c) {
	return new \App\Controllers\RepresentationsController($c);
};

$container['projectsController'] = function($c) {
	return new \App\Controllers\ProjectsController($c);
};

$container['trajectoriesController'] = function($c) {
	return new \App\Controllers\TrajectoriesController($c);
};

$container['dataController'] = function($c) {
	return new \App\Controllers\DataController($c);
};

$container['shortURLController'] = function($c) {
	return new \App\Controllers\ShortURLController($c);
};

//GLOBALS
$container['global'] = function($c) {
	return $c->get('globals');
};

//HANDLERS
$container['errorHandler'] = function ($c) {
    return new \App\Handlers\Error($c['logger'], $c);
};
