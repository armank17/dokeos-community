<?php
include 'ppsdk_include_path.inc';

require_once 'Multi.php';
require_once 'PayPal.php';
require_once 'PayPal/Profile/Handler/Array.php';
require_once 'PayPal/Profile/API.php';
require_once 'PayPal/Type/MassPayRequestType.php';
require_once 'PayPal/Type/MassPayRequestItemType.php';
require_once 'PayPal/Type/MassPayResponseType.php';
require_once 'SampleLogger.php';
require_once 'lib/constants.inc.php';

session_start();

$was_submitted = false;

$logger = new SampleLogger('MassPayReceipt.php', PEAR_LOG_DEBUG);
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
$masspay_request =& PayPal::getType('MassPayRequestType');
if (PayPal::isError($masspay_request)) {
   $logger->_log('Error in request: '. print_r($masspay_request, true));
} else {
   $logger->_log('Create request: '. print_r($masspay_request, true));
}

// Set request fields
$emailSubject=$_POST['emailSubject'];
$receiverType=$_POST['receiverType'];

for($i=0,$j=0; $i< count($_POST["receiveremail"]); $i++)
	{
		if (isset($_POST['receiveremail'][$i]) && $_POST['receiveremail'][$i]!='' )
         {

			$massPayItems[$j] =& PayPal::getType('MassPayRequestItemType');
			$massPayItems[$j]->setReceiverEmail($_POST["receiveremail"][$i]);
			$massPayItems[$j]->setNote($_POST["note"][$i]);
			$massPayItems[$j]->setUniqueId($_POST["uniqueID"][$i]);

			$amtType =& PayPal::getType('BasicAmountType');
			$amtType->setattr('currencyID','USD');
			$amtType->setval($_POST["amount"][$i],'iso-8859-1');
			$massPayItems[$j]->setAmount($amtType);
			$j++;
		 }

	}
$masspay_request->setEmailSubject($emailSubject);
$masspay_request->setReceiverType($receiverType);
//creating multiple occurences of MassPayRequestItem
$multiItems =new MultiOccurs($masspay_request, 'MassPayRequestItem');
	$multiItems->setChildren($massPayItems);
	$masspay_request->setMassPayItem($multiItems);

$logger->_log('Initial request: '. print_r($masspay_request, true));

$caller =& PayPal::getCallerServices($profile);

$response =$caller->MassPay($masspay_request);

$ack = $response->getAck();

$logger->_log('Ack='.$ack);

switch($ack) {
   case ACK_SUCCESS:
   case ACK_SUCCESS_WITH_WARNING:
      // Good to break out;
      break;

   default:
      $_SESSION['response'] = $response;
      $logger->_log('Domasspay failed: ' . print_r($response, true));
      $location = "ApiError.php";
      header("Location: $location");
}

// Otherwise, load the HTML response
require_once 'pages/MasspayReceipt.html.php';

?>