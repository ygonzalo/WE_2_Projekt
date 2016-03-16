<?php
//GET Session
$app->get('/session', function() {
    $db = new DB();
    $session = $db->getSession();
    $response['userID'] = $session['userID'];
    $response['email'] = $session['email'];
    $response['name'] = $session['name'];
    echoResponse(200, $session);
});

//POST Login
$app->post('/login', function() use ($app) {

    $req = json_decode($app->request->getBody());
    $db = new DB();

    $password = $req->user->password;
    $email = $req->user->email;
    $user = $db->getSingleRecord("SELECT userID,name,password,email from user where email='".$email."'");
    if($user) {
        if(password_verify($password,$user['password'])){
            $response['status'] = "success";
            $response['message'] = 'Logged in successfully.';
            $response['name'] = $user['name'];
            $response['userID'] = $user['userID'];
            $response['email'] = $user['email'];
            if(!isset($_SESSION)){
                session_start();
            }
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $user['name'];
        } else {
            $response['status'] = "error";
            $response['message'] = 'Login failed. Incorrect credentials';
        }
    }else {
        $response['status'] = "error";
        $response['message'] = 'No such user is registered';
    }
    echoResponse(200, $response);
});

//POST Signup
$app->post('/signUp', function() use ($app) {
    $req = json_decode($app->request->getBody());
    $response = array();
    $db = new DB();

    $name = $req->user->name;
    $email = $req->user->email;
    $password = $req->user->password;
    $dbUser = $db->getSingleRecord("SELECT 1 FROM user where email='".$email."'");
    if(!$dbUser) {
        $req->user->password = password_hash($password, PASSWORD_DEFAULT);
        $table_name = 'user';
        $column_names = array('name', 'email', 'password');
        $result = $db->insertIntoTable($req->user, $column_names, $table_name);
        if($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "User account created successfully";
            $response["userID"] = $result;
            if (!isset($_SESSION)) {
                session_start();
            }
            $_SESSION['userID'] = $response["userID"];
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to create user. Please try again";
            echoResponse(201, $response);
        }
    }else{
        $response["status"] = "error";
        $response["message"] = "An user with the provided email exists!";
        echoResponse(201, $response);
    }

});

//GET Logout
$app->get('/logout', function() {
    $db = new DB();
    $msg = $db->destroySession();
    $response["status"] = "info";
    $response["message"] = $msg ;
    echoResponse(200, $response);
});

?>