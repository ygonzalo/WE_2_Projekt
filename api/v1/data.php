<?php
//GET Film by name
$app->get('/movie', function() use ($app) {
	//parse JSON body of request
	$req_title = $app->request->params('title');
	$db = new DB();
	$session = $db->getSession();
	$response = array();
	$response['matches'] = array();

	//check if user is authenticated
	if($session['userID'] != '') {

		if($req_title){
			$movie = array();
			$_SESSION['matches'] = array();

			//send request to TMDB API
			$title_url = str_replace(' ','+',$req_title);
			$search_response = file_get_contents('https://api.themoviedb.org/3/search/movie?api_key=fc6230097457cdf6f547373206e12a5d&language=de&query='.$title_url);
			$response_decoded = json_decode($search_response, true);

			//get tmdb configuration needed to build poster path
			//$config_response = file_get_contents('http://api.themoviedb.org/3/configuration?api_key=fc6230097457cdf6f547373206e12a5d');
			//$config = json_decode($config_response);

			$matches = $response_decoded['results'];

			//add each movie to array in response
			foreach ($matches as $item){

				//build poster path with size configuration
				$poster = "http://image.tmdb.org/t/p/w185". $item['poster_path'];

				$movie['movieID'] = $item['id'];
				$movie['title'] = $item['title'];
				$movie['plot'] = $item['overview'];
				$movie['release_date'] = $item['release_date'];
				$movie['original_title'] = $item['original_title'];
				$movie['poster'] = $poster;
				$movie['status'] = null;
				$movie['user_rating'] = null;
				$movie['watched_date'] = null;
				$movie['watchers'] = 0;
				$movie['rating_points'] = 0;

				//prepare sql statements and bind parameters
				$sel_movielist = $db->preparedStmt("SELECT status,user_rating,watched_date FROM movielist WHERE movieID = ? AND userID = ?");
				$sel_movielist->bind_param('ii', $movie['movieID'], $session['userID']);

				$sel_movie = $db->preparedStmt("SELECT watchers,rating_points FROM movie WHERE movieID = ?");
				$sel_movie->bind_param('i', $movie['movieID']);

				//execute query
				$sel_movielist->execute();
				$sel_movielist->bind_result($db_status,$db_user_rating,$db_watched_date);
				while($sel_movielist->fetch()) {
					$movie['status'] = $db_status;
					$movie['user_rating'] = $db_user_rating;
					$movie['watched_date'] = $db_watched_date;
				}

				$sel_movielist->close();

				$sel_movie->execute();
				$sel_movie->bind_result($db_watchers,$db_rating_points);
				if ($sel_movie->fetch()) {
					$movie['watchers'] = $db_watchers;
					$movie['rating_points'] = $db_rating_points;
				}
				$sel_movie->close();
				
				array_push($response['matches'], $movie);
			}

			//copy matches array into session
			$_SESSION['matches'] = $response['matches'];

			$response['status'] = "success";
			echoResponse(200, $response);
		}
	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}
});

