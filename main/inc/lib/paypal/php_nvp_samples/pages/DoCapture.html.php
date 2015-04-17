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

<html>
<head>
<title>PayPal PHP SDK - DoCapture API</title>
<link href="pages/sdk.css" rel="stylesheet" type="text/css" />
</head>
<body alink=#0000FF vlink=#0000FF>
<br>
<center>
<font size=2 color=black face=Verdana><b>DoCapture</b></font>
<br><br>

<form method="post" action="CaptureReceipt.php" name="DoCaptureForm">
<table width=500>
	<tr>
		<td align=right><br>Authorization ID:</td>
		<td align=left><br><input type="text" name="authorization_id" value=<?php echo $authorization_id?>></td>
		<td><b>(Required)</b></td>
	</tr>
	<tr>
		<td align=right>Complete Code Type:</td>
		<td align=left>
			<select name=CompleteCodeType>
				<option value="Complete">Complete</option>
				<option value="NotComplete">NotComplete</option>
			</select>
		</td>
	</tr>
	<tr>
		<td align=right>Amount:</td>
		<td align=left>
			<input type="text" name="amount" value=<?php echo $amount?>>
			<select name=currency>
<?php
   $cur_list = array('USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD');
   for($s=0; $s < sizeof($cur_list); $s++) {
      $selected = (!strcmp($currency_cd, $cur_list[$s])) ? 'selected' : '';
?>
			<option  <?php echo $selected?>><?php echo $cur_list[$s]?></option>

<?php } ?>
			</select>
		</td>
		<td><b>(Required)</b></td>
	</tr>
	<tr>
		<td align=right>Invoice ID:</td>
		<td align=left><input type="text" name="invoice_id"></td>
	</tr>
	<tr>
		<td align=right>Note:</td>
		<td align=left><textarea name="note" cols=30 rows=4></textarea></td>
	</tr>
	<tr>
		<td/>
		<td align=left><br><input type="submit" value="Submit"></td>
	</tr>
</table>
</form>
</center>
<a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>