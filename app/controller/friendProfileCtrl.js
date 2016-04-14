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

	$scope.friendProfileInit = function() {
		$scope.getFriend($routeParams.id);
		$scope.getWatched($routeParams.id);
		$scope.getWatchlist($routeParams.id);
	};
	
}]);
