app.controller('movieCtrl', ['$scope', '$rootScope', '$routeParams', '$location', '$http', 'Data', function ($scope, $rootScope, $routeParams, $location, $http, Data){

	$scope.title = "";

	$scope.searchMovie = function (title) {

		if($location.path == '/results'){
			$location.search('title', title.replace(/ /g, '+'));
		} else {
			$location.path('/results').search('title', title.replace(/ /g, '+'));

		}

		Data.get('movie?title='+$location.search().title).then(function (results) {

			if(results.status == "success") {
				angular.forEach(results.matches, function(match, index){
					match.index = index;
				});
				$scope.searchResults = results;
			}
			else {
				console.log("failed");
			}
		});
	};

	$scope.status = "";
	$scope.changeStatus = function (status, index) {

		Data.post('status', {
			status: status,
			index: index
		}).then(function (results){
			if(results.status == "success") {
			}
		});
	};

	$scope.getWatchlist = function () {
		Data.get('watchlist').then(function (results) {
			if(results.status == "success") {
				$scope.watchList = results;
			}
		})
	};
	
	$scope.includeFilmTemplate = function() {
		if($rootScope.authenticated){
				return "partials/movie_list_template.html";
			}
			return "";
	};
	
	$scope.checkPoster = function(poster) {
		if(poster.endsWith(".jpg"))
		{
			return poster;
		}
		return "resources/images/default_poster.jpg";
	}

	$scope.init = function () {

		var title = $location.search().title;

		if(title) {
			$scope.searchMovie(title);
		}

	};

}]);