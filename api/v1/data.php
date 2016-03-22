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

        $movie = array();
        //search for requested movie in database
        $title_escaped = $db->escapeString($req_title);
        $db_search = $db->getRecords("SELECT m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster FROM movie AS m JOIN movieInfo AS mi ON m.movieID = mi.movieID AND mi.language='de' WHERE mi.title  LIKE '%".$title_escaped."%'");

        if(mysqli_num_rows($db_search)>0) {

            //if there are matches in the database, add them to an in the response
            while($row = $db_search->fetch_assoc()) {

                $movie['title'] = $row['title'];
                $movie['plot'] = $row['plot'];
                $movie['release_date'] = $row['release_date'];
                $movie['poster'] = $row['poster'];

                array_push($response['matches'], $movie);
            }
            $response['status'] = "success";
            echoResponse(200, $response);

        } else {

            //if there are no matches found, send request to TMDB API
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

                $movie['title'] = $item['title'];
                $movie['plot'] = $item['overview'];
                $movie['release_date'] = $item['release_date'];
                $movie['poster'] = $poster;

                array_push($response['matches'], $movie);
            }

            $response['status'] = "success";
            echoResponse(200, $response);

        }
    } else {
        $response['status'] = "error";
        $response['message'] = "Not logged in";
        echoResponse(201, $response);
    }
});

//POST Watched flag
$app->post('/watched', function() use ($app) {
	
	//get JSON body and parse to array
    $req = json_decode($app->request->getBody());
	//REST-Service Response
    $response = array();
	//Movie from session
	$movie = array();
	$db = new DB();
    $table_name = 'movielist';
	$session = $db->getSession();
	
	//is User logged in?
	if($session['userID'] != '') {
		 
		//is movie data in session?
		if ($session['movie']!=''){
			
			
			$movie=$session['movie'];
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
			$watchedmovie = $db->getSingleRecord("SELECT * FROM `".$table_name."` WHERE `userID`= ".$userID." AND `movieID`= \"".$movieID."\"" );
			
					
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
//POST Watchlist flag

//POST Rating

//POST Add friend

?>