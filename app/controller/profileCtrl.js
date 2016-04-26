app.controller('profileCtrl', ['$scope', '$rootScope','$routeParams', '$cookies','$location', 'Data', '$controller', function ($scope, $rootScope, $routeParams,$cookies, $location, Data, $controller) {

	$controller('movieCtrl', {$scope: $scope});

	$controller('friendsCtrl', {$scope: $scope});

	$scope.profile_name = $cookies.get('name');
	$scope.profile_email = $cookies.get('email');
	$scope.color = $cookies.get('color');

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
				$cookies.put('name',results.name);
				$scope.name_changed = false;
			}
		});

	};

	$scope.changeEmail = function(email) {
		Data.put('user/email', {
			email : email
		}).then(function (results) {
			if (results.status == "success") {
				$cookies.put('email',results.email);
				$scope.email_changed = false;
			}
		});

	};

	$scope.changeColor = function(color) {
		Data.put('user/color', {
			color : color
		}).then(function (results) {
			if (results.status == "success") {
				$cookies.put('color',results.color);
			} 
		});

	};
	
	
	$scope.includeProfileImg = function(){
		return "partials/profile_img_template.html";
	};
	
	$scope.getProfileImage = function(){
		Data.get('user/image').then(function (results) {
    
				if(results.status == "success") {
					$scope.profileImage = results;
				}else{
					$scope.profileImage = '1-2-3-2-1';
				}
			})
	};

	$scope.empty_recommendations = false;
	$scope.empty_rec_msg = "";
	$scope.getRecommendations = function(){
		Data.get('/friends/recommendations').then(function (results) {
			if(results.status == "success") {
				switch(results.code){
					case 222: 	$scope.recommendations = results.recommendations;
						break;
					case 223:	$scope.empty_recommendations = true;
								$scope.empty_rec_msg = "Keine neue Empfehlungen";
								break;
					default:	break;
				}
			}else{
				
			}
		})
	};
	
	$scope.init = function() {
		$scope.getRequests();
		$scope.getSentRequests();
		$scope.getRecommendations();

	}
}]);