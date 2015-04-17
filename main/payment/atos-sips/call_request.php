<?php

// resetting the course id
$cidReset = true;

// name of the language file that needs to be included
$language_file = array ('registration','admin');

require_once '../../inc/global.inc.php';
require_once 'load_pathfile.inc.php';
$libpath = api_get_path(LIBRARY_PATH);
require_once  $libpath.'sessionmanager.lib.php';
require_once  $libpath.'usermanager.lib.php';
require_once api_get_path( SYS_PATH ) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';
$scController = new ShoppingCartController();
$scController->processPaymentCc( $_REQUEST );
$pay_type = $_REQUEST['txtPaymentType'];
//display the header
Display::display_header(get_lang('TrainingCategory'));

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

if ($pay_type == 1) {
    if (!empty($catalogue['cc_payment_message'])) {
        echo '<div class="messages-payment" style="margin-bottom:10px;padding:4px;">'.$catalogue['cc_payment_message'].'</div>';
    }
} else if ($pay_type == 3) {
    if(!empty($catalogue['installment_payment_message'])) {
        echo '<div class="messages-payment" style="margin-bottom:10px;padding:4px;">'.$catalogue['installment_payment_message'].'</div>';
    }
}
$tableau = $_SESSION['shopping_cart']['transaction_result']['details'];
$code = $tableau[1];
$error = $tableau[2];
$message = $tableau[3];

// analyse du code retour
if (( $code == "" ) && ( $error == "" )) {
    print ("<BR><CENTER>Erreur appel request</CENTER><BR>");
    print ("executable request non trouve $path_bin");
    print ("<br><br><br>");
    //print "Votre demande n'a pas été exécuté, s'il vous plaît Give it a try en quelques minutes. Merci.<br>";
}
// Erreur, affiche le message d'erreur
else if ($code != 0) {
    print ("<center><b><h2>Erreur appel API de paiement.</h2></center></b>");
    print ("<br><br><br>");
    print (" Message erreur : $error <br>");
    //print "Votre demande n'a pas été exécuté, s'il vous plaît Give it a try en quelques minutes. Merci.<br>";
}
// OK, affiche le formulaire HTML
else {
    print ("<br><br>");		
    # OK, affichage du mode DEBUG si activé
    print (" $error <br>");		
    print ("  $message <br>");
}

print '</div>';

// display the footer
Display::display_footer();
?>
