<?php

include 'ppsdk_include_path.inc';

require_once 'PayPal.php';
require_once 'PayPal/Profile/Handler/Array.php';
require_once 'PayPal/Profile/API.php';
require_once 'PayPal/Type/BasicAmountType.php';
require_once 'PayPal/Type/PaymentDetailsType.php';

require_once 'PayPal/Type/DoExpressCheckoutPaymentRequestType.php';
require_once 'PayPal/Type/DoExpressCheckoutPaymentRequestDetailsType.php';
require_once 'PayPal/Type/DoExpressCheckoutPaymentResponseType.php';

require_once 'lib/constants.inc.php';
require_once 'SampleLogger.php';

define('PAYPAL_URL', 'https://www.' . ENVIRONMENT . '.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=');

session_start();

$profile = $_SESSION['APIProfile'];

$logger = new SampleLogger('ECReceipt.php', PEAR_LOG_DEBUG);

// Verify that user is logged in
if(! isset($profile)) {
   // Not logged in -- Back to the login page
   
   $logger->_log('You are not logged in;  return to index.php'); 
   $location = 'index.php';
   header("Location: $location"); 
} else {
  $logger->_log('profile: '. print_r($profile, true));  
}

$token = $_REQUEST['token'];
$paymentAmount = $_REQUEST['paymentAmount'];
$paymentType = $_REQUEST['paymentType'];
$currencyCodeType = $_REQUEST['currencyCodeType'];
$payerID = $_REQUEST['payerID'];

$ec_details =& PayPal::getType('DoExpressCheckoutPaymentRequestDetailsType');

$ec_details->setToken($token);
$ec_details->setPayerID($payerID);
$ec_details->setPaymentAction($paymentType);

$amt_type =& PayPal::getType('BasicAmountType');
$amt_type->setattr('currencyID', $currencyCodeType);
$amt_type->setval($paymentAmount, 'iso-8859-1');  

$payment_details =& PayPal::getType('PaymentDetailsType');
$payment_details->setOrderTotal($amt_type);

$ec_details->setPaymentDetails($payment_details);

$ec_request =& PayPal::getType('DoExpressCheckoutPaymentRequestType');
$ec_request->setDoExpressCheckoutPaymentRequestDetails($ec_details);

$caller =& PayPal::getCallerServices($profile);

// Execute SOAP request
$response = $caller->DoExpressCheckoutPayment($ec_request);
// $display = print_r($response, true);
$logger->_log('DoExpressCheckoutPayment response: '. print_r($response,true));

$ack = $response->getAck();

$logger->_log('Ack='.$ack);

switch ($ack) {
   case ACK_SUCCESS:
   case ACK_SUCCESS_WITH_WARNING:
      // Show HTML below
      break;
   
   default:
      $_SESSION['response'] =& $response;   
      $logger->_log('DoExpressCheckoutPayment failed: ' . print_r($response, true));
      $location = "ApiError.php";
      header("Location: $location");  
}

// Marshall data out of response
$details = $response->getDoExpressCheckoutPaymentResponseDetails();
$payment_info = $details->getPaymentInfo();
$tran_ID = $payment_info->getTransactionID();

$amt_obj = $payment_info->getGrossAmount();
$amt = $amt_obj->_value;
$currency_cd = $amt_obj->_attributeValues['currencyID'];
$display_amt = $currency_cd.' '.$amt;


?>

<html>
<head>
<title>PayPal PHP SDK - DoExpressCheckoutPayment API</title>
</head> 
<body alink=#0000FF vlink=#0000FF>
<br>
<center>
<font size=2 color=black face=Verdana><b>DoExpressCheckoutPayment</b></font>
<br><br>

<b>Thank you for your payment!</b><br><br>
<table width=400>
	<tr>
		<td>Transaction ID:</td>
		<td><?php echo $tran_ID?></td>
	</tr>
	<tr>
		<td>Amount:</td>
		<td><?php echo $display_amt?></td>
	</tr>
</table>

</center>
<a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>