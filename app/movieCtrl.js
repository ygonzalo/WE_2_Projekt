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
			status: status
		}).then(function (results){
			if(results.status == "success") {
			}
		});
	};

	$scope.empty_watchlist = false;
	$scope.empty_watchlist_msg = "";
	$scope.getWatchlist = function () {
		Data.get('movies/watchlist').then(function (results) {
			if(results.status == "success") {
				switch(results.code){
					case 207: 	$scope.watchlist = results.matches;
								break;
					case 208:	$scope.empty_watchlist = true;
								$scope.empty_watchlist_msg = "Keine Filme in Watchlist";
								break;
					default:	break;
				}
			}
		})
	};

	$scope.empty_watched = false;
	$scope.empty_watched_msg = "";
	$scope.getWatched = function () {
		Data.get('movies/watched').then(function (results) {
			if(results.status == "success") {
				switch(results.code){
					case 209: 	$scope.watched = results.matches;
						break;
					case 210:	$scope.empty_watched = true;
								$scope.empty_watched_msg = "Keine Filme als 'gesehen' markiert";
								break;
					default:	break;
				}
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
	};

	$scope.init = function () {

		var title = $location.search().title;

		if(title) {
			$scope.searchMovie(title);
		}

	};

}]);