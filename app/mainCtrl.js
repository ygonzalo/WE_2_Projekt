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
		
		$scope.getColor = function (user) {
			Data.get('user/color').then(function (results) {
    
				if(results.status == "success") {
					$rootScope.color = results;
				}else{
					$rootScope.color = 'default';
				}
           
			})
		};
		
		
		
}]);