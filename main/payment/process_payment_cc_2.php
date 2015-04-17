<?php

// resetting the course id
$cidReset = true;

// name of the language file that needs to be included
$language_file = array (
    'registration', 'admin', 'group' );

// including the global Dokeos file
require_once dirname( __FILE__ ) . '/../inc/global.inc.php';
$libpath = api_get_path( LIBRARY_PATH );
require_once (api_get_path( LIBRARY_PATH ) . 'sessionmanager.lib.php');
require_once (api_get_path( LIBRARY_PATH ) . 'language.lib.php');
require_once $libpath . 'usermanager.lib.php';
require_once $libpath . 'fileUpload.lib.php';
// Loading Paypal
require_once api_get_path(SYS_PATH) . 'main/inc/lib/paypal/PaypalModel.php';

$this_section = SECTION_PLATFORM_ADMIN;
// get course in the shopping cart
if ( isset( $_GET ['cat_id'] ) )
{
    $_SESSION ['cat_id'] = intval( $_GET ['cat_id'] );
}
$cat_id = intval( $_SESSION ['cat_id'] );

$cat = SessionManager::get_session_category( $cat_id );
$product = SessionManager::get_session_category( $_REQUEST ['id'] );
$statusResponse = FALSE;
$responseTransaction = NULL;


if ( isset( $_SESSION['user_info'] )  ||  isset( $_SESSION ['_user'] ))
{
    // add course within the credit card transaction manager
    $objPaypal = PaypalModel::create();
    $objPaypal->setProduct( $product );
    
    $savedTransaction = $objPaypal->processFormSession( $_SESSION, $_REQUEST, $_POST );
    
    $user_id = $objPaypal->getUserId();
    
    if ( is_numeric( $savedTransaction ) )
    {
        // register selected courses and sessions
        if ( isset( $_SESSION ['selected_sessions'] ) && isset( $_SESSION ['cat_id'] ) && isset( $_SESSION ['cours_rel_session'] ) )
        {
            SessionManager::register_user_to_selected_courses_session( $_SESSION ['cours_rel_session'], $user_id, $_SESSION ['selected_sessions'], $_SESSION ['cat_id'] );
        }
    }
    $statusResponse = $objPaypal->getStatus();
    $responseTransaction = $objPaypal->getResponse();
    
    
    
    /*
     * SESSION REGISTERING
     */
    $action_url = api_get_path( WEB_PATH ) . 'user_portal.php?nosession=true';
    $user_info = api_get_user_info( $user_id );
    $extra_field = UserManager::get_extra_user_data( $user_id );
    /*$_user ['firstName'] = $user_info ['firstname'];
    $_user ['lastName'] = $user_info ['lastname'];
    $_user ['mail'] = $user_info ['mail'];
    $_user ['language'] = $user_info ['language'];
    $_user ['user_id'] = $user_id;*/
    //$is_allowedCreateCourse = $user_info ['status'] == 1;
    //api_session_register( '_user' );
    //api_session_register( 'is_allowedCreateCourse' );
    
    $recipient_name = $user_info ['firstName'] . ' ' . $user_info ['lastName'];
    // stats
    //event_login();
    // last user login date is now
    //$user_last_login_datetime = 0; // used as a unix timestamp it will
                                   // correspond to : 1 1 1970
    //api_session_register( 'user_last_login_datetime' );

}

// display the header
Display::display_header( get_lang( 'TrainingCategory' ) );
?>
<div id="content">

<?php

$msg_end_payment = api_get_settings_options('messageEndPayment');
$msg_end_payment  =  (!empty($msg_end_payment[0]['value']))?$msg_end_payment[0]['value']:get_lang('YourOperationHasBeenSavedSucesfully');

if ( ! is_null( $responseTransaction ) )
{   
    ?>

	<h3><?php echo ($statusResponse) ?  $msg_end_payment : get_lang('OperationNotCompleted') ;?></h3>

<?php
    
    echo $responseTransaction;
    
    if ( $statusResponse )
    {
        echo "<p>" . get_lang( 'Dear' ) . " " . stripslashes( Security::remove_XSS( $recipient_name ) ) . ",<br /><br />" . get_lang( 'PersonalSettings' ) . ".</p>\n";
        echo "<p>" . get_lang( 'MailHasBeenSent' ) . ".</p>";
        echo "<form action=\"", $action_url, "\"  method=\"post\">\n", "<button type=\"submit\" class=\"next\" name=\"next\" value=\"", get_lang( 'Next' ), "\" validationmsg=\" ", get_lang( 'Next' ), " \">" . get_lang( 'GoToPortalHome' ) . "</button>\n", "</form><br />\n";
        if ( isset( $_SESSION ['pay_type'] ) )
        {
            unset( $_SESSION ['pay_type'] );
        }
        if ( isset( $_SESSION ['new_uid'] ) )
        {
            unset( $_SESSION ['new_uid'] );
        }
        if ( isset( $_SESSION ['new_catid'] ) )
        {
            unset( $_SESSION ['new_catid'] );
        }
        
        // unset sessions
        SessionManager::clear_catalogue_order_process();
    }
}
else
{
    echo get_lang('YourSessionOrderIsOver').'&nbsp;<a href="'.api_get_path(WEB_PATH).'">'.get_lang('GoToCatalogue').'</a>';
}
?>


</div>

<?php echo Display::display_footer(); 