<?php
   // PHP code
   
  
?>

<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <title>PayPal SDK - GetTransactionDetails API</title>
    <link href="pages/sdk.css" rel="stylesheet" type="text/css"/>
</head>
    
<body>

    <center>
    <form action="TransactionDetails.php" method="get" name="GetTransactionDetailsForm">
        <span id=apiheader>GetTransactionDetails</span>
        <br><br>
        <table class="api">
            <tr>
                <td class="header">
                    Transaction ID:
                    </td>
                <td>
                    <input type="text" name="transactionID" value="7J110007888511720"/>
                    (Required)</td>
            </tr>
            <tr>
                <td colspan="2">
                    <center>
                        <input type="submit" value="Submit"/></center>
                </td>
            </tr>
        </table>
    </form>
    </center>
    <br/><a id="CallsLink" class="home" href="Calls.html">Home</a>
</body>
</html>
