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

		$scope.empty_requests = false;
		$scope.empty_requests_msg = "";
		$scope.getRequests = function() {
			Data.get('friends/requests').then(function (results) {
				if (results.status == "success") {
					switch(results.code){
						case 216: 	$scope.requests = results.requests;
									break;
						case 217:	$scope.empty_requests = true;
									$scope.empty_requests_msg = "Keine neue Freundschaftsanfragen";
									break;
						default:	break;
					}
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

		$scope.empty_friendlist = false;
		$scope.empty_friendlist_msg = "";
		$scope.getFriends = function() {
			Data.get('friends').then(function (results) {
				if (results.status == "success") {
					switch(results.code){
						case 219: 	$scope.friends = results.friends;
							break;
						case 220:	$scope.empty_friendlist = true;
									$scope.empty_friendlist_msg = "Keine Freunde in deiner Freundeliste";
							break;
						default:	break;
					}
				}else{
					console.log(results.message);
				}
			});
		};

		$scope.friend_query = "";
		$scope.empty_friendsearch = false;
		$scope.empty_friendsearch_msg = "";
		$scope.searchFriend = function(query) {
			if(query.length>0){
				Data.get('friends/search/'+query).then(function (results) {
					if (results.status == "success") {
						switch(results.code){
							case 211: 	$scope.users = results.users;
								break;
							case 212:	$scope.empty_friendsearch = true;
								$scope.empty_friendsearch_msg = "Keine Treffer";
								break;
							default:	break;
						}
					}
				});
			} else {
				$scope.empty_friendsearch_msg = "";
			}
		}
		
	}]);