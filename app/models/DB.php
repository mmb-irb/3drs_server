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
	
		// Convert DateTime objects to array format
		$doc = $this->convertDateTimeToArray($doc);

		$bulk = new \MongoDB\Driver\BulkWrite;
    
    	$bulk->insert($doc);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}

	private function convertDateTimeToArray($data) {
		if ($data instanceof \DateTime) {
			// Convert PHP DateTime to array format to match the old VM format
			return [
				'date' => $data->format('Y-m-d H:i:s.u'),
				'timezone_type' => 3,
				'timezone' => $data->getTimezone()->getName()
			];
		}

		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = $this->convertDateTimeToArray($value);
			}
		}

		// Handle objects (like stdClass from MongoDB queries)
		if (is_object($data) && !($data instanceof \DateTime)) {
			foreach ($data as $key => $value) {
				$data->$key = $this->convertDateTimeToArray($value);
			}
		}

		return $data;
	}

	public function updateDocument($collection, $doc, $set, $options = null) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
   		$bulk->update($doc, $set, $options);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}

	public function deleteDocument($collection, $doc) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
   		$bulk->delete($doc);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}

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
