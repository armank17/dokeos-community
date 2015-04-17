<html>
<head>
<title>PayPal PHP SDK - GetRecurringPaymentsProfileDetails API</title>
<link href="../pages/sdk.css" rel="stylesheet" type="text/css"/>
</head>

<body alink=#0000FF vlink=#0000FF>


<br>
<center>
<font size=2 color=black face=Verdana><b>Get Recurring Payments Profile Details</b></font>
<br><br>
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
    			if($key != 'RegularRecurringPaymentsPeriod') {
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
    	}
    ?>
</table>

</center>
<a id="CallsLink" href="RecurringPayments.php">Recurring Payments Home</a>
</body>
</html>