<?php
namespace App\Controllers;

class ProjectsController extends Controller {

	protected $table = 'representations';

	// PRIVATE FUNCTIONS

	// check uploaded files
	private function checkInputFiles($files) {

		foreach ($files as $key => $file) {
			if($file->getError() !== UPLOAD_ERR_OK) {
				return [false, "Error: some of the files was not correctly uploaded."];
			}
			if($file->getSize() === 0) {
				return [false, "Error: empty files not allowed."];
			}
		}

		return [true, "Files ok."];

	}

	// check PDB files
	private function checkPDBFiles($files) {

		foreach ($files as $file) {
			$url = sprintf($this->global['pdbapi'], $file);
			if(!$this->utils->checkURL($url))
				return [false, "Error: $file id was not found."];
		}

		return [true, "PDB files ok."];

	}

	// insert data into DB
	private function insertData($data) { 
		
		// create document
		$this->db->insertDocument($this->table, $data);

		return true;
	
	}

	// save uploaded files to GridFS
	private function saveFiles($id, $files) {
	
		$files_id = [];
		foreach ($files as $key => $file) {
			$filepath = $id.'/'.$this->utils->sanitizeFileName($file->getClientFilename());
			// TODO: THINK IF project_id IS NECESSARY OR TO PUT IT IN AN ARRAY WITH ALL THE PROJECTS
			// THIS FILE BELONGS TO (SHARING CASES)
			$meta = [ 'project_id' => $id, 'file_type' => pathinfo($filepath)['extension'], 'name' =>  pathinfo($filepath)['filename'].'.'.pathinfo($filepath)['extension']];
			$files_id[] = [
				'id' => $this->db->insertStringToFile($meta, $filepath, file_get_contents($file->file)),
				'name' => pathinfo($filepath)['filename'],
				'ext' => pathinfo($filepath)['extension']
			];
		}

		return $files_id;

	}

	// save PDB files to GridFS
	private function savePDBFiles($id, $files) {
	
		$files_id = [];
		foreach ($files as $file) {
			$url = sprintf($this->global['pdbapi'], $file);
			$filepath = $id.'/'.$file;
			// TODO: THINK IF project_id IS NECESSARY OR TO PUT IT IN AN ARRAY WITH ALL THE PROJECTS
			// THIS FILE BELONGS TO (SHARING CASES)
			$meta = [ 'project_id' => $id, 'file_type' => 'pdb', 'name' =>  $file.'.pdb' ];
			$files_id[] = [
				'id' => $this->db->insertStringToFile($meta, $filepath, file_get_contents($url)),
				'name' => $file,
				'ext' => '.pdb'
			];
		}

		return $files_id;

	}

	private function generateProjectData($id, $files) {

		$content_files = [];
		foreach ($files as $file) {
			$content_files[] = [
				'id' => $file['id'],
				'name' => $file['name'],
				'ext' => $file['ext'],
				'type' => null,
				'trajectory' => null
			];
		}

		$default_representation = uniqid('');

		$data = [
			'_id' => $id,
			'status' => 'w',
			'orientation' => null,
			'uploadDate' => $this->utils->newDate(),
			'background' => '#f1f1f1',
			'files' => $content_files,
			'currentStructure' => $content_files[0]['id'],
			'currentRepresentation' => $default_representation,
			'defaultRepresentation' => $default_representation,
			'structure' => [],
			'settings' => [],
			'representations' => [
				[
					'id' => $default_representation, 
					'name' => 'Default',
					'visible' => true,
					'opacity' => 1
				]
			]
		];

		return $data;
	}

	// create new project from uploaded files
	private function newFromUploadedFile($files) {
		list($check_input, $msg_check_input) = $this->checkInputFiles($files);
		if(!$check_input) return ['error', null, $msg_check_input];

		// create project ID
		$id = uniqid('', true);

		// generate data
		$data = $this->generateProjectData($id, $this->saveFiles($id, $files));

		// create entry in DB
		$this->insertData($data);

		return ['success', $id, 'New project '.$id.' succesfully created.'];
	}

	// create new project from PDB files
	private function newFromPDB($files) {
		list($check_input, $msg_check_input) = $this->checkPDBFiles($files);
		if(!$check_input) return ['error', null, $msg_check_input];

		// create project ID
		$id = uniqid('', true);

		// generate data
		$data = $this->generateProjectData($id, $this->savePDBFiles($id, $files));

		// create entry in DB
		$this->insertData($data);

		return ['success', $id, 'New project '.$id.' succesfully created.'];
	}

	// PUBLIC FUNCTIONS

	// save files and create new Project
	public function createNewProject($files, $type) {

		switch($type) {
			case 0: return $this->newFromPDB($files);
					break;
			case 1: return $this->newFromUploadedFile($files);
					break;
			default:
					return ['error', null, 'Something went wrong, please try again'];
		}

	}

	

}