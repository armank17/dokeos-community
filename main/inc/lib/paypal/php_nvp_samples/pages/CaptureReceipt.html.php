<?php

   require_once 'PayPal/Type/DoCaptureResponseDetailsType.php';
   require_once 'PayPal/Type/DoCaptureResponseType.php';

   // Process response
   $capture_details = $response->getDoCaptureResponseDetails();

   $authorization_id = $capture_details->getAuthorizationID();
   $payment_info = $capture_details->getPaymentInfo();
   $tran_ID = $payment_info->getTransactionID();
   $payment_status = $payment_info->getPaymentStatus();

   $gross_amt = $payment_info->getGrossAmount();
   $amt = $gross_amt->_value;
   $currency_cd = $gross_amt->_attributeValues['currencyID'];
   $amt_display = $currency_cd.' '.$amt;

?>

<html>
<head>
<title>PayPal PHP SDK - DoCapture API</title>
<link href="pages/sdk.css" rel="stylesheet" type="text/css" />
</head>
<body alink=#0000FF vlink=#0000FF>
<br>
<center>
<font size=2 color=black face=Verdana><b>Do Capture</b></font>
<br><br>

<b>Authorization captured!</b><br><br>
<table width=400>
	<tr>
		<td>Authorization ID:</td>
		<td><?php echo $authorization_id?></td>
	</tr>
	<tr>
		<td>Transaction ID:</td>
		<td><?php echo $tran_ID?></td>
	</tr>
	<tr>
		<td>Payment Status:</td>
		<td><?php echo $payment_status?></td>
	</tr>
	<tr>
		<td>Gross Amount:</td>
		<td><?php echo $amt_display?></td>
	</tr>
	<tr>
		<td>Paymemt Status:</td>
		<td><?php echo $payment_info->getPaymentStatus()?></td>
	</tr>
	<tr>
		<td>Pending Reason:</td>
		<td><?php echo $payment_info->getPendingReason()?></td>
	</tr>
	<tr>
		<td>Protection Eligibility:</td>
		<td><?php echo $payment_info->getProtectionEligibility()?></td>
	</tr>
</table>

</center>
<a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>