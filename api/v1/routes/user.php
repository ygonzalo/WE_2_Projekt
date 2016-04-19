<?php

require_once("helper_methods.php");

//GET Session
$app->get('/session', function() {
	$db = new DB();
	$session = $db->getSession();
	$response = array();
	$response['userID'] = $session['userID'];
	$response['email'] = $session['email'];
	$response['name'] = $session['name'];
	echoResponse(200, $response);
});

//POST Login
$app->post('/user/login', function() use ($app) {

	$req = json_decode($app->request->getBody());
	$db = new DB();

	$password = $req->user->password;
	$email = $req->user->email;
	$user_stmt = $db->preparedStmt("SELECT `userID`,`name`,`password`,`email` FROM `user` WHERE `email`= ?");
	$user_stmt->bind_param('s',$email);
	$user_stmt->execute();
	$user_stmt->bind_result($db_userID,$db_name,$db_password,$db_email);
	$user_stmt->store_result();
	$user_stmt->fetch();
	if($user_stmt->num_rows>0) {
		if(password_verify($password,$db_password)){
			if(!isset($_SESSION)){
				session_start();
			}
			$_SESSION['userID'] = $db_userID;
			$_SESSION['email'] = $db_email;
			$_SESSION['name'] = $db_name;

			$response['status'] = 'success';
			$response['code'] = 202;
			echoResponse(200, $response);
		} else {
			$response['status'] = "error";
			$response['code'] = 502;
			echoResponse(201, $response);
		}
	}else {
		$response['status'] = "error";
		$response['code'] = 503;
		echoResponse(201, $response);
	}
	$user_stmt->free_result();
	$user_stmt->close();

});

//POST Signup
$app->post('/user/signUp', function() use ($app) {
	$req = json_decode($app->request->getBody());
	$response = array();
	$db = new DB();

	$name = $req->user->name;
	$email = $req->user->email;
	$password = $req->user->password;
	$user_stmt = $db->preparedStmt("SELECT 1 FROM user where email=?");
	$user_stmt->bind_param('s',$email);
	$user_stmt->execute();
	$user_stmt->store_result();
	$user_stmt->fetch();

	if($user_stmt->num_rows==0) {
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		$ins_stmt = $db->preparedStmt("INSERT INTO user(name,email,password) VALUES (?,?,?)");
		$ins_stmt->bind_param('sss',$name,$email,$hashed_password);
		$insert_status = $ins_stmt->execute();
		$ins_stmt->store_result();

		if($insert_status != false) {
			$response['status'] = "success";
			if (!isset($_SESSION)) {
				session_start();
			}
			$_SESSION['userID'] = $ins_stmt->insert_id;
			$_SESSION['name'] = $name;
			$_SESSION['email'] = $email;
			$response['code'] = 202;
			echoResponse(200, $response);
		} else {
			$response['status'] = "error";
			$response['code'] = 504;
			echoResponse(201, $response);
		}
		$ins_stmt->free_result();
		$ins_stmt->close();
	}else{
		$response['status'] = "error";
		$response['code'] = 505;
		echoResponse(201, $response);
	}
	$user_stmt->free_result();
	$user_stmt->close();
});

//GET Logout
$app->get('/user/logout', function() {
	$db = new DB();
	$msg = $db->destroySession();
	$response["status"] = "success";
	$response["code"] = 203 ;
	echoResponse(200, $response);
});

//PUT Passwort (Passwort ändern)
$app->put('/user/password', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());

	//check if user is logged in
	if($session['userID']!='') {

		$userID = $session['userID'];
		$old_pwd = $req->old_pwd;
		$new_pwd = $req->new_pwd;

		$sel_pwd = $db->preparedStmt("SELECT password FROM user WHERE userID = ?");
		$sel_pwd->bind_param('i',$userID);
		$sel_pwd->execute();
		$sel_pwd->bind_result($db_password);
		$sel_pwd->fetch();
		$sel_pwd->close();

		if(password_verify($old_pwd,$db_password)){

			$hashedPassword = password_hash($new_pwd,PASSWORD_DEFAULT);

			$upd_pwd = $db->preparedStmt("UPDATE user SET password = ? WHERE userID = ?");
			$upd_pwd->bind_param('si', $hashedPassword,$userID);
			$upd_pwd->execute();
			$upd_pwd->close();

			$response['status'] = "success";
			$response['code'] = 226;
			echoResponse(200, $response);
		} else {

			$response['status'] = "error";
			$response['code'] = intval('518');
			echoResponse(201, $response);
		}

	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}

});

//PUT Email (Email ändern)
$app->put('/user/email', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());

	//check if user is logged in
	if($session['userID']!='') {
		$email = $req->email;

		$changeEmail = $db->preparedStmt("UPDATE user SET email = ? WHERE userID = ?");
		$changeEmail->bind_param('si', $email,$session['userID']);
		$changeEmail->execute();
		$changeEmail->close();

		$sel_email = $db->preparedStmt("SELECT email FROM user WHERE userID = ?");
		$sel_email->bind_param('i',$session['userID']);
		$sel_email->execute();
		$sel_email->bind_result($db_email);
		$sel_email->fetch();

		$response['email'] = $db_email;

		$sel_email->close();

		$db->changeSessionEmail($db_email);

		$response['status'] = "success";
		$response['code'] = 227;
		echoResponse(200, $response);


	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//PUT Name (Name ändern)
$app->put('/user/name', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());

	//check if user is logged in
	if($session['userID']!='') {
		$name = $req->name;

		$changeEmail = $db->preparedStmt("UPDATE user SET name = ? WHERE userID = ?");
		$changeEmail->bind_param('si', $name,$session['userID']);
		$changeEmail->execute();
		$changeEmail->close();

		$sel_email = $db->preparedStmt("SELECT name FROM user WHERE userID = ?");
		$sel_email->bind_param('i',$session['userID']);
		$sel_email->execute();
		$sel_email->bind_result($db_name);
		$sel_email->fetch();

		$response['name'] = $db_name;

		$sel_email->close();

		$db->changeSessionName($db_name);

		$response['status'] = "success";
		$response['code'] = 228;
		echoResponse(200, $response);


	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//GET Color
$app->get('/user/color', function() use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {
		$userID = $session['userID'];


		$sel_col= $db->preparedStmt("SELECT color FROM user WHERE userID = ?");
		$sel_col->bind_param('i', $userID);
		$sel_col->execute();
		$sel_col->bind_result($db_color);
		$sel_col->fetch();

		$response['color'] = $db_color;
		$response['status'] = "success";
		$response['code'] = 229;
		echoResponse(200, $response);

		$sel_col->close();

	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}

});

//GET Image
$app->get('/user/image', function() use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {
		$userID = $session['userID'];

		$sel_col= $db->preparedStmt("SELECT image FROM user WHERE userID = ?");
		$sel_col->bind_param('i', $userID);
		$sel_col->execute();
		$sel_col->bind_result($db_image);
		$sel_col->fetch();

		$response['image'] = $db_image;
		$response['status'] = "success";
		$response['code'] = 229;
		echoResponse(200, $response);

		$sel_col->close();

	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}

});

