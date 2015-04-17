<html>
<head>
	<title>PayPal PHP SDK - DoAuthorization API</title>
	<link href="pages/sdk.css" rel="stylesheet" type="text/css" />
</head>
<body alink=#0000FF vlink=#0000FF>
	<br>
	<center>
		<font size=2 color=black face=Verdana><b>Do Authorization</b></font>
		<br><br>

		<b>Authorization Response Details!</b><br><br>
		<table width=400>
			<tr>
				<td>Authorization ID:</td>
				<td><?php echo $response->getTransactionID()?></td>
			</tr>
			<tr>
				<td>Amount:</td>
				<td><?php echo ($response->getAmount()->_attributeValues['currencyID'].' '.$response->getAmount()->_value)?></td>
			</tr>
			<tr>
				<td>Paymemt Status:</td>
				<td><?php echo $response->getAuthorizationInfo()->getPaymentStatus()?></td>
			</tr>
			<tr>
				<td>Pending Reason:</td>
				<td><?php echo $response->getAuthorizationInfo()->getPendingReason()?></td>
			</tr>
			<tr>
				<td>Protection Eligibility:</td>
				<td><?php echo $response->getAuthorizationInfo()->getProtectionEligibility()?></td>
			</tr>
		</table>

<?php
	if ($response->getAck() == ACK_SUCCESS_WITH_WARNING) {
?>
		<br><b>Warning!</b><br>
		<table>
<?php
	$errors = $response->getErrors();
	for ($i = 0; $i < count($errors); $i++) {
?>
			<tr>
				<td>Error Number:</td>
				<td><?php echo $errors[$i]->getErrorCode()?></td>
			</tr>
			<tr>
				<td>Short Message:</td>
				<td><?php echo $errors[$i]->getShortMessage()?></td>
			</tr>
			<tr>
				<td>Long Message:</td>
				<td><?php echo $errors[$i]->getLongMessage()?></td>
			</tr>
<?php
	}
}
?>
		</table>
	</center>
	<a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>