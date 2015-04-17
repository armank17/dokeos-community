<?php
// resetting the course id
$cidReset = true;

// name of the language file that needs to be included
$language_file = array ('registration','admin','group');

// resetting the course id
//$cidReset = true;

// setting the help
$help_content = 'platformadministrationsessionadd';

require_once '../../inc/global.inc.php';
require_once 'load_pathfile.inc.php';
$libpath = api_get_path(LIBRARY_PATH);
require_once $libpath.'sessionmanager.lib.php';
require_once $libpath.'usermanager.lib.php';
require_once $libpath.'fileUpload.lib.php';
require_once api_get_path( SYS_PATH ) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';
$scController = new ShoppingCartController();
$scController->responsePaymentCc( $_REQUEST );
//display the header
Display::display_header(get_lang('TrainingCategory'));

// Récupération de la variable cryptée DATA
$message="message={$_POST['DATA']}";

// Initialisation du chemin du fichier pathfile (à modifier)
$pathfile="pathfile=".api_get_path(SYS_CODE_PATH)."payment/atos-sips/param/pathfile";

// Initialisation du chemin de l'executable response (à modifier)
$path_bin = api_get_path(SYS_CODE_PATH)."payment/atos-sips/bin/response";

// Appel du binaire response
$result=exec("$path_bin $pathfile $message");

// on separe les differents champs et on les met dans une variable tableau
$tableau = explode ("!", $result);

// Récupération des données de la réponse
$code = $tableau[1];
$error = $tableau[2];
$merchant_id = $tableau[3];
$merchant_country = $tableau[4];
$amount = $tableau[5];
$transaction_id = $tableau[6];
$payment_means = $tableau[7];
$transmission_date= $tableau[8];
$payment_time = $tableau[9];
$payment_date = $tableau[10];
$response_code = $tableau[11];
$payment_certificate = $tableau[12];
$authorisation_id = $tableau[13];
$currency_code = $tableau[14];
$card_number = $tableau[15];
$cvv_flag = $tableau[16];
$cvv_response_code = $tableau[17];
$bank_response_code = $tableau[18];
$complementary_code = $tableau[19];
$complementary_info = $tableau[20];
$return_context = $tableau[21];
$caddie = $tableau[22];
$receipt_complement = $tableau[23];
$merchant_language = $tableau[24];
$language = $tableau[25];
$customer_id = $tableau[26];
$order_id = $tableau[27];
$customer_email = $tableau[28];
$customer_ip_address = $tableau[29];
$capture_day = $tableau[30];
$capture_mode = $tableau[31];
$data = $tableau[32];

print '<div id="content">';

