<?php
$authorization_id = $_REQUEST['authorization_id'];
if(!isset($authorization_id))
   $authorization_id = '';
?>

<html>
<head>
<title>PayPal PHP SDK - DoVoid API</title>
<link href="pages/sdk.css" rel="stylesheet" type="text/css" />
</head>
<body alink=#0000FF vlink=#0000FF>
<br>
<center>
<font size=2 color=black face=Verdana><b>DoVoid</b></font>
<br><br>

<form method="post" action="VoidReceipt.php" name="DoVoidForm">
<table width=500>
	<tr>
		<td align=right>Authorization ID:</td>
		<td align=left><input type="text" name="authorization_id" value=<?php echo $authorization_id?>></td>
		<td><b>(Required)</b></td>
	</tr>
	<tr>
		<td align=right>Note:</td>
		<td align=left><textarea name="note" cols=30 rows=4></textarea></td>
	</tr>
	<tr>
		<td align=right></td>
		<td align=left><br><input type="submit" value="Submit"></form></td>
	</tr>
</table>
</form>
</center>
<a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>