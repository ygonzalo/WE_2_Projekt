<?php
//GET findUser
$app->get('/findUser', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req_user = $app->request->params('user');


	//check if user is logged in
	if($session['userID']!='') {
		$userInput= "%".$req_user."%";

		$sel_user = $db->preparedStmt("SELECT userID,name,email,points FROM user WHERE name LIKE ?  OR email LIKE ?");
		$sel_user->bind_param('ss', $userInput, $userInput);
		$sel_user->execute();
		$sel_user->store_result();
		$sel_user->bind_result($db_userID,$db_name,$db_email,$db_points);

		if($sel_user->num_rows>0){
			$userID=$session['userID'];
			$response['users'] = array();
			$user = array();
			while ($sel_user->fetch()) {
				//user who is logged in doesn't appear in the list
				if($db_userID!=$userID){
					$user['userID'] = $db_userID;
					$user['name'] = $db_name;
					$user['email'] = $db_email;
					$user['points'] = $db_points;
					array_push($response['users'], $user);
				}
			}
			$response['status'] = "success";
			echoResponse(200, $response);
		} else {
			$response['status'] = "error";
			$response['message'] = "No user found";
			echoResponse(201, $response);
		}
		$sel_user->free_result();
		$sel_user->close();


	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}

});

//PUT Accept or deny friend request
$app->put('/request', function() use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());

	//check if user is logged in
	if($session['userID']!='') {
		$userID=$session['userID'];
		$friendID=$req->friendID;
		$status=$req->status;

		//get data from friends db
		$sel_rel = $db->preparedStmt("SELECT userID,friendID,status,since FROM friends WHERE userID = ?  AND friendID = ? OR userID = ?  AND friendID = ?");
		$sel_rel->bind_param('iiii', $userID, $friendID, $friendID,$userID);
		$sel_rel->execute();
		$sel_rel->store_result();
		$sel_rel->bind_result($db_userID,$db_friendID,$db_status,$db_since);

		if($db_friendID==$userID){
			//check if relationship is already in table and update status if necessary
			if($sel_rel->num_rows>0){
				$sel_rel->fetch();

				if($db_status==$status){
					//status already set
					$response['status'] = "error";
					$response['message'] = "Status already set";
					echoResponse(201, $response);

				}else if($db_status=="requested"){

					$upd_friend = $db->preparedStmt("UPDATE friends SET status = ?, since = ? WHERE userID = ?  AND friendID = ?");

					switch ($status) {
						case "accepted":
							//set timezone and get current date
							date_default_timezone_set('Europe/Berlin');
							$since = date("Y-m-d");
							//bind params to statement and execute
							$upd_friend->bind_param('ssii', $status, $since, $friendID, $userID);
							$upd_friend->execute();
							$upd_friend->close();
							$response['status'] = "success";
							$response['message'] = "Status changed to accepted";
							echoResponse(200, $response);
							break;

						case "denied":
							$since = null;
							//bind params to statement and execute
							$upd_friend->bind_param('ssii', $status, $since, $friendID, $userID);
							$upd_friend->execute();
							$upd_friend->close();
							$response['status'] = "success";
							$response['message'] = "Status changed to denied";
							echoResponse(200, $response);
							break;
						default:
							$response['status'] = "error";
							$response['message'] = "Invalid status";
							echoResponse(201, $response);
							break;
					}
				}
			}else {
				$response['status'] = "error";
				$response['message'] = "No relationship found in DB";
				echoResponse(201, $response);
			}


		} else {
			$response['status'] = "error";
			$response['message'] = "Requests cannot be accepted/denied by the user who sent it";
			echoResponse(201, $response);
		}
	}
});

//POST Add friend
$app->post('/request', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());

	//check if user is logged in
	if($session['userID']!='') {

		$userID=$session['userID'];
		$friendID=$req->friendID;

		if($friendID!=$userID) {
			//check if there is a user with the id in friendID
			$stmt_friend = $db->preparedStmt("SELECT 1 FROM user WHERE userID LIKE ?");
			$stmt_friend->bind_param('i', $friendID);
			$stmt_friend->execute();

			$stmt_friend->store_result();

			if ($stmt_friend->num_rows > 0) {

				//check if there is already a relationship between the users
				$sel_rel = $db->preparedStmt("SELECT 1 FROM friends WHERE userID = ?  AND friendID = ? OR userID = ?  AND friendID = ?");
				$sel_rel->bind_param('iiii', $userID, $friendID, $friendID, $userID);
				$sel_rel->execute();
				$sel_rel->store_result();

				if ($sel_rel->num_rows == 0) {
					$ins_friend = $db->preparedStmt("INSERT INTO friends(userID, friendID, status) VALUES(?,?,'requested')");
					$ins_friend->bind_param('ii', $userID, $friendID);
					$ins_friend->execute();
					$ins_friend->close();
					$response['status'] = "success";
					$response['message'] = "Request sent";
					echoResponse(200, $response);
				} else {
					$response['status'] = "error";
					$response['message'] = "Relationship already exists";
					echoResponse(201, $response);
				}

				$sel_rel->free_result();
				$sel_rel->close();

			} else {
				$response['status'] = "error";
				$response['message'] = "User not found";
				echoResponse(201, $response);
			}
			$stmt_friend->free_result();
			$stmt_friend->close();
		}else{
			$response['status'] = "error";
			$response['message'] = "It is only possible to send requests to other users";
			echoResponse(201, $response);
		}
	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}

});

