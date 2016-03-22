<?php
//POST Film by name
$app->post('/movie', function() use ($app) {
	//parse JSON body of request
	$req = json_decode($app->request->getBody());
	$req_title = $req->title;
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

				$movie['id'] = $item['id'];
				$movie['title'] = $item['title'];
				$movie['plot'] = $item['overview'];
				$movie['release_date'] = $item['release_date'];
				$movie['poster'] = $poster;

				//search the users movie list
				$db_movie_list = $db->getSingleRecord("SELECT `status`,`user_rating`,`watched_date` FROM `movieList` WHERE `movieID` = ".$movie['id']);

				$movie['status'] = null;
				$movie['rating'] = null;
				$movie['date'] = null;

				if(!empty($db_movie_list)){
					$movie['status'] = $db_movie_list['status'];
					$movie['rating'] = $db_movie_list['rating'];
					$movie['date'] = $db_movie_list['date'];
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
	//REST-Service Response
	$response = array();
	//Movie from session
	$movie = array();
	$db = new DB();
	$table_name = 'movieList';
	$session = $db->getSession();

	//is User logged in?
	if($session['userID']!= '') {

		//is movie data in session?
		if (!empty($session['matches'])){

			$sess=array();
			$sess["matches"]=$session['matches'];
			$title=$movie['title'];
			$plot=$movie['plot'];
			$release_date=$movie['release_date'];
			$poster=$movie['poster'];

			//get userID
			$userID = intval($session['userID']);

			//get data from JSON
			$status = $req->status;
			$movieID = $req->movieID;
			$date	= date("Y-m-d");

			//get movie if already used
			$watchedmovie = $db->getSingleRecord("SELECT * FROM `".$table_name."` WHERE `userID`= ".$userID." AND `movieID`= ".$movieID );


			//debug
			echo "UserID: ".$userID."<br>";
			echo "Status: ".$status."<br>";
			echo "MovieID: ".$movieID."<br>";
			echo "Table_name: ".$table_name."<br>";
			echo "Movie Daten: ";
			print_r($watchedmovie);
			echo "<br>";

			//movie already in db, just change status
			if($watchedmovie!=''){

				echo "movie already used";


				//movie not in db, add relationship
			}else{
				echo "new movie";

				$object = (object) array($movieID, $userID, $status, $date);
				$column_names = array('movieID', 'userID' , 'status', 'date');
				$db->insertIntoTable($object,$column_names,$table_name);


			}
		}else{
			$response['status'] = "error";
			$response['message'] = "No movie data";
			echoResponse(201, $response);
		}
	}else{
		$response['status'] = "error";
		$response['message'] = "Not logged in";
		echoResponse(201, $response);
	}

});

//GET Watchlist
$app->post('/watchlist', function() use ($app) {

	//get JSON body and parse to array
	$req = json_decode($app->request->getBody());
	//REST-Service Response
	$response = array();
	$db = new DB();
	$session = $db->getSession();

	//is User logged in?
	if(!empty($session['userID'])) {

		$userID = $session['userID'];

		$movieIDS = $db->getRecords("SELECT m.ratings, m.ratingPoints, m.watchers, mi.title, mi.plot, mi.release, ml. FROM movie AS m JOIN movieInfo As mi ON `movieID` JOIN movieList AS ml ON `movieID` WHERE `userID`=$userID AND `status`= \"watched\"");


		/*SELECT m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, ml.watched_date  FROM movie AS m JOIN movieInfo As mi ON `movieID` JOIN movieList AS ml ON `movieID` WHERE `userID`=1 AND `status`= "watched"

		SELECT m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, ml.watched_date
		FROM movieList  WHERE `userID`=1 AND `status`= "watched" AS ml
		LEFT JOIN movieinfo AS mi ON mi.movieID
		LEFT JOIN movie AS m ON m.movieID


		SELECT m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, ml.watched_date
		FROM movieList AS ml
		LEFT JOIN movieinfo AS mi ON mi.movieID = ml.movieID
		LEFT JOIN movie AS m ON m.movieID = ml.movieID*/
	}
});
//GET Watched


//POST Watchlist flag

//POST Rating

//POST Add friend

//GET Friends


?>