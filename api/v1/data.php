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
        $db_search = $db->getSingleRecord("SELECT m.ratings, m.ratingPoints, m.watchers, mi.title, mi.plot, mi.release FROM movie AS m JOIN movieInfo AS mi ON m.movieID = mi.movieID AND mi.language='de' WHERE mi.title LIKE '%".$title_escaped."%'");
        if(empty($db_search)) {
            $titleUrl = str_replace(' ','+',$req_title);
            $searchResponse = file_get_contents('https://api.themoviedb.org/3/search/movie?api_key=fc6230097457cdf6f547373206e12a5d&language=de&query='.$titleUrl);
            $result_decoded = json_decode($searchResponse,true);
            $matches = $result_decoded->results;

            if(!(empty($matches))) {
                foreach($matches as $item) {
                    $config_response = file_get_contents('http://api.themoviedb.org/3/configuration?api_key=fc6230097457cdf6f547373206e12a5d');
                    $config = json_decode($config_response);
                    $poster = $config->images->base_url . "/".$config->images->poster_sizes[2]. $item->poster_path;
                    $movie['movieID'] = $item->id;
                    $movie['title'] = $item->title;
                    $movie['plot'] = $item->overview;
                    $movie['release'] = $item->release_date;
                    $movie['poster'] = $poster;
                    $movie['language'] = "de";
                    $movie_table = "movie";
                    $movie_info_table = "movieInfo";
                    $m_columns = array('movieID');
                    $mi_columns = array('movieID','language','title','plot','poster','release');

                    $new_movie = $db->insertIntoTable($movie, $m_columns, $movie_table);
                    $new_movie_info = $db->insertIntoTable($movie, $mi_columns, $movie_info_table);

                    if($new_movie != 0  && $new_movie_info != 0) {
                        array_push($response['matches'],$movie);
                    } else {
                        $response['status'] = "error";
                        $response['message'] = "There was a problem saving the movie";
                        echoResponse(201, $response);
                    }
                }
                $response['status'] = "success";
                echoResponse(200, $response);
            }
        } else {
            foreach($db_search as $item) {
                $movie['movieID'] = $item['movieID'];
                $movie['title'] = $item['title'];
                $movie['plot'] = $item['plot'];
                $movie['release'] = $item['release'];
                $movie['poster'] = $item['poster'];

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



    echoResponse(200,$response);
});

//POST Watched flag
$app->post('/watched', function() use ($app) {
	
	//get JSON body and parse to array
    $req = json_decode($app->request->getBody());
    $response = array();
	$db = new DB();
    $table_name = 'movieList';
	
	//get session to use userID
	$session = $db->getSession();
	$userID = $session['userID'];
	
	//get data from (JSON-)array
	$status = $req->watchedmovie->status;
	$movieID = $req->watchedmovie->movieID;
	
	//TODO - date aktuell vom Server nehmen, nicht über den POST
	$date	= "2000-01-01";
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
	

});
//POST Watchlist flag

//POST Rating

//POST Add friend

?>