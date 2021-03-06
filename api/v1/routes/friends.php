<?php

require_once("helper_methods.php");

//GET findUser
$app->get('/friends/search/:query', function($query) use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {
		$userID=$session['userID'];
		$userInput= "%".$query."%";

		$sel_user = $db->preparedStmt("SELECT userID,name,email,points FROM user WHERE name LIKE ?  OR email LIKE ?");
		$sel_user->bind_param('ss', $userInput, $userInput);
		$sel_user->execute();
		$sel_user->store_result();
		$sel_user->bind_result($db_userID,$db_name,$db_email,$db_points);

		if($sel_user->num_rows>0){
			$response['users'] = array();
			$user = array();
			while ($sel_user->fetch()) {
				//user who is logged in doesn't appear in the list
				if($db_userID!=$userID && !isFriend($userID,$db_userID) && !requestSent($db_userID,$userID)){
					$user['userID'] = $db_userID;
					$user['name'] = $db_name;
					$user['email'] = $db_email;
					$user['points'] = $db_points;
					if(requestSent($userID,$db_userID)){
						$user['requested'] = true;
					}else {
						$user['requested'] = false;
					}
					array_push($response['users'], $user);
				}
			}
			if(count($response['users'])>0)
			{
				$response['status'] = "success";
				$response['code'] = 211;
				echoResponse(200, $response);
			}
			else {
				$response['status'] = "success";
				$response['code'] = 212;
				echoResponse(200, $response);
			}
		} else {
			$response['status'] = "success";
			$response['code'] = 212;
			echoResponse(200, $response);
		}
		$sel_user->free_result();
		$sel_user->close();

	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}

});

//POST Send friend request
$app->post('/friends/:friendID/request', function($friendID) use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {

		$userID=$session['userID'];

		if($friendID!=$userID) {

			if (userExists($friendID)) {

				if (!isFriend($userID,$friendID)) {
					$ins_friend = $db->preparedStmt("INSERT INTO friends(userID, friendID, status) VALUES(?,?,'requested')");
					$ins_friend->bind_param('ii', $userID, $friendID);
					$ins_friend->execute();
					$ins_friend->close();

					$user_name = getUsername($userID);
					$friend_email = getUserEmail($friendID);

					$message = "Du hast eine neue Freundschaftsanfrage von $user_name";
					$subject = "Neue Freundschaftsanfrage";

					sendMail($friend_email,$subject,$message);

					$response['status'] = "success";
					$response['code'] = 213;
					echoResponse(200, $response);
				} else {
					$response['status'] = "error";
					$response['code'] = 510;
					echoResponse(201, $response);
				}

			} else {
				$response['status'] = "error";
				$response['code'] = 511;
				echoResponse(201, $response);
			}
		}else{
			$response['status'] = "error";
			$response['code'] = 511;
			echoResponse(201, $response);
		}
	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}

});

