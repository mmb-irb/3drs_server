<?php
namespace App\Classes;
use App\Models\Model;

// TODO: create new classes/ folder with utilities

class Utilities extends Model {

	protected $tablePDB = 'PDB_Entry';

	protected $mime_types = array(
		"none" => "text/plain",
		"log" => "text/plain",
		"txt" => "text/plain",
		"err" => "text/plain",
		"out" => "text/plain",
		"csv" => "text/plain",
		"gff" => "text/plain",
		"gff3"=> "text/plain",
		"wig" => "text/plain",
		"bed" => "text/plain",
		"bedgraph"=> "text/plain",
		"sh"  => "text/plain",
		"pdb" => "text/plain",
		"crd" => "chemical/x-pdb",
		"xyz" => "chemical/x-xyz",
		"cube" => "text/plain",
		"xvg" => "text/plain",
		"yml" => "text/yaml",
		"yaml" => "text/yaml",
		"cpt" => "application/octet-stream",
		"edr" => "application/octet-stream",
		"tpr" => "application/octet-stream",
		"cdf" => "application/octet-stream",
		"xtc" => "application/octet-stream",
		"trr" => "application/octet-stream",
		"gro" => "application/octet-stream",
		"dcd" => "application/octet-stream",
		"exe" => "application/octet-stream",
		"gtar"=> "application/octet-stream",
		"bam" => "application/octet-stream",
		"sam" => "application/octet-stream",
		"tar" => "application/x-tar",
		"gz"  => "application/gzip",
		"tgz" => "application/application/x-gzip",
		"z"   => "application/octet-stream",
		"rar" => "application/octet-stream",
		"bz2" => "application/x-gzip",
		"zip" => "application/zip",
		"h"   => "text/plain",
		"htm" => "text/html",
		"html"=> "text/html",
		"gif" => "image/gif",
		"bmp" => "image/bmp",
		"ico" => "image/x-icon",
		"jfif"=> "image/pipeg",
		"jpe" => "image/jpeg",
		"jpeg"=> "image/jpeg",
		"jpg" => "image/jpeg",
		"rgb" => "image/x-rgb",
		"svg" => "image/svg+xml",
		"json" => "application/json",
		"png" => "image/png",
		"tif" => "image/tiff",
		"tiff"=> "image/tiff",
		"ps"  => "application/postscript",
		"eps" => "application/postscript",
		"js"  => "application/x-javascript",
		"pdf" => "application/pdf",
		"doc" => "application/msword",
		"xls" => "application/vnd.ms-excel",
		"ppt" => "application/vnd.ms-powerpoint",
		"tsv" => "text/tab-separated-values");

	public function getCURLData($url) {

		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);

		return $data;

	}

	// change function name
	public function getPDBList($str) {

		$pdbs = $this->dbPDB->getDocuments($this->tablePDB, ['_id' => ['$regex' => strtoupper($str)]], ['projection' => ['_id'=>1]]);

		$aux = array();
 	 	foreach ($pdbs as $arr) {
		  foreach ($arr as $k) array_push($aux, $k);
 		}

		if(is_array($aux[0])) {
			$seeker = $aux[0];
			$len = count($aux[0]);
		}else {
			$seeker = $aux;
			$len = count($aux);
		}

		return $seeker;

	}

	public function sanitizeFileName($fn) {

		$filename = preg_replace("/([^\w\d\-_.])/", "", $fn);
		$filename = preg_replace("/([\.]{2,})/", "", $filename);

		return $filename;

	}

	public function checkURL($url) {
		$handle = curl_init($url);
		curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

		/* Get the HTML or whatever is linked in $url. */
		$response = curl_exec($handle);

		$out = true;

		/* Check for 404 (file not found). */
		$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		if($httpCode == 404) {
			$out = false;
		}

		curl_close($handle);

		return $out;
	}

	public function newDate() {
		$d = new \DateTime();
		$d->setTimezone(new \DateTimeZone('Europe/Andorra'));
		return $d;
	}
	
	public function newExpDate() {
		$d = new \DateTime();
		$d->add(new \DateInterval('P'.$this->global['expiration'].'D'));
		$d->setTimezone(new \DateTimeZone('Europe/Andorra'));
		return $d;
	}

	public function getContentType($type) {

		if(!isset($this->mime_types[$type])) return "text/plain";
		else return $this->mime_types[$type];

	}











	/*public function downloadFile($file) {
		
  	if (file_exists($file)) {
 
 	    $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $file);
      finfo_close($finfo);
  
      header('Content-Description: File Transfer');
      header('Content-Type: '.$mime);
      header("Content-Transfer-Encoding: Binary");
      header('Content-Disposition: attachment; filename="'.basename($file).'"');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file));
      ob_clean();
      flush();
      readfile($file);
      exit;

  	}

	}*/


	/*public function downloadFromApi($destination, $id, $type, $name, $pdb) {

		set_time_limit(0);
		$pdbdownloaded = fopen ($destination.'/'.$name, 'w+');

		if(isset($pdb)) $ch = curl_init(sprintf($this->global['api']['ligfile'], $type, $id, $pdb));
		else $ch = curl_init(sprintf($this->global['api']['pdbfile'], $type, $id));

		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch, CURLOPT_FILE, $pdbdownloaded); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch); 
		curl_close($ch);
		return fclose($pdbdownloaded);

	}

	public function getMoment() {
  	
		return date("Y/m/d*H:i:s");
 	
	}

	public function startsWith($haystack, $needle) {

     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
	
	}

	public function getExtension($filename) {
	
		return pathinfo($filename, PATHINFO_EXTENSION);	

	}

	public function getHumanDate($date) {
	
		return date("Y/m/d H:i" , $date);
	}

	public function getSize($bytes) {

		if ($bytes >= 1073741824) {
			$bytes = (number_format($bytes / 1073741824, 2) + 0). ' GB';
		}
		elseif ($bytes >= 1048576) {
			$bytes = (number_format($bytes / 1048576, 2) + 0) . ' MB';
		}
		elseif ($bytes >= 1024) {
			$bytes = (number_format($bytes / 1024, 2) + 0). ' KB';
		}
		elseif ($bytes >= 0) {
			$bytes = ($bytes + 0). ' B';
		}
		
		return $bytes;

	}

	public function downloadXML($rest) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $rest);
		$fileContents = curl_exec($ch);
		curl_close($ch);

		$fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);

		$fileContents = trim(str_replace('"', "'", $fileContents));

		$simpleXml = simplexml_load_string($fileContents);

		$json = json_encode($simpleXml);

		$json = str_replace("@","",$json);

		$json = json_decode($json);

		return $json;

	}*/

	
}

