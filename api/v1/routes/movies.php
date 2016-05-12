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
					$movie['liked'] = null;
					$movie['watched_date'] = null;
					$movie['watchers'] = 0;
					$movie['likes'] = 0;

					//prepare sql statements and bind parameters
					$sel_movielist = $db->preparedStmt("SELECT status,liked,watched_date FROM movielist WHERE movieID = ? AND userID = ?");
					$sel_movielist->bind_param('ii', $movie['movieID'], $session['userID']);

					$sel_movie = $db->preparedStmt("SELECT watchers,likes FROM movie WHERE movieID = ?");
					$sel_movie->bind_param('i', $movie['movieID']);

					//execute query
					$sel_movielist->execute();
					$sel_movielist->store_result();
					$sel_movielist->bind_result($db_status,$db_liked,$db_watched_date);
					if($sel_movielist->num_rows>0){
						while($sel_movielist->fetch()) {
							$movie['status'] = $db_status;
							$movie['liked'] = (bool)$db_liked;
							$movie['watched_date'] = $db_watched_date;
						}
					}
					$sel_movielist->free_result();
					$sel_movielist->close();

					$sel_movie->execute();
					$sel_movie->store_result();
					$sel_movie->bind_result($db_watchers,$db_likes);
					if($sel_movie->num_rows>0){
						while ($sel_movie->fetch()) {
							$movie['watchers'] = $db_watchers;
							$movie['likes'] = $db_likes;
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

//GET Film by ID
$app->get('/movies/details/:movieID', function($movieID) use ($app) {
	$db = new DB();
	$session = $db->getSession();
	$response = array();
	$matches = array();
	$matches['matches'] = array();
	$_SESSION['matches'] = array();

	//check if user is authenticated
	if($session['userID'] != '') {
		$userID = $session['userID'];
		$movie = array();

		$sel_movie = $db->preparedStmt('SELECT m.movieID, m.original_title, m.likes, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID WHERE m.movieID = ?');
		$sel_movie->bind_param('i',$movieID);
		$sel_movie->execute();
		$sel_movie->store_result();

		if($sel_movie->num_rows>0) {

			$sel_movie->bind_result($db_movieID,$db_original_title,$db_likes,$db_watchers, $db_title, $db_plot, $db_release_date, $db_poster);
			$sel_movie->fetch();

			$movie['movieID'] = $db_movieID;
			$movie['title'] = $db_title;
			$movie['plot'] = $db_plot;
			$movie['release_date'] = date("d.m.Y", strtotime($db_release_date));
			$movie['original_title'] = $db_original_title;
			$movie['poster'] = $db_poster;
			$movie['watchers'] = $db_watchers;
			$movie['likes'] = $db_likes;

			$movie['status'] = null;
			$movie['liked'] = false;
			$movie['watched_date'] = null;

			$sel_status = $db->preparedStmt('SELECT status, liked, watched_date FROM movielist WHERE movieID = ? AND userID = ?');
			$sel_status->bind_param('ii',$movieID,$userID);
			$sel_status->execute();
			$sel_status->store_result();
			if($sel_status->num_rows>0){
				$sel_status->bind_result($db_status,$db_liked,$db_watched_date);
				$sel_status->fetch();

				$movie['status'] = $db_status;
				$movie['liked'] = (bool)$db_liked;
				$movie['watched_date'] = $db_watched_date;
			}

			$sel_status->free_result();
			$sel_status->close();

			$response = array('movie' => $movie);

			array_push($matches['matches'],$movie);
			$_SESSION['matches'] = $matches['matches'];

			$response['status'] = "success";
			echoResponse(200, $response);
		} else {
			$search_response = file_get_contents('https://api.themoviedb.org/3/movie/'.$movieID.'?api_key=fc6230097457cdf6f547373206e12a5d&language=de');
			$response_decoded = json_decode($search_response, true);

			//build poster path with size configuration
			$poster = "https://image.tmdb.org/t/p/w185". $response_decoded['poster_path'];

			$movie['movieID'] = $response_decoded['id'];
			$movie['title'] = $response_decoded['title'];
			$movie['plot'] = $response_decoded['overview'];
			$movie['release_date'] = $response_decoded['release_date'];
			$movie['original_title'] = $response_decoded['original_title'];
			$movie['poster'] = $poster;
			$movie['watchers'] = 0;
			$movie['likes'] = 0;

			$movie['status'] = null;
			$movie['liked'] = false;
			$movie['watched_date'] = null;

			$response = array('movie' => $movie);

			array_push($matches['matches'],$movie);
			$_SESSION['matches'] = $matches['matches'];

			$response['status'] = "success";
			echoResponse(200, $response);
		}

		$sel_movie->free_result();
		$sel_movie->close();

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

		//get userID
		$userID = $session['userID'];


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

				//Add 1 watcher
				if ($status == "watched" && !isMovieWatched($movieID,$userID)) {

					$watchers = $movie['watchers'] + 1;
					date_default_timezone_set('Europe/Berlin');

					$watched_date = date("Y-m-d");
				} else if($status == "watchlist" && isMovieWatched($movieID,$userID)) {

					$watchers = $movie['watchers'] - 1;
					$watched_date = null;
				} else {

					$watchers = $movie['watchers'];
					$watched_date = null;
				}


				$sel_status = $db->preparedStmt("SELECT status,liked FROM movielist WHERE movieID=? AND userID=?");
				$sel_status->bind_param("ii", $movieID, $userID);

				$sel_watchers = $db->preparedStmt("SELECT watchers,likes FROM movie WHERE movieID=?");
				$sel_watchers->bind_param("i", $movieID);

				if($status == "watched" || $status == "watchlist")
				{

					//check if movie is already in db, if not, add it to db with info
					if (!isMovieInDB($movieID)) {

						$insert_watched_movie = $db->preparedStmt("INSERT INTO movie(movieID, original_title, watchers) VALUES (?,?,?)");
						$insert_watched_movie->bind_param("iss", $movieID, $movie['original_title'], $watchers);

						$insert_watched_movie->execute();
						$insert_watched_movie->close();

						$insert_movielist = $db->preparedStmt("INSERT INTO movielist(movieID,userID,status,watched_date) VALUES (?,?,?,?)");
						$insert_movielist->bind_param("iiss", $movieID, $userID, $status, $watched_date);

						$insert_movielist->execute();
						$insert_movielist->close();

						$insert_movieinfo = $db->preparedStmt("INSERT INTO movieinfo(movieID,plot,title,release_date,poster) VALUES (?,?,?,?,?)");
						$insert_movieinfo->bind_param("issss", $movieID, $movie['plot'], $movie['title'], $movie['release_date'], $movie['poster']);

						$insert_movieinfo->execute();
						$insert_movieinfo->close();

					} else {

						//check if movie is in user's list. If it is, just change status, if not, add to db
						if (!isMovieInMovielist($movieID,$userID)) {
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


					$sel_status->execute();
					$sel_status->bind_result($db_status,$db_liked);
					$sel_status->fetch();
					$sel_status->close();

					$sel_watchers->execute();
					$sel_watchers->bind_result($db_watchers,$db_likes);
					$sel_watchers->fetch();
					$sel_watchers->close();

					$response['movie_status'] = $db_status;
					$response['watchers'] = $db_watchers;
					$response['likes'] = $db_likes;
					$response['liked'] = (bool)$db_liked;
					$response['status'] = "success";
					$response['code'] = 206;
					echoResponse(200, $response);

				} else if($status == "deleted" ) {

					//Check if movie is in user's movielist
					if (isMovieInMovielist($movieID, $userID)) {

						$del_movielist = $db->preparedStmt("DELETE FROM movielist WHERE movieID = ? AND userID = ?");
						$del_movielist->bind_param('ii', $movieID, $userID);
						$del_movielist->execute();
						$del_movielist->close();

						$watchers = $movie['watchers'];
						$likes = $movie['likes'];

						//Remove 1 watcher if movie was watched
						if (isMovieWatched($movieID, $userID)) {

							$watchers = $movie['watchers'] - 1;

							//Remove 1 like if movie was liked
							if (isMovieLiked($movieID, $userID)) {

								$likes = $movie['likes'] - 1;
							}
						}

						$update_movie = $db->preparedStmt("UPDATE movie SET watchers=?, likes=? WHERE movieID=?");
						$update_movie->bind_param('iii',$watchers,$likes,$movieID);
						$update_movie->execute();
						$update_movie->close();
					}


					$sel_status->execute();
					$sel_status->bind_result($db_status,$db_liked);
					$sel_status->fetch();
					$sel_status->close();

					$sel_watchers->execute();
					$sel_watchers->bind_result($db_watchers,$db_likes);
					$sel_watchers->fetch();
					$sel_watchers->close();

					$response['movie_status'] = $db_status;
					$response['watchers'] = $db_watchers;
					$response['likes'] = $db_likes;
					$response['liked'] = (bool)$db_liked;
					$response['status'] = "success";
					$response['code'] = 206;
					echoResponse(200, $response);
				} else {
					$response['status'] = "error";
					$response['code'] = 508;
					echoResponse(201, $response);
				}
			} else{
				$response['status'] = "error";
				$response['code'] = 506;
				echoResponse(201, $response);
			}
		} else {
			$response['status'] = "error";
			$response['code'] = 507;
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
		$movie_stmt = $db->preparedStmt("SELECT m.movieID, m.original_title, m.likes, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID 
JOIN movielist AS ml ON ml.movieID = m.movieID WHERE userID= ? AND status= 'watchlist'");
		$movie_stmt->bind_param('i', $id);
		$movie_stmt->execute();
		$movie_stmt->store_result();
		$movie_stmt->bind_result($db_movieID,$db_original_title,$db_likes,$db_watchers, $db_title, $db_plot, $db_release_date, $db_poster);

		if($movie_stmt->num_rows>0){
			$response['matches'] = array();
			$movie = array();
			while ($movie_stmt->fetch()) {

				$movielist_stmt = $db->preparedStmt("SELECT ml.status, ml.liked FROM movielist AS ml WHERE userID= ? AND movieID= ?");
				$movielist_stmt->bind_param('ii', $userID,$db_movieID);
				$movielist_stmt->execute();
				$movielist_stmt->bind_result($db_status, $db_liked);
				$movielist_stmt->fetch();

				$movie['movieID'] = $db_movieID;
				$movie['title'] = $db_title;
				$movie['plot'] = $db_plot;
				$movie['release_date'] = $db_release_date;
				$movie['original_title'] = $db_original_title;
				$movie['poster'] = $db_poster;
				$movie['status'] = $db_status;
				$movie['liked'] = (bool)$db_liked;
				$movie['watchers'] = $db_watchers;
				$movie['likes'] = $db_likes;
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
		$movie_stmt = $db->preparedStmt("SELECT m.movieID, m.original_title, m.likes, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster
FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID 
JOIN movielist AS ml ON ml.movieID = m.movieID WHERE userID= ? AND status= 'watched'");
		$movie_stmt->bind_param('i', $id);
		$movie_stmt->execute();
		$movie_stmt->store_result();
		$movie_stmt->bind_result($db_movieID,$db_original_title,$db_likes,$db_watchers, $db_title, $db_plot, $db_release_date, $db_poster);

		if($movie_stmt->num_rows>0){
			$movie = array();
			while ($movie_stmt->fetch()) {
				$movielist_stmt = $db->preparedStmt("SELECT ml.status, ml.liked FROM movielist AS ml WHERE userID= ? AND movieID= ?");
				$movielist_stmt->bind_param('ii', $userID,$db_movieID);
				$movielist_stmt->execute();
				$movielist_stmt->bind_result($db_status, $db_liked);
				$movielist_stmt->fetch();

				$movie['movieID'] = $db_movieID;
				$movie['title'] = $db_title;
				$movie['plot'] = $db_plot;
				$movie['release_date'] = $db_release_date;
				$movie['original_title'] = $db_original_title;
				$movie['poster'] = $db_poster;
				$movie['status'] = $db_status;
				$movie['liked'] = (bool)$db_liked;
				$movie['watchers'] = $db_watchers;
				$movie['likes'] = $db_likes;
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

//PUT Like/unlike
$app->put('/movies/:movieID/like', function($movieID) use ($app) {

	//get JSON body and parse to array
	$req = json_decode($app->request->getBody());
	$like = $req->like;

	//REST-Service Response
	$response = array();

	//get session
	$db = new DB();
	$session = $db->getSession();

	//check if user is authenticated
	if($session['userID'] != '') {
		$userID=$session['userID'];

		if(isMovieWatched($movieID,$userID)){

			$sel_rating = $db->preparedStmt("SELECT likes FROM movie WHERE movieID = ?");
			$sel_rating->bind_param("i", $movieID);
			$sel_rating->execute();
			$sel_rating->bind_result($db_likes);
			$sel_rating->fetch();
			$sel_rating->close();

			$liked_int = (int)$like;

			//Update movie user like
			$update_movielist = $db->preparedStmt("UPDATE movielist SET liked = ? WHERE movieID=? AND userID=?");
			$update_movielist->bind_param("iii", $liked_int,$movieID,$userID);
			$update_movielist->execute();
			$update_movielist->close();

			$likes = $db_likes;

			if($like){
				$likes += 1;
			} else {
				$likes -= 1;
			}

			$update_movie = $db->preparedStmt("UPDATE movie SET likes=? WHERE movieID=?");
			$update_movie->bind_param("ii",$likes,$movieID);
			$update_movie->execute();
			$update_movie->close();

			$response['liked'] = $like;
			$response['likes'] = $likes;
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
