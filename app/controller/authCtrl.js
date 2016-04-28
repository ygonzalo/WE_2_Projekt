app.controller('authCtrl', function ($scope, $rootScope, $routeParams, $location, $http, $cookies, Data) {
    //initially set those objects to null to avoid undefined error
    $scope.login = {};
    $scope.signup = {};
    $scope.doLogin = function (user) {
        Data.post('user/login', {
            user: user
        }).then(function (results) {
            if (results.status == "success") {
                $location.path('/home');
            }
        });
    };
    $scope.signup = {email:'',password:'',name:''};
    $scope.signUp = function (user) {
        Data.post('user/signUp', {
            user: user
        }).then(function (results) {
            if (results.status == "success") {
                $location.path('/home');
            }else{
                $scope.results = results;
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
    }
	$scope.showScore= false;
	$scope.ratePassword = function(password){
		
		if(password!= undefined && password != null){
			var result = zxcvbn(password);
			$scope.showScore= true;
			sdiv = document.getElementById("scoreDiv");
			span= document.getElementById("scoreValue");
			var score=result.score;
			
			switch(score){
				
				case 0: sdiv.style.width = '20%';
						sdiv.style.backgroundColor = 'rgb(255, 51, 51)';
						span.innerHTML="Schlecht";
					break;
				case 1: sdiv.style.width = '40%';
						sdiv.style.backgroundColor = 'rgb(255, 153, 102)';
						span.innerHTML="Mäßig";
					break;
				case 2: sdiv.style.width = '60%';
						sdiv.style.backgroundColor = 'rgb(255, 221, 153)';
						span.innerHTML="Okay";
					break;
				case 3: sdiv.style.width = '80%';
						sdiv.style.backgroundColor = 'rgb(255, 255, 0)';
						span.innerHTML="Gut";
					break;
				case 4: sdiv.style.width = '100%';
						sdiv.style.backgroundColor = 'rgb(153, 255, 51)';
						span.innerHTML="Sehr gut";
					break;
				default: sdiv.style.width = '20%';
						 sdiv.style.backgroundColor = 'rgb(255, 51, 51)';
						 span.innerHTML="Schlecht";
					
			}
			
		}else{
			$scope.showScore= false;
				
		}
	}
});