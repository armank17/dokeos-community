<?php

require_once 'PayPal/Type/GetExpressCheckoutDetailsResponseDetailsType.php';
require_once 'PayPal/Type/GetExpressCheckoutDetailsResponseType.php';

$resp_details = $response->getGetExpressCheckoutDetailsResponseDetails();
$logger->_log('GetExpressCheckoutDetails: '.print_r($resp_details, true));

$payer_info = $resp_details->getPayerInfo();
$payer_id = $payer_info->getPayerID();

$address = $payer_info->getAddress();
$street1 = $address->getStreet1();
$street2 = $address->getStreet2();
$city_name = $address->getCityName();
$state_province = $address->getStateOrProvince();
$postal_code = $address->getPostalCode();
$country_code = $address->getCountryName();
$paymentDetails = $resp_details->getPaymentDetails();//->getOrderTotal();
$OrderTotal = $paymentDetails->getOrderTotal(); 
$shipDiscount = $paymentDetails->getShippingDiscount();
$paymentAmount = $OrderTotal->_value + $shipDiscount->_value;
$order_total = $currencyCodeType.' '.$paymentAmount;
$userSelectedOptions = $resp_details->getUserSelectedOptions();
$shipCalculationMode = $userSelectedOptions->getShippingCalculationMode();
$shipName = $userSelectedOptions->getShippingOptionName();
$shipAmountType = $userSelectedOptions->getShippingOptionAmount();
$shipAmount = $currencyCodeType.' '.$shipAmountType->_value;  

$final_url = 'ECReceipt.php?token='.$token.'&payerID='.$payer_id.'&paymentAmount='.$paymentAmount.'&currencyCodeType='.$currencyCodeType.'&paymentType='.$paymentType;

?>


<html>
<head>
    <title>PayPal PHP SDK - ExpressCheckout API</title>
    <link href="pages/sdk.css" rel="stylesheet" type="text/css" />
</head>
<body>

<center>
<table width=400>

<tr>
   <td><b>Order Total:</b></td>
   <td><?php echo $order_total?></td>
</tr>

<tr>
   <td colspan="2"><b>Shipping Address:</b></td>
</tr>

<tr>
   <td>Street 1:</td>
   <td><?php echo $street1?></td>
</tr>

<tr>
   <td>Street 2:</td>
   <td><?php echo $street2?></td>
</tr>

<tr>
   <td>City:</td>
   <td><?php echo $city_name?></td>
</tr>

<tr>
   <td>State:</td>
   <td><?php echo $state_province?></td>
</tr>

<tr>
   <td>Postal code:</td>
   <td><?php echo $postal_code?></td>
</tr>

<tr>
   <td>Country:</td>
   <td><?php echo $country_code?></td>
</tr>
<tr>
	<td >
    	ShippingCalculationMode:</td>
    <td>
    	<?php echo $shipCalculationMode ?></td>
</tr>
<tr>
	<td >
    	ShippingOptionAmount:</td>
    <td><?php echo $shipAmount ?>
    </td>
</tr>
<tr>
	<td >
    	ShippingOptionName:</td>
	<td>
    	<?php echo $shipName ?></td>
</tr>

</table>

<!-- Link to ECReceipt.php -->
<a id="ECReceiptLink" href="<?php echo $final_url?>">Pay</a>

</center>

<br>
<b><a id="CallsLink" href="Calls.html">Home</a></b>

</body>
</html>