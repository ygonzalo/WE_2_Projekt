app.controller('mainCtrl', ['$scope', '$rootScope', '$routeParams', '$location', 'Data',
	function ($scope, $rootScope, $routeParams, $location, Data) {

		$scope.includeSearch = function(){
			if($rootScope.authenticated){
				return "partials/search.html";
			}
			return "";
		};

		$scope.includeHeader = function(){
			return "partials/header.html";
		};
}]);