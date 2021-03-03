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

      public function getProjectInfo($request, $response, $args) {
            
            $output = $this->dataController->retrieveProjectInfo($args['id']);
            if(!$output) {
                  $code = 404;
                  $errMsg = "Requested project not found;";
	            throw new \Exception($errMsg, $code);
            }
            return $response->withJson($output, 200, JSON_PRETTY_PRINT);

      }

      public function getFile($request, $response, $args) {

            $f = $this->dataController->retrieveData($args['id']);
            $i = $this->dataController->retrieveFileInfo($args['id']);

            $contentype = $this->utils->getContentType($i->file_type);
            $response = $response->withHeader('Content-Type', $contentype)
                        ->withHeader('Content-Description', 'File Transfer')
                        ->withHeader('Content-Disposition', 'attachment; filename="'.$i->name.'"')
                        ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                        ->withHeader('Pragma', 'public');

            echo $f;

            return $response;
      }

      public function updateProject($request, $response, $args) {

            $input = $request->getParsedBody();

            list($status, $message) = $this->dataController->updateData($args['id'], $input);

            return $response->withJson(['status' => $status, 'message' => $message], 200, JSON_PRETTY_PRINT);

      }

      // TODO SPECAIL FOR NEW REPRESENTATION? UNIQUE ID'S??? SEND ALL DATA FROM CLIENT AND PUSH NEW ONE WITH UNIQUE ID OR UPDATE

}
