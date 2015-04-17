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

require_once '../lib/constants.inc.php';
require_once '../SampleLogger.php';

$details = $response;

?>

<html>
<head>
<title>PayPal PHP SDK - 3DSecure DoDirectPayment API</title>
<link href="pages/sdk.css" rel="stylesheet" type="text/css"/>
</head>

<body alink=#0000FF vlink=#0000FF>


<br>
<center>
<font size=2 color=black face=Verdana><b>3DSecure Do Direct Payment</b></font>
<br><br>

<b>Thank you for your payment!</b><br><br>
<table width=400>
	<?php 
    	foreach($response as $key => $value) {
    		if(is_object($value)){
    			dumpObject($value);    			
    		}
    		else {
    			if($key[0] != '_' && $value != null) 
    				echo "<tr><td>$key:</td><td>$value</td>";	
    		}
    		
    	}
    	
    	function dumpObject($obj) {
    		foreach($obj as $key => $value) {
    			if(is_object($value)){
    				if(is_a($value, 'basicamounttype')) {
    					$currency = $value->_attributeValues; 
    					echo "<tr><td>$key:</td><td>$value->_value " .  $currency["currencyID"] . "</td>";
    				}
    				else {
    					dumpObject($value);
    				}
    			}
    			else {
    				if($key[0] != '_' && $value != null) 
    					echo "<tr><td>$key:</td><td>$value</td>";
    					
    			}		
    			
    		}	
    	}	
    ?>
</table>

</center>
<a id="CallsLink" href="../Calls.html">Home</a>
</body>
</html>