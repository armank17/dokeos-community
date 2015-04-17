<?php
   
   require_once 'PayPal/Type/GetBalanceResponseType.php';
   require_once 'PayPal/Type/BasicAmountType.php';
   require_once 'PayPal/Type/AbstractResponseType.php';

   // Process response
   $balance_details = $response->getBalance();

   ?>

<html>
<head>
<title>PayPal PHP SDK - DoCapture API</title>
<link href="pages/sdk.css" rel="stylesheet" type="text/css" />
</head>
<body alink=#0000FF vlink=#0000FF>
<br>
<center>
<font size=2 color=black face=Verdana><b>Get Balance</b></font>

<table>
	<tr>
		<td><?php echo $balance_details->_value;?></td>
		<td><?php echo $balance_details->getattr("currencyID"); ?></td>
	</tr>
</table>
</center>
<a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>