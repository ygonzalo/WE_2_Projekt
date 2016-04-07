app.controller('friendsCtrl', ['$scope', '$rootScope', '$routeParams', '$location', 'Data',
	function ($scope, $rootScope, $routeParams, $location, Data) {

		$scope.sendFriendRequest = function(user){
			Data.post('friends/'+user.userID+'/request').then(function (results) {
				if (results.status == "success") {
					user.requested = true;
				}
			});
		};

		$scope.cancelFriendRequest = function(user){
			Data.delete('friends/'+user.userID+'/request').then(function (results) {
				if (results.status == "success") {
					user.requested = false;
				}
			});
		};
		
		$scope.acceptFriendRequest = function(userID){

			Data.put('friends/'+userID+'/request',{
				status : "accepted"	
		}).then(function (results) {
				if (results.status == "success") {
					$scope.getRequests();
				}else{
					console.log(results.message);
				}
			});
		};

		$scope.denyFriendRequest = function(userID){
			Data.put('friends/'+userID+'/request',{
				status : "denied"
			}).then(function (results) {
				if (results.status == "success") {
					$scope.getRequests();
				}
			});
		};
		
		$scope.getRequests = function() {
			Data.get('friends/requests').then(function (results) {
				if (results.status == "success") {
					$scope.requests = results.requests;
				}
			});
		};

		$scope.deleteFriend = function(userID) {
			Data.delete('friends/'+userID).then(function (results) {
				if (results.status == "success") {
					$scope.getFriends();
				}
			});
		};

		$scope.getFriends = function() {
			Data.get('friends').then(function (results) {
				if (results.status == "success") {
					$scope.friends = results.friends;
				}else{
					console.log(results.message);
				}
			});
		};

		$scope.searchFriend = function(query) {
			Data.get('friends/search/'+query).then(function (results) {
				if (results.status == "success") {
					$scope.users = results.users;
				}
			});
		}
		
	}]);