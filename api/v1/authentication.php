<?php
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

?>