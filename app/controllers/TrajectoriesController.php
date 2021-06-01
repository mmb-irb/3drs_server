<?php
namespace App\Controllers;

class TrajectoriesController extends Controller {

	protected $table = 'representations';

	// PRIVATE FUNCTIONS

	// check uploaded files
	private function checkInputFiles($files) {

		foreach ($files as $key => $file) {
			// error
			if($file->getError() !== UPLOAD_ERR_OK) {
				return [false, "Error: some of the files was not correctly uploaded."];
			}
			// not empty
			if($file->getSize() === 0) {
				return [false, "Error: empty files not allowed."];
			}
			// correct type
			$ext =  pathinfo($file->getClientFilename())['extension'];
			if(!in_array($ext, $this->global['filetypes']['trajectories'])) {
				return [false, "Error: $ext extension is not allowed."];
			}
		}

		return [true, "Files ok."];

	}

	// save uploaded files to **GridFS** (now FS)
	private function saveTrajectory($project, $structure, $files) {
	
		/*$files_id = [];
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

		return $files_id;*/

		// create project folder
		//var_dump($this->global['trajPath'].$project);
		if(!file_exists($this->global['trajPath'].$project)) $dirOk = mkdir($this->global['trajPath'].$project, 0755);
		//var_dump($dirOk);

		$file = reset($files);
		$fname = $file->getClientFilename();
		$filepath = $project.'/'.$structure.'.'.pathinfo($fname)['extension'];
		$size = $file->getSize();
		//var_dump($this->global['trajPath'].$filepath, $size);
		$file->moveTo($this->global['trajPath'].$filepath);

		return [$filepath, $size];

	}

	// PUBLIC FUNCTIONS

	public function addTrajectory($input, $files) {

		$project = $input['project'];
		$structure = $input['structure'];

		list($check_input, $msg_check_input) = $this->checkInputFiles($files);
		if(!$check_input) return ['error', null, null, $msg_check_input];

		list($path, $size) = $this->saveTrajectory($project, $structure, $files);

		$data = [
			'path' => $path,
			'size' => $size,
			'settings' => [
				'autoplay' => false,
				'step' => 1,
				'timeout' => 100,
				'init' => 0,
				'end' => null,
				'range' => [ 0, null],
				'loop' => false,
				'interpolation' => '',
				'bounce' => false
			],
			'uploadDate' => $this->utils->newDate(),
		];

		$this->db->updateDocument(
            $this->table, 
            ['$and' => [ ['_id' => $project], ['files.id' => $structure] ]],
			['$set' => ['files.$.trajectory' => $data]]
        );

		$this->dataController->updateLastUpdate($project);

		return ['success', $project, $data, 'Trajectory successfully added to '.$structure.' structure'];

	}

	public function updateTrajectory($id, $input) {

		$structure = $input['structure'];
		$settings = $input['settings'];

		if(!$project = reset($this->db->getDocuments($this->table, ['_id' => $id], []))) {
			$code = 404;
            $errMsg = "Requested project not found;";
	    	throw new \Exception($errMsg, $code);
		}

        if(!$project = reset($this->db->getDocuments($this->table, ['files.id' => $structure], []))) {
			$code = 404;
            $errMsg = "Requested structure not found;";
	    	throw new \Exception($errMsg, $code);
		}

		$this->db->updateDocument(
            $this->table, 
            ['$and' => [ ['_id' => $id], ['files.id' => $structure] ]], 
            ['$set' => ['files.$.trajectory.settings' => $settings]]
        );

		$this->dataController->updateLastUpdate($id);

		return ['success', 'Settings for '.$structure.' trajectory of '.$id.' project successfully updated'];

	}

}