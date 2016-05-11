app.controller('movieCtrl', ['$scope', '$rootScope', '$routeParams', '$cookies', '$location', 'ngDialog', '$controller','Data', function ($scope, $rootScope, $routeParams,$cookies, $location, ngDialog,$controller, Data){

	$controller('friendsCtrl', {$scope: $scope});

	$scope.title = "";
	$scope.loading = false;
	$scope.searchMovie = function (title) {

		if($location.path() == '/results'){
			$location.search('title', title.replace(/ /g, '+'));
		} else {
			$location.path('/results').search('title', title.replace(/ /g, '+'));

		}

		$scope.loading = true;
		$scope.not_found = false;
		Data.get('movies/search/'+$location.search().title).then(function (results) {

			if(results.status == "success") {
				$scope.loading = false;
				$scope.results = results.matches;

				if(results.matches.length == 0){
					$scope.not_found = true;
				}

			}
		});
	};

	$scope.changeStatus = function (status, movie) {

		if(status==movie.status){
			status = 'deleted';
		}

		Data.post('movies/'+ movie.movieID +'/status', {
			status: status
		}).then(function (results){
			if(results.status == "success") {
				movie.status = results.movie_status;
			} else {
				console.log(results.code);

			}
		});
	};

	$scope.empty_watchlist = false;
	$scope.empty_watchlist_msg = "";
	$scope.getWatchlist = function (userID) {

		userID = userID == 'undefined' ? ('/' + userID) : '';

		Data.get('movies/watchlist'+userID).then(function (results) {
			if(results.status == "success") {
				switch(results.code){
					case 207:
						if($location.path() == '/watchlist') {
							$scope.results = results.matches;
						} else {
							$scope.watchlist = results.matches;
						}
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

		userID = userID == 'undefined' ? ('/' + userID) : '';

		Data.get('movies/watched'+userID).then(function (results) {
			if(results.status == "success") {
				switch(results.code){
					case 209:
						if($location.path() == '/watched') {
							$scope.results = results.matches;
						} else {
							$scope.watched = results.matches;
						}
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
				return "partials/movie_boxes_template.html";
			}
			return "";
	};

	$scope.includeFilmListTemplate = function() {
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
		
	};

	$scope.rec_movie = {};
	$scope.friends = {};
	$scope.openRecommendDialog = function (movie) {
		$scope.rec_movie = movie;
		$scope.friends = $scope.getFriends();
		$scope.recommendDialog = ngDialog.open({
			template: 'partials/recommend_dialog.html',
			scope: $scope,
			closeByEscape: true,
			className: 'ngdialog-theme-plain',
			preCloseCallback:function(){
				$scope.error = "";
				$scope.rec_error = false;
			}
		});
	};


	$scope.likeMovie = function (movie) {

		Data.put('/movies/'+movie.movieID+'/like', {
			like: !movie.liked
		}).then(function (results) {
			if(results.status == "success") {
				if(results.liked){
					movie.liked = results.liked;
					movie.likes = results.likes;
				}else {
					movie.liked = results.liked;
					movie.likes = results.likes;
				}
			}else{
				console.log(results.code);
			}
		});
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
		Data.get('movies/details/'+$routeParams.movieID).then(function (results) {
			if(results.status == "success") {
				$scope.movie = results.movie;
			}
		});
	};
	
	$scope.buttonColor = {};
	$scope.buttonColors = function(){
		
		switch($cookies.get('color')){
			case 'banana': 		$scope.btnColor = {'color':'rgb(239,231,16)'}; break;
			case 'apple':		$scope.btnColor = {'color':'rgb(149,211,28)'}; break;
			case 'raspberry':	$scope.btnColor = {'color':'rgb(234,53,143)'}; break;
			case 'plum':		$scope.btnColor = {'color':'rgb(105,22,233)'}; break;
			default:			$scope.btnColor = {'color':'rgb(111,111,111)'};
		}
		
	};
	
	$scope.initResults = function () {

		$scope.buttonColors();

		var title = $location.search().title;

		if(title) {
			$scope.searchMovie(title);
		}

	};

	$scope.initWatchlist = function () {

		$scope.buttonColors();

		$scope.getWatchlist();

	};

	$scope.initWatched = function () {

		$scope.buttonColors();

		$scope.getWatched();

	};
}]);