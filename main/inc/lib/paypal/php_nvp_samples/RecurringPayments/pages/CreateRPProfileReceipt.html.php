<?php

$details = $response->CreateRecurringPaymentsProfileResponseDetails;

?>

<html>
<head>
<title>PayPal PHP SDK - CreateRecurringPaymentsProfile API</title>
<link href="../pages/sdk.css" rel="stylesheet" type="text/css"/>
</head>

<body alink=#0000FF vlink=#0000FF>


<br>
<center>
<font size=2 color=black face=Verdana><b>Create Recurring Payments Profile</b></font>
<br><br>
<table width=400>
	<?php 
    	foreach($details as $key => $value) {
    		
    		if($key[0] != '_' && $value != null)
		   		echo "<tr><td>$key:</td><td>$value</td>";

	   		if($key == 'ProfileID')
	   			$profileID = $value;
		   		
    	}
    	foreach($response as $key => $value) {
    		
    		if($key[0] != '_' && $value != null && $key != 'CreateRecurringPaymentsProfileResponseDetails')
		   		echo "<tr><td>$key:</td><td>$value</td>";
    	}		
    ?>
    <tr>
    	<td>
    		<a id="GetRPProfileDetailsLink" href="GetRPProfileDetails.html?profileID=<?php echo $profileID ;?>">Get Recurring Payments Details</a>
    	</td>
    </tr>
</table>

</center>
<a id="CallsLink" href="RecurringPayments.php">Recurring Payments Home</a>
</body>
</html>