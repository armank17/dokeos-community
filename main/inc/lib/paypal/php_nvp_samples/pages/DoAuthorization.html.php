<?php
	$order_id = $_REQUEST['order_id'];
	if(!isset($order_id)) {
		$order_id = '';
	}
	$amount = $_REQUEST['amount'];
	if(!isset($amount)) {
		$amount = '0.00';
	}
	$currency_cd = $_REQUEST['currency'];
	if(!isset($currency_cd)) {
		$currency_cd = 'USD';
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>PayPal SDK - DoAuthorization</title>
	<link href="sdk.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<center>
		<form method="post" action="AuthorizationReceipt.php" name="DoAuthorizationForm">
		<center>
<font size=2 color=black face=Verdana><b>DoAuthorization</b></font>
<br><br>
			</center>
			<table width=500>
			
				<tr>
					<td align=right><br>Order ID:</td>
					<td align=left><br><input type="text" name="order_id" value=<?php echo $order_id?>></td>
					<td><b>(Required)</b></td>
				</tr>
				<tr>
					<td align=right>Amount:</td>
					<td align=left>
						<input type="text" name="amount" value=<?php echo $amount?>>
						<select name=currency>
<?php
	$currencies = array('USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD');
	for($i = 0; $i < count($currencies); $i++) {
?>
							<option <?php echo (($currency_cd == $currencies[$i]) ? 'selected' : '')?>><?php echo $currencies[$i]?></option>
<?php
	}
?>
						</select>
					</td>
					<td><b>(Required)</b></td>
				</tr>
				<tr>
					<td/>
					<td align=left><br><input type="submit" value="Submit"></td>
				</tr>
			</table>
		</form>
	</center>
	<a class="home" href="Calls.html">Home</a>
</body>
</html>