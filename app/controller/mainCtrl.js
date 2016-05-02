app.controller('mainCtrl', ['$scope', '$rootScope', '$routeParams', '$cookies','$location', 'Data',
	function ($scope, $rootScope, $routeParams, $cookies, $location, Data) {

		$scope.includeSearch = function(){
			if($rootScope.authenticated){
				return "partials/search.html";
			}
			return "";
		};

		$scope.includeHeader = function(){
			return "partials/header.html";
		};
		
		$scope.includeFooter = function(){
			return "partials/footer.html";
		};
		
		$scope.initMain = function()
		{
			
		}
		
}]);