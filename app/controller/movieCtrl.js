app.controller('movieCtrl', ['$scope', '$rootScope', '$routeParams', '$cookies', '$location', 'ngDialog', '$controller','Data', function ($scope, $rootScope, $routeParams,$cookies, $location, ngDialog,$controller, Data){

	$controller('friendsCtrl', {$scope: $scope});

	$scope.title = "";
	$scope.searchMovie = function (title) {

		if($location.path() == '/results'){
			$location.search('title', title.replace(/ /g, '+'));
		} else {
			$location.path('/results').search('title', title.replace(/ /g, '+'));

		}
		
		Data.get('movies/search/'+$location.search().title).then(function (results) {

			if(results.status == "success") {
				$scope.results = results;
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
	$scope.getWatchlist = function (userID) {

		userID = typeof userID !== 'undefined' ? userID : $cookies.userID;

		Data.get('movies/watchlist/'+userID).then(function (results) {
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
		});
	};

	$scope.empty_watched = false;
	$scope.empty_watched_msg = "";
	$scope.getWatched = function (userID) {

		userID = typeof userID !== 'undefined' ? userID : $cookies.userID;
		
		Data.get('movies/watched/'+userID).then(function (results) {
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

	$scope.openMovie = function(movie){
		$cookies.movie_details = JSON.stringify(movie);
		$location.path("/details").search('');
	};

	$scope.rec_movie = {};
	$scope.friends = {};
	$scope.openRecommendDialog = function (movie) {
		$scope.rec_movie = movie;
		$scope.friends = $scope.getFriends();
		$scope.recommendDialog = ngDialog.open({ template: 'partials/recommend_dialog.html', scope: $scope, closeByEscape: true, className: 'ngdialog-theme-plain' });
	};

	$scope.error = "";
	$scope.rec_error = false;
	$scope.recommendMovie = function (userID,movieID) {
		Data.post('/friends/'+userID+'/recommend', {
			movieID: movieID
		}).then(function (results) {
			if(results.status == "success") {
				switch(results.code){
					case 221: 	$scope.recommendDialog.close();
						break;
					default:	break;
				}
			}else{
				$scope.rec_error = true;
				switch(results.code){
					case 522: 	$scope.error="Freund hat den Film bereits gesehen";
								break;
					case 523:	$scope.error="Film schon empfohlen";
								break;
					case 517:  	$scope.error="Nur gesehene Filme können empfohlen werden";
								break;
					default:	break;
				}

			}
		});
	};

	$scope.initMovieDetails = function () {
		$scope.movie = JSON.parse($cookies.movie_details);
	};
	
	$scope.initResults = function () {

		var title = $location.search().title;

		if(title) {
			$scope.searchMovie(title);
		}

	};
}]);