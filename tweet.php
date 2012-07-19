<?php

/**
* tweet.php
* Posting a tweet with OAuth
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

// Use Matt Harris' OAuth library to make the connection
// This lives at: https://github.com/themattharris/tmhOAuth
require 'tmhOAuth/tmhOAuth.php';

// POST variables
$tweetId =			$_POST["tweetId"];
$tweetUser =		$_POST["tweetUser"];
$tweetUserImage =	$_POST["tweetUserImage"];
$tweetText =		stripslashes($_POST["tweetText"]);
$tweetTime =		$_POST["tweetTime"];
$replyUser =		$CASattributes["email"];
$replyText =		stripslashes($_POST["replyText"]);
$replyTime =		time();

// import database file
include_once('database.php');

// import twitterCredentials file
include_once('twitterCredentials.php');

try {

	// Define PHP Data Object (PDO)
	$db = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
	
	// Define PDO MySQL statement (SELECT)
	$selectStatement = $db->prepare("SELECT * FROM tweets WHERE tweetId=:tweetId");
	
	// Define placeholders and values in array
	$values = array(
		":tweetId"=>$tweetId
	);
	
	// Execute MySQL statement using array of values
	$selectStatement->execute($values);
	
	$rows = $selectStatement->rowCount();
	
	if ($rows != 0) {
		echo("Tweet has already been replied to");
	}
	else {
	
		try {
			// Define PHP Data Object (PDO)
			$db = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
			
			$insertStatement = $db->prepare("INSERT INTO tweets (tweetId, tweetUser, tweetUserImage, tweetText, tweetTime, replyUser, replyText, replyTime) values (:tweetId, :tweetUser, :tweetUserImage, :tweetText, :tweetTime, :replyUser, :replyText, :replyTime)");
			
			// Define placeholders and values in array
			$tweetValues = array(
				":tweetId"=>$tweetId,
				":tweetUser"=>$tweetUser,
				":tweetUserImage"=>$tweetUserImage,
				":tweetText"=>$tweetText,
				":tweetTime"=>$tweetTime,
				":replyUser"=>$replyUser,
				":replyText"=>$replyText,
				":replyTime"=>$replyTime
			);
			
			// Execute MySQL statement using array of values
			$insertStatement->execute($tweetValues);
			
			// Set the authorization values
			$connection = new tmhOAuth($twitterCredentials);		
			
			// Make the API call
			$code = $connection->request('POST', 
				$connection->url('1/statuses/update'), 
				array('status' => $replyText
			));
			
			if ($code == 200) {
			
				echo("Success!\n\nYou have successfully replied to this tweet!");
				
			}
			else {
			
				error_log($tmhOAuth->response['response']);
				
				try {
				
					// Define PHP Data Object (PDO)
					$db = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
					
					$deleteStatement = $db->prepare("DELETE FROM tweets WHERE tweetId=:tweetId");
		
					// Define placeholders and values in array
					$deleteValues = array(
						":tweetId"=>$tweetId
					);
					
					// Execute MySQL statement using array of values
					$deleteStatement->execute($deleteValues);
					
					echo("Failure!\n\nIt looks like something went wrong. Sorry about that. Please try again later.");
					
				}
				catch(PDOException $e) {
			
					error_log($e->getMessage(), 0);
				
					echo("We're sorry. It looks like something went wrong. Please try again later.");
					
				}				
			}
		}
		catch(PDOException $e) {
			
			error_log($e->getMessage(), 0);
		
			echo("We're sorry. It looks like something went wrong. Please try again later.");
			
		}
	}
}
catch(PDOException $e) {

	error_log($e->getMessage(), 0);
		
	echo("We're sorry. It looks like something went wrong. Please try again later.");

}

?>