<?php
require_once("db.php");
require(".././libs/Slim/Slim.php");

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$routeFiles = (array) glob('routes/*.php');
foreach($routeFiles as $routeFile) {
	require_once($routeFile);
}
//require_once("documentation.php");

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