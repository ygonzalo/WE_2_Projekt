<?php

require_once("helper_methods.php");

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

			//Check is result of search is empty
			if(!empty($matches)){
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
				$response['code'] = 204;
				$response['status'] = "success";
				echoResponse(200, $response);
			} else {
				$response['status'] = "success";
				$response['code'] = 205;
				echoResponse(200, $response);
			}
		}
	} else {
		$response['status'] = "error";
		$response['code'] = 501;
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
					$response['code'] = 206;
					echoResponse(200, $response);
				} else {
					$response['status'] = "error";
					$response['code'] = 506;
					echoResponse(201, $response);
				}

			}else{
				$response['status'] = "error";
				$response['code'] = 507;
				echoResponse(201, $response);
			}
		} else {
			$response['status'] = "error";
			$response['code'] = 508;
			echoResponse(201, $response);
		}

	}else{
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//GET Watchlist
$app->get('/movies/watchlist(/:id)', function($id='') use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if(!empty($session['userID'])) {
		$userID = $session['userID'];

		if($id == null){
			$id = $userID;
		}

		//prepare sql statement and bind parameters
		$movie_stmt = $db->preparedStmt("SELECT m.movieID, m.original_title, m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID 
JOIN movielist AS ml ON ml.movieID = m.movieID WHERE userID= ? AND status= 'watchlist'");
		$movie_stmt->bind_param('i', $id);
		$movie_stmt->execute();
		$movie_stmt->store_result();
		$movie_stmt->bind_result($db_movieID,$db_original_title,$db_ratings,$db_rating_points,$db_watchers, $db_title, $db_plot, $db_release_date, $db_poster);

		if($movie_stmt->num_rows>0){
			$response['matches'] = array();
			$movie = array();
			while ($movie_stmt->fetch()) {

				$movielist_stmt = $db->preparedStmt("SELECT ml.status, ml.user_rating FROM movielist AS ml WHERE userID= ? AND movieID= ?");
				$movielist_stmt->bind_param('ii', $userID,$db_movieID);
				$movielist_stmt->execute();
				$movielist_stmt->bind_result($db_status, $db_user_rating);
				$movielist_stmt->fetch();

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

				$movielist_stmt->close();
			}
			$response['status'] = "success";
			$response['code'] = 207;
			echoResponse(200, $response);
		} else {
			$response['status'] = "success";
			$response['code'] = 208;
			echoResponse(200, $response);
		}
		$movie_stmt->free_result();
		$movie_stmt->close();

	}else{
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}
});

//GET Watched
$app->get('/movies/watched(/:id)', function($id='') use ($app) {

	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//check if user is logged in
	if(!empty($session['userID'])) {
		$userID = $session['userID'];

		if($id == null){
			$id = $userID;
		}

		$response['matches'] = array();

		//prepare sql statement and bind parameters
		$movie_stmt = $db->preparedStmt("SELECT m.movieID, m.original_title, m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID 
JOIN movielist AS ml ON ml.movieID = m.movieID WHERE userID= ? AND status= 'watched'");
		$movie_stmt->bind_param('i', $id);
		$movie_stmt->execute();
		$movie_stmt->store_result();
		$movie_stmt->bind_result($db_movieID,$db_original_title,$db_ratings,$db_rating_points,$db_watchers, $db_title, $db_plot, $db_release_date, $db_poster);

		if($movie_stmt->num_rows>0){
			$movie = array();
			while ($movie_stmt->fetch()) {
				$movielist_stmt = $db->preparedStmt("SELECT ml.status, ml.user_rating FROM movielist AS ml WHERE userID= ? AND movieID= ?");
				$movielist_stmt->bind_param('ii', $userID,$db_movieID);
				$movielist_stmt->execute();
				$movielist_stmt->bind_result($db_status, $db_user_rating);
				$movielist_stmt->fetch();

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

				$movielist_stmt->close();
			}
			$response['status'] = "success";
			$response['code'] = 209;
			echoResponse(200, $response);
		} else {
			$response['status'] = "success";
			$response['code'] = 210;
			echoResponse(200, $response);
		}
		$movie_stmt->free_result();
		$movie_stmt->close();

	}else{
		$response['status'] = "error";
		$response['code'] = 501;
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
			$response['code'] = 509;
			echoResponse(201, $response);
		}
	} else {
		$response['status'] = "error";
		$response['code'] = 501;
		echoResponse(201, $response);
	}

});
