<?php

include 'ppsdk_include_path.inc';

require_once 'PayPal.php';
require_once 'PayPal/Profile/Handler/Array.php';
require_once 'PayPal/Profile/API.php';
require_once 'PayPal/Type/GetTransactionDetailsRequestType.php';
require_once 'SampleLogger.php';
require_once 'lib/constants.inc.php';

session_start();

$was_submitted = false;

$logger = new SampleLogger('TransactionDetails.php', PEAR_LOG_DEBUG);
$logger->_log('POST variables: '. print_r($_POST, true));

$profile = $_SESSION['APIProfile'];

// Verify that user is logged in
if(! isset($profile)) {
    // Not logged in -- Back to the login page

    $logger->_log('You are not logged in;  return to index.php');
    header("Location: index.php");
    exit;
} else {
    $logger->_log('Profile from session: '.print_r($profile, true));
}

// Build our request from $_POST
// $trans_details = new TransactionSearchRequestType();
$trans_details =& PayPal::getType('GetTransactionDetailsRequestType');
if (PayPal::isError($trans_details)) {
   $logger->_log('Error in request: '. print_r($trans_details, true));
} else {
   $logger->_log('Create request: '. print_r($trans_details, true));
}

// Set request fields
$tran_id = $_GET['transactionID'];
if(isset($tran_id)) {
   $trans_details->setTransactionId($tran_id, 'iso-8859-1');
} else {
   // Error: transaction ID was not set
   $location = 'GetTransactionDetails.php';
   header("Location: $location");
   exit;
}


$logger->_log('Initial request: '. print_r($trans_details, true));

$caller =& PayPal::getCallerServices($profile);

$response = $caller->GetTransactionDetails($trans_details);

$ack = $response->getAck();

$logger->_log('Ack='.$ack);

switch($ack) {
   case ACK_SUCCESS:
   case ACK_SUCCESS_WITH_WARNING:
      // Good to break out;
      break;

   default:
      $_SESSION['response'] = $response;
      $logger->_log('GetTransactionDetails failed: ' . print_r($response, true));
      $location = "ApiError.php";
      header("Location: $location");
      exit;
}

// Otherwise, load the HTML response
require_once 'pages/TransactionDetails.html.php';
