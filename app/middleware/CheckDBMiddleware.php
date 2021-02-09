<?php

namespace App\Middleware;

class CheckDBMiddleware extends Middleware {

	public function __invoke($request, $response, $next) {

		if(!$this->db->checkDB()) {

			throw new \Exception("Unable to found Mongo server", 503);

		}
	
		$response = $next($request, $response);

		return $response;

	}


}
