<?php
   $authorization_id = $response->getAuthorizationID();
?>

<html>
<head>
<title>PayPal PHP SDK - DoVoid API</title>
<link href="pages/sdk.css" rel="stylesheet" type="text/css" />
</head> 
<body alink=#0000FF vlink=#0000FF>
<br>
<center>
<font size=2 color=black face=Verdana><b>Do Void</b></font>
<br><br>

<b>Authorization voided!</b><br><br>
<table width=400>
	<tr>
		<td>Authorization ID:</td>
		<td><?php echo $authorization_id?></td>
	</tr>
</table>

</center>
<a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>