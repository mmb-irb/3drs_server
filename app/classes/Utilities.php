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

		/*$pdbs = $this->dbPDB->getDocuments($this->tablePDB, ['_id' => ['$regex' => strtoupper($str)]], ['projection' => ['_id'=>1]]);

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

		return $seeker;*/

		$url = sprintf($this->global['pdbapi'], "?fields=_id");

		// Fetch data from URL
		$data = file_get_contents($url);

		if (empty($data)) {
			return [];
		}

		// Parse the plain text response
		$lines = explode("\n", $data);
		$results = array();
		
		// Convert search string to uppercase for case-insensitive matching
		$searchStr = strtoupper($str);
		
		foreach ($lines as $line) {
			$line = trim($line);
			
			// Skip empty lines and comment lines (starting with #)
			if (empty($line) || strpos($line, '#') === 0) {
				continue;
			}
			
			// Extract the PDB ID (first column before tab)
			$parts = explode("\t", $line);
			$pdbId = trim($parts[0]);
			
			// Check if the PDB ID contains the search string
			if (!empty($pdbId) && strpos(strtoupper($pdbId), $searchStr) !== false) {
				array_push($results, $pdbId);
			}
		}

		// Sort results alphabetically
		sort($results);

		return $results;

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

	public function generateShortURL($len) {
		$hex = md5(str_shuffle("a7B0CDef6gH2iJK4L5m3noPQrSt1uVW89xyZ") . uniqid("", true));

		$pack = pack('H*', $hex);

		$uid = base64_encode($pack);        // max 22 chars

		$uid = ereg_replace("[^A-Za-z0-9]", "", $uid);    // mixed case

		if ($len<4) $len=4;
    	if ($len>128) $len=128;

		while (strlen($uid)<$len) $uid = $uid . gen_uuid(22);     // append until length achieved

		return substr($uid, 0, $len);
	}
	
}

