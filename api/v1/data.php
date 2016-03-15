<?php
//GET Film by name

//POST Watched flag
$app->post('/watched', function() use ($app) {
	
	//get JSON body and parse to array
    $req = json_decode($app->request->getBody());
    $response = array();
	$db = new DB();
	
	//get session to use userID
	$session = $db->getSession();
	$userID = $session['userID'];
	
	//get data from (JSON-)array
	$status = $req->watchedmovie->status;
	$movieID = $req->watchedmovie->movieID;
	
	//TODO - date aktuell vom Server nehmen, nicht über den POST
	$date	= "2000-01-01";
	$watchedmovie = $db->getSingleRecord("SELECT * FROM `movielist` WHERE `movieID` LIKE `".$movieID."` AND `userID` = `".$userID."`");
	
	
	//movie already in db, just change watched
	if(!$watchedmovie){
		
		
		
	//movie not in db, add relationship
	}else{
		$table_name = 'movielist';
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