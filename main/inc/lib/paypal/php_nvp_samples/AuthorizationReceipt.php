<?php
include 'ppsdk_include_path.inc';

require_once 'PayPal.php';
require_once 'PayPal/Profile/Handler/Array.php';
require_once 'PayPal/Profile/API.php';
require_once 'PayPal/Type/DoAuthorizationRequestType.php';
require_once 'PayPal/Type/DoAuthorizationResponseType.php';
// Add all of the types
require_once 'PayPal/Type/BasicAmountType.php';

require_once 'lib/constants.inc.php';
require_once 'SampleLogger.php';

session_start();

$was_submitted = false;

$logger = new SampleLogger('AuthorizationReceipt.php', PEAR_LOG_DEBUG);
$logger->_log('POST variables: '. print_r($_POST, true));

$profile = $_SESSION['APIProfile'];

// Verify that user is logged in
if(! isset($profile)) {
   // Not logged in -- Back to the login page

   $logger->_log('You are not logged in;  return to index.php');
   $location = 'index.php';
   header("Location: $location");
} else {
   $logger->_log('Profile from session: '.print_r($profile, true));
}

// Build our request from $_POST
$authorization_request =& PayPal::getType('DoAuthorizationRequestType');
if (PayPal::isError($authorization_request)) {
   $logger->_log('Error in request: '. print_r($authorization_request, true));
}

// Set request fields
$authorization_request->setTransactionID($_POST['order_id']);
$authorization_request->setTransactionEntity('Order');

$amtType =& PayPal::getType('BasicAmountType');
$amtType->setattr('currencyID', $_POST['currency']);
$amtType->setval($_POST['amount'], 'iso-8859-1');
$authorization_request->setAmount($amtType);

$logger->_log('Initial request: '. print_r($authorization_request, true));

$caller =& PayPal::getCallerServices($profile);

$response =$caller->DoAuthorization($authorization_request);

$ack = $response->getAck();

$logger->_log('Ack='.$ack);

switch($ack) {
   case ACK_SUCCESS:
   case ACK_SUCCESS_WITH_WARNING:
      // Good to break out;
      break;

   default:
      $_SESSION['response'] = $response;
      $logger->_log('DoAuthorization failed: ' . print_r($response, true));
      $location = "ApiError.php";
      header("Location: $location");
}

// Otherwise, load the HTML response
require_once 'pages/AuthorizationReceipt.html.php';

?>