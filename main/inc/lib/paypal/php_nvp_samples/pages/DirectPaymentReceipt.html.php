<?php

require_once 'PayPal.php';
require_once 'PayPal/Profile/Handler/Array.php';
require_once 'PayPal/Profile/API.php';
require_once 'PayPal/Type/DoDirectPaymentRequestType.php';
require_once 'PayPal/Type/DoDirectPaymentRequestDetailsType.php';
require_once 'PayPal/Type/DoDirectPaymentResponseType.php';
// Add all of the types
require_once 'PayPal/Type/BasicAmountType.php';
require_once 'PayPal/Type/PaymentDetailsType.php';
require_once 'PayPal/Type/AddressType.php';
require_once 'PayPal/Type/CreditCardDetailsType.php';
require_once 'PayPal/Type/PayerInfoType.php';
require_once 'PayPal/Type/PersonNameType.php';

require_once 'lib/constants.inc.php';
require_once 'SampleLogger.php';

$tran_ID = $response->getTransactionID();
$avs_code = $response->getAVSCode();
$cvv2 = $response->getCVV2Code();
$amt_obj = $response->getAmount();
$amt = $amt_obj->_value;
$currency_cd = $amt_obj->_attributeValues['currencyID'];
$amt_display = $currency_cd.' '.$amt;

?>

<html>
<head>
<title>PayPal PHP SDK - DoDirectPayment API</title>
<link href="pages/sdk.css" rel="stylesheet" type="text/css"/>
</head>

<body alink=#0000FF vlink=#0000FF>


<br>
<center>
<font size=2 color=black face=Verdana><b>Do Direct Payment</b></font>
<br><br>

<b>Thank you for your payment!</b><br><br>
<table width=400>
	<tr>
		<td>Transaction ID:</td>
		<td><?php echo $tran_ID; ?></td>
	</tr>
	<tr>
		<td>Amount:</td>
		<td><?php echo $amt_display;?></td>
	</tr>
	<tr>
		<td>AVS:</td>
		<td><?php echo $avs_code;?></td>
	</tr>
	<tr>
		<td>CVV2:</td>
		<td><?php echo $cvv2;?></td>
	</tr>
</table>

</center>
<a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>