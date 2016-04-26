<!DOCTYPE html>
<html lang="en" data-ng-app="wtmApp">

  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no" >
    
	<link rel="shortcut icon" href="">
    <link rel="stylesheet" href="resources/search.css">
    <link rel="stylesheet" href="resources/index.css">
    <link rel="stylesheet" href="resources/header.css">
	<link rel="stylesheet" href="resources/footer.css">
    <link rel="stylesheet" href="resources/partials.css">
    <link rel="stylesheet" href="resources/overrides.css">
    <link rel="stylesheet" href="resources/modals.css">
    <link rel="stylesheet" href="resources/ngDialog-theme-plain.css">
    <link rel="stylesheet" href="resources/ngDialog.css">
    <link rel="stylesheet" href="resources/font-awesome-4.6.1/css/font-awesome.min.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
    <link rel="icon" href="resources/images/wtm_logo_circle.ico" type="image/ico" />
	
	<title>Watched That Movie</title>
  </head>

  <body data-ng-controller="mainCtrl">
    <div data-ng-include="includeHeader()" id="header"></div>
    <div data-ng-include="includeSearch()" id="search_box"></div>
    <div data-ng-view="" id="ng-view"></div>
	<div data-ng-include="includeFooter()" id="footer"></div>
  </body>
  <script src="js/angular.min.js"></script>
  <script src="js/angular-route.min.js"></script>
  <script src="js/angular-cookies.min.js"></script>
  <script src="js/angular-animate.min.js" ></script>
  <script src="js/ngDialog.min.js" ></script>
  <script src="app/app.js"></script>
  <script src="app/data.js"></script>
  <script src="app/controller/authCtrl.js"></script>
  <script src="app/controller/movieCtrl.js"></script>
  <script src="app/controller/friendsCtrl.js"></script>
  <script src="app/controller/homeCtrl.js"></script>
  <script src="app/controller/profileCtrl.js"></script>
   <script src="app/controller/imprintCtrl.js"></script>
  <script src="app/controller/friendProfileCtrl.js"></script>
  <script src="app/controller/mainCtrl.js"></script>
  <script src="js/render_profile_img.js"></script>
</html>

