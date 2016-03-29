var app = angular.module('wtmApp', ['ngRoute', 'ngAnimate']);

app.config(['$routeProvider',
  function ($routeProvider) {
        $routeProvider.
			when('/login', {
				title: 'Login',
				templateUrl: 'partials/login.html',
				controller: 'authCtrl'
			})
            .when('/logout', {
                title: 'Logout',
                templateUrl: 'partials/login.html',
                controller: 'authCtrl'
            })
            .when('/signup', {
                title: 'Signup',
                templateUrl: 'partials/signup.html',
                controller: 'authCtrl'
            })
            .when('/home', {
                title: 'Home',
                templateUrl: 'partials/home.html',
                controller: 'authCtrl'
            })
            .when('/results', {
                title: 'Search',
                templateUrl: 'partials/results.html',
                controller: 'movieCtrl',
                role: '0'
            })
            .when('/profile', {
                title: 'Profil',
                templateUrl: 'partials/profile.html',
                controller: 'authCtrl',
                role: '0'
            })
            .when('/', {
                title: 'Home',
                templateUrl: 'partials/home.html',
                controller: 'authCtrl',
                role: '0'
            })
            .when('/error', {
                title: 'Error 404',
                templateUrl: 'partials/error.html',
                controller: 'authCtrl',
                role: '0'
            })
            .otherwise({
                //redirectTo: '/error'
            });
  }])
    .run(function ($rootScope, $location, Data) {
        $rootScope.$on("$routeChangeStart", function (event, next, current) {
            $rootScope.authenticated = false;
            Data.get('session').then(function (results) {
                if (results.userID) {
                    $rootScope.authenticated = true;
                    $rootScope.userID = results.userID;
                    $rootScope.name = results.name;
                    $rootScope.email = results.email;
                } else {
                    var nextUrl = next.$$route.originalPath;
                    if (nextUrl == '/signup' || nextUrl == '/login') {

                    } else {
                        $location.path("/login");
                    }
                }
            });
        });
    });