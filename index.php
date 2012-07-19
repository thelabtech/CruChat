<?php

/**
* index.php
* Main site file
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
	
	<meta name="apple-mobile-web-app-capable" content="yes" />
	
	<link rel="shortcut icon" href="images/favicon.ico" />
	
	<link rel="stylesheet" href="bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="css/style.css?v01" />
	
	<script src="js/jquery-1.7.2.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui-1.8.21.custom.min.js" type="text/javascript"></script>
	
	<script src="bootstrap/js/bootstrap-tab.js" type="text/javascript"></script>
	<script src="bootstrap/js/bootstrap-modal.js" type="text/javascript"></script>
	<script src="bootstrap/js/bootstrap-tooltip.js" type="text/javascript"></script>
	<script src="bootstrap/js/bootstrap-popover.js" type="text/javascript"></script>
	<script src="bootstrap/js/bootstrap-tour.js" type="text/javascript"></script>
	
	<script src="js/scripts.js?v01" type="text/javascript"></script>
	<script src="js/tour.js?v01" type="text/javascript"></script>
	
	<script src="js/jquery-simplyCountable.js" type="text/javascript"></script>
	<script src="js/jquery.cookie.js" type="text/javascript"></script>
	
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-33438601-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
	
</head>

<body>

	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<a id="btnMenu" class="btn" href="#">
					<i class="icon-th-list"></i>&nbsp;<strong>Menu</strong>
				</a>
				<ul class="nav">
					<li>
						<a class="brand" id="mainTitle" href="http://www.cruchat.com" onClick="_gaq.push(['_trackEvent', 'Navbar', 'Click', 'Brand']);">
							Cru Chat
						</a>
					</li>
				</ul>
				<ul class="nav pull-right">
					<li>
						<a id="tourStart" class="restart" href="#" onClick="_gaq.push(['_trackEvent', 'Navbar', 'Click', 'Help']);">
							<!--<i class="icon-question-sign icon-white"></i>--><strong>&nbsp;Help</strong>
						</a>
					</li>
					<li>
						<a href="logout.php" onClick="_gaq.push(['_trackEvent', 'Navbar', 'Click', 'Logout']);">
							<!--<i class="icon-off icon-white"></i>--><strong>&nbsp;Logout</strong>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<div id="mainContainer">
		<div id="listContent">
			<ul>
				<li class="active">
					<a id="linkTimeline" href="#timeline" data-toggle="tab" onClick="_gaq.push(['_trackEvent', 'Menu', 'Click', 'Home']);">
						<i class="icon-home icon-white menu"></i><span>Home</span>
					</a>
				</li>
				
				<li>
					<a id="linkConversations" href="#myconversations" data-toggle="tab" onClick="_gaq.push(['_trackEvent', 'Menu', 'Click', 'My Conversations']);">
						<i class="icon-user icon-white menu"></i><span>My Conversations</span>
					</a>
				</li>
				
				<li>
					<a id="linkAllConversations" href="#allconversations" data-toggle="tab" onClick="_gaq.push(['_trackEvent', 'Menu', 'Click', 'All Conversations']);">
						<i class="icon-globe icon-white menu"></i><span>All Conversations</span>
					</a>
				</li>
				
				<!--
				<li>
					<a class="linkSearch" id="cruchatdemo" href="#search-cruchatdemo" data-toggle="tab">
						<i class="icon-search icon-white menu"></i><span>#cruchatdemo</span>
					</a>
				</li>
				-->
				
				<li>
					<a class="linkSearch" id="pray_for_me" href="#search-pray_for_me" data-toggle="tab" onClick="_gaq.push(['_trackEvent', 'Menu', 'Click', 'Pray for me']);">
						<i class="icon-search icon-white menu"></i><span>"pray for me"</span>
					</a>
				</li>
			
			</ul>
		</div>
		
		<div id="tabContent" class="tab-content">
			
			<div id="timeline" class="tab-pane active tweetContent">
				<div class="tabHeader">
					<a href="#" id="refreshTimeline" class="btn btn-info refresh-home refreshTimeline floatleft" onClick="_gaq.push(['_trackEvent', 'Refresh', 'Click', 'Home']);">
						<i class="icon-refresh icon-white"></i>
					</a>
					<ul class="nav nav-tabs">
						<li class="active">
							<a href="#timelineTweets" data-toggle="tab" onClick="_gaq.push(['_trackEvent', 'Tab', 'Click', 'Timeline']);">
								<h3>
									Timeline
								</h3>
							</a>
						</li>
						<li>
							<a href="#timelineMentions" data-toggle="tab" onClick="_gaq.push(['_trackEvent', 'Tab', 'Click', 'Mentions']);">
								<h3>
									Mentions
								</h3>
							</a>
						</li>
					</ul>
				</div>
				<div class="tab-content">
					<div id="timelineTweets" class="tab-pane active">
						<div id="twitterUserTimeline" class="tweets">
						</div>
					</div>
					<div id="timelineMentions" class="tab-pane">
						<div id="twitterUserMentions" class="tweets">
						</div>
					</div>
				</div>
			</div>
			
			<div id="myconversations" class="tab-pane tweetContent">
				<div class="tabHeader">
					<h2>
						My Conversations<a href="#" id="refreshConversations" class="btn btn-info refresh floatleft" onClick="_gaq.push(['_trackEvent', 'Refresh', 'Click', 'My Conversations']);"><i class="icon-refresh icon-white"></i></a>
					</h2>
				</div>
				<div id="twitterMyConversations" class="tweets">
				</div>
			</div>
			
			<div id="allconversations" class="tab-pane tweetContent">
				<div class="tabHeader">
					<h2>
						All Conversations<a href="#" id="refreshAllConversations" class="btn btn-info refresh floatleft" onClick="_gaq.push(['_trackEvent', 'Refresh', 'Click', 'All Conversations']);"><i class="icon-refresh icon-white"></i></a>
					</h2>
				</div>
				<div id="twitterAllConversations" class="tweets">
				</div>
			</div>
			
			<!--
			<div id="search-cruchatdemo" class="tab-pane tweetContent">
				<div class="tabHeader">
					<h2>
						Search: "#cruchatdemo"<a class="btn btn-info refresh floatleft linkSearch" id="refreshSearch-cruchatdemo" href="#"><i class="icon-refresh icon-white"></i></a>
					</h2>
				</div>				
				<div id="twitterSearch-cruchatdemo" class="tweets">
				</div>
			</div>
			-->
			
			<div id="search-pray_for_me" class="tab-pane tweetContent">
				<div class="tabHeader">
					<h2>
						Search: "pray for me"<a class="btn btn-info refresh floatleft linkSearch" id="refreshSearch-pray_for_me" href="#" onClick="_gaq.push(['_trackEvent', 'Refresh', 'Click', 'Pray for me']);"><i class="icon-refresh icon-white"></i></a>
					</h2>
				</div>				
				<div id="twitterSearch-pray_for_me" class="tweets">
				</div>
			</div>
			
		</div>
	</div>
	
	<div id="footer">
		<p>
			<a id="feedback" href="http://mhub.cc/s/749" onClick="_gaq.push(['_trackEvent', 'Footer', 'Click', 'Feedback']);">Give Feedback</a>
		</p>
	</div>
	
	<div id="spinner" class="spinner" style="display:none;">
    	<img id="img-spinner" src="images/spinner.gif" alt="Loading"/>
	</div>

</body>

</html>