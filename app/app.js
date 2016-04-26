var app = angular.module('wtmApp', ['ngRoute', 'ngCookies', 'ngDialog']);

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
                controller: 'homeCtrl'
            })
            .when('/results', {
                title: 'Search',
                templateUrl: 'partials/results.html',
                controller: 'movieCtrl',
                role: '0'
            })
            .when('/movie/:movieID', {
                title: 'Movie details',
                templateUrl: 'partials/movie_template.html',
                controller: 'movieCtrl',
                role: '0'
            })
            .when('/profile', {
                title: 'Profil',
                templateUrl: 'partials/profile.html',
                controller: 'profileCtrl',
                role: '0'
            })
            .when('/friend/:id', {
                title: 'Friend profile',
                templateUrl: 'partials/friend_profile.html',
                controller: 'friendProfileCtrl',
                role: '0'
            })
			.when('/imprint', {
                title: 'Imprint',
                templateUrl: 'partials/imprint.html',
                controller: 'imprintCtrl',
                role: '0'
            })
            .when('/', {
                title: 'Home',
                templateUrl: 'partials/home.html',
                controller: 'homeCtrl',
                role: '0'
            })
            .otherwise ({
                title: 'Error 404',
                templateUrl: 'partials/error.html',
                controller: 'authCtrl',
                role: '0'
            });
  }])
    .run(function ($cookies, $rootScope, $location, Data) {
        $rootScope.$on("$routeChangeStart", function (event, next, current) {
            $rootScope.authenticated = false;
            Data.get('session').then(function (results) {
                var nextUrl = next.$$route.originalPath;

                if (results.userID) {
                    $rootScope.authenticated = true;
                    $cookies.put('userID', results.userID);
                    $cookies.put('name', results.name);
                    $cookies.put('email', results.email);

                    if (nextUrl == '/signup' || nextUrl == '/login') {
                        $location.path("/home");
                    }

                } else {
                    if (nextUrl == '/signup' || nextUrl == '/login') {

                    } else {
                        $location.path("/login");
                    }
                }
            });
        });
    });