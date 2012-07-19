<?php

/**
* retrieve.php
* Perform Twitter GET requests & parse data
* @author Cru
* @license GNU Public License
*/

// import phpCAS lib
include_once('CAS.php');

// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, 'signin.ccci.org' ,443, '/cas');

// no SSL validation for the CAS server
phpCAS::setNoCasServerValidation();

// Handle SAML logout requests that emanate from the CAS host exclusively.
// Failure to restrict SAML logout requests to authorized hosts could
// allow denial of service attacks where at the least the server is
// tied up parsing bogus XML messages.
phpCAS::handleLogoutRequests(true, 'signin.ccci.org');

// Force CAS authentication on any page that includes this file
phpCAS::forceAuthentication();

// Display an error if Employee ID is absent
$noEmployee = <<<EOF
 
Sorry, this site is only for Cru employees.
If you are one and you see this, please let <a href="mailto:jonathan.whitney@keynote.org">Jonathan Whitney</a> know.
 
EOF;

$CASattributes = phpCAS::getAttributes();
if (empty($CASattributes['emplid'])) {
	echo $noEmployee;
	exit;
}

$replyUser = $CASattributes["email"];

require 'tmhOAuth/tmhOAuth.php';
require 'tmhOAuth/tmhUtilities.php';

$requestType = $_REQUEST["type"];

// import database file
include_once('database.php');

// import twitterCredentials file
include_once('twitterCredentials.php');

