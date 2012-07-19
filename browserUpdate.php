<?php

/**
* browserupdate.php
* Site for browser updating
* @author Cru
* @license GNU Public License
*/

// import phpCAS lib
include_once('login.php');

?>

<!doctype html>
<html lang="en">
<head>
	
	<title>Cru Chat</title>
	
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<meta charset="utf-8">
	
	<link rel="shortcut icon" href="images/favicon.ico" />
	
	<link rel="stylesheet" href="bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="css/style.css?v01" />
	
	<script src="js/jquery-1.7.2.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui-1.8.21.custom.min.js" type="text/javascript"></script>
	
</head>

<body>

	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<a id="btnMenu" class="btn" href="#">
					<i class="icon-th-list"></i> Menu
				</a>
				<a class="brand" id="mainTitle" href="http://www.godtoolsapp.com/cruchat">
					Cru Chat
				</a>
			</div>
		</div>
	</div>

	<div id="mainContainer">
		<div id="browserInfo">
			<p>This app is not compatible with your current browser. We recommend using one of the following browsers:</p>
			<ul>
				<li>Chrome (<a href="http://www.google.com/chrome">Download</a>)</li>
				<li>Firefox (<a href="http://www.mozilla.org/en-US/firefox/new/">Download</a>)</li>
				<li>Safari (<a href="http://www.apple.com/safari/">Download</a>)</li>
				<li>Internet Explorer (Version 9) (<a href="http://windows.microsoft.com/en-us/internet-explorer/products/ie/home/">Download</a>)</li>
			</ul>
			<p>Please download and install one of these browsers in order to use this app.</p>
		</div>
	</div>
	
	<div id="footer">
		<p>
			<a href="http://missionhub.com">Give Feedback</a>
		</p>
	</div>

</body>

</html>