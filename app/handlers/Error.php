<?php
 
namespace App\Handlers;
 
use Monolog\Logger;

final class Error extends \Slim\Handlers\Error
{
    protected $logger;

    protected $container;

    protected $code;

    protected $message;
 
    public function __construct(Logger $logger, $container) {
        $this->logger = $logger;
        $this->container = $container;
    }
 
    public function __invoke($request, $response, \Exception $exception) {

		// Log the message
		$this->logger->error("API - ".$exception->getMessage(), ["code" => $exception->getCode()]);

		// Return Exception data as JSON
		$data = [
			'code'    => $exception->getCode(),
			'message' => $exception->getMessage(),
		];

		$response = $response->withStatus($data['code'])
		->withHeader('Content-Type', 'application/json')
		->write(json_encode($data,JSON_PRETTY_PRINT));

		return $response;

	}
}