if ($requestType == "timeline") {

	$tmhOAuth = new tmhOAuth($twitterCredentials);
	
	$code = $tmhOAuth->request('GET',
		$tmhOAuth->url('1/statuses/user_timeline'),
		array(
			'include_entities' => '1',
			'include_rts'      => '1',
			'screen_name'      => 'cru_chat',
			'count'            => 200,
		)
	);
	
	if ($code == 200) {
	
		$timeline = json_decode($tmhOAuth->response['response'], true);
		
		$responseHtml = array();
		
		foreach ($timeline as $tweet) :
			$entified_tweet = tmhUtilities::entify_with_options($tweet);
			$diff = time() - strtotime($tweet['created_at']);
			$created_at = parseTime($diff, $tweet['created_at']);
			
			$permalink  = str_replace(
				array(
					'%screen_name%',
					'%id%',
					'%created_at%'
				),
				array(
					$tweet['user']['screen_name'],
					$tweet['id_str'],
					$created_at,
				),
				'<a href="https://twitter.com/%screen_name%/status/%id%">%created_at%</a>'
			);
			
			$tweetIdString = $tweet['id_str'];
			$tweetUser = $tweet['user']['name'];
			$tweetUserName = $tweet['user']['screen_name'];
			$tweetProfileImage = $tweet['user']['profile_image_url'];

			$responseHtml[] = "<div class='tweet' id='tweet-$tweetIdString'><span class='profileImage'><img width='48px' height='48px' src='$tweetProfileImage' /></span><p class='text'><span class='username'><strong>CruChat</strong>&nbsp;<small>&#64;Cru_Chat</small></span><br /><span class='tweetText'>$entified_tweet</span><br /><span class='time'><small>$permalink</small></span></p></div>";
		
		endforeach;
		
		if (empty($responseHtml)) {
			
			$responseEmpty = "<h2>Welcome to Cru Chat!</h2><h3>It looks like things are just getting started. Start making some tweets and you will be able to see them here.</h3>";
			
			echo $responseEmpty;
			
		}
		else {
		
			$responseHtmlString = implode($responseHtml);
		
			echo $responseHtmlString;
		}		
	}
	else {
		
		error_log($tmhOAuth->response['response']);
		
		$responseHtmlString = "<h2>We're sorry. It looks like something went wrong.</h2><h2><a href='index.php' rel='external'>GO TO HOME PAGE</a></h2>";
		
		echo $responseHtmlString;
		
	}
}
elseif ($requestType == "mentions") {

	$tmhOAuth = new tmhOAuth($twitterCredentials);
	
	$code = $tmhOAuth->request('GET',
		$tmhOAuth->url('1/statuses/mentions'),
		array(
			'include_entities' => '1',
			'include_rts'      => '1',
			'screen_name'      => 'cru_chat',
			'count'            => 100,
		)
	);
	
	if ($code == 200) {
	
		$timeline = json_decode($tmhOAuth->response['response'], true);
		
		$responseHtml = array();
		
		foreach ($timeline as $tweet) :
			$entified_tweet = tmhUtilities::entify_with_options($tweet);
			$diff = time() - strtotime($tweet['created_at']);
			$created_at_raw = $tweet['created_at'];
			$created_at = parseTime($diff, $tweet['created_at']);
			
			$permalink  = str_replace(
				array(
					'%screen_name%',
					'%id%',
					'%created_at%'
				),
				array(
					$tweet['user']['screen_name'],
					$tweet['id_str'],
					$created_at,
				),
				'<a href="https://twitter.com/%screen_name%/status/%id%">%created_at%</a>'
			);
			
			$tweetIdString = $tweet['id_str'];
			$tweetUser = $tweet['user']['name'];
			$tweetUserName = $tweet['user']['screen_name'];
			$tweetProfileImage = $tweet['user']['profile_image_url'];
			
			// Define PHP Data Object (PDO)
			$db = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
			
			// Define PDO MySQL statement (SELECT)
			$checkStatement = $db->prepare("SELECT * FROM tweets WHERE tweetId=:tweetId");
			$checkValues = array(":tweetId"=>$tweetIdString);
			$checkStatement->execute($checkValues);
			$checkRows = $checkStatement->rowCount();
		
			if ($checkRows != 0) {
			
				$checkResults = $checkStatement->fetch(PDO::FETCH_ASSOC);
				$replyUser = $checkResults["replyUser"];
				
				$responseHtml[] = "<div class='tweet' id='tweet-$tweetIdString'><span class='profileImage'><img width='48px' height='48px' src='$tweetProfileImage' /></span><p class='text'><span class='username'><strong>$tweetUser</strong>&nbsp;<small>&#64;$tweetUserName</small></span><br /><span class='tweetText'>$entified_tweet</span><br /><span class='time'><small>$permalink</small></span><br /><span class='replied'><small>This tweet was replied to by $replyUser</small></span></p></div>";
				
			}
			else {
			
				$responseHtml[] = "<div class='tweet' id='tweet-$tweetIdString'><span class='profileImage'><img width='48px' height='48px' src='$tweetProfileImage' /></span><p class='text'><span class='username'><strong>$tweetUser</strong>&nbsp;<small>&#64;$tweetUserName</small></span><br /><span class='tweetText'>$entified_tweet</span><br /><span class='time'><small>$permalink</small></span><span class='timeRaw'>$created_at_raw</span><br /><span class='reply'><a class='btn btn-success btnReply' id='reply-$tweetIdString' href='#modal-$tweetIdString' data-toggle='modal'>Reply</a></span><span class='replySuccess'></span></p></div><div class='modal hide' id='modal-$tweetIdString'><form class='modal-form' id='form-$tweetIdString' action=''><div class='modal-header'><button type='button' class='close' data-dismiss='modal'>&times;</button><h3>Reply to @$tweetUser</h3></div><div class='modal-body'><textarea class='span6' rows='5' id='text-$tweetIdString'>@$tweetUserName</textarea><p><span class='counter'>Characters left: </span><span class='counter' id='counter-$tweetIdString'></span><br/ ><span class='counter-error'></span></p><input type='submit' class='btn btn-info' value='Tweet' /></form></div><div class='modal-footer'><div class='tweet tweet-modal alignleft'><span class='profileImage'><img width='48' height='48' src='$tweetProfileImage' /></span><p class='text'><span class='username'><strong>$tweetUserName</strong>&nbsp;<small>@$tweetUser</small></span><br /><span class='tweetText'>$entified_tweet</span><br /><span class='time'><small>$created_at</small></span></p></div></div><script type='text/javascript'>$('#text-$tweetIdString').simplyCountable({ counter: '#counter-$tweetIdString', countType: 'characters', wordSeparator: ' ', maxCount: 140, strictMax: false, countDirection: 'down', safeClass: 'safe', overClass: 'over', onOverCount: function() { $('#form-$tweetIdString').find('input[type=\"submit\"]').attr('disabled','disabled'); $('#form-$tweetIdString').find('.counter-error').text('You have exceeded the character limit!'); }, onSafeCount: function() { $('#form-$tweetIdString').find('input[type=\"submit\"]').removeAttr('disabled'); $('#form-$tweetIdString').find('.counter-error').text(''); } });</script></div>";
				
			}
		
		endforeach;
		
		if (empty($responseHtml)) {
			
			$responseEmpty = "<h2>No one has mentioned you!</h2><h3>Don't fret. As soon as you start tweeting odds are someone will mention you. Keep your head up.</h3>";
			
			echo $responseEmpty;
			
		}
		else {
		
			$responseHtmlString = implode($responseHtml);
			
			echo $responseHtmlString;
			
		}
		
	}
	else {
		
		error_log($tmhOAuth->response['response']);
		
		$responseHtmlString = "<h2>We're sorry. It looks like something went wrong.</h2><h2><a href='index.php' rel='external'>GO TO HOME PAGE</a></h2>";
		
		echo $responseHtmlString;
		
	}
	
}
elseif ($requestType == "myconversations") {
	
	try {
		
		// Define PHP Data Object (PDO)
		$db = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
		
		// Define PDO MySQL statement (SELECT)
		$selectStatement = $db->prepare("SELECT * FROM tweets WHERE replyUser=:replyUser");
		$selectValues = array(":replyUser"=>$replyUser);
		$selectStatement->execute($selectValues);
		$rows = $selectStatement->rowCount();

		if ($rows != 0) {
			
			$selectResults = array();
			
			for ($i=1; $i<=$rows; $i++) {
				$selectResults = $selectStatement->fetch(PDO::FETCH_ASSOC);
				
				$resultsTweetId =			$selectResults["tweetId"];
				$resultsTweetUser =			$selectResults["tweetUser"];
				$resultsTweetUserImage = 	$selectResults["tweetUserImage"];
				$resultsTweetText =			stripslashes($selectResults["tweetText"]);
				$tweetDiff = time() - strtotime($selectResults["tweetTime"]);
				$resultsTweetTime = parseTime($tweetDiff, $selectResults["tweetTime"]);
				
				$resultsReplyUser = $selectResults["replyUser"];
				$resultsReplyText = stripslashes($selectResults["replyText"]);
				$replyDiff = time() - $selectResults["replyTime"];
				$resultsReplyTime = parseTime($replyDiff, $selectResults["replyTime"]);
				
				$responseHtml[$i] = "<div class='convo'><div class='convoTweet'><div class='tweet' id='convo-tweet-$resultsTweetId'><p><span class='tweetHeader'><strong>Original Tweet:</strong></span></p><span class='profileImage'><img width='48px' height='48px' src='$resultsTweetUserImage' /></span><p class='text'><span class='username'</span><a href='http://twitter.com/#!/$resultsTweetUser' rel='external'><strong>@$resultsTweetUser</strong></a></span><br /><span class='tweetText'>$resultsTweetText</span><br /><span class='time'><small><a href='https://twitter.com/$resultsTweetUser/status/$resultsTweetId' rel='external'>$resultsTweetTime</a></small></span></p></div></div><div class='convoReply'><div class='tweet' id='convo-reply-$resultsTweetId'><p><span class='replyHeader'><strong>Your Reply Tweet:</strong></span></p><span class='profileImage'><img width='48px' height='48px' src='https://twimg0-a.akamaihd.net/profile_images/2241868801/Cru_logo_basic_reasonably_small_normal.jpg' /></span><p class='text'><span class='tweetText'>$resultsReplyText</span><br /><span class='time'><small>$resultsReplyTime</small></span></p></div></div></div>";
				
			}
			
			$responseHtmlString = implode(array_reverse($responseHtml));
		
			echo $responseHtmlString;
			
		}
		else {
		
			$responseEmpty = "<h2>You don't have any conversations yet!</h2><h3>Once you start replying to some tweets your conversations will show up here.</h3>";
				
			echo $responseEmpty;
		
		}
	}
	catch(PDOException $e) {
	
		error_log($e->getMessage(), 0);
		
		$responseHtmlString = "<h2>We're sorry. It looks like something went wrong.</h2><h2><a href='index.php' rel='external'>GO TO HOME PAGE</a></h2>";
		
		echo $responseHtmlString;
		
	}
}
elseif ($requestType == "allconversations") {
	
	try {
		
		// Define PHP Data Object (PDO)
		$db = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
		
		// Define PDO MySQL statement (SELECT)
		$selectStatement = $db->prepare("SELECT * FROM tweets");
		$selectStatement->execute();
		$rows = $selectStatement->rowCount();
	
		$selectResults = array();
		
		for ($i=1; $i<=$rows; $i++) {
			$selectResults = $selectStatement->fetch(PDO::FETCH_ASSOC);
			
			$resultsTweetId =			$selectResults["tweetId"];
			$resultsTweetUser =			$selectResults["tweetUser"];
			$resultsTweetUserImage = 	$selectResults["tweetUserImage"];
			$resultsTweetText =			stripslashes($selectResults["tweetText"]);
			$tweetDiff = time() - strtotime($selectResults["tweetTime"]);
			$resultsTweetTime = parseTime($tweetDiff, $selectResults["tweetTime"]);
			
			$resultsReplyUser = $selectResults["replyUser"];
			$resultsReplyText = stripslashes($selectResults["replyText"]);
			$replyDiff = time() - $selectResults["replyTime"];
			$resultsReplyTime = parseTime($replyDiff, $selectResults["replyTime"]);
			
			$responseHtml[$i] = "<div class='convo'><div class='convoTweet'><div class='tweet' id='convo-tweet-$resultsTweetId'><p><span class='tweetHeader'><strong>Original Tweet:</strong></span></p><span class='profileImage'><img width='48px' height='48px' src='$resultsTweetUserImage' /></span><p class='text'><span class='username'</span><a href='http://twitter.com/#!/$resultsTweetUser' rel='external'><strong>@$resultsTweetUser</strong></a></span><br /><span class='tweetText'>$resultsTweetText</span><br /><span class='time'><small><a href='https://twitter.com/$resultsTweetUser/status/$resultsTweetId' rel='external'>$resultsTweetTime</a></small></span></p></div></div><div class='convoReply'><div class='tweet' id='convo-reply-$resultsTweetId'><p><span class='replyHeader'><strong>Reply Tweet:<br /><small>Made by $resultsReplyUser</small></strong></span></p><span class='profileImage'><img width='48px' height='48px' src='https://twimg0-a.akamaihd.net/profile_images/2241868801/Cru_logo_basic_reasonably_small_normal.jpg' /></span><p class='text'><span class='tweetText'>$resultsReplyText</span><br /><span class='time'><small>$resultsReplyTime</small></span></p></div></div></div>";
		}
		
		if (empty($responseHtml)) {
		
			$responseEmpty = "<h2>There are no conversations yet!</h2><h3>It looks no one has replied to a tweet yet. Once they do you will see them here.</h3>";
			
			echo $responseEmpty;
			
		}
		else {
		
			$responseHtmlString = implode(array_reverse($responseHtml));
	
			echo $responseHtmlString;
		
		}
	}
	catch(PDOException $e) {
	
		error_log($e->getMessage(), 0);
		
		$responseHtmlString = "<h2>We're sorry. It looks like something went wrong.</h2><h2><a href='index.php' rel='external'>GO TO HOME PAGE</a></h2>";
		
		echo $responseHtmlString;
		
	}
}
elseif ($requestType == "search") {
	
	$searchTerm = $_REQUEST["term"];
	$searchTermReplace = '"' . str_replace('_', '%20', $searchTerm) . '"' . '+OR+' . str_replace('_', '', $searchTerm);
	
	if ($searchTermReplace == "cruchatdemo") {
		$idValue = "1";
	}
	else if ($serachTermReplace == "pray for me") {
		$idValue = "2";
	}
	
	try {
	
		$requestTimeStamp = time();
		
		// Define PHP Data Object (PDO)
		$db = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
		
		// Define PDO MySQL statement (SELECT)
		$selectStatement = $db->prepare("SELECT * FROM cache WHERE id=:idValue");
		$selectValues = array(":idValue"=>$idValue);
		$selectStatement->execute($selectValues);
		$selectResults = $selectStatement->fetch(PDO::FETCH_ASSOC);
		$selectJson = $selectResults["returnString"];
		$selectTimeStamp = $selectResults["timeStamp"];
		
		$timeDifference = $requestTimeStamp - $selectTimeStamp;
		
		// Define PDO statement (UPDATE)
		$insertStatement = $db->prepare("UPDATE cache SET returnString=:returnStringValue, timeStamp=:timeStampValue WHERE id=:idValue");
		
		if ($timeDifference >= 5) {
			
			// Define twitter GET search URL and get results
			$twitterSearch = "http://search.twitter.com/search.json?q=$searchTermReplace&lang=en&result_type=recent&page=1&rpp=100&include_entities=true&lang=en";
			$twitterJson = file_get_contents($twitterSearch);			
			$twitterSearchTime = time();
			$twitterValues = array(":returnStringValue"=>$twitterJson, ":timeStampValue"=>$twitterSearchTime, ":idValue"=>$idValue);
			$insertStatement->execute($twitterValues);
			
			$twitterParse = json_decode($twitterJson);
			
			$numberResults = count($twitterParse->{"results"});
			
			for ($i=0; $i<$numberResults; $i++) {
				
				$tweetTimeRaw = $twitterParse->{"results"}[$i]->{"created_at"};
				$tweetTimeDiff = time() - strtotime($tweetTimeRaw);
				$tweetTime = parseTime($tweetTimeDiff, $tweetTimeRaw);
				$tweetUser = $twitterParse->{"results"}[$i]->{"from_user"};
				$tweetUserName = $twitterParse->{"results"}[$i]->{"from_user_name"};
				$tweetUserUrl = "http://twitter.com/#!/$tweetUser";
				$tweetUserImage = $twitterParse->{"results"}[$i]->{"profile_image_url"};
				$tweetText = stripslashes($twitterParse->{"results"}[$i]->{"text"});
				$tweetId = $twitterParse->{"results"}[$i]->{"id_str"};
				
				try {
		
					// Define PHP Data Object (PDO)
					$db = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
					
					// Define PDO MySQL statement (SELECT)
					$checkStatement = $db->prepare("SELECT * FROM tweets WHERE tweetId=:tweetId");
					$checkValues = array(":tweetId"=>$tweetId);
					$checkStatement->execute($checkValues);
					$checkRows = $checkStatement->rowCount();
				
					if ($checkRows != 0) {
					
						$checkResults = $checkStatement->fetch(PDO::FETCH_ASSOC);
						$replyUser = $checkResults["replyUser"];
						
						$results[$i] = "<div class='tweet' id='tweet-$tweetId'><span class='profileImage'><img width='48' height='48' src='$tweetUserImage' /></span><p class='text'><span class='username'><a href='$tweetUserUrl' rel='external'><strong>$tweetUserName</strong></a>&nbsp;<small>@$tweetUser</small></span><br /><span class='tweetText'>$tweetText</span><br /><span class='time'><small><a href='https://twitter.com/$tweetUser/status/$tweetId' rel='external'>$tweetTime</a></small></span><br /><span class='replied'><small>This tweet was replied to by $replyUser</small></span></p></div>";
						
					}
					else {
					
						$results[$i] = "<div class='tweet' id='tweet-$tweetId'><span class='profileImage'><img width='48' height='48' src='$tweetUserImage' /></span><p class='text'><span class='username'><a href='$tweetUserUrl' rel='external'><strong>$tweetUserName</strong></a>&nbsp;<small>@$tweetUser</small></span><br /><span class='tweetText'>$tweetText</span><br /><span class='time'><small><a href='https://twitter.com/$tweetUser/status/$tweetId' rel='external'>$tweetTime</a></small></span><span class='timeRaw'>$tweetTimeRaw</span><br /><span class='reply'><a class='btn btn-success btnReply' id='reply-$tweetId' href='#modal-$tweetId' data-toggle='modal'>Reply</a></span><span class='replySuccess'></span></p></div><div class='modal hide' id='modal-$tweetId'><form class='modal-form' id='form-$tweetId' action=''><div class='modal-header'><button type='button' class='close' data-dismiss='modal'>&times;</button><h3>Reply to @$tweetUser</h3></div><div class='modal-body'><textarea class='span6' rows='5' id='text-$tweetId'>@$tweetUser</textarea><br /><p class='resources'><h4>Suggested Resources:</h4><p></p>EveryStudent.com: <i>Why is Life so Hard?</i>&nbsp;(&nbsp;<a href='http://bit.ly/hOC5bA' target='_blank'>Preview</a>&nbsp;&#124;&nbsp;<a class='resourceInsert' id='resource-1-$tweetId' href='#' name='http://bit.ly/hOC5bA'>Insert</a>&nbsp;)<br />EveryStudent.com: <i>What's My Purpose in Life?</i>&nbsp;(&nbsp;<a href='http://bit.ly/8urrEa' target='_blank'>Preview</a>&nbsp;&#124;&nbsp;<a class='resourceInsert' id='resource-2-$tweetId' href='#' name='http://bit.ly/8urrEa'>Insert</a>&nbsp;)<br />EveryStudent.com: <i>Where is God in the Midst of Tragedy?</i>&nbsp;(&nbsp;<a href='http://bit.ly/39D6dh' target='_blank'>Preview</a>&nbsp;&#124;&nbsp;<a class='resourceInsert' id='resource-3-$tweetId' href='#' name='http://bit.ly/39D6dh'>Insert</a>&nbsp;)</p><p><span class='counter'>Characters left: </span><span class='counter' id='counter-$tweetId'></span><br/ ><span class='counter-error'></span></p><input type='submit' class='btn btn-info' value='Tweet' /></form></div><div class='modal-footer'><div class='tweet tweet-modal alignleft'><span class='profileImage'><img width='48' height='48' src='$tweetUserImage' /></span><p class='text'><span class='username'><strong>$tweetUserName</strong>&nbsp;<small>@$tweetUser</small></span><br /><span class='tweetText'>$tweetText</span><br /><span class='time'><small>$tweetTime</small></span></p></div></div><script type='text/javascript'>$('#text-$tweetId').simplyCountable({ counter: '#counter-$tweetId', countType: 'characters', wordSeparator: ' ', maxCount: 140, strictMax: false, countDirection: 'down', safeClass: 'safe', overClass: 'over', onOverCount: function() { $('#form-$tweetId').find('input[type=\"submit\"]').attr('disabled','disabled'); $('#form-$tweetId').find('.counter-error').text('You have exceeded the character limit!'); }, onSafeCount: function() { $('#form-$tweetId').find('input[type=\"submit\"]').removeAttr('disabled'); $('#form-$tweetId').find('.counter-error').text(''); } });</script></div>";
						
					}
					
				}
				catch(PDOException $e) {
					
					error_log($e->getMessage(), 0);
			
					$responseHtmlString = "<h2>We're sorry. It looks like something went wrong.</h2><h2><a href='index.php' rel='external'>GO TO HOME PAGE</a></h2>";
					
					echo $responseHtmlString;
					
				}
			}
			
			if (empty($results)) {
			
				$responseEmpty = "<h2>No search results!</h2><h3>Do I hear crickets? I guess no one wants to talk about that topic. You might want to search for something else.</h3>";
				
				echo $responseEmpty;
				
			}
			else {
			
				$responseHtmlString = implode($results);
	
				echo $responseHtmlString;
				
			}		
		}
		else {
		
			$twitterJson = $selectJson;
			
			$twitterParse = json_decode($twitterJson);
			
			$numberResults = count($twitterParse->{"results"});
			
			for ($i=0; $i<$numberResults; $i++) {
				
				$tweetTimeRaw = $twitterParse->{"results"}[$i]->{"created_at"};
				$tweetTimeDiff = time() - strtotime($tweetTimeRaw);
				$tweetTime = parseTime($tweetTimeDiff, $tweetTimeRaw);
				$tweetUser = $twitterParse->{"results"}[$i]->{"from_user"};
				$tweetUserName = $twitterParse->{"results"}[$i]->{"from_user_name"};
				$tweetUserUrl = "http://twitter.com/#!/$tweetUser";
				$tweetUserImage = $twitterParse->{"results"}[$i]->{"profile_image_url"};
				$tweetText = stripslashes($twitterParse->{"results"}[$i]->{"text"});
				$tweetId = $twitterParse->{"results"}[$i]->{"id_str"};
				
				try {
		
					// Define PHP Data Object (PDO)
					$db = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
					
					// Define PDO MySQL statement (SELECT)
					$checkStatement = $db->prepare("SELECT * FROM tweets WHERE tweetId=:tweetId");
					$checkValues = array(":tweetId"=>$tweetId);
					$checkStatement->execute($checkValues);
					$checkRows = $checkStatement->rowCount();
				
					if ($checkRows != 0) {
					
						$checkResults = $checkStatement->fetch(PDO::FETCH_ASSOC);
						$replyUser = $checkResults["replyUser"];
						
						$results[$i] = "<div class='tweet' id='tweet-$tweetId'><span class='profileImage'><img width='48' height='48' src='$tweetUserImage' /></span><p class='text'><span class='username'><a href='$tweetUserUrl' rel='external'><strong>$tweetUserName</strong></a>&nbsp;<small>@$tweetUser</small></span><br /><span class='tweetText'>$tweetText</span><br /><span class='time'><small><a href='https://twitter.com/$tweetUser/status/$tweetId' rel='external'>$tweetTime</a></small></span><br /><span class='replied'><small>This tweet was replied to by $replyUser</small></span></p></div>";
						
					}
					else {
					
						$results[$i] = "<div class='tweet' id='tweet-$tweetId'><span class='profileImage'><img width='48' height='48' src='$tweetUserImage' /></span><p class='text'><span class='username'><a href='$tweetUserUrl' rel='external'><strong>$tweetUserName</strong></a>&nbsp;<small>@$tweetUser</small></span><br /><span class='tweetText'>$tweetText</span><br /><span class='time'><small><a href='https://twitter.com/$tweetUser/status/$tweetId' rel='external'>$tweetTime</a></small></span><span class='timeRaw'>$tweetTimeRaw</span><br /><span class='reply'><a class='btn btn-success btnReply' id='reply-$tweetId' href='#modal-$tweetId' data-toggle='modal'>Reply</a></span></p></div><div class='modal hide' id='modal-$tweetId'><form class='modal-form' id='form-$tweetId' action=''><div class='modal-header'><button type='button' class='close' data-dismiss='modal'>&times;</button><h3>Reply to @$tweetUser</h3></div><div class='modal-body'><textarea class='span6' rows='5' id='text-$tweetId'>@$tweetUser</textarea><br /><p class='resources'><h4>Suggested Resources:</h4><p></p>EveryStudent.com: <i>Why is Life so Hard?</i>&nbsp;(&nbsp;<a href='http://bit.ly/hOC5bA' target='_blank'>Preview</a>&nbsp;&#124;&nbsp;<a class='resourceInsert' id='resource-1-$tweetId' href='#' name='http://bit.ly/hOC5bA'>Insert</a>&nbsp;)<br />EveryStudent.com: <i>What's My Purpose in Life?</i>&nbsp;(&nbsp;<a href='http://bit.ly/8urrEa' target='_blank'>Preview</a>&nbsp;&#124;&nbsp;<a class='resourceInsert' id='resource-2-$tweetId' href='#' name='http://bit.ly/8urrEa'>Insert</a>&nbsp;)<br />EveryStudent.com: <i>Where is God in the Midst of Tragedy?</i>&nbsp;(&nbsp;<a href='http://bit.ly/39D6dh' target='_blank'>Preview</a>&nbsp;&#124;&nbsp;<a class='resourceInsert' id='resource-3-$tweetId' href='#' name='http://bit.ly/39D6dh'>Insert</a>&nbsp;)</p><p><span class='counter'>Characters left: </span><span class='counter' id='counter-$tweetId'></span><br/ ><span class='counter-error'></span></p><input type='submit' class='btn btn-info' value='Tweet' /></form></div><div class='modal-footer'><div class='tweet tweet-modal alignleft'><span class='profileImage'><img width='48' height='48' src='$tweetUserImage' /></span><p class='text'><span class='username'><strong>$tweetUserName</strong>&nbsp;<small>@$tweetUser</small></span><br /><span class='tweetText'>$tweetText</span><br /><span class='time'><small>$tweetTime</small></span></p></div></div><script type='text/javascript'>$('#text-$tweetId').simplyCountable({ counter: '#counter-$tweetId', countType: 'characters', wordSeparator: ' ', maxCount: 140, strictMax: false, countDirection: 'down', safeClass: 'safe', overClass: 'over', onOverCount: function() { $('#form-$tweetId').find('input[type=\"submit\"]').attr('disabled','disabled'); $('#form-$tweetId').find('.counter-error').text('You have exceeded the character limit!'); }, onSafeCount: function() { $('#form-$tweetId').find('input[type=\"submit\"]').removeAttr('disabled'); $('#form-$tweetId').find('.counter-error').text(''); } });</script></div>";
						
					}
					
				}
				catch(PDOException $e) {
					
					error_log($e->getMessage(), 0);
			
					$responseHtmlString = "<h2>We're sorry. It looks like something went wrong.</h2><h2><a href='index.php' rel='external'>GO TO HOME PAGE</a></h2>";
					
					echo $responseHtmlString;
					
				}
			}
			
			$responseHtmlString = implode($results);
	
			echo $responseHtmlString;
			
		}
		
	}
	catch(PDOException $e) {
		
		error_log($e->getMessage(), 0);
		
		$responseHtmlString = "<h2>We're sorry. It looks like something went wrong.</h2><h2><a href='index.php' rel='external'>GO TO HOME PAGE</a></h2>";
		
		echo $responseHtmlString;
		
	}
}

