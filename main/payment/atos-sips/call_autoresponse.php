<?php
// name of the language file that needs to be included
$language_file = array ('registration','admin','group');

require_once '../../inc/global.inc.php';
require_once 'load_pathfile.inc.php';
$libpath = api_get_path(LIBRARY_PATH);
require_once $libpath.'sessionmanager.lib.php';
require_once $libpath.'usermanager.lib.php';
require_once $libpath.'fileUpload.lib.php';

// Récupération de la variable cryptée DATA
$message="message={$_POST['DATA']}"; 

// Initialisation du chemin du fichier pathfile (à modifier)
$pathfile="pathfile=".api_get_path(SYS_CODE_PATH)."payment/atos-sips/param/pathfile";

//Initialisation du chemin de l'executable response (à modifier)
$path_bin = api_get_path(SYS_CODE_PATH)."payment/atos-sips/bin/response";

// Appel du binaire response
$result=exec("$path_bin $pathfile $message");

// on separe les differents champs et on les met dans une variable tableau
$tableau = explode ("!", $result);

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
$complementary_info= $tableau[20];
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


// Initialisation du chemin du fichier de log (à modifier)
$logfile = api_get_path(SYS_CODE_PATH)."payment/atos-sips/log/logfile.txt";

// Ouverture du fichier de log en append
$fp=fopen($logfile, "a");

//  analyse du code retour
if (($code == "") && ($error == "" )) {
    fwrite($fp, "erreur appel response\n");
    print ("executable response non trouve $path_bin\n");
}
// Erreur, sauvegarde le message d'erreur
else if ( $code != 0 ) {
    fwrite($fp, " API call error.\n");
    fwrite($fp, "Error message :  $error\n");
}
// Erreur, affiche le message d'erreur
else if ($response_code !== '00') {
	fwrite($fp, " Authorization refused.\n");
}
// OK, Sauvegarde des champs de la réponse
else {	  
		
	$values =  unserialize(base64_decode($caddie));	
    // first save registered user
    $user_id = api_get_user_id();
    if (isset($values['user_info'])) {        
        $lastname = $values['user_info']['lastname'];
        $firstname = $values['user_info']['firstname'];
        $status = 5;
        $hash = api_generate_password(3);        
        $part1 = $firstname[0];
        $exp_lname = explode(' ', $lastname);
        $part2 = (is_array($exp_lname) && count($exp_lname) > 1)?$exp_lname[0]:$lastname;        
        $genera_uname = strtolower($part1.$part2.$hash);                
		$genera_uname=replace_accents($genera_uname);	
        $username = $genera_uname;
        $email = $values['user_info']['email'];
		if (empty($values['user_info']['user_id'])) {
				$user_id = UserManager::create_user($firstname, $lastname, $status, $email, $username);
		} else {
				$user_id = $values['user_info']['user_id'];
				UserManager::update_user($user_id, $firstname, $lastname, $username);
		}
		$extras = array();
		foreach($values['user_info'] as $key => $value) {
				if (substr($key, 0, 6) == 'extra_') { //an extra field
					$myres = UserManager::update_extra_field_value($user_id, substr($key, 6), $value);
				}
		}
    }
        
    // save payer
    if (isset($values['payer_info'])) {
        $values['payer_info']['student_id'] = $user_id;
        $payer_id = SessionManager::save_payer_user($values['payer_info']);
    }
        
    // save payment
    $from = isset($values['from'])?$values['from']:'';
    $params = array(
                'user_id' => $user_id,
                'sess_id' => intval($values['cat_id']),
                'pay_type' => intval($values['pay_type']),
                'pay_data' => $result,
                'from' => $from
              );
    if ($user_id) {
        $saved = SessionManager::save_payment_atos($params);    
    }
    
    if ($saved) {
        // register selected courses and sessions
        if (isset($values['selected_sessions']) && isset($values['cat_id']) && isset($values['cours_rel_session'])) {
            SessionManager::register_user_to_selected_courses_session($values['cours_rel_session'], $user_id, $values['selected_sessions'], $values['cat_id']);
        }
        //$_SESSION['new_uid']    = $user_id;
        //$_SESSION['new_catid']  = $values['cat_id'];
        $tbl_payment_atos = Database::get_main_table(TABLE_MAIN_PAYMENT_ATOS);        
        Database::query("UPDATE $tbl_payment_atos SET transaction_id=$transaction_id WHERE user_id=$user_id AND sess_id={$values['cat_id']}");
    }    
    
}
fclose ($fp);
// unset sessions
//SessionManager::clear_catalogue_order_process();
?>