//Cancel friend request
$app->delete('/friends/:friendID/request', function($friendID) use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {
		$userID = $session['userID'];
		if(requestSent($userID, $friendID)){
			$del_request = $db->preparedStmt("DELETE FROM friends WHERE userID=? AND friendID=?");
			$del_request->bind_param('ii',$userID,$friendID);
			$del_request->execute();
			$del_request->close();
			$response['status'] = "success";
			$response['code'] = 214;
			echoResponse(200, $response);
		} else{
			$response['status'] = "error";
			$response['code'] = 521;
			echoResponse(201, $response);
		}

	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//PUT Accept or deny friend request
$app->put('/friends/:friendID/request', function($friendID) use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());

	//check if user is logged in
	if($session['userID']!='') {
		$userID=$session['userID'];
		$status=$req->status;

		//get data from friends db
		$sel_rel = $db->preparedStmt("SELECT userID,friendID,status,since FROM friends WHERE userID = ?  AND friendID = ? OR userID = ?  AND friendID = ?");
		$sel_rel->bind_param('iiii', $userID, $friendID, $friendID,$userID);
		$sel_rel->execute();
		$sel_rel->store_result();
		$sel_rel->bind_result($db_userID,$db_friendID,$db_status,$db_since);

		$sel_rel->fetch();

		if($db_friendID==$userID){

			//check if relationship is already in table and update status if necessary
			if($sel_rel->num_rows>0){
				$sel_rel->fetch();

				if($db_status==$status){
					//status already set
					$response['status'] = "error";
					$response['code'] = "Status already set";
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
							$response['code'] = 214;
							echoResponse(200, $response);
							break;

						case "denied":
							$since = null;
							//bind params to statement and execute
							$upd_friend->bind_param('ssii', $status, $since, $friendID, $userID);
							$upd_friend->execute();
							$upd_friend->close();
							$response['status'] = "success";
							$response['code'] = 215;
							echoResponse(200, $response);
							break;
						default:
							$response['status'] = "error";
							$response['code'] = 513;
							echoResponse(201, $response);
							break;
					}
				}
			}else {
				$response['status'] = "error";
				$response['code'] = 514;
				echoResponse(201, $response);
			}


		} else {
			$response['status'] = "error";
			$response['code'] = 515;
			echoResponse(201, $response);
		}
	}else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//GET pending friend requests
$app->get('/friends/requests/pending', function() use ($app) {
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
			$response['status'] = "success";
			$response['code'] = 216;
			echoResponse(200, $response);
		} else {
			$response['status'] = "success";
			$response['code'] = 217;
			echoResponse(200, $response);
		}
	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//GET sent friend requests
$app->get('/friends/requests/sent', function() use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {
		$userID = $session['userID'];
		$response['requests'] = array();

		$sel_requests = $db->preparedStmt("SELECT f.friendID,u.name FROM friends AS f JOIN user AS u ON f.friendID = u.userID WHERE f.userID = ? AND f.status = 'requested'");
		$sel_requests->bind_param('i', $userID);
		$sel_requests->execute();

		$sel_requests->store_result();
		$sel_requests->bind_result($db_userID,$db_name);

		if($sel_requests->num_rows>0) {
			$user = array();
			while($sel_requests->fetch()){
				$user['userID'] = $db_userID;
				$user['name'] = $db_name;
				$user['requested'] = true;
				array_push($response['requests'],$user);
			}
			$response['status'] = "success";
			$response['code'] = 230;
			echoResponse(200, $response);
		} else {
			$response['status'] = "success";
			$response['code'] = 231;
			echoResponse(200, $response);
		}
	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//DELETE friend
$app->delete('/friends/:friendID', function($friendID) use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {
		$userID=$session['userID'];
		//look up if friend exists
		$sel_friends = $db->preparedStmt("SELECT f.userID,f.friendID,f.since FROM friends AS f WHERE f.userID = ? AND f.friendID = ? OR f.userID = ? AND f.friendID = ? ");
		$sel_friends->bind_param('iiii', $userID, $friendID ,$friendID ,$userID);
		$sel_friends->execute();
		$sel_friends->store_result();

		//delete if exists
		if($sel_friends->num_rows>0){

			$del_friends = $db->preparedStmt("DELETE FROM friends WHERE userID = ? AND friendID = ? OR userID = ? AND friendID = ?");
			$del_friends->bind_param('iiii', $userID, $friendID ,$friendID ,$userID);
			$del_friends->execute();
			$response['status'] = "success";
			$response['code'] = 218;
			echoResponse(200, $response);

		}else{
			$response['status'] = "error";
			$response['code'] = 516;
			echoResponse(201, $response);
		}
	}else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//GET Friends
$app->get('/friends', function() use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {

		$userID=$session['userID'];
		$response['friends'] = array();

		$sel_friends = $db->preparedStmt("SELECT f.userID,f.friendID,f.since FROM friends AS f WHERE (f.userID = ? OR f.friendID = ?) AND f.status = 'accepted'");
		$sel_friends->bind_param('ii', $userID, $userID);
		$sel_friends->execute();

		$sel_friends->store_result();
		$sel_friends->bind_result($db_userID,$db_friendID,$db_since);

		if($sel_friends->num_rows>0){
			$sel_user = $db->preparedStmt("SELECT name, image FROM user WHERE userID = ?");

			while($sel_friends->fetch()){
				$user = array();
				if($db_userID==$userID){
					$sel_user->bind_param('i',$db_friendID);
					$sel_user->execute();
					$sel_user->bind_result($db_name,$db_image);
					$sel_user->fetch();

					$user['userID'] = $db_friendID;
					$user['name'] = $db_name;
					$user['image'] = $db_image;
					$user['since'] = $db_since;

					array_push($response['friends'], $user);

				}else if($db_friendID==$userID){
					$user['userID'] = $db_userID;
					$sel_user->bind_param('i',$db_userID);
					$sel_user->execute();
					$sel_user->bind_result($db_name,$db_image);
					$sel_user->fetch();

					$user['userID'] = $db_userID;
					$user['name'] = $db_name;
					$user['image'] = $db_image;
					$user['since'] = $db_since;

					array_push($response['friends'], $user);
				}
			}

			$sel_user->close();
			$response['status'] = "success";
			$response['code'] = 219;
			echoResponse(200, $response);
		}else {
			$response['status'] = "success";
			$response['code'] = 220;
			echoResponse(200, $response);
		}

		$sel_friends->free_result();
		$sel_friends->close();

	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}

});

//POST Send a movie recommendation to a friend
$app->post('/friends/:friendID/recommend', function($friendID) use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());

	//movieID to recommend
	$movieID = $req->movieID;

	//check if user is logged in
	if($session['userID']!='') {
		$userID = $session['userID'];

		if(isFriend($userID,$friendID)){

			$sel_recommendation = $db->preparedStmt("SELECT 1 FROM recommendations WHERE fromID= ? AND toID=? AND movieID=?");
			$sel_recommendation->bind_param('iii',$userID,$friendID,$movieID);
			$sel_recommendation->execute();
			$sel_recommendation->store_result();

			if($sel_recommendation->num_rows==0){
				if(isMovieWatched($movieID,$userID)){
					if(!isMovieWatched($movieID,$friendID)) {
						//Insert new recommendation
						$stmt_recommendations = $db->preparedStmt("INSERT INTO recommendations(fromID,toID,movieID) VALUES(?,?,?)");
						$stmt_recommendations->bind_param('iii', $userID, $friendID, $movieID);
						$stmt_recommendations->execute();
						$stmt_recommendations->close();

						$response['status'] = "success";
						$response['code'] = 221;
						echoResponse(200, $response);
					} else {
						$response['status'] = "error";
						$response['code'] = 522;
						echoResponse(201, $response);
					}
				}else{
					$response['status'] = "error";
					$response['code'] = 517;
					echoResponse(201, $response);
				}
			}else{
				$response['status'] = "error";
				$response['code'] = 523;
				echoResponse(201, $response);
			}

			$sel_recommendation->free_result();
			$sel_recommendation->close();

		} else{
			$response['status'] = "error";
				$response['code'] = 516;
			echoResponse(201, $response);
		}

	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}

});

//GET recommendations
$app->get('/friends/recommendations', function() use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {
		$userID = $session['userID'];
		$stmt_rec = $db->preparedStmt("SELECT r.recID,r.fromID,u.name,r.movieID,r.read FROM recommendations AS r JOIN user AS u ON r.fromID = u.userID WHERE r.toID = ?");
		$stmt_rec->bind_param('i',$userID);
		$stmt_rec->execute();

		$stmt_rec->store_result();
		$stmt_rec->bind_result($db_recID,$db_fromID,$db_name,$db_movieID,$db_read);

		$response['recommendations'] = array();


		if($stmt_rec->num_rows>0){

			while($stmt_rec->fetch()){
				$reco = array();
				$reco['from'] = $db_fromID;
				$reco['name'] = $db_name;
				$reco['recID'] = $db_recID;
				$reco['read'] = (bool)$db_read;

				$movie_stmt = $db->preparedStmt("SELECT m.movieID, mi.title
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID WHERE m.movieID= ?");
				$movie_stmt->bind_param('i',$db_movieID);
				$movie_stmt->execute();
				$movie_stmt->store_result();
				$movie_stmt->bind_result($db_movieID,$db_title);

				if($movie_stmt->num_rows>0){

					$movie_stmt->fetch();

					$reco['movieID'] = $db_movieID;
					$reco['title'] = $db_title;

				}

				$movie_stmt->free_result();
				$movie_stmt->close();
				array_push($response['recommendations'],$reco);
			}

			$response['status'] = "success";
			$response['code'] = 222;
			echoResponse(200, $response);

		} else{
			$response['status'] = "success";
			$response['code'] = 223;
			echoResponse(200, $response);
		}

		$stmt_rec->free_result();
		$stmt_rec->close();

	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//DELETE recommendation
$app->delete('/friends/recommendations/:recID', function($recID) use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {

		$del_recommendation = $db->preparedStmt("DELETE FROM recommendations WHERE recID = ?");
		$del_recommendation->bind_param('i',$recID);
		if($del_recommendation->execute()){
			$del_recommendation->close();
			$response['status'] = "success";
			$response['code'] = 235;
			echoResponse(201, $response);
		} else {
			$del_recommendation->close();
			$response['status'] = "error";
			$response['code'] = 524;
			echoResponse(201, $response);
		}
	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//PUT recommendation read
$app->put('/friends/recommendations/:recID', function($recID) use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {

		$update_recommendation = $db->preparedStmt("UPDATE recommendations AS r SET r.read = 1 WHERE recID = ?");
		$update_recommendation->bind_param('i',$recID);
		if($update_recommendation->execute()){
			$update_recommendation->close();
			$response['status'] = "success";
			$response['code'] = 236;
			echoResponse(201, $response);
		} else {
			$update_recommendation->close();
			$response['status'] = "error";
			$response['code'] = 525;
			echoResponse(201, $response);
		}
	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//GET Movies which are in both the user's watchlists
$app->get('/friends/:friendID/commonWatchlist', function($friendID) use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {
		$userID = $session['userID'];

		if (isFriend($userID,$friendID)){
			$sel_movielist = $db->preparedStmt("SELECT ml1.movieID FROM movielist AS ml1 JOIN movielist AS ml2 ON ml2.movieID = ml1.movieID WHERE ml1.userID = ? AND ml2.userID = ? AND ml1.status = 'watchlist' AND ml2.status = 'watchlist'");
			$sel_movielist->bind_param('ii',$userID,$friendID);
			$sel_movielist->execute();
			$sel_movielist->store_result();
			$sel_movielist->bind_result($db_movieID);

			$response['matches'] = array();

			if($sel_movielist->num_rows>0) {

				$sel_movie = $db->preparedStmt("SELECT m.original_title, m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID WHERE m.movieID= ?");

				$sel_movie->bind_param('i',$db_movieID);
				while($sel_movielist->fetch()){

					$sel_movie->execute();
					$sel_movie->bind_result($db_original_title,$db_ratings,$db_rating_points,$db_watchers, $db_title, $db_plot, $db_release_date, $db_poster);
					$sel_movie->fetch();

					$movie = array();
					$movie['movieID'] = $db_movieID;
					$movie['title'] = $db_title;
					$movie['plot'] = $db_plot;
					$movie['release_date'] = $db_release_date;
					$movie['original_title'] = $db_original_title;
					$movie['poster'] = $db_poster;
					$movie['watchers'] = $db_watchers;
					$movie['rating_points'] = $db_rating_points;
					array_push($response['matches'], $movie);

				}
				$sel_movie->close();

				$sel_movielist->free_result();
				$sel_movielist->close();
				$response['status'] = "success";
				$response['code'] = 224;
				echoResponse(200, $response);
			}else {
				$response['status'] = "success";
				$response['code'] = 225;
				echoResponse(200, $response);
			}

		} else {
			$response['status'] = "error";
			$response['code'] = 516;
			echoResponse(201, $response);
		}

	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}

});

//GET Friend profile data
$app->get('/friends/profile/:friendID', function($friendID) use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {
		$userID = $session['userID'];

		if($userID!=$friendID){
			if(isFriend($userID,$friendID)){
				$sel_friend = $db->preparedStmt("SELECT u.userID, u.name, u.points, u.image FROM user AS u WHERE u.userID= ?");
				$sel_friend->bind_param('i',$friendID);
				$sel_friend->execute();
				$sel_friend->bind_result($db_userID,$db_name,$db_points,$db_image);
				$sel_friend->store_result();
				$sel_friend->fetch();

				$friend = array();

				if($sel_friend->num_rows>0){

					$friend['userID'] = $db_userID;
					$friend['name'] = $db_name;
					$friend['points'] = $db_points;
					$friend['image'] = $db_image;

					$sel_since = $db->preparedStmt("SELECT f.since FROM friends AS f WHERE (f.userID = ? AND f.friendID = ?) OR (f.userID = ? AND f.friendID = ?)");
					$sel_since->bind_param('iiii',$userID,$friendID,$friendID,$userID);
					$sel_since->execute();
					$sel_since->bind_result($db_since);
					$sel_since->fetch();

					$friend['since'] = $db_since;

					$sel_since->free_result();
					$sel_since->close();

					$response = array('friend' => $friend);
				}

				$sel_friend->free_result();
				$sel_friend->close();

				$response['status'] = "success";
				$response['code'] = 232;
				echoResponse(200, $response);

			}else{
				$response['status'] = "error";
				$response['code'] = 522;
				echoResponse(201, $response);
			}
		}
	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//GET Notifications
$app->get('/friends/notifications', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {
		$userID = $session['userID'];

		$count_friendreq = $db->preparedStmt("SELECT COUNT(*) FROM friends WHERE friendID = ? AND status = 'requested'");
		$count_friendreq->bind_param('i',$userID);
		$count_friendreq->execute();
		$count_friendreq->bind_result($db_request_count);
		$count_friendreq->fetch();
		$count_friendreq->close();

		$count_rec = $db->preparedStmt("SELECT COUNT(*) FROM recommendations AS r WHERE r.toID = ? AND r.read = 0");
		$count_rec->bind_param('i',$userID);
		$count_rec->execute();
		$count_rec->bind_result($db_rec_count);
		$count_rec->fetch();
		$count_rec->close();

		$response['status'] = "success";
		$response['friend_requests'] = $db_request_count;
		$response['recommendations'] = $db_rec_count;
		$response['code'] = 234;
		echoResponse(201, $response);


	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});