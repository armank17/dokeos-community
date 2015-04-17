<?php

include 'ppsdk_include_path.inc';

require_once 'SampleLogger.php';

session_start();

$caller = $_SESSION['caller'];
// $profile = $_SESSION['APIProfile'];

$logger = new SampleLogger('RefundTransaction.php', PEAR_LOG_DEBUG);

// Verify that user is logged in
if(! isset($caller)) {
    // Not logged in -- Back to the login page
    
    $logger->_log('You are not logged in;  return to index.php');
    header("Location: index.php");
    exit;
} else {
   $logger->_log('profile: '. print_r($profile, true));
}

require_once 'pages/RefundTransaction.html.php';
