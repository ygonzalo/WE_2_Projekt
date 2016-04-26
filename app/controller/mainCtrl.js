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
		
		$scope.getColor = function (user) {
			Data.get('user/color').then(function (results) {
				if(results.status == "success") {
					$cookies.put('color',results.color);
				}else{
					$cookies.put('color','default');
				}
           
			})
		};
		
		$scope.initMain = function()
		{
			$scope.getColor();
		}
		
}]);