<?php

include '../ppsdk_include_path.inc';

require_once 'PayPal.php';
require_once 'PayPal/Profile/Handler/Array.php';
require_once 'PayPal/Profile/API.php';
require_once 'PayPal/Type/CreateRecurringPaymentsProfileRequestType.php';
require_once 'PayPal/Type/CreateRecurringPaymentsProfileRequestDetailsType.php';
require_once 'PayPal/Type/CreateRecurringPaymentsProfileResponseType.php';
require_once 'PayPal/Type/RecurringPaymentsProfileDetailsType.php';
require_once 'PayPal/Type/ScheduleDetailsType.php';
require_once 'PayPal/Type/BillingPeriodDetailsType.php';

// Add all of the types
require_once 'PayPal/Type/BasicAmountType.php';
require_once 'PayPal/Type/PaymentDetailsType.php';
require_once 'PayPal/Type/AddressType.php';
require_once 'PayPal/Type/CreditCardDetailsType.php';
require_once 'PayPal/Type/PayerInfoType.php';
require_once 'PayPal/Type/PersonNameType.php';

require_once '../lib/constants.inc.php';
require_once '../SampleLogger.php';

define('UNITED_STATES', 'US');

session_start();

$was_submitted = false;

$logger = new SampleLogger('CreateRPProfileReceipt.php', PEAR_LOG_DEBUG);
$logger->_log('POST variables: '. print_r($_POST, true));

$profile = $_SESSION['APIProfile'];
// $caller = $_SESSION['caller'];

// Verify that user is logged in
if(! isset($profile)) {
   // Not logged in -- Back to the login page

   $logger->_log('You are not logged in;  return to index.php');
   $location = '../index.php';
   header("Location: $location");
} else {
   $logger->_log('Profile from session: '.print_r($profile, true));
}

// Build our request from $_POST
$crpp_request =& PayPal::getType('CreateRecurringPaymentsProfileRequestType');
if (PayPal::isError($crpp_request)) {
   $logger->_log('Error in request: '. print_r($crpp_request, true));
} else {
   $logger->_log('Create request: '. print_r($crpp_request, true));
}

$logger->_log('Initial request: '. print_r($crpp_request, true));

/**
 * Get posted request values
 */
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$creditCardType = $_POST['creditCardType'];
$creditCardNumber = $_POST['creditCardNumber'];
$expDateMonth = $_POST['expDateMonth'];
// Month must be padded with leading zero
$padDateMonth = str_pad($expDateMonth, 2, '0', STR_PAD_LEFT);

$expDateYear = $_POST['expDateYear'];
$cvv2Number = $_POST['cvv2Number'];
$address1 = $_POST['address1'];
$address2 = $_POST['address2'];
$city = $_POST['city'];
$state = $_POST['state'];
$zip = $_POST['zip'];
$amount = $_POST['amount'];

$profileDesc = $_POST['profileDesc'];
$billingPeriod = $_POST['billingPeriod'];
$billingFrequency = $_POST['billingFrequency'];
$totalBillingCycles = $_POST['totalBillingCycles'];

$profileStartDateDay = $_POST['profileStartDateDay'];
// Day must be padded with leading zero
$padprofileStartDateDay = str_pad($profileStartDateDay, 2, '0', STR_PAD_LEFT);
$profileStartDateMonth = $_POST['profileStartDateMonth'];
// Month must be padded with leading zero
$padprofileStartDateMonth = str_pad($profileStartDateMonth, 2, '0', STR_PAD_LEFT);
$profileStartDateYear = $_POST['profileStartDateYear'];

$profileStartDate = $profileStartDateYear . '-' . $padprofileStartDateMonth . '-' . $padprofileStartDateDay . 'T00:00:00Z'; 

// Populate SOAP request information
$shipTo =& PayPal::getType('AddressType');
$shipTo->setName($firstName.' '.$lastName);
$shipTo->setStreet1($address1);
$shipTo->setStreet2($address2);
$shipTo->setCityName($city);
$shipTo->setStateOrProvince($state);
$shipTo->setCountry(UNITED_STATES);
$shipTo->setPostalCode($zip);

$RPProfileDetails =& PayPal::getType('RecurringPaymentsProfileDetailsType');
$RPProfileDetails->setBillingStartDate($profileStartDate);

$crpp_details =& PayPal::getType('CreateRecurringPaymentsProfileRequestDetailsType');
$crpp_details->setRecurringPaymentsProfileDetails($RPProfileDetails);

// Credit Card info
$card_details =& PayPal::getType('CreditCardDetailsType');
$card_details->setCreditCardType($creditCardType);
$card_details->setCreditCardNumber($creditCardNumber);
$card_details->setExpMonth($padDateMonth);
$card_details->setExpYear($expDateYear);
$card_details->setCVV2($cvv2Number);
$logger->_log('card_details: '. print_r($card_details, true));

$payer =& PayPal::getType('PayerInfoType');
$person_name =& PayPal::getType('PersonNameType');
$person_name->setFirstName($firstName);
$person_name->setLastName($lastName);
$payer->setPayerName($person_name);

$payer->setPayerCountry(UNITED_STATES);
$payer->setAddress($shipTo);

$card_details->setCardOwner($payer);

$crpp_details->setCreditCard($card_details);
$scheduleDetails =& PayPal::getType('ScheduleDetailsType');
$scheduleDetails->setDescription($profileDesc);

$billingPeriodDetails =& PayPal::getType('BillingPeriodDetailsType');
$billingPeriodDetails->setBillingPeriod($billingPeriod);
$billingPeriodDetails->setBillingFrequency($billingFrequency);
$billingPeriodDetails->setTotalBillingCycles($totalBillingCycles);
$billingPeriodDetails->setAmount($amount);

$scheduleDetails->setPaymentPeriod($billingPeriodDetails);
$crpp_details->setScheduleDetails($scheduleDetails);
$crpp_request->setCreateRecurringPaymentsProfileRequestDetails($crpp_details);

$caller =& PayPal::getCallerServices($profile);

// Execute SOAP request
$response = $caller->CreateRecurringPaymentsProfile($crpp_request);

$ack = $response->getAck();

$logger->_log('Ack='.$ack);

switch($ack) {
   case ACK_SUCCESS:
   case ACK_SUCCESS_WITH_WARNING:
      // Good to break out;
      break;

   default:
      $_SESSION['response'] =& $response;
      $logger->_log('CreateRecurringPaymentsProfile failed: ' . print_r($response, true));
      $location = "../ApiError.php?HomeLink=RecurringPayments/RecurringPayments.php&HomeLinkName=RecurringPaymentsHome";
      header("Location: $location");
}

// Otherwise, load the HTML response

require_once 'pages/CreateRPProfileReceipt.html.php';

?>



