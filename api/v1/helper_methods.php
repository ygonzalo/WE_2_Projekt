<?php

function isMovieWatched($movieID,$userID) {

	$db = new DB();

	//check if user has watched the movie
	$stmt_movielist = $db->preparedStmt("SELECT 1 FROM movielist WHERE userID = ? AND movieID = ? AND status = 'watched'");
	$stmt_movielist->bind_param('ii',$userID,$movieID);

	$stmt_movielist->execute();
	$stmt_movielist->store_result();

	if($stmt_movielist->num_rows>0){
		$stmt_movielist->free_result();
		$stmt_movielist->close();
		return true;
	} else {
		$stmt_movielist->free_result();
		$stmt_movielist->close();
		return false;
	}
}

function isFriend($userID, $friendID) {
	$db = new DB();

	//check if user is a friend
	$stmt_friends = $db->preparedStmt("SELECT 1 FROM friends WHERE ((friendID = ? AND userID = ?) OR (friendID = ? AND userID = ?)) AND status = 'accepted'");
	$stmt_friends->bind_param('iiii',$friendID,$userID,$userID,$friendID);

	$stmt_friends->execute();
	$stmt_friends->store_result();

	if($stmt_friends->num_rows>0) {
		$stmt_friends->free_result();
		$stmt_friends->close();
		return true;
	} else {
		$stmt_friends->free_result();
		$stmt_friends->close();
		return false;
	}
}

function requestSent($userID, $friendID){
	$db = new DB();

	//check if user is a friend
	$stmt_friends = $db->preparedStmt("SELECT 1 FROM friends WHERE userID = ? AND friendID = ? AND status = 'requested'");
	$stmt_friends->bind_param('ii',$userID,$friendID);

	$stmt_friends->execute();
	$stmt_friends->store_result();

	if($stmt_friends->num_rows>0) {
		$stmt_friends->free_result();
		$stmt_friends->close();
		return true;
	} else {
		$stmt_friends->free_result();
		$stmt_friends->close();
		return false;
	}
}

function movieIsRated($userID,$movieID) {

	$db = new DB();
	
	//check if user already gave a rating
	$stmt_movielist = $db->preparedStmt("SELECT user_rating FROM movielist WHERE userID = ? AND movieID = ?");
	$stmt_movielist->bind_param('ii',$userID,$movieID);

	$stmt_movielist->execute();
	$stmt_movielist->store_result();
	$stmt_movielist->bind_result($db_user_rating);

	$stmt_movielist->fetch();

	if($db_user_rating != null) {
		$stmt_movielist->free_result();
		$stmt_movielist->close();
		return true;
	} else {
		$stmt_movielist->free_result();
		$stmt_movielist->close();
		return false;
	}
}

function userExists($userID) {

	$db = new DB();
	
	//check if there is a user with the given id
	$stmt_user = $db->preparedStmt("SELECT 1 FROM user WHERE userID = ?");
	$stmt_user->bind_param('i', $userID);
	$stmt_user->execute();

	$stmt_user->store_result();
	
	if($stmt_user->num_rows>0){
		$stmt_user->free_result();
		$stmt_user->close();
		return true;
	} else {
		$stmt_user->free_result();
		$stmt_user->close();
		return false;
	}
}

function getUsername($userID) {
	$db = new DB();

	//check if there is a user with the given id
	$stmt_user = $db->preparedStmt("SELECT name FROM user WHERE userID = ?");
	$stmt_user->bind_param('i', $userID);
	$stmt_user->execute();

	$stmt_user->store_result();
	$stmt_user->bind_result($db_name);

	if($stmt_user->num_rows>0){
		$stmt_user->free_result();
		$stmt_user->close();
		return $db_name;
	} else {
		$stmt_user->free_result();
		$stmt_user->close();
		return null;
	}
}

function getUserEmail($userID) {
	$db = new DB();

	//check if there is a user with the given id
	$stmt_user = $db->preparedStmt("SELECT email FROM user WHERE userID = ?");
	$stmt_user->bind_param('i', $userID);
	$stmt_user->execute();

	$stmt_user->store_result();
	$stmt_user->bind_result($db_email);

	if($stmt_user->num_rows>0){
		$stmt_user->free_result();
		$stmt_user->close();
		return $db_email;
	} else {
		$stmt_user->free_result();
		$stmt_user->close();
		return null;
	}
}

function sendMail($to,$subject,$message){

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	// Additional headers
	$headers .= 'To:'.$to."\r\n";
	$headers .= 'From: Watched That Movie <noreply@watchedthatmovie.ixdee.de>' . "\r\n";
	
	mail($to,$subject,$message,$headers);
}