//POST Status flag
$app->post('/status', function() use ($app) {

	//get JSON body and parse to array
	$req = json_decode($app->request->getBody());
	$index = $req->index;
	$status = $req->status;

	//REST-Service Response
	$response = array();

	//Movie from session
	$db = new DB();
	$session = $db->getSession();

	//is user logged in?
	if($session['userID']!= '') {

		if($status == "watched" || $status == "watchlist" || $status == "deleted" )
		{
			//is movie data in session?
			if (!empty($session['matches'])){

				$movie = $session['matches'][$index];

				$movieID = $movie['movieID'];

				$movie['language'] = "de";

				if($status == "watched") {

					$watchers = $movie['watchers'] +1;
					date_default_timezone_set('UTC');

					$watched_date = date("Y-m-d");
				} else {
					$watchers = $movie['watchers'];
					$watched_date = null;

				}

				//get userID
				$userID = $session['userID'];

				//prepare sql statement and bind parameters
				$movie_stmt = $db->preparedStmt("SELECT 1 FROM movie WHERE movieID = ?");
				$movielist_stmt = $db->preparedStmt("SELECT 1 FROM movielist WHERE movieID = ? AND userID = ?");
				$movie_stmt->bind_param('i', $movieID);
				$movielist_stmt->bind_param('ii', $movieID, $userID );

				//execute queries
				$movie_stmt->execute();
				$movie_res = $movie_stmt->get_result();

				$movielist_stmt->execute();
				$movielist_res = $movielist_stmt->get_result();


				//check if movie is already in db, if not, add it to db with info
				if($movie_res->num_rows != 1) {

					$insert_watched_movie = $db->preparedStmt("INSERT INTO movie(movieID, original_title, watchers) VALUES (?,?,?)");
					$insert_watched_movie->bind_param("iss", $movieID, $movie['original_title'], $watchers);

					$insert_watched_movie->execute();
					$insert_watched_movie->close();

					$insert_movielist = $db->preparedStmt("INSERT INTO movielist(movieID,userID,status,watched_date) VALUES (?,?,?,?)");
					$insert_movielist->bind_param("iiss", $movieID, $userID, $status, $watched_date);

					$insert_movielist->execute();
					$insert_movielist->close();

					$insert_movieinfo = $db->preparedStmt("INSERT INTO movieinfo(movieID,language,plot,title,release_date,poster) VALUES (?,?,?,?,?,?)");
					$insert_movieinfo->bind_param("isssss", $movieID,$movie['language'],$movie['plot'],$movie['title'],$movie['release_date'],$movie['poster']);

					$insert_movieinfo->execute();
					$insert_movieinfo->close();

					$response['status'] = "success";
					echoResponse(200, $response);
				} else {
					//check if movie is in user's list. If it is, just change status, if not, add to db
					if($movielist_res->num_rows != 1){
						if($status == "watched") {
							$update_watched_movie = $db->preparedStmt("UPDATE movie SET watchers = ? WHERE movieID = ?");
							$update_watched_movie->bind_param("ii", $watchers, $movieID);

							$update_watched_movie->execute();
							$update_watched_movie->close();
						}
						$insert_movielist = $db->preparedStmt("INSERT INTO movielist(movieID,userID,status,watched_date) VALUES (?,?,?,?)");
						$insert_movielist->bind_param("iiss", $movieID, $userID, $status, $watched_date);

						$insert_movielist->execute();
						$insert_movielist->close();

					}else {

						if($status == "watched") {

							//Add 1 watcher to movie
							$update_watched_movie = $db->preparedStmt("UPDATE movie SET watchers = ? WHERE movieID = ?");
							$update_watched_movie->bind_param("ii", $watchers, $movieID);

							$update_watched_movie->execute();
							$update_watched_movie->close();
						}

						//Update movie status
						$update_watchlist = $db->preparedStmt("UPDATE movielist SET status=?,watched_date=? WHERE movieID=? AND userID=?");
						$update_watchlist->bind_param("ssii", $status,$watched_date, $movieID, $userID);

						$update_watchlist->execute();
						$update_watchlist->close();


					}

					$response['status'] = "success";
					echoResponse(200, $response);
				}

			}else{
				$response['status'] = "error";
				$response['message'] = "No movie data";
				echoResponse(201, $response);
			}
		} else {
			$response['status'] = "error";
			$response['message'] = "Wrong status";
			echoResponse(201, $response);
		}

	}else{
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}
});

//GET Watchlist
$app->get('/watchlist', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//is User logged in?
	if(!empty($session['userID'])) {
		$userID = $session['userID'];

		//prepare sql statement and bind parameters
		$movielist_stmt = $db->preparedStmt("SELECT m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster, ml.status 
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID 
JOIN movielist AS ml ON ml.movieID = m.movieID WHERE userID= ? AND status= \"watchlist\"");
		$movielist_stmt->bind_param('i', $userID);

		$movielist_stmt->execute();
		$ml_result = $movielist_stmt->get_result();
		$movielist_stmt->close();

		if(mysqli_num_rows($ml_result)>0){

			$response['matches'] = array();
			while ($movie = $ml_result->fetch_assoc()) {
				array_push($response['matches'], $movie);
			}
			$response['status'] = "success";
			echoResponse(200, $response);
		} else {
			$response['status'] = "error";
			$response['message'] = "No movies in watchlist";
			echoResponse(201, $response);
		}
	}
});

//GET Watched
$app->get('/watched', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//is User logged in?
	if(!empty($session['userID'])) {
		$userID = $session['userID'];

		//prepare sql statement and bind parameters
		$movielist_stmt = $db->preparedStmt("SELECT m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date,mi.poster, ml.status 
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID 
JOIN movielist AS ml ON ml.movieID = m.movieID WHERE userID= ? AND status= \"watched\"");
		$movielist_stmt->bind_param('i', $userID);

		$movielist_stmt->execute();
		$ml_result = $movielist_stmt->get_result();
		$movielist_stmt->close();

		if(mysqli_num_rows($ml_result)>0){

			$response['matches'] = array();
			while ($movie = $ml_result->fetch_assoc()) {
				array_push($response['matches'], $movie);
			}
			$response['status'] = "success";
			echoResponse(200, $response);
		} else {
			$response['status'] = "error";
			$response['message'] = "No movies marked as watched";
			echoResponse(201, $response);
		}
	}
});

//POST Rating
$app->post('/rating', function() use ($app) {

	//get JSON body and parse to array
	$req = json_decode($app->request->getBody());
	$rating = $req->rating;

	//REST-Service Response
	$response = array();

	//get session
	$db = new DB();
	$session = $db->getSession();

	//check if user is authenticated
	if($session['userID'] != '') {

	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}

});

