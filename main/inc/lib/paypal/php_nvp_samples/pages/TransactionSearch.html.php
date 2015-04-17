<?php
/**
 * Default startDateStr to yesterday date and endtDateStr to today date in mm/dd/yyyy format
 */
if (isset($_POST['startDateStr'])) {
    $start_date_str = $_POST['startDateStr'];
} else {
   $yesterdayDate = time()-86400; 
   $start_date_str = date('m/d/Y', $yesterdayDate);
}
if (isset($_POST['endDateStr'])) {
    $end_date_str = $_POST['endDateStr'];
} else {
   $currentDate = time(); 
   $end_date_str = date('m/d/Y', $currentDate);
}
?>

<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <title>PayPal PHP SDK - TransactionSearch API</title>
    <link href="pages/sdk.css" rel="stylesheet" type="text/css"/>
</head>

<body>
    <center>
        <form action="TransactionSearchResults.php" method="post" name="TransactionSearchForm">
            <span id=apiheader>TransactionSearch</span>
            <br>
            <table class="api">
                <tr>
                    <td class="header">
                        StartDate:</td>
                    <td>
                        <input type="text" name="startDateStr" maxlength="10" size="10" value="<?php echo $start_date_str ?>" />
                    </td>
                    <td>
                        (Required)</td>
                </tr>
                <tr>
                <td></td>
                    <td>
                        MM/DD/YYYY
                    </td>
                </tr>
                <tr>
                    <td class="header">
                        EndDate:</td>
                    <td>
                        <input type="text" name="endDateStr" maxlength="10" size="10"  value="<?php echo $end_date_str ?>" />
                    </td>
                    <td>
                    </td>
                </tr>
                <tr>
                <td></td>
                    <td>
                        MM/DD/YYYY
                        </td>
                    <td>
                    </td>
                </tr>
                <tr>
                    <td class="header">
                        TransactionID:</td>
                    <td>
                        <input type="text" name="transactionID"/></td>
                </tr>
                <tr>
                    <td class="header">
                    </td>
                    <td>
                        <br/>
                        <input type="submit" value="Submit"/></td>
                </tr>
            </table>
        </form>
    </center>
    <br/>
    <a id="CallsLink" class="home" href="Calls.html">Home</a>
</body>
</html>
