<?php
namespace App\Controllers;

class RepresentationsController extends Controller {

	protected $table = 'representations';

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
				'selection' => ''
			];
		}

		$repr = uniqid('');
		$new_repr = [
			'id' => $repr, 
			'name' => $data['name'],
			'visible' => true,
			'opacity' => 1,
			'navigation' => [],
			'structures' => $structures,
			'mol_repr' => 'cartoon',
            'radius' => 5,
            'color_scheme' => 'sstruc'
		];

        $this->db->updateDocument(
            $this->table, 
            ['_id' => $id], 
            ['$push' => ['representations' => $new_repr]]
        );

		return ['success', $new_repr, $repr.' representation of '.$id.' project successfully updated'];
	}	
}