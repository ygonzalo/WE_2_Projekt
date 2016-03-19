<?php
//POST Film by name
$app->post('/movie', function() use ($app) {
    $req = json_decode($app->request->getBody());
    $req_title = $req->title;
    $db = new DB();
    $session = $db->getSession();
    $response = array();
    $response['matches'] = array();

    if($session['userID'] != '') {

        $movie = array();
        $title_escaped = $db->escapeString($req_title);
        $db_search = $db->getRecords("SELECT m.ratings, m.rating_points, m.watchers, mi.title, mi.plot, mi.release_date, mi.poster FROM movie AS m JOIN movieInfo AS mi ON m.movieID = mi.movieID AND mi.language='de' WHERE mi.title  LIKE '%".$title_escaped."%'");

        if(mysqli_num_rows($db_search)>0) {

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
            $title_url = str_replace(' ','+',$req_title);
            $search_response = file_get_contents('https://api.themoviedb.org/3/search/movie?api_key=fc6230097457cdf6f547373206e12a5d&language=de&query='.$title_url);
            $response_decoded = json_decode($search_response, true);

            $config_response = file_get_contents('http://api.themoviedb.org/3/configuration?api_key=fc6230097457cdf6f547373206e12a5d');
            $config = json_decode($config_response);

            $matches = $response_decoded['results'];


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
    $response = array();
	$db = new DB();
    $table_name = 'movielist';
	
	if($session['userID'] != '') {
		 
	//get session to use userID
		$session = $db->getSession();
		$userID = $session['userID'];
		
		//get data from (JSON-)array
		$status = $req->watchedmovie->status;
		$movieID = $req->watchedmovie->movieID;
		
		//TODO - date aktuell vom Server nehmen, nicht über den POST
		$date	= date("Y-m-d H:i:s");
		$watchedmovie = $db->getSingleRecord("SELECT * FROM ".$table_name." WHERE `movieID` LIKE `".$movieID."` AND `userID` = `".$userID."`");
		
		
		//movie already in db, just change watched
		if(!$watchedmovie){
			
			
			
		//movie not in db, add relationship
		}else{

			//TODO - object must be a object!!!!
			$object = array($movieID, $userID, $status, $date);
			$column_names = array('movieID', 'userID' , 'status', 'date');
			$db->insertIntoTable($object,$column_names,$table_name);
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