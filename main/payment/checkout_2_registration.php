<?php
$cidReset = true;
$language_file = array('registration', 'admin');
// setting the help
$help_content = 'platformadministrationsessionadd';

require_once dirname(__FILE__) . '/../inc/global.inc.php';

require_once api_get_path(LIBRARY_PATH) . 'sessionmanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'language.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
require_once api_get_path(SYS_PATH) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';
if (isset($_GET['prevStep']) && !empty($_GET['prevStep']) && $_GET['prevStep'] == 2) {
    unset($_SESSION['shopping_cart']['steps'][3]);
    unset($_SESSION['shopping_cart']['steps'][4]);
}

if(isset($_GET['chr_type']) && $_GET['chr_type'] == '0'){
    unset($_SESSION['shopping_cart']['steps'][1]);
    unset($_SESSION['shopping_cart']['steps'][3]);
    unset($_SESSION['shopping_cart']['steps'][4]);
    $_SESSION['shopping_cart']['chr_type'] = '0';
    $_SESSION['shopping_cart']['code'] = $_GET['id'];
    $_SESSION['shopping_cart']['free'] = $_GET['id'];
    $title = get_lang("ShopPersonalData");
}else{
    $title = get_lang('StudentPersonalData'); 
    //unset($_SESSION['shopping_cart']['chr_type']);
}
$objShoppingCartController = new ShoppingCartController();
$stepNumber = 2;

// Validate shopping cart steps
$objShoppingCartController->checkStep($stepNumber, $_SESSION);
$objForm = $objShoppingCartController->getFormByStepNumber($stepNumber, $_SESSION);
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/hopscotch-0.1.1.min.js"></script>';
$htmlHeadXtra[] = '<link type="text/css" href="' . api_get_path(WEB_CSS_PATH) . 'hopscotch-0.1.1.min.css" rel="stylesheet" />';
if(isset($_GET['s']) && $_GET['s']== 0){
//$htmlHeadXtra[] = '<script type="text/javascript">
// // Define the tour!
//    var tour = {
//      id: "hello-hopscotch",
//      steps: [
//        {
//          title: "'.  get_lang('LoginRequired').'",
//          content: "'.  get_lang('langNotAllowedOrSessionTimeout').'",
//          target: "startlogin",
//          placement: "right"
//        }
//      ]
//    };
//
//    // Start the tour!
//    hopscotch.startTour(tour);    
//</script>';
//$htmlHeadXtra[]='<style>
//div.hopscotch-bubble h3 {padding-left:30px!important;
//div.hopscotch-bubble .hopscotch-bubble-number {display:none!important;}
//}    
//</style>';
}

if ($objForm->validate()) {
    if (api_get_user_id() == 0) {
        //register User
        $user_id = $objShoppingCartController->registerUserShop();
        if(!empty($user_id)){
            $_SESSION['_user']['user_id'] = $user_id;
        //error_log(api_get_user_id());
        $resProcessForm = $objShoppingCartController->processCheckoutFormByStep($stepNumber, $objForm, $_SESSION);
        
        if ($resProcessForm) {
            if ($_SESSION['_user']['user_id']) {
                header("location: " . api_get_path(WEB_CODE_PATH) . 'payment/checkout_3_registration.php?next=3');
            } else {
                $email = $_SESSION['student_info']['email'];

                if ($email != "") {
                    $main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
                    $sql_query = 'SELECT email FROM ' . $main_user_table . ' WHERE email = "' . Database::escape_string($email) . '"';
                    $sql_result = Database::query($sql_query, __FILE__, __LINE__);
                    $result = Database :: fetch_array($sql_result);
                    if ($result <= 0) {
                        header("location: ". api_get_path(WEB_CODE_PATH).'payment/checkout_3_registration.php?next=3');  
                    }
                }
            }
            if($_SESSION['shopping_cart']['chr_type'] == '0'){
                $url =  api_get_path(WEB_PATH)."main/payment/checkout_3_registration.php";
                header("location: " . $url);
            }
                
            
        }
        }  else {
            //without session
            header("location: ".api_get_path(WEB_PATH).'main/payment/checkout_2_registration.php?id='.$_SESSION['cat_id'].'&prev=2&s=0');  
        }
    }else{
        //error_log(api_get_user_id());
        $resProcessForm = $objShoppingCartController->processCheckoutFormByStep($stepNumber, $objForm, $_SESSION);
        
        if ($resProcessForm) {
            if ($_SESSION['_user']['user_id']) {
                header("location: " . api_get_path(WEB_CODE_PATH) . 'payment/checkout_3_registration.php?next=3');
            } else {
                $email = $_SESSION['student_info']['email'];

                if ($email != "") {
                    $main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
                    $sql_query = 'SELECT email FROM ' . $main_user_table . ' WHERE email = "' . Database::escape_string($email) . '"';
                    $sql_result = Database::query($sql_query, __FILE__, __LINE__);
                    $result = Database :: fetch_array($sql_result);
                    if ($result <= 0) {
                        header("location: ". api_get_path(WEB_CODE_PATH).'payment/checkout_3_registration.php?next=3');  
                    }
                }
            }
            if($_SESSION['shopping_cart']['chr_type'] == '0'){
                $url =  api_get_path(WEB_PATH)."main/payment/checkout_3_registration.php";
                header("location: " . $url);
            }
                
            
        }
    }
}
$this_section = SECTION_PLATFORM_ADMIN;
//display the header
Display::display_header(get_lang('TrainingCategory'));
?>
<div id="content">
<?php echo $objShoppingCartController->getBreadCrumbs($_SESSION, $_GET); ?>
    <div class="row">
        <div class="form_header register-payment-steps-name">
            <h2><?php echo $title;?></h2>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#startlogin").click(function() {
                $("#showloging").css({"display": "inline"});
                $("#pstartlogin").css({"display": "none"});
                $(".hopscotch-bubble").css({"display": "none"});
            });
        });
        function prev_step(prev_url) {
            document.location.href = prev_url;
        }
    </script>
    <div id="showloging" style="display: none;">
<?php
$urlForm2 = api_get_path(WEB_PATH) . api_get_self();

$form = new FormValidator('formLogin', 'post', $urlForm2);
$form->addElement('html', '<p id="pstartlogin2"><b>' . get_lang('LoginForContinue') . ': </b></p>');
$form->addElement('text', 'login', get_lang('UserName'));
$form->addElement('password', 'password', get_lang('Pass'));
$form->addElement('hidden', 'current_location', 'payment');
$form->addElement('hidden', 'redirect_url', $urlForm2);
$form->addElement('style_submit_button', 'submitAuth', get_lang('langEnter'), array('class' => 'add'));
$form->display();
?>
    </div>
        <?php
        $objForm->display();
        ?>
</div>
        <?php
        Display::display_footer();
        