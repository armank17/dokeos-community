<?php

/**
 * PHP SDK Credentials page
 * 
 * $Id: index.php,v 1.18 2006/03/17 08:01:20 dennis Exp $
 * 
 */

include 'ppsdk_include_path.inc';

require_once 'PayPal.php';
require_once 'PayPal/Profile/API.php';
require_once 'PayPal/Profile/Handler.php';
require_once 'PayPal/Profile/Handler/Array.php';
require_once 'lib/api_form_validators.inc.php';
require_once 'lib/functions.inc.php';
require_once 'lib/constants.inc.php';
require_once 'SampleLogger.php';

session_start();

$dummy= @new APIProfile();
$environments = $dummy->getValidEnvironments();

$was_submitted = false;

$logger = new SampleLogger('WebPaymentPro.php', PEAR_LOG_DEBUG);
$logger->_log(print_r($_POST, true));

if(isset($_POST['submitted']))
{
   $logger->_log('submitted');
   unset($_POST['submitted']);
   
   $handler = & ProfileHandler_Array::getInstance(array(
            'username' => $_POST['apiUsername'],
            'certificateFile' => null,
            'subject' => null,
            'environment' => ENVIRONMENT ));
            
   $pid = ProfileHandler::generateID();
   
   $profile =  new APIProfile($pid, $handler);
   $logger->_log('Profile: '. print_r($profile, true));
   
   $save_file;
   
   if(isset($_FILES['certFile'])) {
      
      // Use my account   
      if(!file_exists($_FILES['certFile']['tmp_name'])) {
         $errors['certFile'] = "You must provide a Certificate for the profile";
      } else {
         if(!is_uploaded_file($_FILES['certFile']['tmp_name'])) {
             $errors['certFile'] = "Invalid file upload, cannot save profile";
         }
      }
      // Defined in lib/functions.inc.php 
      $cert_save_path = _getProfileCertSavePath();
      $logger->_log('cert_save_path is '. $cert_save_path);
      // $logger->_log('Check errors array: '. print_r($errors, true));
      
      if(empty($errors)) {
         // Keep going
         $save_file = "$cert_save_path/$pid.cert";            
         if(!move_uploaded_file($_FILES['certFile']['tmp_name'],
                                $save_file))
         {
             $errors['unknown'][] = "Could not store uploaded certificate '{$_FILES['certFile']['tmp_name']}'";
         }
         
      }
    }
   
    if(array_key_exists('DefaultButton', $_POST) ) { 
      
       // Use Default sdk-seller account
       
      // Determine $cert_file
      $doc_root=$_SERVER['DOCUMENT_ROOT'];
      $path_parts = pathinfo($_SERVER['SCRIPT_NAME']);
      $path_info = $path_parts['dirname'];
      $cert_file_path = $doc_root . $path_info . '/sdk-seller_cert_key_pem.txt';
      
   	$logger->_log('sdk-seller cert_file_path is '. $cert_file_path);
   	

   	/**
   	 *                    W A R N I N G
   	 * Do not embed plaintext credentials in your application code.
   	 * Doing so is insecure and against best practices.
   	 * 
   	 * Your API credentials must be handled securely. Please consider
   	 * encrypting them for use in any production environment, and ensure
   	 * hat only authorized individuals may view or modify them.
   	 */
      
      // Use default sdk-seller account
      $profile->setAPIUsername('sdk-seller_api1.sdk.com');
      $profile->setAPIPassword('12345678');
      $profile->setSignature(null); 
      $profile->setCertificateFile($cert_file_path);
	$profile->setSignature($_POST['signature']); 
      $profile->setEnvironment(ENVIRONMENT);         
      $logger->_log('Profile: '. print_r($profile, true));
   
    
   } else {
      
      // Use My Account
      // Either with certificate file or 3-token
      
      // Validate posted variables
      $errors = validate_form_input($_POST);
      if(!empty($_REQUEST['authSignature']))
      {
      	$profile->setAuthSignature($_REQUEST['authSignature']);
      	$profile->setAuthToken($_REQUEST['authToken']);
      	$profile->setAuthTimestamp($_REQUEST['authTimesStamp']);
      } 
      else
      {
      
      $profile->setAPIUsername($_POST['apiUsername']);
      $profile->setAPIPassword($_POST['apiPassword']);
      $profile->setSignature($_POST['signature']);
      $profile->setSubject($_POST['subjectEmail']); 
      
       } 
    
      if(isset($save_file))
         $profile->setCertificateFile($save_file); 
      
      $profile->setEnvironment(ENVIRONMENT);         
      $logger->_log('Profile: '. print_r($profile, true));
         
   }
   
   $caller =& PayPal::getCallerServices($profile);
   // $logger->_log('caller: '. print_r($caller, true)); 
   
   // Save our profile to the session
   $_SESSION['APIProfile'] = $profile;
   $_SESSION['caller'] = $caller;
         
   $logger->_log('forward to Calls.html');
   
   $location = 'Calls.html';
   header("Location: $location");

}  // submitted form

require_once "pages/WebPaymentPro.html.php";

?>
