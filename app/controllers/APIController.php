<?php
namespace App\Controllers;

class APIController extends Controller {

      public function home($request, $response, $args) {
            $output = ['Back-end for '.$this->global['longProjectName'].' web application'];
            return $response
                        /*->withHeader('Access-Control-Allow-Origin', '*')
                        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')*/
                        ->withJson($output, 200, JSON_PRETTY_PRINT);
      }

	public function getPDBList($request, $response, $args) {
            $output = $this->utils->getPDBList($args['id']);
            return $response->withJson($output, 200, JSON_PRETTY_PRINT);
      }	

      public function uploadPDB($request, $response, $args) {

            $input = $request->getParsedBody();
            list($status, $id, $message) = $this->reprController->createNewRepresentation($input['structures'], 0);

            $output = ['status' => $status, 'id' => $id, 'message' => $message];
            return $response->withJson($output, 200, JSON_PRETTY_PRINT);
      }

      public function uploadFile($request, $response, $args) {
            $files = $request->getUploadedFiles();
            list($status, $id, $message) = $this->reprController->createNewRepresentation($files, 1);

            $output = ['status' => $status, 'id' => $id, 'message' => $message];
            return $response->withJson($output, 200, JSON_PRETTY_PRINT);
      }

}
