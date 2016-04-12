app.controller('profileCtrl', ['$scope', '$rootScope','$routeParams', '$cookies','$location', 'Data', '$controller', function ($scope, $rootScope, $routeParams,$cookies, $location, Data, $controller) {

	$controller('movieCtrl', {$scope: $scope});

	$controller('friendsCtrl', {$scope: $scope});

	$scope.profile_name = $cookies.name;
	$scope.profile_email = $cookies.email;

	$scope.compareName = function(name) {
		if(name==$cookies.name){
			return $scope.name_changed = false;
		} else {
			return $scope.name_changed = true;
		}
	};

	$scope.compareEmail = function(email) {
		if(email==$cookies.email){
			return $scope.email_changed = false;
		} else {
			return $scope.email_changed = true;
		}
	};

	$scope.old_pwd_wrong = false;
	$scope.old_pwd = "";
	$scope.new_pwd = "";
	$scope.changePassword = function(old_pwd,new_pwd) {
		Data.put('user/password', {
			old_pwd : old_pwd,
			new_pwd : new_pwd
		}).then(function (results) {
			if (results.status == "success") {
				$scope.old_pwd = "";
				$scope.new_pwd = "";
			} else {
				if(results.code == 518){
					$scope.old_pwd_wrong = true;
				}
			}
		});
	};

	$scope.changeName = function(name) {
		Data.put('user/name', {
			name : name
		}).then(function (results) {
			if (results.status == "success") {
				$cookies.name = results.name;
				$scope.name_changed = false;
			} else {
				console.log(results.message);
			}
		});

	};

	$scope.changeEmail = function(email) {
		Data.put('user/email', {
			email : email
		}).then(function (results) {
			if (results.status == "success") {
				$cookies.email = results.email;
				$scope.email_changed = false;
			} else {
				console.log(results.message);
			}
		});

	};
	
	
	$scope.init = function() {
		$scope.getRequests();
		$scope.getSentRequests();

	}
}]);