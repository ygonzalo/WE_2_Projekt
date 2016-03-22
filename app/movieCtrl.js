app.controller('movieCtrl',function ($scope, $rootScope, $routeParams, $location, $http, Data){

	$scope.title = "";
	$scope.searchMovie = function (title) {
		Data.post('movie', {
			title: title
		}).then(function (results) {
			if(results.status == "success") {
				$rootScope.results = results;
				$location.path('/results');
			}
		});
	};

	$scope.status = "";
	$scope.changeStatus = function (status) {
		Data.post('status', {
			status: status
		}).then(function (results){
			if(results.status == "success") {

			}
		})
	};

	$scope.getWatchlist = function () {
		Data.get('watchlist').then(function (results) {
			if(results.status == "success") {
				$scope.results = results;
			}
		})
	}
});