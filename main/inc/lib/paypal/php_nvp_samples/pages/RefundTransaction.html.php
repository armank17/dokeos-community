<?php
   
   $transaction_id = $_REQUEST['transaction_id'];
   if(!isset($transaction_id))
      $transaction_id='';
   $amount = $_REQUEST['amount'];
   if(!isset($amount))
      $amount = '0.00';
   $currency = $_REQUEST['currency'];
   if(!isset($currency))
      $currency = 'USD'; 
?>

<html>
<head>
<title>PayPal PHP SDK - RefundTransaction API</title>
<link href="pages/sdk.css" rel="stylesheet" type="text/css"/>
</head>

<body alink=#0000FF vlink=#0000FF>
<br>
<center>
<font size=2 color=black face=Verdana><b>RefundTransaction</b></font>
<br><br>

<form method="post" action="RefundReceipt.php" name="RefundTransactionForm">
<table width=500>
	<tr>
		<td align=right>Transaction ID:</td>
		<td align=left><input type="text" name="transactionID" value=<?php echo $transaction_id?>></td>
		<td><b>(Required)</b></td>
	</tr>
	<tr>
		<td align=right>Refund Type:</td>
		<td align=left>
			<select name=refundType>
				<option value="Full">Full</option>
				<option value="Partial">Partial</option>
			</select>
		</td>
	</tr>
	<tr>
		<td align=right>Amount:</td>
		<td align=left>
			<p>
				<!-- <input type="text" name="amount" value=0.00> -->
				<input type="text" name="amount" value=<?php echo $amount?>>
				<?php
				// Add currency hidden field 
				if(isset($currency) && strlen($currency > 0)) {
				?>
				<input type=hidden name=currency value="<?php echo $currency?>">
				<?php } ?>
				
				<select name="currency">
					<option value=USD>USD</option>
					<option value=GBP>GBP</option>
					<option value=JPY>JPY</option>
					<option value=JPY>JPY</option>
					<option value=CAD>CAD</option>
					<option value=AUD>AUD</option>
				</select>

			</p>
		</td>
	</tr>
	<tr>
		<td/>
		<td><b>(Required if Partial Refund)</b></td>
	</tr>
	<tr>
		<td align=right>Memo:</td>
		<td align=left><textarea name="memo" cols=30 rows=4></textarea></td>
	</tr>
	<tr>
		<td align=right></td>
		<td align=left><br><input type="submit" value="Submit"></td>
	</tr>
</table>
</form>
</center>
<br>
<a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>
