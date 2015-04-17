<?php
include 'ppsdk_include_path.inc';

require_once 'SampleLogger.php';

session_start();

$profile = $_SESSION['APIProfile'];

$logger = new SampleLogger('DoMassPay.php', PEAR_LOG_DEBUG);

// Verify that user is logged in
if(! isset($profile)) {
   // Not logged in -- Back to the login page
   
   $logger->_log('You are not logged in;  return to index.php'); 
   $location = 'index.php';
   header("Location: $location"); 
   exit;
} else {
  $logger->_log('profile: '. print_r($profile, true)); 
}

require_once 'pages/DoMassPay.html.php';
?>