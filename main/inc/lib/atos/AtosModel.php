<?php

require_once dirname(__FILE__) . '/../../global.inc.php';
// including additional libraries
require_once api_get_path(LIBRARY_PATH) . 'sessionmanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'language.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH) . 'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'mail.lib.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'course.lib.php';

/**
 * Description of AtosModel
 *
 * @author wlopez
 */
class AtosModel {

   protected $_response;
   protected $_status = FALSE;
   protected $_resArray = array();
   protected $_mySession = array();
   protected $_product = NULL;
   protected $_user = NULL;
   protected $_userId = NULL;

   const ATOS_TYPE_ONLINE = 1;

   const ATOS_TYPE_CHEQUE = 2;

   public static function create() {
      return new AtosModel();
   }

   public function setProduct($product) {
      $this->_product = $product;
   }

   public function setResponse($response) {
      $this->_response = $response;
   }

   public function getStatus() {
      return $this->_status;
   }

   public function getUserId() {
      return $this->_userId;
   }

   public function createParam($session, $request) {
      // Affectation des paramètres obligatoires
      $objEcommerce = new EcommerceAtos();
      $setting = $objEcommerce->getGatewaySettings();
      $cost = $session["shopping_cart"]['total'];
      $cost = !empty($cost)?($cost*100):'000';    
      $id = $setting['keyapi']->value;
      $parm = "merchant_id=".$id;
      $parm = "$parm merchant_country=fr";
      $parm = "$parm amount=$cost";
      $currency_code = api_get_setting('e_commerce_catalog_currency');
      $parm = "$parm currency_code=$currency_code";
      //$parm = "$parm currency_code={$cat['currency']}";

      $parm = "$parm payment_means=CB,2,VISA,2,MASTERCARD,2";
      $parm = "$parm header_flag=yes"; // (yes/no)
      //var_dump($session);exit;
      $data = array();
      $user_info = $session['student_info'];
      
      if (isset($_SESSION['student_info'])) {
         $data['user_info'] = $user_info;
      }
      /*if (isset($_SESSION['payer_info'])) {
         $data['payer_info'] = $_SESSION['payer_info'];
      }*/
      /*if (isset($_SESSION['cat_id'])) {
         $data['cat_id'] = $_SESSION['cat_id'];
      }*/
      $data['pay_type'] = $request['txtPaymentType'];
      $session['pay_type'] = $data['pay_type'];
      /*if (isset($_SESSION['selected_sessions'])) {
         $data['selected_sessions'] = $_SESSION['selected_sessions'];
      }*/
      /*if (isset($_SESSION['cours_rel_session'])) {
         $data['cours_rel_session'] = $_SESSION['cours_rel_session'];
      }*/
      $data = base64_encode(serialize($data));
      $parm = "$parm caddie=$data";

      //$parm="$parm return_context=";
      // Initialisation du chemin du fichier pathfile (à modifier)
      $parm = "$parm pathfile=" . api_get_path(SYS_CODE_PATH) . "payment/atos-sips/param/pathfile";

      // URL de retour suite a paiement accepte
      $parm = "$parm normal_return_url=" . api_get_path(WEB_CODE_PATH) . "payment/atos-sips/call_response.php";
      //$parm="$parm normal_return_url=".api_get_path(WEB_CODE_PATH)."payment/atos-sips/call_autoresponse.php";
      // URL de traitement d'un paiement refuse
      $href = api_get_path(WEB_CODE_PATH) . "payment/checkout_3_registration.php?next=3";
      $parm = "$parm cancel_return_url=" . $href;

      /* Extra params */
      // $parm="$parm language=fr";   
      // $parm="$parm capture_day=";
      // $parm="$parm capture_mode=";
      // $parm="$parm bgcolor=";
      // $parm="$parm block_align=";
      // $parm="$parm block_order=";
      // $parm="$parm textcolor=";
      // $parm="$parm receipt_complement=";
      // $parm="$parm caddie=";
      // $parm="$parm customer_id=";
      // $parm="$parm customer_email=";
      // $parm="$parm customer_ip_address=";
      //$parm="$parm data=1!2!3";      
      // $parm="$parm target=";
      //$parm="$parm order_id=123";
      // Les valeurs suivantes ne sont utilisables qu'en pré-production
      // Elles nécessitent l'installation de vos fichiers sur le serveur de paiement
      // $parm="$parm normal_return_logo=";
      // $parm="$parm cancel_return_logo=";
      // $parm="$parm submit_logo=";
      // $parm="$parm logo_id=";
      // $parm="$parm logo_id2=";
      // $parm="$parm advert=";
      // $parm="$parm background_id=";
      // $parm="$parm templatefile=";
      // Initialisation du chemin de l'executable response (à modifier)
      $path_bin = api_get_path(SYS_CODE_PATH) . "payment/atos-sips/bin/request";
      
      return "$path_bin $parm";

   }

