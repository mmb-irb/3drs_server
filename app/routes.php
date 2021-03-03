<?php

use App\Middleware\CheckDBMiddleware;

// Creating routes
$app->group('', function() use ($container) {

	$this->get('[/]', 'apiController:home');
	// PDB list
	$this->get('/pdb[/{id}]', 'apiController:getPDBList');
	// upload PDB / files
	$this->group('/upload', function() use ($container) {   
		$this->post('/pdb[/]', 'apiController:uploadPDB');
		$this->post('/file[/]', 'apiController:uploadFile');
	});
	// get project metadata
	$this->get('/project/{id}', 'apiController:getProjectInfo');
	// get file
	$this->get('/download/{id}', 'apiController:getFile');
	// update project
	$this->post('/update/{id}', 'apiController:updateProject');

})->add(new CheckDBMiddleware($container));