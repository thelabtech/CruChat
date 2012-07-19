<?php

/**
* login.php
* Login using CAS
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

// Display an error if Employee ID is absent
$noAuthorization = <<<EOF
 
Sorry, this site is only for authorized Cru employees.
If you are one and you see this, please let <a href="mailto:jonathan.whitney@keynote.org">Jonathan Whitney</a> know.
 
EOF;

$CASattributes = phpCAS::getAttributes();
if (empty($CASattributes['emplid'])) {
	echo $noEmployee;
	exit;
}
else {

	// import database file
	include_once('database.php');
	
	try {
	
		// Define PHP Data Object (PDO)
		$relayDb = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
		
		// Define PDO MySQL statement (SELECT)
		$relayStatement = $relayDb->prepare("SELECT * FROM relay WHERE relayEmployeeId=:relayEmployeeId");
		
		// Define placeholders and values in array
		$relayValues = array(
			":relayEmployeeId"=>$CASattributes['emplid']
		);
		
		// Execute MySQL statement using array of values
		$relayStatement->execute($relayValues);
		
		$relayRows = $relayStatement->rowCount();
		
		if ($relayRows == 0) {
		
			try {
				// Define PHP Data Object (PDO)
				$insertDb = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
				
				$insertStatement = $insertDb->prepare("INSERT INTO relay (relayUsername, relayEmail, relayEmployeeId) values (:relayUsername, :relayEmail, :relayEmployeeId)");
				
				// Define placeholders and values in array
				$insertValues = array(
					":relayUsername"=>$CASattributes['username'],
					":relayEmail"=>$CASattributes['email'],
					":relayEmployeeId"=>$CASattributes['emplid']
				);
				
				// Execute MySQL statement using array of values
				$insertStatement->execute($insertValues);
			}
			catch(PDOException $e) {
			
				echo($e->getMessage());
				
			}
		}
		
// 		try {
// 		
// 			// Define PHP Data Object (PDO)
// 			$userDb = new PDO("mysql:host=$dbHost; dbname=$dbName", $dbUsername, $dbPassword);
// 			
// 			// Define PDO MySQL statement (SELECT)
// 			$userStatement = $userDb->prepare("SELECT * FROM users WHERE email=:email");
// 			
// 			// Define placeholders and values in array
// 			$userValues = array(
// 				":email"=>$CASattributes['email']
// 			);
// 			
// 			// Execute MySQL statement using array of values
// 			$userStatement->execute($userValues);
// 			
// 			$userRows = $userStatement->rowCount();
// 			
// 			if ($userRows == 0) {
// 				echo $noAuthorization;
// 				exit;
// 			}
// 			
// 		}
// 		catch(PDOException $e) {
// 	
// 			echo($e->getMessage());
// 		
// 		}
		
	}
	catch(PDOException $e) {
	
		echo($e->getMessage());
	
	}

}

?>