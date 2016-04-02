app.controller('movieCtrl', ['$scope', '$rootScope', '$routeParams', '$location', '$http', 'Data', function ($scope, $rootScope, $routeParams, $location, $http, Data){

	$scope.title = "";

	$scope.searchMovie = function (title) {

		if($location.path == '/results'){
			$location.search('title', title.replace(/ /g, '+'));
		} else {
			$location.path('/results').search('title', title.replace(/ /g, '+'));

		}

		Data.get('movies/search/'+$location.search().title).then(function (results) {

			if(results.status == "success") {
				$scope.results = results;
			}
			else {
				console.log("failed");
			}
		});
	};

	$scope.status = "";
	$scope.changeStatus = function (status, movieID) {

		Data.post('movies/'+ movieID +'/status', {
			status: status,
		}).then(function (results){
			if(results.status == "success") {
			}
		});
	};

	$scope.getWatchlist = function () {
		Data.get('movies/watchlist').then(function (results) {
			if(results.status == "success") {
				$scope.result = results;
			}
		})
	};

	$scope.getWatched = function () {
		Data.get('movies/watched').then(function (results) {
			if(results.status == "success") {
				$scope.result = results;
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