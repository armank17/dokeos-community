<?php
// resetting the course id
$cidReset = true;
$language_file = array('registration', 'admin');
// setting the help
$help_content = 'platformadministrationsessionadd';
// including the global Dokeos file
require dirname(__FILE__) . '/../inc/global.inc.php';

// including additional libraries
require_once(api_get_path(LIBRARY_PATH) . 'sessionmanager.lib.php');
require_once(api_get_path(LIBRARY_PATH) . 'language.lib.php');
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH) . 'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';
require_once api_get_path(SYS_PATH) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';
require_once api_get_path(SYS_PATH) . 'main/core/controller/shopping_cart/CatalogueController.php';

$objShoppingCartController = new ShoppingCartController();
$stepNumber = 4;
$objShoppingCartController->checkStep($stepNumber, $_SESSION);
$this_section = SECTION_PLATFORM_ADMIN;
$payment_method = (isset($_REQUEST['txtPaymentType'])) ? $_REQUEST['txtPaymentType'] : $_SESSION['student_info']['payment_method'];
$_SESSION['student_info']['payment_method'] = $payment_method;

if ($_SESSION['_user']['user_id']) {
    if ($_REQUEST['keyword'] == '') {
        $userInfo['firstname'] = Database::escape_string($_REQUEST['ufn']);
        $userInfo['email'] = Database::escape_string($_REQUEST['uemail']);
        $userInfo['lastname'] = Database::escape_string($_REQUEST['uln']);
        $userInfo['phone'] = Database::escape_string($_REQUEST['uph']);
        $userInfo['extra_city'] = Database::escape_string($_REQUEST['ucity']);
        $userInfo['extra_organization'] = Database::escape_string($_REQUEST['uorg']);
        $userInfo['extra_zipcode'] = Database::escape_string($_REQUEST['uzip']);
        $userInfo['extra_street'] = Database::escape_string($_REQUEST['ustr']);
        $userInfo['extra_addressline2'] = Database::escape_string($_REQUEST['ustr2']);
        $userInfo['extra_tva_id'] = Database::escape_string($_REQUEST['utva']);
        if ($_REQUEST['uis'] == 'yes') {
            $userInfo['error'] = 'error';
            $autofocusEmail = 'autofocus=""';
        } elseif ($_REQUEST['uis'] == 'no') {
            $userInfo['error'] = '';
            if ($userInfo['firstname'] == '') {
                $autofocusName = 'autofocus=""';
            }
        }
        if (isset($_SESSION['student_info']['identification']) && $_SESSION['student_info']['identification'] == 0):
            $user_data_info = UserManager::get_user_info_by_id($_SESSION['_user']['user_id'], true);
            $_SESSION['student_info']['user_id'] = $user_data_info['user_id'];
            $_SESSION['student_info']['status'] = $user_data_info['status'];
            $_SESSION['student_info']['official_code'] = $user_data_info['official_code'];
            $_SESSION['student_info']['username'] = $user_data_info['username'];

            $user_id = $_SESSION['_user']['user_id'];
            $extra_data = UserManager::get_extra_user_data($user_id, true);

            foreach ($extra_data as $key => $value) {
                if (!isset($_SESSION['student_info'][$key]) && empty($_SESSION['student_info'][$key])) {
                    $_SESSION['student_info'][$key] = $value;
                }
            }
            $userInfo = $_SESSION['student_info'];
            $autofocusAddress = 'autofocus=""';
        endif;
    } else {//Existing User
        $user_data_info = Usermanager :: get_user_info_by_email($_REQUEST['keyword']);
        $userInfo = $user_data_info;
        $user_id = $userInfo['user_id'];
        $message = '';
        if (count($userInfo) == 1) {
            $message = get_lang('UserNotFound');
        }
    }
}

$errForm = array();
if ($_SESSION['err_form']) {
    $errForm = $_SESSION['err_form'];
    unset($_SESSION['err_form']);
}