function parseTime($timeDiff, $timeDate) {
	if ($timeDiff < 1) {
		$resultsTime = 'Just now';
	}
	elseif ($timeDiff < 60) {
		$resultsTime = $timeDiff;
		if ($resultsTime == 1) {
			$resultsTime = $resultsTime . ' second ago';
		}
		else {
			$resultsTime = $resultsTime . ' seconds ago';
		}
	}
	elseif ($timeDiff < 60*60) {
		$resultsTime = floor($timeDiff/60);
		if ($resultsTime == 1) {
			$resultsTime = $resultsTime . ' minute ago';
		}
		else {
			$resultsTime = $resultsTime . ' minutes ago';
		}
	}
	elseif ($timeDiff < 60*60*24) {
		$resultsTime = floor($timeDiff/(60*60));
		if ($resultsTime == 1) {
			$resultsTime = $resultsTime . ' hour ago';
		}
		else {
			$resultsTime = $resultsTime . ' hours ago';
		}
	}
	elseif ($timeDiff < 60*60*24*7) {
		$resultsTime = floor($timeDiff/(60*60*24));
		if ($resultsTime == 1) {
			$resultsTime = $resultsTime . ' day ago';
		}
		else {
			$resultsTime = $resultsTime . ' days ago';
		}
	}
	else {
		$resultsTime = date('d M', strtotime($timeDate));
	}
	return($resultsTime);
}

?>