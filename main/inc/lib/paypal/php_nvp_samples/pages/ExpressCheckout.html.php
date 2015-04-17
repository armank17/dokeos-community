<?php
	require_once 'lib/constants.inc.php';

	$paymentType = $_GET['paymentType'];
?>


<html>
<head>
    <title>PayPal PHP SDK - ExpressCheckout API</title>
    <link href="pages/sdk.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <center>
	<form action="ReviewOrder.php" method="post" name="ExpressCheckoutForm">
	<input type=hidden name=paymentType value=<?php echo $paymentType?>>
	<span id=apiheader>SetExpressCheckout</span>
    <table class="api">
         <tr>
            <td colspan="2">
                <br />
                <center>
                You must be logged into <a href="<?php echo DEV_CENTRAL_URL?>" id="PayPalDeveloperCentralLink" target="_blank">Developer
                    Central</a><br />
                <br />
                </center>
            </td>
        </tr>
	<tr>
		<td align=right><br>Buyer's Email:</td>
		<td align=left><br><input type="text" name="buyersemail" value="postauth-hold@paypal.com"><b>(Required)</b></td>
	</tr>
	<tr>
        	<td colspan="2" align=right>
        		<table>
			        <tr>
								<td class="field">
									CDs:</td>
								<td>
									<input type="text" size="30" maxlength="32" name="L_NAME1" value="Path To Nirvana" /></td>
			
			
							<td class="field"> Amount:  </td>
							<td>
								<input type="text" name="L_AMT1" size="5" maxlength="32" value="39.00" /> </td>
			
								 <td class="field">
								Quantity:   </td>
							<td>
								 <input type="text" size="3" maxlength="32" name="L_QTY1" value="2" /> </td>
			
						</tr>
						 <tr>
								<td class="field">
									Books:</td>
								<td>
									<input type="text" size="30" maxlength="32" name="L_NAME0" value="Know Thyself" /> </td>
			
			
							<td class="field">
								Amount: <br /> </td>
							<td>
								<input type="text" name="L_AMT0" size="5" maxlength="32" value="9.00"  /> </td>
			
								 <td class="field">
								Quantity:   </td>
							   <td>  <input type="text" size="3" maxlength="32" name="L_QTY0" value="2"  /> </td>
			
						</tr>
						
			    </table>
					
        	</td>
        </tr>
        <tr>
            <td class="field" align=right>
                Currency:</td>
            <td>
                <select name="currencyCodeType">
                <option value="USD">USD</option>
                <option value="GBP">GBP</option>
                <option value="EUR">EUR</option>
                <option value="JPY">JPY</option>
                <option value="CAD">CAD</option>
                <option value="AUD">AUD</option>
                </select>
                (Required)</td>
        </tr>
        <tr>
        	<td align="right" class=header>
        		<br><br>
        		Ship To
        	</td>
        	<td></td>
        </tr>
        <tr>
        	<td class="field" align="right">
				 Name:</td>
			<td>
				<input type="text" size="30" maxlength="32" name="NAME" value="True Seeker" /></td>
		</tr>
		<tr>
			<td class="field" align="right">
				Street:</td>
			<td>
				<input type="text" size="30" maxlength="32" name="SHIPTOSTREET" value="111, Bliss Ave" /></td>
		</tr>
		<tr>
			<td class="field" align="right">
				City:</td>
			<td>
				<input type="text" size="30" maxlength="32" name="SHIPTOCITY" value="San Jose" /></td>
		</tr>
		<tr>
			<td class="field" align="right">
				State:</td>
			<td>
				<input type="text" size="30" maxlength="32" name="SHIPTOSTATE" value="CA" /></td>
		</tr>
		<tr>
			<td class="field" align="right">
				Country:</td>
			<td>
				<input type="text" size="30" maxlength="32" name="SHIPTOCOUNTRYCODE" value="US" /></td>
		</tr>
		<tr>
			<td class="field" align="right">
				Zip Code:</td>
			<td>
				<input type="text" size="30" maxlength="32" name="SHIPTOZIP" value="95128" /></td>
            </tr>
        <tr>
            <td>
                <br />
                <br />
                <input type="image" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" />
            </td>
            <td>
                <br />
                <br />
                Save time. Pay securely without sharing your financial information.
            </td>
        </tr>
    </table>
	</form>
    </center>
    <br />
    <a id="CallsLink" class="home" href="Calls.html">Home</a>
</body>
</html>
