<?php

// resetting the course id
$cidReset = true;

// name of the language file that needs to be included
$language_file = array ('registration','admin');

require_once '../inc/global.inc.php';
$libpath = api_get_path(LIBRARY_PATH);
require_once  $libpath.'sessionmanager.lib.php';
require_once  $libpath.'usermanager.lib.php';
// Section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;

if (isset($_GET['cat_id'])) {
	$_SESSION['cat_id'] = intval($_GET['cat_id']);
}

$cat_id = intval($_SESSION['cat_id']);
$cat = SessionManager::get_session_category($cat_id);

// get destination back
if (isset($_GET['from'])) {
	$_SESSION['from'] = $_GET['from'];
}

if (isset($_GET['pay_type'])) {
	$pay_type = intval($_GET['pay_type']);
	// get cost by payment type
	$_SESSION['pay_type'] = $pay_type;
	$country_code = isset($_SESSION['payer_info'])?$_SESSION['payer_info']['country']:$_SESSION['user_info']['country'];
	if (!isset($_SESSION['user_info']['country'])) {
		$user_id      = api_get_user_id();
		$extra_field  = UserManager::get_extra_user_data($row_users['user_id']);
		$country_code = $extra_field['country'];
	}
	if (isset($cat['cost'])) {
		// TVA
		if (!empty($country_code)) {
			$cost = SessionManager::get_user_amount_pay_atos($cat['cost'], $country_code);
			if ($pay_type == 3) {
				$next_quota     = SessionManager::get_next_quota_install_to_pay($user_id, $cat_id);
				$install_cost   = SessionManager::get_cost_installment_quota($cat_id, $next_quota);
				$cost = SessionManager::get_user_amount_pay_atos($install_cost, $country_code);
			}
		}
	}
	$cost = !empty($cost)?($cost*100):'000';
}

//display the header
Display::display_header(get_lang('TrainingCategory'));

$from = 'register';
$href = api_get_path(WEB_CODE_PATH).'admin/payment_options.php?iden='.$_SESSION['iden'].'&wish='.$_SESSION['wish'].'&id='.$_SESSION['cat_id'].'&prev=4';

if (!isset($_SESSION['cat_id'])) {
	echo '<div class="actions">';
	echo '<center>'.get_lang('YourSessionOrderIsOver').'<br /><a href="'.api_get_path(WEB_PATH).'">'.get_lang('GoToCatalogue').'</a></center>';
	echo '</div>';
	// display the footer
	Display::display_footer();
	exit;
}

if (api_get_user_id()) {
	echo '<div class="actions">';
	echo '<a href="'.$href.'">'.Display::return_icon('pixel.gif', get_lang("Previous"), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Back').'</a>';
	echo '<a href="'.api_get_path(WEB_CODE_PATH).'payment/session_category_payments"">'.Display::return_icon('pixel.gif', get_lang("Catalogue"), array('class' => 'toolactionplaceholdericon toolactioncatalogue')).get_lang('Catalogue').'</a>';
	echo '<a href="'.api_get_path(WEB_CODE_PATH).'payment/installment_payment.php">'.Display::return_icon('pixel.gif', get_lang("InstallmentPaymentInfo"), array('class' => 'toolactionplaceholdericon toolactionother')).get_lang('InstallmentPaymentInfo').'</a>&nbsp;';
	echo '</div>';
} else {
	echo '<div class="actions">';
	echo '<a href="'.$href.'">'.Display::return_icon('pixel.gif', get_lang("Previous"), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Back').'</a>';
	echo '</div>';
}

print '<div id="content">';
print '<center><h3>'.get_lang('ChooseCreditCard').'</h3></center>';

$topic     = SessionManager::get_topic_info($cat['topic']);
$catalogue = SessionManager::get_catalogue_info($topic['catalogue_id']);

if ($pay_type == 1) {
	if (!empty($catalogue['cc_payment_message'])) {
		echo '<div class="messages-payment" style="margin-bottom:10px;padding:4px;">'.$catalogue['cc_payment_message'].'</div>';
	}
} else if ($pay_type == 3) {
	if(!empty($catalogue['installment_payment_message'])) {
		echo '<div class="messages-payment" style="margin-bottom:10px;padding:4px;">'.$catalogue['installment_payment_message'].'</div>';
	}
}

// Affectation des param�tres obligatoires
$parm="merchant_id=011223344551111";
$parm="$parm merchant_country=fr";
$parm="$parm amount=$cost";
$parm="$parm currency_code={$cat['currency']}";

$parm="$parm payment_means=CB,2,VISA,2,MASTERCARD,2";
$parm="$parm header_flag=yes"; // (yes/no)

$data = array();
if (isset($_SESSION['user_info'])) {
	$data['user_info'] = $_SESSION['user_info'];
}
if (isset($_SESSION['payer_info'])) {
	$data['payer_info'] = $_SESSION['payer_info'];
}
if (isset($_SESSION['cat_id'])) {
	$data['cat_id'] = $_SESSION['cat_id'];
}
if (isset($_SESSION['pay_type'])) {
	$data['pay_type'] = $_SESSION['pay_type'];
}
if (isset($_SESSION['selected_sessions'])) {
	$data['selected_sessions'] = $_SESSION['selected_sessions'];
}
if (isset($_SESSION['cours_rel_session'])) {
	$data['cours_rel_session'] = $_SESSION['cours_rel_session'];
}

$data = base64_encode(serialize($data));

$processPaymentUrl = 'process_payment_cc.php?id=' . $_REQUEST['id'];
$mc = base64_encode('MasterCard');
$visa = base64_encode('Visa');
$discover = base64_encode('Discover');
$americanexpress = base64_encode('Amex');

echo <<<EOF
<div class="banklogo">
	<ul>
		<li>
			<a href="{$processPaymentUrl}&cc={$mc} ">
				<img border="0" src="images/mastercard.png" alt="Mastercard" title="Mastercard">
			</a>
		</li>
		<li>
			<a href="{$processPaymentUrl}&cc={$visa}">
				<img border="0" src="images/visa.png" alt="Visa" title="Visa">
			</a>
		</li>
		<li>
			<a href="{$processPaymentUrl}&cc={$discover}">
			<img border="0" src="images/discover.png" alt="Discover" title="Discover">
			</a>
		</li>
		<li>
		<a href="{$processPaymentUrl}&cc={$americanexpress}">	
			<img border="0" src="images/americanex.png" alt="American Express" title="American Express">
		</a>
		</li>
	</ul>
</div>

EOF;

print '</div>';

// display the footer
Display::display_footer();