$condition = $_REQUEST['cond'];
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.validate.js"></script>';
$htmlHeadXtra[] = "<script type='text/javascript'>                   
    var type = '$condition';
    function typeValidater(e, object) {
        type = object.value;
        var boolean = false;
        //alignbutton();
        if(type == 'exist') {
            boolean = true;
            $('#label_search').hide();
            $('#button_search').show();
        } else {
            $('#button_search').hide();
            $('#label_search').show();
        }
        $('#first_name').attr('readonly', boolean);
        $('#last_name').attr('readonly', boolean);
        $('#first_name').val('');
        $('#last_name').val('');
        $('#phone').val('');
        $('#email').focus();
    }
    
    function search_user() {
        if ($('#email').val() == '') {
            $('#email').focus();
            $('#button_search').css({ 'position': 'absolute', 'top': '64px'});
        } else {
            url = 'cond='+type+'&keyword='+($('#email').val());
            window.location.href='../payment/checkout_4_payment_data.php?'+url;
            //$('#button_search').css({ 'position': 'absolute', 'top': '114px'});
        }
    }
    function alignbutton(){
    if($('#emptyPage').css('display')=='block'){
        $('#button_search').css({ 'position': 'absolute', 'top': '114px', 'left': '52%'});
    }else{
        $('#button_search').css({ 'position': 'absolute', 'top': '64px', 'left': '52%'});
    }
        //$('#button_search').css({ 'position': 'absolute', 'top': '64px'});
//        if($('label[class=\"error\"]').css('display')=='none' || $('label[class=\"error\"]').text()==''){
//            $('#button_search').css({ 'top': '0px'});
//        }else{
//            $('#button_search').css({ 'top': '-7px'});
//        }
    }
    function search_user_by_email(e, object) {
    //alignbutton();
        var key = e.keyCode || e.which;
        if (key == 13) {
            if (type == 'exist') {
                e.cancelBubble = true;
                e.returnValue = false;
                search_user();
            } else {
                if (object.value == '') {
                    $('#email').focus();
                } else {
                    var custom = $('.civility').serialize();
                    var first_name = $('.first_name').val();
                    var last_name = $('.last_name').val();
                    var email = $('.email').val();
                    var phone = $('.phone').val();
                    var country = $('.country').val();
                    var custom2 = $('.civility').val();
                    var address1 = $('.address1').val();
                    var address2 = $('.address2').val();
                    var city = $('.city').val();
                    var zip = $('.zip').val();
                    var organization = $('input[name=organization]').val();
                    var tva = $('input[name=tva_id]').val();
                    url = '?uev=new&first_name='+first_name+'&last_name='+last_name+'&email='+email+'&phone='+phone+'&country='+country+'&custom='+custom2+'&address1='+address1+'&city='+city+'&organization='+organization+'&zip='+zip+'&address2='+address2+'&tva='+tva;
                    window.location.href='" . api_get_path(WEB_AJAX_PATH) . "updatevalue.ajax.php'+url;
                    return false;
                }
            }
        }
    }
</script>";
$htmlHeadXtra[] = '<script>
        $(document).ready(function() {
        alignbutton();
        $("#button_search").hide();
        $("#color_button_validate").click(function(e) {
        var user_id = $(".user_id").val();
        var UserActive = $(".UserActive").val();
        var custom = $(".civility").serialize();
        var first_name = $(".first_name").val();
        var last_name = $(".last_name").val();
        var email = $(".email").val();
        var phone = $(".phone").val();
        var country = $(".country").val();
        var custom2 = $(".civility").val();
        var address1 = $(".address1").val();
        var address2 = $(".address2").val();
        var city = $(".city").val();
        var zip = $(".zip").val();
        var organization = $("input[name=organization]").val();
        var tva = $("input[name=tva_id]").val();

        if (UserActive == "false") {//Collectivity
            url = "?uev=new&first_name="+first_name+"&last_name="+last_name+"&email="+email+"&phone="+phone+"&country="+country+"&custom="+custom2+"&address1="+address1+"&city="+city+"&organization="+organization+"&zip="+zip+"&address2="+address2+"&tva="+tva;
            if (type == "") {
                type = "new";
            }
            if (type == "new") {
                if (first_name != "" && last_name != "" && email != "" && organization != "" && zip != "" && city != "" && address1 != "") {
                    window.location.href = "' . api_get_path(WEB_AJAX_PATH) . 'updatevalue.ajax.php" + url;
                    return false;
                }
            } else {
                $("input[name=\'custom\']").val(custom);
                $("#paypal-form").submit();
                return false;
            }
        } else {//Individual
            $("input[name=\'custom\']").val(custom);
            $("#paypal-form").submit();
            return false;
        }
        });

        if ($("#paypal-form").length > 0) {
            $("#paypal-form").validate({
                rules: {
                    email: {
                        required: true,
                    },
                    first_name: {
                        required: true
                    },
                    last_name: {
                        required: true
                    },
                    organization: {
                        required: true
                    },
                    address1: {
                        required: true
                    },
                    zip: {
                        required: true
                    },
                    city: {
                        required: true
                    }
                },
                messages: {
                    email: {
                        required: "' . get_lang('ThisFieldIsRequired') . '"
                    },
                    first_name: {
                        required: "' . get_lang('ThisFieldIsRequired') . '"
                    },
                    last_name: {
                        required: "' . get_lang('ThisFieldIsRequired') . '"
                    },
                    organization: {
                        required: "' . get_lang('ThisFieldIsRequired') . '"
                    },
                    address1: {
                        required: "' . get_lang('ThisFieldIsRequired') . '"
                    },
                    zip: {
                        required: "' . get_lang('ThisFieldIsRequired') . '"
                    },
                    city: {
                        required: "' . get_lang('ThisFieldIsRequired') . '"
                }
            }
        });
        }

        })
        </script>
        <style>
            label.error {color:red;}
        </style>
        ';

