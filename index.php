<!DOCTYPE html>
<html lang="en" ng-app="wtmApp">

  <head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="css/main.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
    <title>Watched That Movie</title>
  </head>

  <body>
    <div class="header">
      
    </div>
    <div data-ng-view="" id="ng-view"></div>
  </body>
  <script src="js/angular.min.js"></script>
  <script src="js/angular-route.min.js"></script>
  <script src="js/angular-animate.min.js" ></script>
  <script src="app/app.js"></script>
  <script src="app/data.js"></script>
  <script src="app/authCtrl.js"></script>
  <script src="app/movieCtrl.js"></script>
</html>

