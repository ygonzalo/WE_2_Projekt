app.controller('profileCtrl', ['$scope', '$rootScope', '$routeParams', '$location', 'Data', '$controller', function ($scope, $rootScope, $routeParams, $location, Data, $controller) {

	$controller('movieCtrl', {$scope: $scope});

	$controller('friendsCtrl', {$scope: $scope});
	
}]);