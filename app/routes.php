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
		$this->post('/traj[/]', 'apiController:uploadTrajectory');
	});
	// get project metadata
	$this->get('/project/{id}', 'apiController:getProjectInfo');
	// get file
	$this->get('/download/{id}', 'apiController:getFile');
	// update project
	$this->post('/update/{id}', 'apiController:updateProject');
	// representations
	$this->group('/representation', function() use ($container) {  
		// create new representation in project 
		$this->post('/{id}', 'apiController:newRepresentation');
		// update representation
		$this->patch('/{id}/{repr}', 'apiController:updateRepresentation');
		// delete representation
		$this->delete('/{id}/{repr}', 'apiController:deleteRepresentation');
	});
	// share project
	$this->post('/share/{id}', 'apiController:shareProject');
	// fork project
	$this->post('/fork/{id}', 'apiController:forkProject');
	// trajectories
	$this->group('/trajectory', function() use ($container) {  
		// update trajectory
		$this->patch('/{id}', 'apiController:updateTrajectory');
	});

})->add(new CheckDBMiddleware($container));