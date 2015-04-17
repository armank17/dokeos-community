<html>
<head>
	<title>PayPal PHP SDK - DoReAuthorization API</title>
	<link href="pages/sdk.css" rel="stylesheet" type="text/css" />
</head>
<body alink=#0000FF vlink=#0000FF>
	<br>
	<center>
		<font size=2 color=black face=Verdana><b>Do ReAuthorization</b></font>
		<br><br>

		<b>ReAuthorization Succeeded!</b><br><br>
		<table width=400>
			<tr>
				<td>Authorization ID:</td>
				<td><?php echo $response->getAuthorizationID()?></td>
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
	</center>
	<a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>