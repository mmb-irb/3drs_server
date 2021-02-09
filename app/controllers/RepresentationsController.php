<?php
namespace App\Controllers;

class RepresentationsController extends Controller {

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
			$files_id[] = $this->db->insertStringToFile($id, $filepath, file_get_contents($file->file));
		}

		return $files_id;

	}

	// save PDB files to GridFS
	private function savePDBFiles($id, $files) {
	
		$files_id = [];
		foreach ($files as $file) {
			$url = sprintf($this->global['pdbapi'], $file);
			$filepath = $id.'/'.$file;
			$files_id[] = $this->db->insertStringToFile($id, $filepath, file_get_contents($url));
		}

		return $files_id;

	}
	
	// create new representation from uploaded files
	private function newFromUploadedFile($files) {
		list($check_input, $msg_check_input) = $this->checkInputFiles($files);
		if(!$check_input) return ['error', null, $msg_check_input];

		// create representation ID
		$id = uniqid('', true);
		// generate representation data
		$data["_id"] = $id;

		// save files to GridFS
		$data["files"] = $this->saveFiles($id, $files);

		// create entry in DB
		$this->insertData($data);

		return ['success', $id, 'New representation '.$id.' succesfully created.'];
	}

	// create new representation from PDB files
	private function newFromPDB($files) {

		list($check_input, $msg_check_input) = $this->checkPDBFiles($files);
		if(!$check_input) return ['error', null, $msg_check_input];

		// create representation ID
		$id = uniqid('', true);
		// generate representation data
		$data["_id"] = $id;

		// save files to GridFS
		$data["files"] = $this->savePDBFiles($id, $files);

		// create entry in DB
		$this->insertData($data);

		return ['success', $id, 'New representation '.$id.' succesfully created.'];
	}

	// PUBLIC FUNCTIONS

	// save files and create new Representation
	public function createNewRepresentation($files, $type) {

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