Display::display_header(get_lang('TrainingCategory'));
$currency = Currency::create()->getCurrencyByIsoCode(api_get_setting('e_commerce_catalog_currency'));
global $charset;
?>
<div id="content">
    <?php echo $objShoppingCartController->getBreadCrumbs($_SESSION, $_GET); ?>
    <div id="paypal-form-wrap" style="margin-top: 20px; clear:both">
        
        <form class="" action="<?php echo PAYPAL_URL ?>" method="post" id="paypal-form">
            <input type="hidden" name="cmd" value="_cart">
            <input type="hidden" name="charset" value="<?php echo $charset; ?>">
            <input type="hidden" name="upload" value="1">
            <input type="hidden" name="business" value="<?php echo api_get_payment_setting('email'); ?>">
            <input type="hidden" name="USER" value="<?php echo API_USERNAME; ?>">
            <input type="hidden" name="PWD" value="<?php echo API_PASSWORD; ?>">
            <input type="hidden" name="SIGNATURE " value="<?php echo API_SIGNATURE; ?>">
            <input type="hidden" name="currency_code" value="<?php echo $currency['code'] ?>">
            <input type="hidden" name="return" value="<?php echo api_get_path(WEB_PATH) . 'main/payment/process_payment_validation.php?uid=' . $user_id ?>">
            <input type="hidden" name="custom" value="" />
            <input type="hidden" name="cancel_return" value="<?php echo api_get_path(WEB_PATH) . 'main/payment/checkout_3_registration.php?next=3' ?>">
            <input type="hidden" class="user_id" name="user_id" value="<?php echo $user_id; ?>"/>
            <input type="hidden" name="rm" value="2"/>
            <input type="hidden" name="address_override" value="0"/>
            <input type="hidden" name="tax_cart" value="<?php echo number_format($_SESSION['shopping_cart']['total_tax_amount'], 2, '.', ''); ?>">
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
            <table class="table-shopp" style="width: 600px;">
                <?php
                $gateway = $objEcommerce->getGatewaySettings();
                $workSpace = trim($gateway['workspace']->value);
                if ($workSpace == 0) {
                    echo '
            <tr>
                <td colspan="2">
                    <div>
                    ' . sprintf(get_lang('TestModeActivated'), '<a href="https://developer.paypal.com" style="text-decoration:underline;" target="_blank">ici</a>') . '
                    </div>
                </td>
            </tr>    
                ';
                }
                if ($userInfo['firstname'] == '' && $_REQUEST['uis'] == '') {
                    $autofocusEmail = 'autofocus=""';
                }
                if ($userInfo['firstname'] != '' && $_REQUEST['uis'] == '') {
                    $autofocusOrg = 'autofocus=""';
                }

                if ($condition == 'exist') {
                    $checkedExist = 'checked';
                    $checkedNew = '';
                    $readonly = 'readonly';
                } else {
                    $checkedExist = '';
                    $checkedNew = 'checked';
                    $readonly = '';
                }
                $UserActive = 'false';
                if (isset($_SESSION['student_info']['identification']) && $_SESSION['student_info']['identification'] == 0):
                    $UserActive = 'true';
                endif;
                ?>
                <tr>
                    <td></td>
                    <td>
                        <input style="display: none;" type="text" hidden="" readonly="" id="UserActive" class="UserActive" value="<?php echo $UserActive; ?>"/>
                    </td>
                </tr>
                <?php
                if ($message != '') {
                    $userInfo['email'] = $_REQUEST['keyword'];
                    echo "<script>$('#button_search').css({ 'position': 'absolute', 'top': '114px', 'left': '469px'})</script>";
                    ?>
                    <tr>
                        <td></td>
                        <td><div id="emptyPage" style="margin-bottom: 10px;"><?php echo $message; ?></div></td>
                    </tr>
                <?php }
                ?>

                <?php if (isset($_SESSION['student_info']['identification']) && $_SESSION['student_info']['identification'] == 1): ?>
                    <td colspan="2"><b><?php echo get_lang('Register User'); ?>:</b></td>
                    <tr>
                        <td><?php echo $type; ?></td>
                        <td>
                            <input id="typeNew" type="radio" name="groupType" value="new" <?php echo $checkedNew; ?> onclick="typeValidater(event, this)">New User
                            <input id="typeExist" type="radio" name="groupType" <?php echo $checkedExist; ?> value="exist" onclick="typeValidater(event, this)">Existing User<br>
                        </td>
                    </tr>
                    <tr>
                        <td id="lblEmail"><?php echo get_lang('Email'); ?><span class="sym-error"> * </span>:</td>
                        <td>
                            <input id="email" class="email" type="text" size="30" name="email" <?php echo $autofocusEmail; ?> value="<?php echo $userInfo['email']; ?>" value="." onkeypress="return search_user_by_email(event, this);" >
                            <?php if ($userInfo['error'] != '') { ?>
                                <span class="sym-error"> Email already exist </span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            $style = 'style="margin-left: 14px; display: none;"';
                            if ($condition == 'exist') {
                                $style = 'style="margin-left: 14px;"';
                            }
                            ?>
                            <button type="button" class="button_search" style="position:absolute;top:64px;left: 52%;" name="search" id="button_search" <?php echo $style; ?> onclick="search_user();" ><?php echo get_lang('Search'); ?></button>
                        </td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <td><?php echo get_lang('Civility'); ?>:</td>
                    <td>
                        <select id="civility" name="civility" style="width:250px;" class="civility">
                            <option value="">--</option>
                            <option <?php echo ($userInfo['civility'] == get_lang('Mr') ? ' selected="selected"' : ''); ?> value="<?php echo get_lang('Mr') ?>"><?php echo get_lang('Mr') ?></option>
                            <option <?php echo ($userInfo['civility'] == get_lang('Mrs') ? ' selected="selected"' : ''); ?> value="<?php echo get_lang('Mrs') ?>"><?php echo get_lang('Mrs') ?></option>
                            <option <?php echo ($userInfo['civility'] == get_lang('Miss') ? ' selected="selected"' : ''); ?> value="<?php echo get_lang('Miss') ?>"><?php echo get_lang('Miss') ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php echo get_lang('FirstName'); ?><span class="sym-error"> * </span>:</td>
                    <td><input id="first_name" class="first_name" type="text" size="30" maxlength="32" name="first_name" <?php echo $autofocusName; ?> <?php echo $readonly; ?> value="<?php echo $userInfo['firstname']; ?>">
                        <?php echo (isset($errForm['firstName'])) ? '<span>' . $errForm['firstName'] . '</span>' : ''; ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo get_lang('LastName'); ?><span class="sym-error"> * </span>:</td>
                    <td><input id="last_name" class="last_name" type="text" size="30" maxlength="32" name="last_name" <?php echo $readonly; ?> value="<?php echo $userInfo['lastname']; ?>">
                        <?php echo (isset($errForm['LastName'])) ? '<span>' . $errForm['LastName'] . '</span>' : ''; ?>
                    </td>
                </tr>
                <?php if (isset($_SESSION['student_info']['identification']) && $_SESSION['student_info']['identification'] == 0): ?>
                    <tr>
                        <td><?php echo get_lang('Email'); ?><span class="sym-error"> * </span>:</td>
                        <td><input id="email" class="email" type="text" size="30" name="email" value="<?php echo $userInfo['email']; ?>" value="." onkeypress="return search_user_by_email(event, this)"></td>
                    </tr>
                <?php endif; ?>
                <td colspan="2"><b><?php echo get_lang('BillingAddress'); ?>:</b></td>
                </tr>
                <?php if (isset($_SESSION['student_info']['identification']) && $_SESSION['student_info']['identification'] == 1): ?>            
                    <tr>
                        <td><?php echo get_lang('Organization'); ?><span class="sym-error"> * </span>:</td>
                        <td><input type="text" size="25" maxlength="100" name="organization" <?php echo $autofocusOrg; ?> class="custom" value="<?php echo $userInfo['extra_organization']; ?>"></td>
                    </tr>
                    <tr>
                        <td><?php echo get_lang('TVA'); ?>:</td>
                        <td><input type="text" size="25" maxlength="100" name="tva_id" class="custom" value="<?php echo $userInfo['extra_tva_id']; ?>"></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td><?php echo get_lang('Country'); ?>:</td>
                    <td><?php
                        $countries = LanguageManager::get_countries(null, 'iso');
                        $cboCountries = '<select class="country" name="country">' . PHP_EOL;

                        $flag = !empty($userInfo['country']) ? $userInfo['country'] : 'FR';
                        foreach ($countries as $countryK => $country) {
                            $cboCountries .= '<option value="' . $countryK . '"';

                            if ($flag == $countryK) {
                                $cboCountries .= ' selected="selected" ';
                            }
                            $cboCountries .= '>' . $country . '</option>' . "\n";
                        }
                        echo $cboCountries .= '</select>';
                        ?>
                    </td>
                </tr>            
                <tr>
                    <td><?php echo get_lang('Street'); ?><span class="sym-error"> * </span>:</td>
                    <td><input class="address1" type="text" size="25" maxlength="100" name="address1" <?php echo $autofocusAddress; ?> value="<?php echo $userInfo['extra_street']; ?>"></td>
                </tr>
                <tr>
                    <td><?php echo get_lang('AdditionalStreet'); ?>:</td>
                    <td><input class="address2" type="text"  size="25" maxlength="100" name="address2" value="<?php echo $userInfo['extra_addressline2']; ?>"><?php echo get_lang('Optional'); ?></td>
                </tr>
                <tr>
                    <td><?php echo get_lang('Zipcode'); ?><span class="sym-error"> * </span>:</td>
                    <td><input class="zip" type="text" size="10" maxlength="10" name="zip" value="<?php echo $userInfo['extra_zipcode']; ?>"><!--(<:?php echo '5 '.get_lang('Or').' 9 '.get_lang('digits');?>)--></td>
                </tr>
                <tr>
                    <td><?php echo get_lang('City'); ?><span class="sym-error"> * </span>:</td>
                    <td><input class="city" type="text" size="25" maxlength="40" name="city" value="<?php echo $userInfo['extra_city']; ?>"></td>
                </tr>
                <tr>
                    <td><?php echo get_lang('Phone'); ?>:</td>
                    <td><input id="phone" class="phone" type="text" size="12" maxlength="14" name="phone" value="<?php echo $userInfo['phone']; ?>"></td>
                </tr>
                <tr>
                    <td style="padding-top:19px;"height="15px"><strong><?php echo get_lang('Amount'); ?>:</strong></td>
                    <td>
                        <input type="hidden" name="amount" value="<?php echo number_format(api_floatval($_SESSION['shopping_cart']['total_price_tax_qty']), 2, '.', ''); ?>" readonly="readonly">
                        <!--
                        <input style="background-color: #ccc;width:50px;" type="text" name="amount_dply" value="<?php echo api_number_format($_SESSION['shopping_cart']['total_price_tax_qty']); ?>" readonly="readonly"> <?php echo $currency['symbol'] . ' (' . $currency['name'] . ')'; ?>
                        -->
                        <!--
                        <input style="background-color: #FFF; border: 0px !important; cursor: pointer; width: 50px" name="amount_dply" value="<?php echo api_number_format($_SESSION['shopping_cart']['total_price_tax_qty']); ?>" readonly="readonly"> <?php echo $currency['symbol'] . ' (' . $currency['name'] . ')'; ?>
                        -->
                        <div name="amount_dply" style="margin-top:15px;font-size: 15px;"><strong><?php echo api_number_format($_SESSION['shopping_cart']['total_price_tax_qty']); ?></strong><?php echo '&nbsp;&nbsp;&nbsp;'.$currency['symbol'] . ' (' . $currency['name'] . ')'; ?></div> 
                    </td>
                </tr>
                <tr>
                    <td/>
                    <td valign="right" align="left"><span class="form_required"> *</span><?php echo str_replace('*', '<span class="form_required"> *</span>', get_lang('FieldRequired')); ?></td>
                </tr>
                <tr>
                    <td/>
                    <td valign="right" align="right"><div class="pull-bottom"><button type="submit" id="color_button_validate" class="add"><?php echo get_lang('Ok'); ?></button></div></td>
                </tr>
            </table>
        </form>
    </div>
</div>
<?php
echo Display::display_footer();
?>
