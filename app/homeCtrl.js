app.controller('homeCtrl', ['$scope', '$rootScope', '$routeParams', '$location', 'Data', '$controller', function ($scope, $rootScope, $routeParams, $location, Data, $controller) {

	$controller('movieCtrl', {$scope: $scope});

	$controller('friendsCtrl', {$scope: $scope});
	
	$scope.hello = "test";

	$scope.init = function () {

		$scope.getWatchlist();
		
		$scope.getFriends();

	};
	
}]);