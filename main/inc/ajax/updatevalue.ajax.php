<?php
require_once '../global.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'usermanager.lib.php';
require_once(api_get_path(LIBRARY_PATH) . 'sessionmanager.lib.php');
require_once(api_get_path(LIBRARY_PATH) . 'language.lib.php');
require_once api_get_path(SYS_PATH) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';
require_once api_get_path(SYS_PATH) . 'main/core/controller/shopping_cart/CatalogueController.php';

$user_id = $_SESSION['_user']['user_id'];
$first_name = $_REQUEST['first_name'];
$last_name = $_REQUEST['last_name'];
$email = $_REQUEST['email'];
$phone = $_REQUEST['phone'];
$country = $_REQUEST['country'];
$custom = $_REQUEST['custom'];
$address1 = $_REQUEST['address1'];
$address2 = $_REQUEST['address2'];
$city = $_REQUEST['city'];
$zip = $_REQUEST['zip'];
$tva = $_REQUEST['tva'];
$organization = $_REQUEST['organization'];

$_SESSION['student_info']['firstname'] = $first_name;
$_SESSION['student_info']['lastname'] = $last_name;
$_SESSION['student_info']['country'] = $country;

$array1 = array(
    'extra_street' => $address1,
    'extra_addressline2' => $address2,
    'phone' => $phone,
    'extra_city' => $city,
    'extra_zip' => $zip,
    'extra_organization' => $organization,
    'extra_tva_id' => $tva
);
$_SESSION['student_info'] = array_merge($array1, $_SESSION['student_info']);
$objShoppingCartController = new ShoppingCartController();
$stepNumber = 4;
$objShoppingCartController->checkStep($stepNumber, $_SESSION);
$currency = Currency::create()->getCurrencyByIsoCode(api_get_setting('e_commerce_catalog_currency'));
global $charset;

//get language
$rs = Database::query("SELECT l.english_name AS language FROM " . Database::get_main_table(TABLE_MAIN_LANGUAGE) . " l INNER JOIN " . Database::get_main_table(TABLE_MAIN_COUNTRY) . " c ON l.isocode=c.iso WHERE UPPER(c.iso) = '" . $country . "'");
$row = Database::fetch_array($rs);
$language = $row['language'];
if ($language == null || $language == '') {
    $language = 'english';
}
//verify if email is already
if ($_REQUEST['uev'] == 'new') {
    $rs = UserManager::get_user_info_by_email($email);
    $url = '';
    if ($first_name != '') {
        $url .= '&ufn=' . $first_name;
    } if ($last_name != '') {
        $url .= '&uln=' . $last_name;
    }
    if ($email != '') {
        $url .= '&uemail=' . $email;
    } if ($phone != '') {
        $url .= '&uph=' . $phone;
    }
    if ($country != '') {
        $url .= '&uct=' . $country;
    } if ($address1 != '') {
        $url .= '&ustr=' . $address1;
    }
    if ($address2 != '') {
        $url .= '&ustr2=' . $address2;
    } if ($city != '') {
        $url .= '&ucity=' . $city;
    }
    if ($zip != '') {
        $url .= '&uzip=' . $zip;
    } if ($organization != '') {
        $url .= '&uorg=' . $organization;
    }
    if ($tva != '') {
        $url .= '&utva=' . $tva;
    }
    if (count($rs) < 2) {//email is available
        $exist = '?uis=no';
        $url = $exist . $url;
        if ($first_name == '' || $last_name == '' || $email == '' || $organization == '' || $address1 == '' || $zip == '' || $city == '') {
            header('Location: /main/payment/checkout_4_payment_data.php' . $url . '');
            exit;
        }
    } else {//User exist
        $exist = '?uis=yes';
        $url = $exist . $url;
        header('Location: /main/payment/checkout_4_payment_data.php' . $url . '');
        exit;
    }
} else {
    $url = '?ufn=' . $first_name . '&uln=' . $last_name . '&uemail=' . $email . '&uph=' . $phone . '&uct=' . $country . '&uciv=' . $custom;
}
$url .= '&ulang=' . $language;
?>
<html>
    <body onload="sendForm()">
        <script language="JavaScript">
        function sendForm() {
            document.form.submit();
        }
        </script>
        <div id="content">
			<?php
			$url_f = '';
			if($_SESSION['shopping_cart']['chr_type']=='0'){$url_f= api_get_path(WEB_PATH) . 'main/payment/process_payment_validation.php?uid=';}else{$url_f = PAYPAL_URL;}
                        $url_f.='&ufn=' . $first_name . '&uln=' . $last_name . '&uemail=' . $email . '&uph=' . $phone . '&uct=' . $country . '&uciv=' . $custom;
			?>
            <div id="paypal-form-wrap" style="margin-top: 20px; clear:both">
                <form action="<?php echo $url_f; ?>" method="post" id="form" name="form">
                    <input type="hidden" name="cmd" value="_cart">
                    <input type="hidden" name="charset" value="<?php echo $charset; ?>">
                    <input type="hidden" name="upload" value="1">
                    <input type="hidden" name="business" value="<?php echo api_get_payment_setting('email'); ?>">
                    <input type="hidden" name="USER" value="<?php echo API_USERNAME; ?>">
                    <input type="hidden" name="PWD" value="<?php echo API_PASSWORD; ?>">
                    <input type="hidden" name="SIGNATURE " value="<?php echo API_SIGNATURE; ?>">
                    <input type="hidden" name="currency_code" value="<?php echo $currency['code'] ?>">
                    <input type="hidden" name="return" value="<?php echo api_get_path(WEB_PATH) . 'main/payment/process_payment_validation.php' . $url ?>">
                    <input type="hidden" name="custom" value="" />
                    <input type="hidden" name="cancel_return" value="<?php echo api_get_path(WEB_PATH) . 'main/payment/checkout_3_registration.php?next=3' ?>">
                    <input type="hidden" class="user_id" name="user_id" value="<?php echo $user_id; ?>"/>
                    <input type="hidden" name="rm" value="2"/>
                    <input type="hidden" name="address_override" value="0"/>
                    <input type="hidden" name="tax_cart" value="<?php echo number_format($_SESSION['shopping_cart']['total_tax_amount'], 2, '.', ''); ?>"/>
                    <?php
                    $items = $_SESSION['shopping_cart']['items'];
                    $items_html = '';
                    $count = 1;
                    foreach ($items as $item) {
                        $items_html .= '
                <input type="hidden" name="item_number_' . $count . '" value="' . $item['code'] . '"> 
                <input type="hidden" name="item_name_' . $count . '" value="' . $item['name'] . '"> 
                <input type="hidden" name="amount_' . $count . '" value="' . number_format(api_floatval($item['price']), 2, '.', '') . '">
                ';
                        $count++;
                    }
                    echo $items_html;
                    ?>
                </form>
            </div>
        </div>
    </body>
</html>
<?php
//}
?>
