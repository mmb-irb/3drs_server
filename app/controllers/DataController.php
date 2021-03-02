<?php
namespace App\Controllers;

class DataController extends Controller {

	protected $table = 'representations';

    // retrieve project info
	public function retrieveProjectInfo($representation) {
        return reset($this->db->getDocuments($this->table, ['_id' => $representation], []));
	}

	// retrieve file info
	public function retrieveFileInfo($file) {
		return $this->db->getMeta($file);
	}

    // retrieve data
	public function retrieveData($file) {
        return $this->db->findById($file);
	}

}