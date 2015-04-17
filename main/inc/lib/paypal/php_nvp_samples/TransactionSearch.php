<?php

include 'ppsdk_include_path.inc';

require_once 'SampleLogger.php';

session_start();

/**
 * Default startDateStr to 7 days ago in mm/dd/yyyy format
 */

$caller = $_SESSION['caller'];
// $profile = $_SESSION['APIProfile'];


$logger = new SampleLogger('TransactionSearch.php', PEAR_LOG_DEBUG);

// Verify that user is logged in
if(! isset($caller)) {
   // Not logged in -- Back to the login page
   
   $logger->_log('You are not logged in;  return to index.php'); 
   $location = 'index.php';
   header("Location: $location"); 
   exit;
} else {
   $logger->_log('profile: '. print_r($profile, true));  
}

require_once 'pages/TransactionSearch.html.php';

?>
