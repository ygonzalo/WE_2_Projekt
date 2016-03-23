<?php
require_once("db.php");
require(".././libs/Slim/Slim.php");

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

require_once("authentication.php");
require_once("data.php");

/*
Encodes response to JSON
*/
function echoResponse($status_code, $response) {
	$app = \Slim\Slim::getInstance();
	$app->status($status_code);
	$app->contentType('application/json; charset=utf-8');

	echo json_encode($response);
}

$app->run();

?>