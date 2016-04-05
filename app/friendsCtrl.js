app.controller('friendsCtrl', ['$scope', '$rootScope', '$routeParams', '$location', 'Data',
	function ($scope, $rootScope, $routeParams, $location, Data) {

		$scope.sendFriendRequest = function(userID){
			Data.post('friends/'+userID+'/request').then(function (results) {
				if (results.status == "success") {
					$scope.requestSent = true;
				}
			});
		};
		
		$scope.acceptFriendRequest = function(userID){
			Data.put('friends/'+userID+'/request',{
				status : "accepted"	
		}).then(function (results) {
				
			});
		};

		$scope.denyFriendRequest = function(userID){
			Data.put('friends/'+userID+'/request',{
				status : "denied"
			}).then(function (results) {
				
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
					
				}
			});
		};

		$scope.getFriends = function() {
			Data.get('friends').then(function (results) {
				if (results.status == "success") {
					$scope.friends = results.friends;
				}
			});
		};
		
	}]);