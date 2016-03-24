app.controller('movieCtrl',function ($scope, $rootScope, $routeParams, $location, $http, Data){

	$scope.title = "";
	$scope.searchMovie = function (title) {
		Data.post('movie', {
			title: title
		}).then(function (results) {
			if(results.status == "success") {
				var matches = results.matches;
				angular.forEach(matches, function(match, index){
					//Just add the index to your item
					match.index = index;
				});
				$rootScope.searchResults = results;
				$location.path('/results');
			}
		});
	};

	$scope.status = "watched";
	$scope.changeStatus = function (status, index) {

		Data.post('status', {
			status: status,
			index: index
		}).then(function (results){
			if(results.status == "success") {
				console.log("hi");
			}
		});
	};

	$scope.getWatchlist = function () {
		Data.get('watchlist').then(function (results) {
			if(results.status == "success") {
				$rootScope.watchList = results;
			}
		})
	};
	
	$scope.includeFilmTemplate = function() {
		if($rootScope.authenticated){
				return "partials/movie_list_template.html";
			}
			return "";
	}

});