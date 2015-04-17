<?php
// resetting the course id
$cidReset = true;

// name of the language file that needs to be included
$language_file = array ('registration','admin');


// including the global Dokeos file
require_once dirname( __FILE__ ) . '/../inc/global.inc.php';

// including additional libraries
require_once(api_get_path(LIBRARY_PATH).'sessionmanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'language.lib.php');
$libpath = api_get_path(LIBRARY_PATH);
require_once  $libpath.'usermanager.lib.php';

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Loading Paypal 
require_once api_get_path(SYS_PATH) . 'main/inc/lib/paypal/PaypalModel.php';

if (isset($_GET['cat_id'])) {
	$_SESSION['cat_id'] = intval($_GET['cat_id']);
}

$cat_id = intval($_SESSION['cat_id']);
$cat = SessionManager::get_session_category($cat_id);
$productId = intval ( $_REQUEST['id'] , 10 );

$product = SessionManager::get_session_category( $productId );
$userInfo = $_SESSION['user_info'];




if( isset( $_SESSION['_user'] ))
{
    if(is_null($_SESSION['user_info']))
    {
        $_SESSION['user_info'] =  UserManager::get_user_info_by_id ( $_SESSION['_user']['user_id'] );         
        unset( $temp );
    }
        
    $userInfo = UserManager::get_user_info_by_id ( $_SESSION['_user']['user_id'] );
    $userInfo['email'] = $_SESSION['_user']['mail'];
        
    $userInfo['country'] = $userInfo ['country_code'];
        
    if ( isset( $userInfo['extra']) )
    {
        $userInfo['extra_street'] = $userInfo['extra']['street']; 
        $userInfo['extra_city'] = $userInfo['extra']['city'];
        $userInfo['extra_street'] = $userInfo['extra']['addressline2'];
        $userInfo['extra_zipcode'] = $userInfo['extra']['zipcode'];
    }
}

//display the header
Display::display_header(get_lang('TrainingCategory'));


?>

<div id="content">

<h3 style="text-align:  center; width: 100%;">Payment une carte de cr&eacute;dit</h3>

<form method="POST" action="process_payment_cc_2.php?id=<?php echo $productId; ?>" name="DoDirectPaymentForm">
<input type="hidden" name="paymentType" value="Sale" />
<table style="width: 600px;">
	<tr>
		<td><?php echo get_lang('FirstName'); ?>:</td>
		<td><input type="text" size="30" maxlength="32" name="firstName" value="<?php echo $userInfo['firstname']; ?>"></td>
	</tr>
	<tr>
		<td><?php echo get_lang('LastName'); ?>:</td>
		<td><input type="text" size="30" maxlength="32" name="lastName" value="<?php echo $userInfo['lastname']; ?>"></td>
	</tr>
	<tr>
		<td><?php echo get_lang('EmailAddress'); ?>:</td>
		<td><input style="background-color: #ccc;" type="text" name="email" value="<?php echo $userInfo['email']; ?>" readonly="readonly"></td>
	</tr>
	<tr>
		<td><?php echo get_lang('ChooseCreditCard'); ?>:</td>
		<td>
			<select name="creditCardType">
				<option value="Visa" selected="selected">Visa</option>
				<option value="MasterCard">MasterCard</option>
				<option value="Discover">Discover</option>
				<option value="Amex">American Express</option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?php echo get_lang('CreditCardNumber'); ?>:</td>
		<td><input type="text" size="19" maxlength="19" name="creditCardNumber"></td>
	</tr>
	<tr>
		<td><?php echo get_lang('CreditCardInstallmentsDates'); ?>:</td>
		<td><p>
			<select name="expDateMonth">
				<option value="1">01</option>
				<option value="2">02</option>
				<option value="3">03</option>
				<option value="4">04</option>
				<option value="5">05</option>
				<option value="6">06</option>
				<option value="7">07</option>
				<option value="8">08</option>
				<option value="9">09</option>
				<option value="10">10</option>
				<option value="11">11</option>
				<option value="12">12</option>
			</select>
			<select name="expDateYear">				
				<option value="2009">2009</option>
				<option value="2010">2010</option>
				<option value="2011">2011</option>
				<option value="2012" selected="selected">2012</option>
				<option value="2013">2013</option>
				<option value="2014">2014</option>
				<option value="2015">2015</option>
				<option value="2016">2016</option>
                                <option value="2017">2017</option>
			</select>
		</p></td>
	</tr>
	<tr>
		<td><?php echo get_lang('CardVerificationNumber'); ?>:</td>
		<td><input type="text" size="3" maxlength="4" name="cvv2Number" value=""></td>
	</tr>
	<tr>
		<td><br><b><?php echo get_lang('BillingAddress');?>:</b></td>
	</tr>
	<tr>
		<td><?php echo get_lang('Street'); ?> 1:</td>
		<td><input type="text" size="25" maxlength="100" name="address1" value="<?php echo $userInfo['extra_street']; ?>"></td>
	</tr>
	<tr>
		<td><?php echo get_lang('AdditionalStreet'); ?>:</td>
		<td><input type="text"  size="25" maxlength="100" name="address2" value="">(<?php echo get_lang('Optional');?>)</td>
	</tr>
	<tr>
		<td><?php echo get_lang('City'); ?>:</td>
		<td><input type="text" size="25" maxlength="40" name="city" value="<?php echo $userInfo['extra_city']; ?>"></td>
	</tr>
	<tr>
		<td><?php echo get_lang('Zipcode'); ?>:</td>
		<td><input type="text" size="10" maxlength="10" name="zip" value="<?php echo $userInfo['extra_zipcode']; ?>">(5 or 9 digits)</td>
	</tr>
	<tr>
		<td><?php echo get_lang('Country'); ?>:</td>
		<td><?php 
$countries = LanguageManager::get_countries(null,'iso');
$cboCountries = '<select name="cboCountry">'.PHP_EOL;
foreach( $countries as $countryK => $country )
{
$cboCountries .= '<option value="'.	$countryK.'"';


if ( $userInfo['country'] == $countryK)
{
	$cboCountries .= ' selected="selected" ';
}
$cboCountries .= '>' . $country . '</option>'."\n";
}
echo $cboCountries .= '</select>';
?></td>
	</tr>
	<tr>
		<td><br><?php echo get_lang('Amount'); ?>:</td>
		<td><br><input style="background-color: #ccc;" type="text" size="4" maxlength="7" name="amount" value="<?php echo $product['cost']; ?>" readonly="readonly"> USD</td>
	</tr>
	<tr>
		<td/>
		<td><b>(DoDirectPayment only supports USD at this time)</b></td>
	</tr>
	<tr>
		<td/>
		<td><input type="Submit" value="<?php echo get_lang('Ok');?>"></td>
	</tr>
</table>
</form>
</div>

<?php echo Display::display_footer(); 