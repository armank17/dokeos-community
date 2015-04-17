<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author Bart Mollet
* @package dokeos.admin
*/

// name of the language file that needs to be included
$language_file = array ('registration','admin');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationsessionadd';

// including the global Dokeos file
require_once dirname( __FILE__ ) . '/../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path( SYS_PATH) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

$objShoppingCartController = new ShoppingCartController();
$stepNumber = 2;
$objShoppingCartController->checkStep($stepNumber, $_SESSION);

$objForm = $objShoppingCartController->getFormByStepNumber($stepNumber, $_SESSION);


if( $objForm->validate()) {
    $user = $objForm->exportValues();
    $id = $user['cat_id'];
    $_SESSION['user_info'] = $user;

    if (empty($user['extra_zipcode'])) {
        $_SESSION['user_info']['extra_zipcode'] = 0;
    }

    $url = api_get_path(WEB_PATH) .'main/admin/feedback.php?iden='.$iden.'&amp;wish='.$wish.'&amp;id='.$id.'&next=4';
    header('location: ' . $url );

}


//display the header
Display::display_header(get_lang('TrainingCategory'));

// start the content div
echo '<div id="content">';
// Steps breadcrumbs
SessionManager::display_steps_breadcrumbs();

if (!isset($_SESSION['steps'][1])) {
    $_SESSION['steps'][1] = true;
}

if (!isset($_SESSION['steps'][2])) {
    $_SESSION['steps'][2] = true;
}

if(isset($_REQUEST['iden'])){
	$iden = $_REQUEST['iden'];
}
else {
	$iden = 0;
}

if(isset($_REQUEST['wish'])){
	$wish = $_REQUEST['wish'];
}
else {
	$wish = 1;
}


if(isset($_REQUEST['id'])){
	$_SESSION['cat_id'] = $_REQUEST['id'];
}

$product = SessionManager::get_session_category($_REQUEST['id']);                                            
echo '<div class="row"><div class="form_header register-payment-steps-name"><h2>'.$product['name'].'</h2></div></div>';





$objForm->display();

// close the content div
echo '</div>';

// display the footer
Display::display_footer();
?>