<?php

/**
* checkReply.php
* Check if tweet exists in database
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

$tweetId = $_REQUEST["tweetId"];

// import database file
include_once('database.php');

try {
	
	// Define PHP Data Object (PDO)
	$db = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
	
	// Define PDO MySQL statement (SELECT)
	$selectStatement = $db->prepare("SELECT * FROM tweets WHERE tweetId=:tweetId");
	$selectValues = array(":tweetId"=>$tweetId);
	$selectStatement->execute($selectValues);
	$rows = $selectStatement->rowCount();

	if ($rows != 0) {
	
		$selectResults = $selectStatement->fetch(PDO::FETCH_ASSOC);
		$replyUser = $selectResults["replyUser"];
	
		$selectResults = array(
			"tweetExists" => "true",
			"replyUser" => $replyUser
		);
		
		echo json_encode($selectResults);
		
	}
	else {
	
		$selectResults = array(
			"tweetExists" => "false",
			"replyUser" => "none"
		);

		echo json_encode($selectResults);
		
	}
}
catch(PDOException $e) {
	
	error_log($e->getMessage(), 0);
	
	$errorJson = array(
		"error" => "true",
	);

	echo json_encode($errorJson);
	
}

?>