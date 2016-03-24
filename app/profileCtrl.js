app.controller('profileCtrl', ['$scope', '$rootScope', '$routeParams', '$location', 'Data', '$controller', function ($scope, $rootScope, $routeParams, $location, Data, $controller) {

	$controller('movieCtrl', {$scope: $scope});

	$scope.getFriends = function() {
		Data.get('friends').then(function (results) {
			if (results.status == "success") {
				$rootScope.friends = results;
			}
		});
	};
	
	
}]);