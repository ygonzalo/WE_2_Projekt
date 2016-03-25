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
			$config_response = file_get_contents('http://api.themoviedb.org/3/configuration?api_key=fc6230097457cdf6f547373206e12a5d');
			$config = json_decode($config_response);

			$matches = $response_decoded['results'];

			//add each movie to array in response
			foreach ($matches as $item){

				$poster = $config->images->base_url.$config->images->poster_sizes[2]. $item['poster_path'];

				$movie['movieID'] = $item['id'];
				$movie['title'] = $item['title'];
				$movie['plot'] = $item['overview'];
				$movie['release_date'] = $item['release_date'];
				$movie['original_title'] = $item['original_title'];

				$movie['poster'] = $poster;

				//search the users movie list
				$db_movie_list = $db->getSingleRecord("SELECT `status`,`user_rating`,`watched_date` FROM `movielist` WHERE `movieID` = ".$movie['movieID']);

				$movie['status'] = null;
				$movie['user_rating'] = null;
				$movie['watched_date'] = null;

				if(!empty($db_movie_list)){
					$movie['status'] = $db_movie_list['status'];
					$movie['user_rating'] = $db_movie_list['user_rating'];
					$movie['watched_date'] = $db_movie_list['watched_date'];
				}

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

				$movie['watched_date'] = null;
				$movie['status'] = $req->status;
				$movie['userID'] = $session['userID'];
				$movie['language'] = "de";

				//get userID
				$userID = intval($session['userID']);

				date_default_timezone_set('UTC');

				//get movie if already used
				$movie_user_list = $db->getSingleRecord("SELECT * FROM `movielist` WHERE `userID`= ".$userID." AND `movieID`= ".$movieID );
				$movie_saved = $db->getSingleRecord("SELECT * FROM `movie` WHERE  `movieID`= ".$movieID );

				//check if movie is already in db, if not, add it to db with info
				if($movie_saved == null) {
					if($status == "watched") {
						$movie['watched_date'] = date("Y-m-d");

						//Insert new movie (with new watcher)
						$db->dbQuery('INSERT INTO movie(movieID, original_title, watchers) VALUES ('.$movieID.',"'.$movie['original_title'].'",watchers+1)');
						echo 'INSERT INTO movie(movieID, original_title, watchers) VALUES ('.$movieID.',"'.$movie['original_title'].'"watchers+1)';

						//Insert watched movie
						$movie_list_table_cols = array('movieID','userID','status','watched_date');
						$db->insertIntoTable($movie,$movie_list_table_cols,'movielist');
					} else {
						//Insert new movie
						$movie_table_cols = array('movieID','original_title');
						$db->insertIntoTable($movie,$movie_table_cols,'movie');

						//Insert movie to user's list
						$movie_list_table_cols = array('movieID','userID','status');
						$db->insertIntoTable($movie,$movie_list_table_cols,'movielist');
					}

					//Insert movie information
					$movie_list_table_cols = array('movieID','language','plot','title',"release_date", "poster");
					$db->insertIntoTable($movie,$movie_list_table_cols,'movieinfo');

					$response['status'] = "success";
					echoResponse(200, $response);
				} else {
					//check if movie is in user's list. If it is, just change status, if not, add to db
					if($movie_user_list == null){
						if($status == "watched") {
							$movie['watched_date'] = date("Y-m-d");

							//Add 1 watcher to movie
							$update_values = 'watchers=watchers+1';
							$db->updateRecord('movie', $update_values, "movieID=".$movieID);

							//Insert watched movie
							$movie_list_table_cols = array('movieID','userID','status','watched_date');
							$db->insertIntoTable($movie,$movie_list_table_cols,'movielist');
						} else {

							//Insert movie to user's list
							$movie_list_table_cols = array('movieID','userID','status');
							$db->insertIntoTable($movie,$movie_list_table_cols,'movielist');
						}
					}else {

						if($status == "watched") {
							$movie['watched_date'] = date("Y-m-d");

							//Update movie status with watched date
							$update_values = 'status="'.$movie['status'].'",watched_date="'.$movie['watched_date'].'"';
							$db->updateRecord('movielist', $update_values, "movieID=$movieID AND userID=$userID");

							//Add 1 watcher to movie
							$update_values = 'watchers=watchers+1';
							$db->updateRecord('movie', $update_values, "movieID=".$movieID);
						} else {

							//Update movie status
							$update_values = 'status="'.$movie['status'].'"';
							$db->updateRecord('movielist', $update_values, "movieID=$movieID AND userID=$userID");
						}

						$response['status'] = "success";
						echoResponse(200, $response);
					}
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

		$watchlist = $db->getRecords("SELECT m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, ml.status FROM movie AS m JOIN movieinfo As mi ON mi.movieID = m.movieID JOIN movielist AS ml ON ml.movieID = m.movieID WHERE userID=$userID AND status= \"watchlist\"");

		if(mysqli_num_rows($watchlist)>0){

			$response['matches'] = array();
			while ($movie = $watchlist->fetch_assoc()) {
				array_push($response['matches'], $movie);
			}
			$response['status'] = "success";
			echoResponse(200, $response);
		}
	}
});

//GET Watched

//POST Rating

//POST Add friend

//GET Friends


?>