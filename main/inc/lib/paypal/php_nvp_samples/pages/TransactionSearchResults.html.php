<html >
<head>
    <title>PayPal PHP SDK: Transaction Search Results</title>
    <link href="pages/sdk.css" rel="stylesheet" type="text/css" />
</head>

<body>

     <br>
	<center>
	<font size=3 color=black face=Verdana><b>Transaction Search Results</b></font>
	<br><br>

   <?php
$ptsr = $response->getPaymentTransactions();
if (!is_array($ptsr)) {
    $ptsr = array($ptsr);
}
$nrecs = sizeof($ptsr);
?>

    <!-- Check for any record  in the result array-->
     <?php  
     
     if (!($ptsr[0]==null))
     {
     ?>
     
    
    <table width=600>
    <tr>
            <td colspan="6" class="thinfield">
                 Results 1 - <?php echo $nrecs ?>
            </td>
        </tr>
        
       
        <tr>
            <td>
            </td>
            <td>
            <b>ID</b></td>
            <td>
            <b>Time</b></td>
            <td>
            <b>Status</b></td>
            <td>
            <b>Payer Name</b></td>
            <td>
            <b>Gross Amount</b></td>
        </tr>
        
        <!-- Sample data
        <tr>
            <td>
                1
            </td>
            <td>
                <a id="TransactionDetailsLinkExample" href="TransactionDetails.html">9HP400517M684113S </a>
            </td>
            <td>
                12/7/2005 9:57:58 AM</td>
            <td>
                Completed</td>
            <td>
            </td>
            <td>
                USD 0.01
            </td>
        </tr>
        -->
        
        <?php
        for ($n = 0; $n < $nrecs; $n++) {
           $tran_id = $ptsr[$n]->getTransactionID();
           $tran_ts = $ptsr[$n]->getTimestamp();
           $tran_status = $ptsr[$n]->getStatus();
           $tran_payer_name = $ptsr[$n]->getPayerDisplayName();
           $gross_amt_obj = $ptsr[$n]->getGrossAmount();
           $tran_amount = $gross_amt_obj->_value;
           
        ?>
        <tr>
            <td>&nbsp;</td> 
            <td>
            <a id="TransactionDetailsLink<?php echo $n ?>" href="TransactionDetails.php?transactionID=<?php echo$tran_id?>"><?php echo$tran_id?></a>
            </td>
            <td><?php echo $tran_ts ?></td>
            <td><?php echo $tran_status ?></td>
            <td><?php echo $tran_payer_name ?></td>
            <td><?php echo $tran_amount ?></td>
        </tr>
        
        <?php }
         }
        else  /*If no record found, Display "No Record Message" */
        {?>
        <tr>
		<td colspan="6" class="field">
			<br> <br> Your search did not match any transactions! 
		</td>
	</tr>
        <?php } ?>
        
    </table>
    <br><br>
    <a id="CallsLink" href="Calls.html">Home</a>
</body>
</html>
