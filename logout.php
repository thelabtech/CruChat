<?php

/**
* logout.php
* Logout using CAS
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

$url = "http://www.cruchat.com";

phpCAS::logoutWithUrl($url)

?>