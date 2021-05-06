<?php
namespace App\Controllers;

class RepresentationsController extends Controller {

	protected $table = 'representations';	

	private function checkRepresentationName($id, $name) {

		//$check = reset($this->db->getDocuments($this->table, ['representations.name' => $name], []));
		$check = reset($this->db->getDocuments($this->table, ['$and' => [ ['_id' => $id], ['representations.name' => $name] ]], []));
		//['$and' => [ ['_id' => $id], ['representations.id' => $repr] ]]

		if($check) return true;
		else return false;
	}

    // create representation
	public function createRepresentation($id, $data) {

		if(!$project = reset($this->db->getDocuments($this->table, ['_id' => $id], []))) {
			$code = 404;
            $errMsg = "Requested project not found;";
	    	throw new \Exception($errMsg, $code);
		}

		$structures = [];
		foreach ($project->files as $file) {
			$structures[] = [
				'id' => $file->id,
				'selection' => [
					'string' => '*',
					'custom' => '',
					'molecules' => []
				]
			];
		}

		// avoid repeated representation name
		/*$name_exists = $this->checkRepresentationName($id, $data['name']);
		if($name_exists) $name = $data['name'].' ('.substr(md5(microtime()),rand(0,26),3).')';
		else*/ $name = $data['name'];

		$repr = uniqid('');
		$new_repr = [
			'id' => $repr, 
			'name' => $name,
			'visible' => true,
			'label' => false,
			'opacity' => 1,
			'settings' => $project->settings,
			'structures' => $structures,
			'mol_repr' => 'line',
            'radius' => [
				'licorice' => [
					'value' => 0.3,
					'min' => 0.2,
					'max' => 1
				],
				'ball+stick' => [
					'value' => 0.3,
					'min' => 0.2,
					'max' => 0.6
				],
				'backbone' => [
					'value' => 0.6,
					'min' => 0.2,
					'max' => 1
				],
				'spacefill' => [
					'value' => 1.5,
					'min' => 1,
					'max' => 3
				]
			],
            'color_scheme' => 'sstruc',
			'color' => '#f1f1f1'
		];

        $this->db->updateDocument(
            $this->table, 
            ['_id' => $id], 
            ['$push' => ['representations' => $new_repr]]
        );

		return ['success', $new_repr, $repr.' representation of '.$id.' project successfully created'];
	}

	// update representation
	public function updateRepresentation($id, $repr, $data) {

		if(!$project = reset($this->db->getDocuments($this->table, ['_id' => $id], []))) {
			$code = 404;
            $errMsg = "Requested project not found;";
	    	throw new \Exception($errMsg, $code);
		}

        if(!$project = reset($this->db->getDocuments($this->table, ['representations.id' => $repr], []))) {
			$code = 404;
            $errMsg = "Requested representation not found;";
	    	throw new \Exception($errMsg, $code);
		}

		$datalist = [];
		$query = [];
		foreach ($data as $key => $value) {
			$datalist[] = $key;
			$query['representations.$.'.$key] = $value;
		}

        $this->db->updateDocument(
            $this->table, 
            ['$and' => [ ['_id' => $id], ['representations.id' => $repr] ]], 
            ['$set' => $query]
        );

		return ['success', 'Data ['.implode(', ', $datalist).'] for '.$repr.' representation of '.$id.' project successfully updated'];
	}

	// delete representation
	public function deleteRepresentation($id, $repr) {

		if(!$project = reset($this->db->getDocuments($this->table, ['_id' => $id], []))) {
			$code = 404;
            $errMsg = "Requested project not found;";
	    	throw new \Exception($errMsg, $code);
		}

        if(!$project = reset($this->db->getDocuments($this->table, ['representations.id' => $repr], []))) {
			$code = 404;
            $errMsg = "Requested representation not found;";
	    	throw new \Exception($errMsg, $code);
		}

		// remove representation from project
		$this->db->updateDocument(
            $this->table, 
            ['$and' => [ ['_id' => $id], ['representations.id' => $repr] ]],
			['$pull' => ['representations' => ['id' => $repr]]]
        );

		// set new current representation
		$project = reset($this->db->getDocuments($this->table, ['_id' => $id], []));
		$new_curr_repr = end($project->representations)->id;
		$this->db->updateDocument($this->table, ['_id' => $id], ['$set' => ['currentRepresentation' => $new_curr_repr]]);

		return ['success', $new_curr_repr, $repr.' representation of '.$id.' project successfully removed'];
	}
}