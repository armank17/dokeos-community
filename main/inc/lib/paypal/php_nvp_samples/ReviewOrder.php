<?php
include 'ppsdk_include_path.inc';

require_once 'PayPal.php';
require_once 'PayPal/Profile/Handler/Array.php';
require_once 'PayPal/Profile/API.php';
require_once 'PayPal/Type/BasicAmountType.php';
require_once 'PayPal/Type/SetExpressCheckoutRequestType.php';
require_once 'PayPal/Type/SetExpressCheckoutRequestDetailsType.php';
require_once 'PayPal/Type/SetExpressCheckoutResponseType.php';

require_once 'PayPal/Type/GetExpressCheckoutDetailsRequestType.php';
require_once 'PayPal/Type/GetExpressCheckoutDetailsResponseDetailsType.php';
require_once 'PayPal/Type/GetExpressCheckoutDetailsResponseType.php';

require_once 'lib/constants.inc.php';
require_once 'SampleLogger.php';

if(ENVIRONMENT == 'live') {
	define('PAYPAL_URL', 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=');
} else {
	define('PAYPAL_URL', 'https://www.' . ENVIRONMENT . '.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=');
}



session_start();

$profile = $_SESSION['APIProfile'];

$logger = new SampleLogger('ReviewOrder.php', PEAR_LOG_DEBUG);

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
if(! isset($token)) {

   // SetExpressCheckout handling
   $serverName = $_SERVER['SERVER_NAME'];
   // Use this to test with NAT
   // $serverName = '192.168.1.10';
   $serverPort = $_SERVER['SERVER_PORT'];

   // $pathInfo = '/php-sdk/samples/php';
   $path_parts = pathinfo($_SERVER['SCRIPT_NAME']);
   $path_info = $path_parts['dirname'];
   $url='http://'.$serverName.':'.$serverPort.$path_info;

   $paymentAmount=$_REQUEST['paymentAmount'];
   $currencyCodeType=$_REQUEST['currencyCodeType'];
   // paymentType is ActionCodeType in ASP SDK
   $paymentType=$_REQUEST['paymentType'];
	
   $personName        = $_REQUEST['NAME'];
   $SHIPTOSTREET      = $_REQUEST['SHIPTOSTREET'];
   $SHIPTOCITY        = $_REQUEST['SHIPTOCITY'];
   $SHIPTOSTATE	      = $_REQUEST['SHIPTOSTATE'];
   $SHIPTOCOUNTRYCODE = $_REQUEST['SHIPTOCOUNTRYCODE'];
   $SHIPTOZIP         = $_REQUEST['SHIPTOZIP'];
   $L_NAME0           = $_REQUEST['L_NAME0'];
   $L_AMT0            = $_REQUEST['L_AMT0'];
   $L_QTY0            =	(int)$_REQUEST['L_QTY0'];
   $L_NAME1           =	$_REQUEST['L_NAME1'];
   $L_AMT1            = $_REQUEST['L_AMT1'];
   $L_QTY1            =	(int)$_REQUEST['L_QTY1'];
	
   $itemamt = 0.00;
   $itemamt = $L_QTY0*$L_AMT0+$L_AMT1*$L_QTY1;
   $amt = 5.00+2.00+1.00+$itemamt;
   $maxamt= $amt+25.00;
   $shipamt = 3.00;
   $sDiscount = -1.00;
   $taxTotal = 1.00;
   $insureAmount = 1.00;
   $orderTotal = $itemamt + $shipamt + $insureAmount + $taxTotal + $sDiscount;
      
   $returnURL = $url.'/ReviewOrder.php?paymentAmount='.$orderTotal.'&currencyCodeType='.$currencyCodeType.'&paymentType='.$paymentType;
   $cancelURL = str_replace('ReviewOrder', 'ExpressCheckout', $returnURL);

   $ec_request =& PayPal::getType('SetExpressCheckoutRequestType');

   //Setting up URL information.
   $ec_details =& PayPal::getType('SetExpressCheckoutRequestDetailsType');
   $ec_details->setReturnURL($returnURL);
   $ec_details->setCancelURL($cancelURL);
   $ec_details->setCallbackTimeout('4');
   $ec_details->setBuyerEmail($_POST['buyersemail']);
   $ec_details->setPaymentAction($paymentType);
   $ec_details->setcpp_header_image('http://img91.imageshack.us/img91/8738/paypalsdk.jpg');  
	
   //Setting up OrderTotal.
   $amt_type =& PayPal::getType('BasicAmountType');
   $amt_type->setattr('currencyID', $currencyCodeType);
   $amt_type->setval($orderTotal, 'iso-8859-1');
   $ec_details->setOrderTotal($amt_type);

   //Setting up Max amount.
   $maxamt_type =& PayPal::getType('BasicAmountType');
   $maxamt_type->setattr('currencyID', $currencyCodeType);
   $maxamt_type->setval($maxamt, 'iso-8859-1');
   $ec_details->setMaxAmount($maxamt_type);
         	
	//Addition of Product Details.	
	$paymentDetailsType = &PayPal::getType('PaymentDetailsType');
	$paymentDetailsItem = &PayPal::getType('PaymentDetailsItemType');
	$paymentDetailsItem->setName($L_NAME0);
	$paymentDetailsItem->setQuantity($L_QTY0, 'iso-8859-1');
	$paymentDetailsItem->setAmount($L_AMT0, 'iso-8859-1');
	$paymentDetailsItem1 = &PayPal::getType('PaymentDetailsItemType');
	$paymentDetailsItem1->setName($L_NAME1);
	$paymentDetailsItem1->setQuantity($L_QTY1, 'iso-8859-1');
	$paymentDetailsItem1->setAmount($L_AMT1, 'iso-8859-1');
	$paymentDetailsType->setPaymentDetailsItem(array('PaymentDetailsItem00' => $paymentDetailsItem, 'PaymentDetailsItem01' => $paymentDetailsItem1));
	
	//Setting up OrderTotal on PaymentDetails.
	$itemTotal_type =& PayPal::getType('BasicAmountType');
   	$itemTotal_type->setattr('currencyID', $currencyCodeType);
   	$itemTotal_type->setval($itemamt, 'iso-8859-1');
   	$paymentDetailsType->setItemTotal($itemTotal_type);
	
	//Setting up Tax details
	$taxTotal_type =& PayPal::getType('BasicAmountType');
   	$taxTotal_type->setattr('currencyID', $currencyCodeType);
   	$taxTotal_type->setval($taxTotal, 'iso-8859-1');
	$paymentDetailsType->setTaxTotal($taxTotal_type);
	
	//Setting up the Shipping discount
	$sDiscount_type =& PayPal::getType('BasicAmountType');
   	$sDiscount_type->setattr('currencyID', $currencyCodeType);
   	$sDiscount_type->setval($sDiscount, 'iso-8859-1');
	$paymentDetailsType->setShippingDiscount($sDiscount_type);
		
	//Setting up Shipping Total on PaymentDetails.
	$shipamt_type =  &PayPal::getType('BasicAmountType');
   	$shipamt_type->setattr('currencyID', $currencyCodeType);
   	$shipamt_type->setval($shipamt, 'iso-8859-1');
	$paymentDetailsType->setShippingTotal($shipamt_type);
	
	$paymentDetailsType->setInsuranceOptionOffered(true);
   	$insureamt1 =  &PayPal::getType('BasicAmountType');
   	$insureamt1->setattr('currencyID', $currencyCodeType);
   	$insureamt1->setval($insureAmount, 'iso-8859-1');
   	$paymentDetailsType->setInsuranceTotal($insureamt1);
	
	$ec_details->setPaymentDetails($paymentDetailsType);
	
   	//Adition of shipping address details. 
   	$shipTo = & PayPal::getType('AddressType');
	$shipTo->setName($personName);
	$shipTo->setCountry($SHIPTOCOUNTRYCODE);
	$shipTo->setCityName($SHIPTOCITY);
	$shipTo->setPostalCode($SHIPTOZIP);
	$shipTo->setStreet1($SHIPTOSTREET); 
	$shipTo->setStateOrProvince($SHIPTOSTATE);
	$ec_details->setAddress($shipTo);
	
	
	//Addition of Shipping cost details.
	$shippingOption0 = &PayPal::getType('ShippingOptionType');
	$shippingOption0->setShippingOptionName('Ground');
	$shippingOption0->setShippingOptionAmount(3.00);
   	$shippingOption0->setShippingOptionIsDefault(true);
   	
   	$shippingOption1 = &PayPal::getType('ShippingOptionType');
   	$shippingOption1->setShippingOptionName('UPS Air');
   	$shippingOption1->setShippingOptionAmount(8.00);
	$shippingOption1->setShippingOptionIsDefault(false);   
   	$ec_details->setFlatRateShippingOptions(array('FlatRateShippingOptions00' => $shippingOption0, 'FlatRateShippingOptions01' => $shippingOption1));
	
   	
   	$ec_request->setSetExpressCheckoutRequestDetails($ec_details);

   	/*
   	 * Creating CallerServices object
   	 */
	$caller =& PayPal::getCallerServices($profile);
	$caller->USE_ARRAYKEY_AS_TAGNAME = true;
	$caller->SUPRESS_OUTTAG_FOR_ARRAY = true;
	$caller->OUTTAG_SUPRESS_ELEMENTS = array('PaymentDetailsItem','FlatRateShippingOptions');
   // Execute SOAP request
   $response = $caller->SetExpressCheckout($ec_request);
   // $display = print_r($response, true);
   $logger->_log('SetExpressCheckout response: '. print_r($response,true));

   $ack = $response->getAck();

   $logger->_log('Ack='.$ack);

   switch($ack) {
      case ACK_SUCCESS:
      case ACK_SUCCESS_WITH_WARNING:
         // Good to break out;

         // Redirect to paypal.com here
         $token = $response->getToken();
         $payPalURL = PAYPAL_URL.$token;
         // $display=$payPalURL;
         $logger->_log('Redirect to PayPal for payment: '. $payPalURL);
         header("Location: ".$payPalURL);
         exit;

      default:
         $_SESSION['response'] =& $response;
         $logger->_log('SetExpressCheckout failed: ' . print_r($response, true));
         $location = "ApiError.php";
         header("Location: $location");
   }

} else {

   // We have a TOKEN from paypal
   // GetExpressCheckoutDetails handling here
   $paymentType=$_REQUEST['paymentType'];
   $token = $_REQUEST['token'];
   $paymentAmount=$_REQUEST['paymentAmount'];
   $currencyCodeType=$_REQUEST['currencyCodeType'];

   $ec_request =& PayPal::getType('GetExpressCheckoutDetailsRequestType');
   $ec_request->setToken($token);

   $caller =& PayPal::getCallerServices($profile);

   // Execute SOAP request
   $response = $caller->GetExpressCheckoutDetails($ec_request);
   // $display = print_r($response, true);
   $logger->_log('GetExpressCheckoutDetails response: '. print_r($response,true));

   $ack = $response->getAck();

   $logger->_log('Ack='.$ack);

   switch($ack) {
      case ACK_SUCCESS:
      case ACK_SUCCESS_WITH_WARNING:
         // Continue on based on the require below...
         break;

      default:
         $_SESSION['response'] =& $response;
         $logger->_log('SetExpressCheckout failed: ' . print_r($response, true));
         $location = "ApiError.php";
         header("Location: $location");
   }

   require_once 'pages/GetExpressCheckoutDetails.html.php';
}

?>
