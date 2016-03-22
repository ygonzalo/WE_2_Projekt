<!DOCTYPE html>
<html lang="en" ng-app="wtmApp">

  <head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="css/main.css">
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
        <div ng-include="includeProfile()"></div>
    </div>
    <div ng-include="includeSearch()"></div>
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

