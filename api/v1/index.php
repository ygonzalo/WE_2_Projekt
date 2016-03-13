<?php
require_once 'db.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

require_once 'auth.php';

/*
Encodes response to JSON
*/
function echoResponse($status_code, $response) {
	$app = \Slim\Slim::getInstance();
	$app->status($status_code);
	$app->contentType('application/json');

	echo json_encode($response);
}



$app->run();

?>