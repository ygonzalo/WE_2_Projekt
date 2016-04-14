app.factory("Friends", ['Data',
	function (Data) {
		
		var obj = {};
		obj.sendFriendRequest = function (user) {
			return Data.post('friends/'+user.userID+'/request').then(function (results) {
				if (results.status == "success") {
					user.requested = true;
				}
			});
		};

		obj.cancelFriendRequest = function (user) {
			return Data.delete('friends/'+user.userID+'/request').then(function (results) {
				if (results.status == "success") {

					if($location.path() == '/profile'){
						$scope.getSentRequests();
					}else if($location.path() == '/home'){
						user.requested = false;
					}

				}else {
					console.log(results.code);
				}
			});
		};

		obj.acceptFriendRequest = function (userID) {
			return Data.put('friends/'+userID+'/request',{
				status : "accepted"
			}).then(function (results) {
				if (results.status == "success") {
					$scope.getRequests();
				}
			});
		};

		obj.denyFriendRequest = function (userID) {
			return Data.put('friends/'+userID+'/request',{
				status : "denied"
			}).then(function (results) {
				if (results.status == "success") {
					$scope.getRequests();
				}
			});
		};

		obj.denyFriendRequest = function (userID) {
			return Data.put('friends/'+userID+'/request',{
				status : "denied"
			}).then(function (results) {
				if (results.status == "success") {
					$scope.getRequests();
				}
			});
		};

		return obj;
	}]);