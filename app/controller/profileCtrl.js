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
					case 223:	$scope.recommendations = {};
								$scope.empty_recommendations = true;
								$scope.empty_rec_msg = "Keine neue Empfehlungen";
								break;
					default:	break;
				}
			}else{
				
			}
		})
	};

	$scope.deleteRecommendation = function(rec){
		Data.delete('friends/recommendations/'+rec.recID).then(function (results) {
			if(results.status == "success") {
				$scope.getRecommendations();
			}
		})
	};

	$scope.readRecommendation = function(rec){
		Data.put('friends/recommendations/'+rec.recID).then(function (results) {
			if(results.status == "success") {
				$scope.recommendations_class = 
				$location.path('/movie/'+rec.movieID);
			}
		})
	};
	
	$scope.renderProfileImage = function(){
	
		Data.get('user/image').then(function (results) {
    		if(results.status == "success") {
				$scope.profileImage = results;
				
				var ausgabe=JSON.stringify(results.image);
			
				var can =	document.getElementById("canvas");
				var ctx = can.getContext("2d");
				var input = ausgabe.substr(1,9);
				var input2 = input.split('-');
									
					ctx.canvas.width  = 320;
					ctx.canvas.height = 400;
					ctx.clearRect(0,0,320,400);
				
					if(typeof input2[4] == 'undefined' && input2[4] == null){
						color1 = getRandomColor(0);
						color2 = getRandomColor(0);
						color3 = getRandomColor(0);
						color4 = getRandomColor(0)
						color5 = getRandomColor(0);
					}else{
						color1 = getRandomColor(input2[0]);
						color2 = getRandomColor(input2[1]);
						color3 = getRandomColor(input2[2]);
						color4 = getRandomColor(input2[3]);
						color5 = getRandomColor(input2[4]);
					}
					
					//erzeugen vierecke
					ctx.fillStyle = color1;
					ctx.fillRect(0,0,100,300);
					ctx.fillStyle = color2;
					ctx.fillRect(0,300,220,100);
					ctx.fillStyle = color3;
					ctx.fillRect(100,0,220,100);
					ctx.fillStyle = color4;
					ctx.fillRect(220,100,100,300);
					ctx.fillStyle = color5;
					ctx.fillRect(100,100,120,200);				
					
			}else{
				$scope.profileImage = '1-1-1-1-1';
				var templates =	document.getElementsByClassName("profile_img_canvas");
				alert(templates);
				for (var i=0; i<templates.length; i++)
				{
					var can = templates[i].getElementsByTagName('canvas')[0];
					var ctx = can.getContext("2d");
					var input = templates[i].getElementsByTagName('input')[0].value;
					var input2 = input.split('-');
									
					ctx.canvas.width  = 320;
					ctx.canvas.height = 400;
					ctx.clearRect(0,0,320,400);
				
					if(typeof input2[4] == 'undefined' && input2[4] == null){
						color1 = getRandomColor(0);
						color2 = getRandomColor(0);
						color3 = getRandomColor(0);
						color4 = getRandomColor(0)
						color5 = getRandomColor(0);
					}else{
						color1 = getRandomColor(input2[0]);
						color2 = getRandomColor(input2[1]);
						color3 = getRandomColor(input2[2]);
						color4 = getRandomColor(input2[3]);
						color5 = getRandomColor(input2[4]);
					}
					
					//erzeugen vierecke
					ctx.fillStyle = color1;
					ctx.fillRect(0,0,100,300);
					ctx.fillStyle = color2;
					ctx.fillRect(0,300,220,100);
					ctx.fillStyle = color3;
					ctx.fillRect(100,0,220,100);
					ctx.fillStyle = color4;
					ctx.fillRect(220,100,100,300);
					ctx.fillStyle = color5;
					ctx.fillRect(100,100,120,200);				
					
				}
			}
		})
	};
	
	
	$scope.init = function() {
		$scope.getRequests();
		$scope.getSentRequests();
		$scope.getRecommendations();
		$scope.renderProfileImage();

	}
}]);