//POST User
$app->post('/user', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());

	//is User logged in?
	if(!empty($session['userID'])) {
		$userInput=array();
		$userInput['input']= "%".$req->input."%";

		$sel_user = $db->preparedStmt("SELECT userID,name,email,points FROM user WHERE name LIKE ?  OR email LIKE ?");
		$sel_user->bind_param('ss', $userInput['input'], $userInput['input']);
		$sel_user->execute();
		$user_result = $sel_user->get_result();
		$sel_user->close();
		$user=array();


		if(mysqli_num_rows($user_result)>0){
			$response['user'] = $user_result->fetch_assoc();
			while ($user = $user_result->fetch_assoc()) {
				array_push($response['user'], $user);
			}
			$response['status'] = "success";
			echoResponse(200, $response);
		} else {
			$response['status'] = "error";
			$response['message'] = "No user found";
			echoResponse(201, $response);
		}

	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}

});

//POST Add friend
$app->post('/friend', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());

	//is User logged in?
	if(!empty($session['userID'])) {

		$relationship=array();
		$relationship['userID']=$session['userID'];
		$relationship['friendID']=$req->friendID;
		$relationship['status']=$req->status;

		//is friendId valid
		$stmt_friend = $db->preparedStmt("SELECT 1 FROM user WHERE userID LIKE ?");
		$stmt_friend->bind_param('i', $relationship['friendID']);
		$stmt_friend->execute();

		$friend_result = $stmt_friend->get_result();
		$stmt_friend->close();

		if($friend_result->num_rows>0){

			//get data from friends db
			$sel_friend = $db->preparedStmt("SELECT userID,friendID,status,since FROM friends WHERE userID = ?  AND friendID = ? OR userID = ?  AND friendID = ?");
			$sel_friend->bind_param('iiii', $relationship['userID'], $relationship['friendID'], $relationship['friendID'],$relationship['userID']);
			$sel_friend->execute();

			$rel_result = $sel_friend->get_result();
			$sel_friend->close();

			//check if relationship is already in table and update status if necessary
			if($rel_result->num_rows>0){
				$rel_arr = $rel_result->fetch_assoc();

				if($rel_arr['status']==$relationship['status']){
					//status already set
					$response['status'] = "error";
					$response['message'] = "Status already set";
					echoResponse(201, $response);

				}else if($rel_arr['status']=="requested" && $rel_arr['friendID']==$relationship['userID']){
					if($relationship['status']=="accepted"){
						//set timezone and get current date
						date_default_timezone_set('UTC');
						$since = date("Y-m-d");

						//status change to accepted
						$upd_friend = $db->preparedStmt("UPDATE friends SET status = ?, since = ? WHERE userID = ?  AND friendID = ?");
						$upd_friend->bind_param('ssii',$relationship['status'], $since, $relationship['friendID'],$relationship['userID']);
						$upd_friend->execute();
						$upd_friend->close();
						$response['status'] = "success";
						$response['message'] = "Status changed to accepted";
						echoResponse(200, $response);
					} else if($relationship['status']=="denied") {
						//status change to denied
						$upd_friend = $db->preparedStmt("UPDATE friends SET status = ? WHERE userID = ?  AND friendID = ?");
						$upd_friend->bind_param('sii',$relationship['status'], $relationship['friendID'],$relationship['userID']);
						$upd_friend->execute();
						$upd_friend->close();
						$response['status'] = "success";
						$response['message'] = "Status changed to denied";
						echoResponse(200, $response);
					}
				} else {
					$response['status'] = "error";
					$response['message'] = "Not allowed";
					echoResponse(201, $response);
				}
			} else {

				if($relationship['status']=="requested") {
					$ins_friend = $db->preparedStmt("INSERT INTO friends(userID, friendID, status) VALUES(?,?,?)");
					$ins_friend->bind_param('iis', $relationship['userID'], $relationship['friendID'],$relationship['status']);
					$ins_friend->execute();
					$ins_friend->close();
					$response['status'] = "success";
					$response['message'] = "Request sent";
					echoResponse(200, $response);
				} else {
					$response['status'] = "error";
					$response['message'] = "Status not valid";
					echoResponse(201, $response);
				}
			}

		}else{
			$response['status'] = "error";
			$response['message'] = "User not found";
			echoResponse(201, $response);
		}
	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}


});

//DELETE friend
$app->delete('/friend', function() use ($app) {
	//TODO: First, check if friend is added and then delete
});

//GET Friends
$app->get('/friendlist', function() use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//is User logged in?
	if(!empty($session['userID'])) {


		$userID=$session['userID'];
			
		$sel_friendlist = $db->preparedStmt("SELECT f.friendID,f.since,u.name FROM friends AS f JOIN user AS u ON f.friendID = u.userID WHERE u.userID = ? AND f.status = 'accepted'");
		$sel_friendlist->bind_param('i', $userID);
		$sel_friendlist->execute();			

		$rel_result = $sel_friendlist->get_result();
		$sel_friendlist->close();

		if(mysqli_num_rows($rel_result)>0){
			$response['user'] = $rel_result->fetch_assoc();
			while ($user = $rel_result->fetch_assoc()) {
				array_push($response['user'], $user);
			}
			$response['status'] = "success";
			echoResponse(200, $response);
		} else {
			$response['status'] = "error";
			$response['message'] = "No friends found";
			echoResponse(201, $response);
		}

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


	//is User logged in?
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


	//is User logged in?
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