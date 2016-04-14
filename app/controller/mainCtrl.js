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
		
		$scope.getColor = function (user) {
			Data.get('user/color').then(function (results) {
    
				if(results.status == "success") {
					$cookies.color = results;
				}else{
					$cookies.color = 'default';
				}
           
			})
		};

		
}]);