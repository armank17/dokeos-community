<?php
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

$objShoppingCartController = new ShoppingCartController();
$stepNumber = 5;

if(isset($_REQUEST['pay'])) {
   $_SESSION['shopping_cart']['steps'][2] = true;
   $_SESSION['shopping_cart']['steps'][3] = true;
}
$objShoppingCartController->checkStep($stepNumber, $_SESSION );
$this_section = SECTION_PLATFORM_ADMIN;

$userInfo = $_SESSION['student_info'];

//if( ! isset( $_SESSION['shopping_cart']['transaction_result'] ) )
//{
//    header('location: '.api_get_path(WEB_PATH) );
//}
//else {
    $transactionResult = $_SESSION['shopping_cart']['transaction_result'];
    unset($_SESSION['shopping_cart']['transaction_result']);
    unset($_SESSION['shopping_cart']['steps']);
//}

Display::display_header(get_lang('TrainingCategory'));
//$successbuy = api_get_payment_setting('successbuy');
//$successbuyuser = api_get_payment_setting('successbuyuser');
//$unsuccessbuy = api_get_payment_setting('unsuccessbuy');
?>
<div id="content">
    
<!--h3>
<!--?php 
//$msg_end_payment = api_get_settings_options('messageEndPayment');
//$msg_end_payment  =  (!empty($msg_end_payment[0]['value']))?$msg_end_payment[0]['value']:get_lang('YourOperationHasBeenSavedSucesfully');
//echo ($transactionResult['completed'])?(isset($_SESSION['_user']['user_id']) ? get_lang('YourOperationHasBeenSavedSucesfullyOfUser'):$msg_end_payment) : get_lang('ThereWasAPaymentProcessProblem') ;
//echo ($transactionResult['completed'])?$msg_end_payment:get_lang('ThereWasAPaymentProcessProblem') ;
?-->
<!--/h3>      
<!--<h3><?php // echo ($transactionResult['completed'])?(isset($_SESSION['_user']['user_id']) ? $successbuy:$successbuyuser) : $unsuccessbuy;?></h3>-->
<?php get_lang($transactionResult['message']);?>
<?php if ($transactionResult['completed']){
$details =  $transactionResult['details'];
?>
<table>
    <?php if($details['TRANSACTIONID']!=''){ ?>
    <tr>
        <th>Transaction ID</th>
        <td><?php echo $details['TRANSACTIONID']; ?></td>
    </tr>
    <?php } if($details['AMT']!=''){?>
    <tr>
        <th>Total Charged Amount</th>
        <td><?php echo $details['AMT']; ?></td>
    </tr>
    <?php } if($details['AMT']!=''){?>
    <tr>
        <th>Transaction Date</th>
        <td><?php echo $details['TIMESTAMP']; ?></td>
    </tr>
    <?php } ?>
    <?php if($_SESSION['link_now']) {
        $subscribedPayment = get_lang('Subscribed');
        $sentEmailPayment = get_lang('sentEmailPayment');
        $accessListPayment = get_lang('accessListPayment'); ?>
    <div style="width:80%;margin-top:8%;text-align:right;font-size:16px;font-weight:bold;font-family:monospace;">
        <?php echo $subscribedPayment; ?><br>
        <?php echo $sentEmailPayment; ?><br>
        <?php echo $accessListPayment; ?>
    </div>
    <div style="float:right;margin-top:-10%;">
        <img style="display:block;margin-bottom:20px;" src="<?php echo api_get_path(WEB_PATH);?>main/application/ecommerce/assets/images/feedback_woman.png">
        <a href="<?php echo api_get_path(WEB_PATH) . 'user_portal.php'; ?>"><button  class="save"><?php echo get_lang("CourseList") ?></button></a>
    </div>
    <?php unset($_SESSION['link_now']); } ?>
</table>
<?php } else{ ?>

<p><?php echo $transactionResult['message']; ?></p>
<p><a href="<?php echo api_get_path(WEB_PATH);?>main/payment/checkout_3_registration.php"><?php echo get_lang('Return');?></a></p>
<?php }?>

</div>
<?php echo Display::display_footer();