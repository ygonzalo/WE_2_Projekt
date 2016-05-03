app.controller('friendProfileCtrl', ['$scope', '$rootScope','$routeParams','$location', 'Data', '$controller', function ($scope, $rootScope, $routeParams, $location, Data, $controller) {

	$controller('movieCtrl', {$scope: $scope});

	$controller('friendsCtrl', {$scope: $scope});

	$scope.friend = {};
	$scope.getFriend = function(userID) {
		Data.get('friends/'+userID).then(function (results) {
			if(results.status == "success") {
				switch(results.code){
					case 232: 	$scope.friend = results.friend;
								break;
					default:	break;
				}
			}
		})
	};

	$scope.renderProfileImage = function(userID){

	Data.get('user/friendimage/'+userID).then(function (results) {
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
	})};
	
	
	$scope.friendProfileInit = function() {
		$scope.getFriend($routeParams.id);
		$scope.getWatched($routeParams.id);
		$scope.getWatchlist($routeParams.id);
		$scope.renderProfileImage($routeParams.id);
	};
	

}]);
