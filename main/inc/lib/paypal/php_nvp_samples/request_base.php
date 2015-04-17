<?php

// error_reporting(E_ALL);
header('Content-Type: text/plain');

require_once 'PayPal.php';
require_once 'PayPal/Profile/Handler/Array.php';
require_once 'PayPal/Profile/API.php';
require_once 'lib/constants.inc.php';

// Settings.
$certfile = dirname(__FILE__) . '/sdk-seller_cert_key_pem.txt';

$certpass = '';
$apiusername = 'sdk-seller_api1.sdk.com';
$apipassword = '12345678';
$subject = null;
$environment = ENVIRONMENT;


$handler =& ProfileHandler_Array::getInstance(array(
    'username' => $apiusername,
    'certificateFile' => $certfile,
    'subject' => $subject,
    'environment' => $environment));

$profile =& APIProfile::getInstance($apiusername, $handler);
$profile->setAPIUsername($apiusername);
$profile->setAPIPassword($apipassword);
$profile->setCertificateFile($certfile);
$profile->setSignature(null);
$profile->setEnvironment($environment);

$caller =& PayPal::getCallerServices($profile);

if (PayPal::isError($caller)) {
    print "Could not create CallerServices instance: ". $caller->getMessage();
    exit;
}
