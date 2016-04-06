app.controller('profileCtrl', ['$scope', '$rootScope', '$routeParams', '$location', 'Data', '$controller', function ($scope, $rootScope, $routeParams, $location, Data, $controller) {

	$controller('movieCtrl', {$scope: $scope});

	$controller('friendsCtrl', {$scope: $scope});


	$scope.compareName = function(name) {
		console.log("got in");

		if(name==$rootScope.name){
			return $scope.changed = false;
		} else {
			console.log("changed");
			return $scope.changed = true;
		}
	};
	
	$scope.init = function() {
		$scope.getRequests();
	}
}]);