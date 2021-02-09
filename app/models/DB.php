<?php
namespace App\Models;

class DB {

	public function __construct($mng, $database) {

		$this->mng = $mng;
		$this->database = $database;

	}

	public function checkDB() {
		try {
			$this->mng->executeCommand('test', new \MongoDB\Driver\Command(['ping' => 1]));
		} catch(\MongoDB\Driver\Exception\ConnectionException $e) {
			return false;
		}
		return true;
	}

	public function getDocuments($collection, $filter, $options) {
		
		$query = new \MongoDB\Driver\Query($filter, $options);
	
		$doc = $this->mng->executeQuery($this->database.".".$collection, $query);

		return $doc->toArray();

	}

	public function insertDocument($collection, $doc) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
    	$bulk->insert($doc);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}

	public function updateDocument($collection, $doc, $set) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
   		$bulk->update($doc, $set);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}

	public function deleteDocument($collection, $doc) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
   		$bulk->delete($doc);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}

	public function dropCollection($collection) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
   		$bulk->delete([]);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}

	public function sum($collection, $field) {

		$command = new \MongoDB\Driver\Command([

			'aggregate' => $collection,
			'pipeline' => [
					['$group' => ['_id' => null, 'sum' => ['$sum' => '$'.$field]]],
			],
			'cursor' => new \stdClass,

		]);

		$cursor = $this->mng->executeCommand($this->database, $command);
	
		foreach ($cursor as $document) {
 		   return $document->sum;
		}	

	}

	public function count($collection) {

		$command = new \MongoDB\Driver\Command(

			[ 'count' => $collection ]
			
		);

		$cursor = $this->mng->executeCommand($this->database, $command);
	
		foreach ($cursor as $document) {
 		   return $document->n;
		}	
	
	}

	// GRIDFS
	// TO CKECK!!!

	public function insertFile($id, $path, $fsname) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		$file = fopen($path, 'rb');
		$meta = [ 'repr_id' => $id ];
		return $bucket->uploadFromStream($fsname, $file, ['metadata' => $meta]);

	}

	public function insertStringToFile($id, $fsname, $string) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		$meta = [ 'repr_id' => $id];
		$stream = $bucket->openUploadStream($fsname, ['metadata' => $meta]);

		fwrite($stream, $string);
		$fileID = $bucket->getFileIdForStream($stream);
		fclose($stream);

		return $fileID;

	}

	/* BAC

	public function findByMeta($options) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		return $bucket->find(['metadata' => $options]);

	}

	public function findByName($name) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		if($this->fsFileExists($name)) {
			$stream = $bucket->openDownloadStreamByName($name);
			return stream_get_contents($stream);
		} else {
			return false;
		}
			
	}

	public function findBinaryByName($name) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		if($this->fsFileExists($name)) {
			$stream = $bucket->openDownloadStreamByName($name);
			return $stream;
		} else {
			return false;
		}
			
	}

	public function removeFileByID($id) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);
		$bucket->delete($id);

	}

	public function fsFileExists($name) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		$cursor = $bucket->find(['filename' => $name]);

		$count = 0;
		foreach($cursor as $c) $count ++;

		return (bool) $count;

	} */

	/* BIOB  API

	public function findById($id) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		$stream = $bucket->openDownloadStream($id);
		return stream_get_contents($stream);
			
	}

	public function findByMeta($file_id) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		return $bucket->find(['metadata.file_id' => $file_id]);

	}
	
	public function findBySetOfMeta($metadata) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		return $bucket->find($metadata);

	}

	public function findByName($name) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		if($this->fsFileExists($name)) {
			$stream = $bucket->openDownloadStreamByName($name);
			return stream_get_contents($stream);
		} else {
			return false;
		}
			
	}

	public function fsFileExists($name) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		$cursor = $bucket->find(['filename' => $name]);

		$count = 0;
		foreach($cursor as $c) $count ++;

		return (bool) $count;

	}*/
	
}
