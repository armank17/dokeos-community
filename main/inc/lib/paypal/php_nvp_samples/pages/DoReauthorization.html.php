<?php

   $authorization_id = $_REQUEST['authorization_id'];
   if(!isset($authorization_id))
      $authorization_id='';
   $amount = $_REQUEST['amount'];
   if(!isset($amount))
      $amount = '0.00';
   $currency_cd = $_REQUEST['currency'];
   if(!isset($currency_cd))
      $currency_cd = 'USD';
           
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title>PayPal SDK - DoReauthorization</title>
    <link href="sdk.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <center>
	<form method="post" action="ReauthorizationReceipt.php" name="DoDirectPaymentForm">
    <table class="api">
        <tr>
            <td colspan="2" class="header">
                DoReauthorization
            </td>
        </tr>
        <tr>
            <td class="field">
                AuthorizationID:</td>
            <td>
                <input type="text" name="authorizationID" />
                (Required)</td>
        </tr>
        <tr>
            <td class="field">
                Amount:</td>
            <td>
                <input type="text" name="amount" size="5" maxlength="7" />
                <select name="currency">
                <option value="USD">USD</option>
                <option value="GBP">GBP</option>
                <option value="EUR">EUR</option>
                <option value="JPY">JPY</option>
                <option value="CAD">CAD</option>
                <option value="AUD">AUD</option>
                </select>
                (Required)</td>
        </tr>
        <tr>
            <td class="field">
            </td>
            <td>
                <input type="submit" value="Submit" />
            </td>
        </tr>
    </table>
	</form>
    </center>
    <a class="home" href="Calls.html">Home</a>
</body>
</html>
