<?php

require_once("helper_methods.php");

/*************************************************************************************
REST API
 **************************************************************************************/

//GET Film by name
$app->get('/movies/search/:title', function($title) use ($app) {
	$db = new DB();
	$session = $db->getSession();
	$response = array();
	$response['matches'] = array();

	//check if user is authenticated
	if($session['userID'] != '') {

		if($title){
			$movie = array();
			$_SESSION['matches'] = array();

			//send request to TMDB API
			$title_url = str_replace(' ','+',$title);
			$search_response = file_get_contents('https://api.themoviedb.org/3/search/movie?api_key=fc6230097457cdf6f547373206e12a5d&language=de&query='.$title_url);
			$response_decoded = json_decode($search_response, true);

			//get tmdb configuration needed to build poster path
			//$config_response = file_get_contents('http://api.themoviedb.org/3/configuration?api_key=fc6230097457cdf6f547373206e12a5d');
			//$config = json_decode($config_response);

			$matches = $response_decoded['results'];

			//add each movie to array in response
			foreach ($matches as $item){

				//build poster path with size configuration
				$poster = "https://image.tmdb.org/t/p/w185". $item['poster_path'];

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
				$sel_movielist->store_result();
				$sel_movielist->bind_result($db_status,$db_user_rating,$db_watched_date);
				if($sel_movielist->num_rows>0){
					while($sel_movielist->fetch()) {
						$movie['status'] = $db_status;
						$movie['user_rating'] = $db_user_rating;
						$movie['watched_date'] = $db_watched_date;
					}
				}
				$sel_movielist->free_result();
				$sel_movielist->close();

				$sel_movie->execute();
				$sel_movie->store_result();
				$sel_movie->bind_result($db_watchers,$db_rating_points);
				if($sel_movie->num_rows>0){
					while ($sel_movie->fetch()) {
						$movie['watchers'] = $db_watchers;
						$movie['rating_points'] = $db_rating_points;
					}
				}
				$sel_movie->free_result();
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
$app->post('/movies/:movieID/status', function($movieID) use ($app) {

	//get JSON body and parse to array
	$req = json_decode($app->request->getBody());
	$status = $req->status;

	//REST-Service Response
	$response = array();

	//Movie from session
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!= '') {

		if($status == "watched" || $status == "watchlist" || $status == "deleted" )
		{
			//is movie data in session?
			if (!empty($session['matches'])) {

				$movie = null;

				foreach ($session['matches'] as $match) {
					if ($movieID == $match['movieID']) {
						$movie = $match;
					}
				}

				if ($movie) {
					$movie['language'] = "de";

					if ($status == "watched") {

						$watchers = $movie['watchers'] + 1;
						date_default_timezone_set('Europe/Berlin');

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
					$movielist_stmt->bind_param('ii', $movieID, $userID);

					//execute query
					$movie_stmt->execute();
					$movie_stmt->store_result();

					//check if movie is already in db, if not, add it to db with info
					if ($movie_stmt->num_rows != 1) {

						$insert_watched_movie = $db->preparedStmt("INSERT INTO movie(movieID, original_title, watchers) VALUES (?,?,?)");
						$insert_watched_movie->bind_param("iss", $movieID, $movie['original_title'], $watchers);

						$insert_watched_movie->execute();
						$insert_watched_movie->close();

						$insert_movielist = $db->preparedStmt("INSERT INTO movielist(movieID,userID,status,watched_date) VALUES (?,?,?,?)");
						$insert_movielist->bind_param("iiss", $movieID, $userID, $status, $watched_date);

						$insert_movielist->execute();
						$insert_movielist->close();

						$insert_movieinfo = $db->preparedStmt("INSERT INTO movieinfo(movieID,language,plot,title,release_date,poster) VALUES (?,?,?,?,?,?)");
						$insert_movieinfo->bind_param("isssss", $movieID, $movie['language'], $movie['plot'], $movie['title'], $movie['release_date'], $movie['poster']);

						$insert_movieinfo->execute();
						$insert_movieinfo->close();

					} else {

						$movielist_stmt->execute();
						$movielist_stmt->store_result();
						//check if movie is in user's list. If it is, just change status, if not, add to db
						if ($movielist_stmt->num_rows != 1) {
							if ($status == "watched") {
								$update_watched_movie = $db->preparedStmt("UPDATE movie SET watchers = ? WHERE movieID = ?");
								$update_watched_movie->bind_param("ii", $watchers, $movieID);

								$update_watched_movie->execute();
								$update_watched_movie->close();
							}
							$insert_movielist = $db->preparedStmt("INSERT INTO movielist(movieID,userID,status,watched_date) VALUES (?,?,?,?)");
							$insert_movielist->bind_param("iiss", $movieID, $userID, $status, $watched_date);

							$insert_movielist->execute();
							$insert_movielist->close();

						} else {

							if ($status == "watched") {

								//Add 1 watcher to movie
								$update_watched_movie = $db->preparedStmt("UPDATE movie SET watchers = ? WHERE movieID = ?");
								$update_watched_movie->bind_param("ii", $watchers, $movieID);

								$update_watched_movie->execute();
								$update_watched_movie->close();
							}

							//Update movie status
							$update_watchlist = $db->preparedStmt("UPDATE movielist SET status=?,watched_date=? WHERE movieID=? AND userID=?");
							$update_watchlist->bind_param("ssii", $status, $watched_date, $movieID, $userID);

							$update_watchlist->execute();
							$update_watchlist->close();

						}
					}

					$response['status'] = "success";
					echoResponse(200, $response);
				} else {
					$response['status'] = "error";
					$response['message'] = "Movie not found";
					echoResponse(201, $response);
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
$app->get('/movies/watchlist', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if(!empty($session['userID'])) {
		$userID = $session['userID'];

		//prepare sql statement and bind parameters
		$movielist_stmt = $db->preparedStmt("SELECT m.movieID, m.original_title, m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster, ml.status, ml.user_rating 
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID 
JOIN movielist AS ml ON ml.movieID = m.movieID WHERE userID= ? AND status= 'watchlist'");
		$movielist_stmt->bind_param('i', $userID);
		$movielist_stmt->execute();
		$movielist_stmt->store_result();
		$movielist_stmt->bind_result($db_movieID,$db_original_title,$db_ratings,$db_rating_points,$db_watchers, $db_title, $db_plot, $db_release_date, $db_poster, $db_status, $db_user_rating);

		if($movielist_stmt->num_rows>0){
			$response['matches'] = array();
			$movie = array();
			while ($movielist_stmt->fetch()) {
				$movie['movieID'] = $db_movieID;
				$movie['title'] = $db_title;
				$movie['plot'] = $db_plot;
				$movie['release_date'] = $db_release_date;
				$movie['original_title'] = $db_original_title;
				$movie['poster'] = $db_poster;
				$movie['status'] = $db_status;
				$movie['user_rating'] = $db_user_rating;
				$movie['watchers'] = $db_watchers;
				$movie['rating_points'] = $db_rating_points;
				array_push($response['matches'], $movie);
			}
			$response['status'] = "success";
			echoResponse(200, $response);
		} else {
			$response['status'] = "error";
			$response['message'] = "No movies in watchlist";
			echoResponse(201, $response);
		}
		$movielist_stmt->free_result();
		$movielist_stmt->close();

	}else{
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}
});

//GET Watched
$app->get('/movies/watched', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if(!empty($session['userID'])) {
		$userID = $session['userID'];

		//prepare sql statement and bind parameters
		$movielist_stmt = $db->preparedStmt("SELECT m.movieID, m.original_title, m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster, ml.status, ml.user_rating 
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID 
JOIN movielist AS ml ON ml.movieID = m.movieID WHERE userID= ? AND status= 'watched'");
		$movielist_stmt->bind_param('i', $userID);
		$movielist_stmt->execute();
		$movielist_stmt->store_result();
		$movielist_stmt->bind_result($db_movieID,$db_original_title,$db_ratings,$db_rating_points,$db_watchers, $db_title, $db_plot, $db_release_date, $db_poster, $db_status, $db_user_rating);

		if($movielist_stmt->num_rows>0){
			$response['matches'] = array();
			$movie = array();
			while ($movielist_stmt->fetch()) {
				$movie['movieID'] = $db_movieID;
				$movie['title'] = $db_title;
				$movie['plot'] = $db_plot;
				$movie['release_date'] = $db_release_date;
				$movie['original_title'] = $db_original_title;
				$movie['poster'] = $db_poster;
				$movie['status'] = $db_status;
				$movie['user_rating'] = $db_user_rating;
				$movie['watchers'] = $db_watchers;
				$movie['rating_points'] = $db_rating_points;
				array_push($response['matches'], $movie);
			}
			$response['status'] = "success";
			echoResponse(200, $response);
		} else {
			$response['status'] = "error";
			$response['message'] = "No movies marked as watched";
			echoResponse(201, $response);
		}
		$movielist_stmt->free_result();
		$movielist_stmt->close();

	}else{
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}
});

//PUT Rating
$app->put('/movies/:movieID/rating', function($movieID) use ($app) {

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
		$userID=$session['userID'];

		if(isMovieWatched($movieID,$userID)){

			$sel_rating = $db->preparedStmt("SELECT user_rating FROM movielist WHERE movieID = ? AND userID = ?");
			$sel_rating->bind_param("ii", $movieID,$userID);
			$sel_rating->execute();
			$sel_rating->bind_result($db_user_rating);
			$sel_rating->fetch();
			$sel_rating->close();

			$sel_rating = $db->preparedStmt("SELECT rating_points, ratings FROM movie WHERE movieID = ?");
			$sel_rating->bind_param("i", $movieID);
			$sel_rating->execute();
			$sel_rating->bind_result($db_rating_points, $db_ratings);
			$sel_rating->fetch();
			$sel_rating->close();

			$ratings = $db_ratings + 1;
			$rating_points = $db_rating_points + $rating;

			//Add one rating to ratings count if movie was not already rated by user
			if(movieIsRated($userID,$movieID)){
				$ratings = $db_ratings;
				$rating_points = $db_rating_points - $db_user_rating + $rating;
			}

			//Update movie user rating
			$update_movielist = $db->preparedStmt("UPDATE movielist SET user_rating = ? WHERE movieID=? AND userID=?");
			$update_movielist->bind_param("iii", $rating,$movieID,$userID);
			$update_movielist->execute();
			$update_movielist->close();


			$update_movielist = $db->preparedStmt("UPDATE movie SET ratings = ?, rating_points = ? WHERE movieID=?");
			$update_movielist->bind_param("iii", $ratings,$rating_points,$movieID);
			$update_movielist->execute();
			$update_movielist->close();

			$response['status'] = "success";
			echoResponse(200, $response);
		}else {
			$response['status'] = "error";
			$response['message'] = "Cannot give rating for unwatched movies";
			echoResponse(201, $response);
		}
	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}

});

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
					$response['message'] = "Request sent";
					echoResponse(200, $response);
				} else {
					$response['status'] = "error";
					$response['message'] = "Relationship already exists";
					echoResponse(201, $response);
				}

			} else {
				$response['status'] = "error";
				$response['message'] = "User not found";
				echoResponse(201, $response);
			}
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



//GET Friend requests
$app->get('/friends/requests', function() use ($app) {
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
			$response['status'] = "success";
			$response['message'] = "No friends found";
			echoResponse(200, $response);
		}

		$sel_friends->free_result();
		$sel_friends->close();

	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
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

			if(isMovieWatched($movieID,$userID)){
				//Insert new recommendation
				$stmt_recommendations = $db->preparedStmt("INSERT INTO recommendations(fromID,toID,movieID) VALUES(?,?,?)");
				$stmt_recommendations->bind_param('iii',$userID,$friendID,$movieID);
				$stmt_recommendations->execute();
				$stmt_recommendations->close();

				$response['status'] = "success";
				echoResponse(200, $response);
			}else{
				$response['status'] = "error";
				$response['message'] = "Cannot recommend a movie that has not been watched";
				echoResponse(201, $response);
			}
		} else{
			$response['status'] = "error";
			$response['message'] = "Friend not added";
			echoResponse(201, $response);
		}

	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
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
		$stmt_rec = $db->preparedStmt("SELECT r.fromID,u.name,r.movieID FROM recommendations AS r JOIN user AS u ON r.fromID = u.userID WHERE r.toID = ?");
		$stmt_rec->bind_param('i',$userID);
		$stmt_rec->execute();

		$stmt_rec->store_result();
		$stmt_rec->bind_result($db_fromID,$db_name,$db_movieID);

		$response['recommendations'] = array();


		if($stmt_rec->num_rows>0){

			echo $userID;

			while($stmt_rec->fetch()){
				$reco = array();
				$reco['from'] = $db_fromID;
				$reco['name'] = $db_name;

				$movie_stmt = $db->preparedStmt("SELECT m.movieID, m.original_title, m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID WHERE m.movieID= ?");
				$movie_stmt->bind_param('i',$db_movieID);
				$movie_stmt->execute();
				$movie_stmt->store_result();
				$movie_stmt->bind_result($db_movieID,$db_original_title,$db_ratings,$db_rating_points,$db_watchers, $db_title, $db_plot, $db_release_date, $db_poster);

				if($movie_stmt->num_rows>0){

					$movie_stmt->fetch();

					$reco['movieID'] = $db_movieID;
					$reco['title'] = $db_title;
					$reco['plot'] = $db_plot;
					$reco['release_date'] = $db_release_date;
					$reco['original_title'] = $db_original_title;
					$reco['poster'] = $db_poster;
					$reco['watchers'] = $db_watchers;
					$reco['rating_points'] = $db_rating_points;

					$movielist_stmt = $db->preparedStmt("SELECT user_rating, status FROM movielist WHERE movieID= ? AND userID = ?");
					$movielist_stmt->bind_param('ii',$db_movieID, $userID);
					$movielist_stmt->execute();
					$movielist_stmt->store_result();
					$movielist_stmt->bind_result($db_user_rating, $db_status);

					if($movielist_stmt->num_rows>0){
						$movielist_stmt->fetch();
						$reco['status'] = $db_status;
						$reco['user_rating'] = $db_user_rating;
					} else{
						$reco['status'] = null;
						$reco['user_rating'] = null;
					}

					$movielist_stmt->free_result();
					$movielist_stmt->close();

				}

				$movie_stmt->free_result();
				$movie_stmt->close();
				array_push($response['recommendations'],$reco);
			}

			$response['status'] = "success";
			echoResponse(200, $response);

		} else{
			$response['status'] = "error";
			$response['message'] = "No new recommendations";
			echoResponse(201, $response);
		}

		$stmt_rec->free_result();
		$stmt_rec->close();

	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
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
				echoResponse(200, $response);
			}else {
				$response['status'] = "error";
				$response['message'] = "No movies in common";
				echoResponse(201, $response);
			}

		} else {
			$response['status'] = "error";
			$response['message'] = "Not a friend";
			echoResponse(201, $response);
		}

	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}

});

//PUT Passwort (Passwort ändern)
$app->post('/user/password', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());

	//check if user is logged in
	if($session['userID']!='') {
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

//PUT Email (Email ändern)
$app->post('/user/email', function() use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();
	$req = json_decode($app->request->getBody());

	//check if user is logged in
	if($session['userID']!='') {
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

//GET Color
$app->get('/user/color', function() use ($app) {
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if($session['userID']!='') {

		$sel_col= $db->preparedStmt("SELECT color FROM user WHERE userID = ?");
		$sel_col->bind_param('i', $userID);
		$sel_col->execute();

		$sel_col->bind_result($db_color);
		$sel_col->fetch();

		$response['color'] = $db_color;;
		$response['status'] = "success";
		echoResponse(200, $response);

		$sel_col->free_result();
		$sel_col->close();

	} else {
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}

});

