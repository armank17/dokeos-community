<?php
   $refund_tran_ID = $response->getRefundTransactionID();
   $gross_amt_obj = $response->getGrossRefundAmount();
   $gross_amt = $gross_amt_obj->_value;
   $currency_cd = $gross_amt_obj->_attributeValues['currencyID'];
?>

<html>
<head>
<title>PayPal PHP SDK - RefundTransaction API</title>
<link href="pages/sdk.css" rel="stylesheet" type="text/css"/>
</head> 
<body alink=#0000FF vlink=#0000FF>

   <!-- Debugging block -->
   <?php
   /*
   echo "<PRE>\n";
   echo "Response:<br>";
   print_r($response);
   echo "</PRE><BR>\n";
   */
   ?>

<br>
<center>
<font size=2 color=black face=Verdana><b>Refund Transaction</b></font>
<br><br>

<b>Transaction refunded!</b><br><br>

<table width=400>
	<tr>
		<td>Transaction ID:</td>
		<td><?php echo $refund_tran_ID?></td>
	</tr>
	<tr>
		<td>Gross Refund Amount:</td>
		<?php
      $display_amt = $currency_cd .' '.$gross_amt;
      ?>
      <td><?php echo $display_amt?></td>

	</tr>
</table>

</center>
<a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>