   public function processParam($param){
      // Appel du binaire request
      $result = exec("$param");
      // On separe les differents champs et on les met dans une variable tableau
      $tableau = explode("!", "$result");
      return $tableau;
   }
   public function processResponse($request){
      $response = array ();
      // Récupération de la variable cryptée DATA
      $message = "message={$request['DATA']}";
      // Initialisation du chemin du fichier pathfile (à modifier)
      $pathfile = "pathfile=" . api_get_path(SYS_CODE_PATH) . "payment/atos-sips/param/pathfile";
      // Initialisation du chemin de l'executable response (à modifier)
      $path_bin = api_get_path(SYS_CODE_PATH) . "payment/atos-sips/bin/response";
      // Appel du binaire response
      $result = exec("$path_bin $pathfile $message");
      // on separe les differents champs et on les met dans une variable tableau
      $this->_resArray = explode("!", $result);
      //var_dump($this->_resArray);exit;
      $code = $this->_resArray[1];
      $error = $this->_resArray[2];
      $amount = $_SESSION["shopping_cart"]['total'];
      $transaction_id = $this->_resArray[6];
      $payment_time = $this->_resArray[9];
      $payment_date = $this->_resArray[10];
      //var_dump(strtotime($payment_date));exit;
      $inttime = strtotime($payment_date);
      //var_dump(date('d/M/Y',$inttime));exit;
      $this->_resArray['AMT'] = $amount;
      $this->_resArray['TIMESTAMP'] = date('d/M/Y',$inttime);
      if (($code == "") && ($error == "")) {  
         $response['completed'] = FALSE;
         $response['transactionId'] = 0;
         $response['error_option'] = 1;
         $response['message'] = $error;
      }
      // Erreur, affiche le message d'erreur
      else if ($code != 0) {
         $response['completed'] = FALSE;
         $response['transactionId'] = 0;
         $response['error_option'] = 2;
         $response['message'] = $error;
      } else {
         $response['completed'] = TRUE;
         $response['transactionId'] = $transaction_id;
         $this->_resArray['TRANSACTIONID'] = $transaction_id;
         $response['message'] = "SUCCESS";
      }
      $response['details'] = $this->_resArray;
      return $response;
   }
   public function processForm($session, $request, $post = null) {
      $response = array();

      $param = $this->createParam($session, $request);

      $this->_resArray = $this->processParam($param);
      /*$code = $this->_resArray[1];
      $error = $this->_resArray[2];
      $message = $this->_resArray[3];*/
      /*if ($code == "" && $error == "") {
         $this->_status = FALSE;
         $response['completed'] = FALSE;
         $response['transactionId'] = 0;
         $response['message'] = $ack;
      } 
      else if ($code == "" && $error == "") {
         $this->_status = TRUE;
         $response['completed'] = TRUE;
         $response['transactionId'] = $this->_resArray['TRANSACTIONID'];
         $response['message'] = $ack;
      } else {
         $response['completed'] = FALSE;
         $response['transactionId'] = 0;
         $response['message'] = $this->_resArray['L_LONGMESSAGE0'];
         ;
      }*/

      $response['details'] = $this->_resArray;

      return $response;
   }

}

?>