// analyse du code retour
if (($code == "") && ($error == "")) {  
    print "<BR><CENTER>erreur appel response</CENTER><BR>";
    print "executable response non trouve $path_bin";
}
// Erreur, affiche le message d'erreur
else if ($code != 0) {
    print "<center><b><h2>Erreur appel API de paiement.</h2></center></b>";
    print "<br><br><br>";
    print " message erreur : $error <br>";
}
// Erreur, affiche le message d'erreur
/*else if ($response_code !== '00') {
    print "<center><b><h2>Authorization refused</h2></center></b>";
    print "<br><br><br>";
}*/
// OK, affichage des champs de la réponse
else {
        
    // get user_id and sess_id   
    $tbl_payment_atos = Database::get_main_table(TABLE_MAIN_PAYMENT_ATOS);     
    $rs_trans = Database::query("SELECT user_id, sess_id FROM $tbl_payment_atos WHERE transaction_id = $transaction_id");
    if (!Database::num_rows($rs_trans)) {        
        // first save registered user
        $user_id = api_get_user_id();
        if (isset($_SESSION['user_info'])) {        
            $lastname = $_SESSION['user_info']['lastname'];
            $firstname = $_SESSION['user_info']['firstname'];
            $status = 5;
            $hash = api_generate_password(3);        
            $part1 = $firstname[0];
            $exp_lname = explode(' ', $lastname);
            $part2 = (is_array($exp_lname) && count($exp_lname) > 1)?$exp_lname[0]:$lastname;        
            $genera_uname = strtolower($part1.$part2.$hash);                
            $genera_uname=replace_accents($genera_uname);	
            $username = $genera_uname;
            $email = $_SESSION['user_info']['email'];
            if (empty($_SESSION['user_info']['user_id'])) {
                $user_id = UserManager::create_user($firstname, $lastname, $status, $email, $username);
            } else {
                $user_id = $_SESSION['user_info']['user_id'];
                UserManager::update_user($user_id, $firstname, $lastname, $username);
            }
            $extras = array();
            foreach($_SESSION['user_info'] as $key => $value) {
                if (substr($key, 0, 6) == 'extra_') { //an extra field
                    $myres = UserManager::update_extra_field_value($user_id, substr($key, 6), $value);
                }
            }
        }

        // save payer
        if (isset($_SESSION['payer_info'])) {
            $_SESSION['payer_info']['student_id'] = $user_id;
            $payer_id = SessionManager::save_payer_user($_SESSION['payer_info']);
        }

        // save payment
        $from = isset($_SESSION['from'])?$_SESSION['from']:'';
        $cat_id = $_SESSION['cat_id'];
        $params = array(
                    'user_id' => $user_id,
                    'sess_id' => intval($cat_id),
                    'pay_type' => intval($_SESSION['pay_type']),
                    'pay_data' => $result,
                    'from' => $from
                  );
        if ($user_id) {
            $saved = SessionManager::save_payment_atos($params);    
        }

        if (is_numeric($saved)) {
            // register selected courses and sessions
            if (isset($_SESSION['selected_sessions']) && isset($_SESSION['cat_id']) && isset($_SESSION['cours_rel_session'])) {
                SessionManager::register_user_to_selected_courses_session($_SESSION['cours_rel_session'], $user_id, $_SESSION['selected_sessions'], $_SESSION['cat_id']);
            }
        }
               
    } else {
	$row_trans = Database::fetch_object($rs_trans);
        $user_id   = $row_trans->user_id;
        $cat_id    = $row_trans->sess_id;        
    }
    
    /*--------------------------------------
                          SESSION REGISTERING
    --------------------------------------*/     
    $action_url  = api_get_path(WEB_PATH).'user_portal.php?nosession=true';                                 
    $user_info   = api_get_user_info($user_id);
    $extra_field = UserManager::get_extra_user_data($user_id);
    $_user['firstName'] = $user_info['firstname'];
    $_user['lastName'] 	= $user_info['lastname'];
    $_user['mail'] 	= $user_info['mail'];
    $_user['language'] 	= $user_info['language'];
    $_user['user_id']	= $user_id;
    $is_allowedCreateCourse = $user_info['status'] == 1;
    api_session_register('_user');
    api_session_register('is_allowedCreateCourse');
    //stats
    event_login();
    // last user login date is now
    $user_last_login_datetime = 0; // used as a unix timestamp it will correspond to : 1 1 1970
    api_session_register('user_last_login_datetime');         
    echo "<p>".get_lang('Dear')." ".stripslashes(Security::remove_XSS($recipient_name)).",<br /><br />".get_lang('PersonalSettings').".</p>\n";
    echo "<p>".get_lang('MailHasBeenSent').".</p>";                  
    echo "<form action=\"", $action_url, "\"  method=\"post\">\n", "<button type=\"submit\" class=\"next\" name=\"next\" value=\"", get_lang('Next'), "\" validationmsg=\" ", get_lang('Next'), " \">".get_lang('GoToPortalHome')."</button>\n", "</form><br />\n";                                    
}
print '</div>';

if (isset($_SESSION['pay_type'])) {
	unset($_SESSION['pay_type']);
} 
if (isset($_SESSION['new_uid'])) {
    unset($_SESSION['new_uid']);
}
if (isset($_SESSION['new_catid'])) {
    unset($_SESSION['new_catid']);
}

// unset sessions
SessionManager::clear_catalogue_order_process();

// display the footer
Display::display_footer();
?>