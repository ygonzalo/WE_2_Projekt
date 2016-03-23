<!DOCTYPE html>
<html lang="en" ng-app="wtmApp">

  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=1024, initial-scale:1.0, maximum-scale=1.0, user-scalable=no" />
    
	<link rel="shortcut icon" href="">
    <link rel="stylesheet" href="resources/search.css">
    <link rel="stylesheet" href="resources/index.css">
    <link rel="stylesheet" href="resources/overrides.css">
    <link rel="stylesheet" href="resources/header.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
    <title>Watched That Movie</title>
  </head>

  <body ng-controller="mainCtrl">
    <div class="header">
		<table class="coloredline" >
			<tr>
				<td id="t1"></td>
				<td id="t2"></td>
				<td id="t3"></td>
				<td id="t4"></td>
			</tr>
		</table>
		<div id="header_content">
			<div class="header_left"></div>
			<div class="header_center">
				<a href="#/home">
					<div id="logo_container">
						<img src="resources/images/wtm_logo.png" id="logo" />
						<div id="logo_text">
							<h1>
							Watched that<br /><b>Movie</b>
							</h1>
						</div>
					</div>
				</a>
			</div>
			<div class="header_right">
				<div ng-include="includeProfile()"></div>
			</div>
		</div>
    </div>
    <div ng-include="includeSearch()" id="search_box"></div>
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

