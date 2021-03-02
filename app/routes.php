<?php

use App\Middleware\CheckDBMiddleware;

// Creating routes
$app->group('', function() use ($container) {

	$this->get('[/]', 'apiController:home');
	$this->get('/pdb[/{id}]', 'apiController:getPDBList');
	$this->post('/upload/pdb[/]', 'apiController:uploadPDB');
	$this->post('/upload/file[/]', 'apiController:uploadFile');
	$this->get('/project/{id}', 'apiController:getProjectInfo');
	$this->get('/download/{id}', 'apiController:getFile');

})->add(new CheckDBMiddleware($container));