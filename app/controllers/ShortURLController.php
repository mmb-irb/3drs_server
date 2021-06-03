<?php
namespace App\Controllers;

class ShortURLController extends Controller {

	protected $table = 'shorturls';

    private function genShortID() {
        $shortid = $this->utils->generateShortURL(6);

        if(reset($this->db->getDocuments($this->table, ['_id' => $shortid], []))) {
			$this->genShortID();
		}

        return $shortid;

    }

    public function createNew($id) {

        $shortid = $this->genShortID();

        $data = [
			'_id' => $shortid,
			'project' => $id,
        ];

        $this->db->insertDocument($this->table, $data);

        return $shortid;

    }

    public function getProject($id) {

        if(!$p = reset($this->db->getDocuments($this->table, ['_id' => $id], []))) {
			$code = 404;
            $errMsg = "Requested project not found;";
	    	throw new \Exception($errMsg, $code);
		}

        return ['success', $p->project];

    }

}