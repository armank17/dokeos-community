
<html xmlns='http://www.w3.org/1999/xhtml'>
	<head>
		<title>PayPal SDK - MassPay</title>
		<link href="sdk.css" rel="stylesheet" type="text/css">
	</head>
	<body>
	<form method="post" action="MassPayReceipt.php">
		<center>
			 <font face=Verdana> <b>MassPay </b> </font>

			<table class="api">
				
				<TR>
					<TD class="field" width="52">EmailSubject</TD>
					<TD ><INPUT id="EmailSubject" type="text" value="You have money!" name="emailSubject" runat="server"></TD>
					<TD class="field"></TD>
					<TD class="field"></TD>
					<TD class="field"></TD>
				</TR>
				
				<TR>
					<TD class="field" width="52">ReceiverType</TD>
					<TD ><INPUT id="ReceiverType" type="text" value="EmailAddress" name="receiverType" runat="server"></TD>
					<TD class="field"></TD>
					<TD class="field"></TD>
					<TD class="field"></TD>
				</TR>
				<TR>
					 <TD class="field" width="52">CurrencyCode</TD>
					 <TD>	<select name="currency">
							<option value="USD" selected>USD</option>
							<option value="GBP">GBP</option>
							<option value="EUR">EUR</option>
							<option value="JPY">JPY</option>
							<option value="CAD">CAD</option>
							<option value="AUD">AUD</option>
						    </select>
					</TD>
					
				</TR>
				<TR>
					<TD class="field" height="14" colSpan="5">
						<P align="center">Mass Pay Item Details</P>
					</TD>
				</TR>
				<tr>
					<td class="field" width="52">
						Payee</td>
					<td class="field">
						ReceiverEmail (Required):</td>
					<td class="field">
						Amount(Required):</td>
					<td class="field">
						UniqueID</td>
					<td class="field">
						Note</td>
				</tr>
				<tr>
					<td width="52">
						<P align="right">1</P>
					</td>
					<td>
						<input type="text" name="receiveremail[]" value="user@paypal.com">
					</td>
					<td>
						<input type="text" name="amount[]" size="5" maxlength="7" value="1.00">
						
					</td>
					<td>
						<input type="text" name="uniqueID[]"></td>
					<td>
						<input type="text" name="note[]">
					</td>
				</tr>
				<tr>
					<td width="52">
						<P align="right">2</P>
					</td>
					<td>
						<input type="text" name="receiveremail[]" value="user@paypal.com">
					</td>
					<td>
						<input type="text" name="amount[]" size="5" maxlength="7" value="1.00">
						
					</td>
					<td>
						<input type="text" name="uniqueID[]"></td>
					<td>
						<input type="text" name="note[]">
					</td>
				</tr>
				<tr>
					<td width="52">
						<P align="right">3</P>
					</td>
					<td>
						<input type="text" name="receiveremail[]" value="user@paypal.com">
					</td>
					<td>
						<input type="text" name="amount[]" size="5" maxlength="7" value="1.00">
						
					</td>
					<td>
						<input type="text" name="uniqueID[]"></td>
					<td>
						<input type="text" name="note[]">
					</td>
				</tr>
				<tr>
					<td class="field" width="52">
					</td>
					<td colspan="5">
						<input type="submit" value="Submit"></td>
				</tr>
			</table>
		</center>
		<a class="home" href="Calls.html">Home</a>
	</form>
	</body>
</html>
