<?php
namespace App\Controllers;

class DataController extends Controller {

	protected $table = 'representations';

    // retrieve project info
	public function retrieveProjectInfo($id) {
        return reset($this->db->getDocuments($this->table, ['_id' => $id], []));
	}

	// retrieve file info
	public function retrieveFileInfo($file) {
		return $this->db->getMeta($file);
	}

    // retrieve data
	public function retrieveData($file) {
        return $this->db->findById($file);
	}

	// update data
	public function updateData($id, $data) {

		if(!reset($this->db->getDocuments($this->table, ['_id' => $id], []))) {
			$code = 404;
            $errMsg = "Requested project not found;";
	    	throw new \Exception($errMsg, $code);
		}

		$dataset = [];
		$datalist = [];
		foreach ($data as $key => $value) {
			$datalist[] = $key;
			$dataset[$key] = $value;
		}

		$this->db->updateDocument($this->table, ['_id' => $id], ['$set' => $dataset]);

		$this->updateLastUpdate($id);

		return ['success', 'Data ['.implode(', ', $datalist).'] for '.$id.' project successfully updated'];
	}

	// update lastUpdate
	public function updateLastUpdate($id) {

		if(!reset($this->db->getDocuments($this->table, ['_id' => $id], []))) {
			$code = 404;
            $errMsg = "Requested project not found;";
	    	throw new \Exception($errMsg, $code);
		}

		$this->db->updateDocument($this->table, ['_id' => $id], ['$set' => ['projectSettings.lastUpdate' => $this->utils->newDate()] ]);
		return true;
	}

	// retrieve settings of multiple projects
	public function retrieveProjectSettings($data) {
		$output = $this->db->getDocuments($this->table, ['_id' => [ '$in' => $data ] ], [ 'projection' => [ 'projectSettings' => 1 ] ]);

		if(!$output) {
			$code = 404;
            $errMsg = "Requested data not found;";
	    	throw new \Exception($errMsg, $code);
		}

		return ['success', $output];
	}
	
	// retrieve settings of multiple projects
	public function retrievePublicProjects($data) {
		$output = $this->db->getDocuments($this->table, 
			['$and' => [ 
				['projectSettings.status' => 'rs' ], 
				['projectSettings.public' => true]
			]], 
			[ 
				'sort' => [ 'projectSettings.uploadDate' => -1 ],
				'limit' => 10,  
				'projection' => [ 'projectSettings' => 1 ] 
			]
		);

		if(!$output) {
			$code = 404;
            $errMsg = "Requested data not found;";
	    	throw new \Exception($errMsg, $code);
		}

		return ['success', $output];
	}

}