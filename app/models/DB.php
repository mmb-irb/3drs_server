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

	public function updateDocument($collection, $doc, $set, $options = null) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
   		$bulk->update($doc, $set, $options);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}

	/*public function deleteDocument($collection, $doc) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
   		$bulk->delete($doc);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}

	public function dropCollection($collection) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
   		$bulk->delete([]);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}*/

	/*public function sum($collection, $field) {

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
	
	}*/

	// GRIDFS
	// WRITE
	public function insertFile($meta, $path, $fsname) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		$file = fopen($path, 'rb');
		$fileID = $bucket->uploadFromStream($fsname, $file, ['metadata' => $meta]);
		return (string) new \MongoDB\BSON\ObjectId($fileID);

	}

	public function insertStringToFile($meta, $fsname, $string) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);

		$stream = $bucket->openUploadStream($fsname, ['metadata' => $meta]);

		fwrite($stream, $string);
		$fileID = $bucket->getFileIdForStream($stream);
		fclose($stream);

		return (string) new \MongoDB\BSON\ObjectId($fileID);

	}

	// READ
	public function findById($id) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);
		try {
			$stream = $bucket->openDownloadStream(new \MongoDB\BSON\ObjectId($id));
			return stream_get_contents($stream);
		} catch(\Exception $e) {
			return false;
		}
		
	}

	public function getMeta($id) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);
		try {
			$stream = $bucket->openDownloadStream(new \MongoDB\BSON\ObjectId($id));
			$metadata = $bucket->getFileDocumentForStream($stream);
			return $metadata->metadata;
		} catch(\Exception $e) {
			return false;
		}

	}

	public function findByMeta($metadata) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);
		try {
			return $bucket->find($metadata);
		} catch(\Exception $e) {
			return false;
		}

	}

	public function findByName($name) {

		$bucket = new \MongoDB\GridFS\Bucket($this->mng, $this->database);
		try {
			$stream = $bucket->openDownloadStreamByName($name);
			return stream_get_contents($stream);
		} catch(\Exception $e) {
			return false;
		}
			
	}
	
}
