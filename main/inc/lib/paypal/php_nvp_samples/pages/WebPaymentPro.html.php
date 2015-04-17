
<?php
require_once 'lib/constants.inc.php';
?>

<html>
<head>
<script language="JavaScript">
	function onClick(type){
		switch(type)
        {
			case 'cert':
				document.LoginForm.apiUsername.disabled = false;
				document.LoginForm.apiPassword.disabled = false;
				document.LoginForm.signature.disabled = true;
				document.LoginForm.certFile.disabled = false;
				//document.LoginForm.privateKeyPassword.disabled = false;
				document.LoginForm.subjectEmail.disabled = false;
				document.LoginForm.authSignature.disabled = true;
				document.LoginForm.authToken.disabled = true;
				document.LoginForm.authTimesStamp.disabled = true;
				break;
			case 'tokens':
				document.LoginForm.apiUsername.disabled = false;
				document.LoginForm.apiPassword.disabled = false;
				document.LoginForm.signature.disabled = false;
				document.LoginForm.certFile.disabled = true;
				//document.LoginForm.privateKeyPassword.disabled = true;
				document.LoginForm.subjectEmail.disabled = false;
				document.LoginForm.authSignature.disabled = true;
				document.LoginForm.authToken.disabled = true;
				document.LoginForm.authTimesStamp.disabled = true;
				break;
			case 'unipay':
				document.LoginForm.apiUsername.disabled = true;
				document.LoginForm.apiPassword.disabled = true;
				document.LoginForm.signature.disabled = true;
				document.LoginForm.certFile.disabled = true;
				//document.LoginForm.privateKeyPassword.disabled = true;
				document.LoginForm.subjectEmail.disabled = false;
				document.LoginForm.authSignature.disabled = true;
				document.LoginForm.authToken.disabled = true;
				document.LoginForm.authTimesStamp.disabled = true;
				break;	
			 case 'permission':	
			    	document.LoginForm.apiUsername.disabled = true;
			        document.LoginForm.apiPassword.disabled = true;
					document.LoginForm.signature.disabled = true;
					document.LoginForm.certFile.disabled = false;
					document.LoginForm.subjectEmail.disabled = true;
					document.LoginForm.authSignature.disabled = false;
					document.LoginForm.authToken.disabled = false;
					document.LoginForm.authTimesStamp.disabled = false;
					break;		
					
				
        }
	}
</script>

	<title>PayPal PHP SDK - API Credentials</title>
	<link href="pages/sdk.css" rel="stylesheet" type="text/css"/>

</head>
<body onload="onClick('tokens');" alink=#0000FF vlink=#0000FF>
<br>
<center>
<font size=3 color=black face=Verdana><b>Use The Default Sandbox API Profile Or Enter Your Own Profile</b></font>
<br><br>
<b><font color="FF0000" size=3 face=Verdana>NOTE: Production code should NEVER expose API credentials in any way! They must be <br> managed securely in your application.</font></b>
<br><br>
<span id=normal>
<font size=3 color=black face=Verdana>
To generate a Sandbox API Certificate, follow these steps: <a href="https://www.paypal.com/IntegrationCenter/ic_certificate.html#step1" target="_blank">API Certificate</a> </span > </b>
</font>
<br><br>

<form  action="WebPaymentPro.php" method="post" enctype="multipart/form-data" name="LoginForm">
<table width="700">
	<tr>
		<td align="right"><span id=normalBold>API Username:</span><br/><span id=smaller>(ex: my_account_api1.paypal.com)</span></td>
		<td>sdk-seller_api1.sdk.com</td>
	</tr>
	<tr>
		<td align="right"><span id=normalBold><b>API Password:</b></span></td>
		<td>12345678</td>
	</tr>
	<tr>
		<td align="right"><span id=normalBold>Encrypted API Certificate:</span><br><span id=smaller>cert_key.pem format</span></td>
		<td>sdk-seller_cert_key_pem.txt</td>
	</tr>
	<tr>
		<td align="right"><span id=normalBold><b>Environment:</b></span></td>
		<td><?php  echo ENVIRONMENT;?></td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td align=left>
		   <input type="hidden" name="environment" value="<?php echo ENVIRONMENT;?>">
			<input type="hidden" name="submitted" value="1">
			<input type="submit" value="Use default account" name="DefaultButton">
		</td>
	</tr>
	<tr>
		<td colspan=3 align=center><br><b>Or enter your own profile...</b></td>
	</tr>
	<tr>
		<td align="right"><span id=normalBold><b>API Credentials:</b></span>
		<td>
			<input type="radio" name="api-type" value="cert" onclick="onClick('cert');">Client Side SSL Certificate<br>
			<input type="radio" name="api-type" value="tokens" checked onclick="onClick('tokens');">3 Tokens Authentication<br>
			<input type="radio" name="api-type" value="unipay" onclick="onClick('unipay');">First Party Email (UniPay)<br>
			<input type="radio" name="api-type" value="permission" onclick="onClick('permission');">Third Party Authorization<br>
			
			
		</td>
	</tr>
	<tr>
		<td align="right"><span id=normalBold><b>API Username:</span><br><span id=smaller>(ex: my_account_api1.paypal.com)</span></td>
		<td><input type="text" name="apiUsername" value=""><b> Not your PayPal Email Address!</b></td>
	</tr>
	<tr>
		<td align="right"><span id=normalBold><b>API Password:</span></td>
		<td><input type="text" name="apiPassword" value=""></td>
	</tr>
	<tr>
		<td align="right"><span id=normalBold>Signature:</span></td>
		<td><input type="text" name="signature" value=""></td>
	</tr>
	<tr>
		<td align="right"><span id=normalBold>Encrypted API Certificate:</span><br><span id=smaller>cert_key.pem format</span></td>
		<td><input type="file" name="certFile" value="" disabled></td>
	</tr>
	<tr>
		<td align="right"><span id=normalBold>Subject:</span></td>
		<td><input type="text" name="subjectEmail" value=""></td>
	</tr>
<tr>
			<td align="right"><font size=2 color=black face=Verdana><b>Access Token:</b></font><br><font size=1 color=black face=Verdana></font></td>
			<td><input type="text" name="authToken" value=""></td>
	</tr>
	<tr>
			<td align="right"><font size=2 color=black face=Verdana><b>Authorization Signature:</b></font><br><font size=1 color=black face=Verdana></font></td>
			<td><input type="text" name="authSignature" value=""></td>
	</tr>
	
	<tr>
			<td align="right"><font size=2 color=black face=Verdana><b>Authorization Timestamp:</b></font><br><font size=1 color=black face=Verdana></font></td>
			<td><input type="text" name="authTimesStamp" value=""></td>
	</tr>
	
	<tr>
		<td align="right"><span id=normalBold><b>Environment:</span></td>
		<td><?php echo ENVIRONMENT;?></td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td align=left>
			<input type="hidden" name="environment" value="<?php echo ENVIRONMENT;?>">
			<input type="hidden" name="submitted" value="1">
			<input type="submit" value="Use my account" name="custom">
		</td>
	</tr>
</table>
</form>
</body>
</html>