app.controller('authCtrl', function ($scope, $rootScope, $routeParams, $location, $http, $cookies, Data, PwdScore) {
    //initially set those objects to null to avoid undefined error
    $scope.login = {};
    $scope.signup = {};
	$scope.login_err = "";
	$scope.login_failed = false;
    $scope.doLogin = function (user) {
        Data.post('user/login', {
            user: user
        }).then(function (results) {
            if (results.status == "success") {
				$rootScope.authenticated = true;
				$cookies.put('userID', results.userID);
				$cookies.put('name', results.name);
				$cookies.put('email', results.email);
                $location.path('/home');
            } else {
				$scope.login_failed = true;
				switch(results.code){
					case 503:
						$scope.login_err = "Benutzer oder Passwort falsch";
						break;
					case 502:
						$scope.login_err = "Benutzer oder Passwort falsch";
						break;
				}
			}
        });
    };

    $scope.signup = {email:'',password:'',name:''};
	$scope.signup_err = "";
	$scope.signup_failed = false;
    $scope.signUp = function (user) {
        Data.post('user/signUp', {
            user: user
        }).then(function (results) {
            if (results.status == "success") {
				$rootScope.authenticated = true;
				$cookies.put('userID', results.userID);
				$cookies.put('name', results.name);
				$cookies.put('email', results.email);
                $location.path('/home');
            }else{
				$scope.signup_failed = true;
				switch(results.code){
					case 504:
						$scope.signup_err = "Benutzer konnte nicht erzeugt werden";
						break;
					case 505:
						$scope.signup_err = "Email bereits verwendet";
						break;
				}
            }
        });
    };

    $scope.logout = function () {
        Data.get('user/logout').then(function (results) {
			if(results.status == "success"){
				$cookies.remove('userID');
				$cookies.remove('email');
				$cookies.remove('name');
				$cookies.remove('color');
				$location.path('login');
			}

        });
    };
	
	$scope.showScore= false;
	$scope.ratePassword = function(password){

		if(password!= undefined && password != null){

			$scope.showScore= true;

			PwdScore.ratePassword(password);

		}else{
			$scope.showScore= false;

		}
	};
});