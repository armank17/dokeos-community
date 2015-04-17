<?php
ob_start();
// resetting the course id
$cidReset = true;
$language_file = array ('registration','admin');

// setting the help
$help_content = 'platformadministrationsessionadd';

// including the global Dokeos file
require dirname(__FILE__) . '/../inc/global.inc.php';


// including additional libraries
require_once(api_get_path(LIBRARY_PATH).'sessionmanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'language.lib.php');
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';
require_once api_get_path(SYS_PATH).'main/core/controller/shopping_cart/shopping_cart_controller.php';
require_once api_get_path(SYS_PATH).'main/core/controller/shopping_cart/CatalogueController.php';

if(isset($_GET['prevStep']) && !empty($_GET['prevStep']) && $_GET['prevStep']==3){
    unset($_SESSION['shopping_cart']['steps'][4]);
}
$objShoppingCartController = new ShoppingCartController();
$stepNumber = 3;
if(isset($_REQUEST['pay'])) {
   $_SESSION['shopping_cart']['steps'][1] = true;
}
$objCommerceManager = new EcommerceManager();

$objShoppingCartController->checkStep($stepNumber, $_SESSION );
$this_section = SECTION_PLATFORM_ADMIN;

Display::display_header(get_lang('TrainingCategory'));
?>
<div id="content">
    <?php echo $objShoppingCartController->getBreadCrumbs($_SESSION, $_GET); ?>
    <div style="display:none;" id="cancell_message_id"><?php echo get_lang('ConfirmYourChoice'); ?></div>
    <div class="row">
        <div class="form_header register-payment-steps-name" style="margin-left: 5px;">
            <h2><?php if($_SESSION['shopping_cart']['chr_type'] != '0'){echo get_lang('PaymentMethods');}  else { echo get_lang("ShopPersonalData");}; ?></h2>
        </div>
        <p><?php //echo get_lang('PaymentMethodsMessage'); ?></p>
    </div>
    <div class="method-payments-icons" style="padding: 0;">
    <?php
    if($_REQUEST['action']=='cancel_order')
    {
        unset($_SESSION['shopping_cart']['items']);
        header("Location: ".api_get_path(WEB_PATH));
        ob_end_flush();
    }    
    if(isset($_SESSION['user_info'])){
       $_SESSION['student_info'] = $_SESSION['user_info'];
       $_SESSION['shopping_cart']['items'] = $_SESSION['selected_courses'];
       $product = SessionManager::get_session_category($_REQUEST['id']);
       $_SESSION["shopping_cart"]['total'] = $product['cost'];
    }
 
    if ((isset($_SESSION['student_info']) && isset($_SESSION['shopping_cart']['items'])) || api_get_user_id()) { ?>
        <script>
            $(document).ready(function(){
                $(".credit-card").click(function(){
                     $("body").find(".credit-card").removeClass('selected_chop_card');
                     $( this ).addClass( 'selected_chop_card' );
                });
                $(".save").click(function(){
                    if($('form#frmPaymentMethod input#txtPaymentType').val() == 0 && $(".payment-methods").length>0){
                        $.alert('<?php echo get_lang("SelectOptionShop")?>','<?php echo get_lang("Error")?>');
                    }else{
                        $('form#frmPaymentMethod').submit();
                    }
                });
            });
        function callPayment(pay_type) {
            if (pay_type != 4) {
				$('form#frmPaymentMethod input#txtPaymentType').val(pay_type);
				//$('form#frmPaymentMethod').submit(); 
       
                } else {
                    $.confirm('<?php echo get_lang('ConfirmYourChoice'); ?>','<?php echo get_lang('ConfirmationDialog'); ?>', function() {
                        window.location.href = "<?php echo api_get_path(WEB_PATH)?>main/payment/checkout_3_registration.php?action=cancel_order";               
                    })
                }
            }
         </script>
        <div>
           <?php
              $urlaction = '';
              if($_SESSION['shopping_cart']['chr_type']=='0'){$urlaction= api_get_path(WEB_PATH) . 'main/payment/process_payment_validation.php?uid=' . api_get_user_id();
              }else{$urlaction = $objCommerceManager->getCurrentPaymentMethod()->getFormUrlPayment();}
          ?>
        <!--<form action="<!--?php echo api_get_path(WEB_PATH);?>main/payment/checkout_4_payment_data.php" method="post" id="frmPaymentMethod">
            <input id="txtPaymentType" name="txtPaymentType" type="hidden" value=""/>
        </form>-->
        <form action="<?php echo $urlaction;?>" method="post" id="frmPaymentMethod">
            <input id="txtPaymentType" name="txtPaymentType" type="hidden" value=""/>
        </form>
            <?php
            //show payment options per catalog
            echo CatalogueController::create()->getActiveCatalogPaymentOptions();
            
        } else {
            echo get_lang('YourSessionOrderIsOver').'&nbsp;<a href="'.api_get_path(WEB_PATH).'">'.get_lang('GoToCatalogue').'</a>';
    }
?>
        </div>
    </div>
    
</div><!-- end div#content -->
<?php Display::display_footer(); 