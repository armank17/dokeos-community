<?php
include 'ppsdk_include_path.inc';

require_once 'PayPal.php';
require_once 'PayPal/Profile/Handler/Array.php';
require_once 'PayPal/Profile/API.php';
require_once 'PayPal/Type/RefundTransactionRequestType.php';
require_once 'PayPal/Type/RefundTransactionResponseType.php';
require_once 'lib/constants.inc.php';
require_once 'SampleLogger.php';

session_start();

$was_submitted = false;

$logger = new SampleLogger('RefundReceipt.php', PEAR_LOG_DEBUG);
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
$refund_request =& PayPal::getType('RefundTransactionRequestType');
if (PayPal::isError($refund_request)) {
   $logger->_log('Error in request: '. print_r($refund_request, true));
} else {
   $logger->_log('Create request: '. print_r($refund_request, true));
}


// Transaction ID is required
$tran_id = $_POST['transactionID'];
if(isset($tran_id)) {
   $refund_request->setTransactionId($tran_id, 'iso-8859-1');
} else {
   // Error: transaction ID is required
   $location = 'RefundTransaction.php';
   header("Location: $location");
}

// refundType is optional and must be translated
// amount is optional
$refundType = $_POST['refundType'];
if(isset($refundType)) {

   $refund_request->setRefundType($refundType, 'iso-8859-1');

   if(strcasecmp($refundType, REFUND_PARTIAL) == 0) {
      // Process amount for partial refund
      $amount = $_POST['amount'];
      if(isset($amount)) {
         $Amount =& PayPal::getType('BasicAmountType');
         $Amount->setattr('currencyID', 'USD');
         $Amount->setval($amount, 'iso-8859-1');
         $refund_request->setAmount($Amount);
      }
   }
}

// memo is optional
$memo = $_POST['memo'];
if(isset($memo)) {
   $refund_request->setTransactionId($tran_id, 'iso-8859-1');
}

$logger->_log('Initial request: '. print_r($refund_request, true));

$caller =& PayPal::getCallerServices($profile);

$response = $caller->RefundTransaction($refund_request);

$ack = $response->getAck();

$logger->_log('Ack='.$ack);

switch($ack) {
   case ACK_SUCCESS:
   case ACK_SUCCESS_WITH_WARNING:
      // Good to break out;
      break;

   default:
      $_SESSION['response'] =& $response;
      $logger->_log('RefundTransaction failed: ' . print_r($response, true));
      $location = "ApiError.php";
      header("Location: $location");
}

// Otherwise, load the HTML response

require_once 'pages/RefundReceipt.html.php';

?>