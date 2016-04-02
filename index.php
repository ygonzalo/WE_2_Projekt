<!DOCTYPE html>
<html lang="en" data-ng-app="wtmApp">

  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, minimum-scale=0.5, user-scalable=yes" >
    
	<link rel="shortcut icon" href="">
    <link rel="stylesheet" href="resources/search.css">
    <link rel="stylesheet" href="resources/index.css">
    <link rel="stylesheet" href="resources/header.css">
    <link rel="stylesheet" href="resources/partials.css">
    <link rel="stylesheet" href="resources/overrides.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
    <title>Watched That Movie</title>
  </head>

  <body data-ng-controller="mainCtrl">
    <div data-ng-include="includeHeader()" id="header"></div>
    <div data-ng-include="includeSearch()" id="search_box"></div>
    <div data-ng-view="" id="ng-view"></div>
  </body>
  <script src="js/angular.min.js"></script>
  <script src="js/angular-route.min.js"></script>
  <script src="js/angular-animate.min.js" ></script>
  <script src="app/app.js"></script>
  <script src="app/data.js"></script>
  <script src="app/authCtrl.js"></script>
  <script src="app/movieCtrl.js"></script>
  <script src="app/mainCtrl.js"></script>
</html>

