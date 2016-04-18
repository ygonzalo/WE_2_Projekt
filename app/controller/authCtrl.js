app.controller('authCtrl', function ($scope, $rootScope, $routeParams, $location, $http, Data) {
    //initially set those objects to null to avoid undefined error
    $scope.login = {};
    $scope.signup = {};
    $scope.doLogin = function (user) {
        Data.post('user/login', {
            user: user
        }).then(function (results) {
            if (results.status == "success") {
                $location.path('/home');
            }
        });
    };
    $scope.signup = {email:'',password:'',name:''};
    $scope.signUp = function (user) {
        Data.post('user/signUp', {
            user: user
        }).then(function (results) {
    
            if (results.status == "success") {
                $location.path('/home');
            }else{
                $scope.results = results;
            }
        });
    };
    $scope.logout = function () {
        Data.get('user/logout').then(function (results) {
            $location.path('login');
        });
    }
});