//GET Friend requests
$app->get('/requests', function() use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {
		$userID = $session['userID'];
		$response['requests'] = array();

		$sel_requests = $db->preparedStmt("SELECT f.userID,u.name FROM friends AS f JOIN user AS u ON f.userID = u.userID WHERE f.friendID = ? AND f.status = 'requested'");
		$sel_requests->bind_param('i', $userID);
		$sel_requests->execute();

		$sel_requests->store_result();
		$sel_requests->bind_result($db_userID,$db_name);

		if($sel_requests->num_rows>0) {
			$user = array();
			while($sel_requests->fetch()){
				$user['userID'] = $db_userID;
				$user['name'] = $db_name;
				array_push($response['requests'],$user);
			}
		}
		$response['status'] = "success";
		echoResponse(200, $response);

	}else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}
});

//DELETE friend
$app->delete('/friend/:id', function($id) use ($app) {
	
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	
	//check if user is logged in
	if($session['userID']!='') {
		$userID=$session['userID'];
		$friendID=$id;
		//look up if friend exists
		$sel_friends = $db->preparedStmt("SELECT f.userID,f.friendID,f.since FROM friends AS f WHERE f.userID = ? AND f.friendID = ? OR f.userID = ? AND f.friendID = ? ");
		$sel_friends->bind_param('iiii', $userID, $friendID ,$friendID ,$userID);
		$sel_friends->execute();
		$sel_friends->store_result();
		
		//delete if exists
		if($sel_friends->num_rows>0){
		
			$del_friends = $db->preparedStmt("DELETE FROM friends WHERE f.userID = ? AND f.friendID = ? OR f.userID = ? AND f.friendID = ?");
			$del_friends->bind_param('iiii', $userID, $friendID ,$friendID ,$userID);
			$del_friends->execute();
			$del_friends->store_result();	
			$response['status'] = "success";
			$response['message'] = "Friend deleted";
			echoResponse(200, $response);			
				
		}else{
			$response['status'] = "error";
			$response['message'] = "Not a friend";
			echoResponse(201, $response);
			
		}
		
	}else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}
	
});

//GET Friends
$app->get('/friendlist', function() use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {

		$userID=$session['userID'];
		$response['friends'] = array();

		$sel_friends = $db->preparedStmt("SELECT f.userID,f.friendID,f.since FROM friends AS f WHERE f.userID = ? OR f.friendID = ? AND f.status = 'accepted'");
		$sel_friends->bind_param('ii', $userID, $userID);
		$sel_friends->execute();

		$sel_friends->store_result();
		$sel_friends->bind_result($db_userID,$db_friendID,$db_since);

		if($sel_friends->num_rows>0){
			$sel_name = $db->preparedStmt("SELECT u.name FROM user AS u WHERE u.userID = ?");

			while($sel_friends->fetch()){
				$user = array();
				if($db_userID==$userID){
					$sel_name->bind_param('i',$db_friendID);
					$sel_name->execute();
					$sel_name->bind_result($db_name);
					$sel_name->fetch();

					$user['userID'] = $db_friendID;
					$user['name'] = $db_name;
					$user['since'] = $db_since;

					array_push($response['friends'], $user);

				}else if($db_friendID==$userID){
					$user['userID'] = $db_userID;
					$sel_name->bind_param('i',$db_userID);
					$sel_name->execute();
					$sel_name->bind_result($db_name);
					$sel_name->fetch();

					$user['userID'] = $db_userID;
					$user['name'] = $db_name;
					$user['since'] = $db_since;

					array_push($response['friends'], $user);
				}
			}

			$sel_name->close();
			$response['status'] = "success";
			echoResponse(200, $response);
		}else {
			$response['status'] = "error";
			$response['message'] = "No friends found";
			echoResponse(201, $response);
		}

		$sel_friends->free_result();
		$sel_friends->close();

	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}

});

//POST Passwort (Passwort ändern)
$app->post('/changePassword', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());


	//check if user is logged in
	if(!empty($session['userID'])) {
		$password = $req->password;
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

		$changePw = $db->preparedStmt("UPDATE user SET password = ? WHERE userID = ?");
		$changePw->bind_param('i', $hashedPassword,$session['userID']);
		$changePw->execute();
		$changePw->close();

		$response['status'] = "success";
		$response['message'] = "Password changed";
		echoResponse(200, $response);

	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}



});
//POST Email (Email ändern)
$app->post('/changeEmail', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());


	//check if user is logged in
	if(!empty($session['userID'])) {
		$email = $req->email;

		$changeEmail = $db->preparedStmt("UPDATE user SET email = ? WHERE userID = ?");
		$changeEmail->bind_param('i', $email,$session['userID']);
		$changeEmail->execute();
		$changeEmail->close();

		$response['status'] = "success";
		$response['message'] = "Email changed";
		echoResponse(200, $response);


	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}



});

?>