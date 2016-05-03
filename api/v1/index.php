<?php
date_default_timezone_set('Europe/Berlin');

require_once("db.php");
require(".././libs/Slim/Slim.php");

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

//Routes
require_once("routes/friends.php");
require_once("routes/movies.php");
require_once("routes/user.php");

require_once("documentation.php");

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
