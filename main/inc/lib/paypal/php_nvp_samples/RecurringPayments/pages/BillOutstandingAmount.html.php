<?php

$details = $response->BillOutstandingAmountResponseDetails;

?>
<html>
<head>
<title>PayPal PHP SDK - BillOutstandingAmount API</title>
<link href="../pages/sdk.css" rel="stylesheet" type="text/css"/>
</head>

<body alink=#0000FF vlink=#0000FF>


<br>
<center>
<font size=2 color=black face=Verdana><b>Bill Outstanding Amount</b></font>
<br><br>
<table width=400>
	<?php 
    	foreach($details as $key => $value) {
    		
    		if($key[0] != '_' && $value != null)
		   		echo "<tr><td>$key:</td><td>$value</td>";
    	}
    	foreach($response as $key => $value) {
    		
    		if($key[0] != '_' && $value != null && $key != 'BillOutstandingAmountResponseDetails')
		   		echo "<tr><td>$key:</td><td>$value</td>";
    	}		
    ?>
</table>

</center>
<a id="CallsLink" href="RecurringPayments.php">Recurring Payments Home</a>
</body>
</html>