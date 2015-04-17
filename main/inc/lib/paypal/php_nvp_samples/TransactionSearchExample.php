<?php

include 'ppsdk_include_path.inc';

require_once 'PayPal.php';
require_once 'PayPal/Profile/API.php';
require_once 'PayPal/Profile/Handler.php';
require_once 'PayPal/Profile/Handler/Array.php';

require_once 'request_base.php';


$trans_search =& PayPal::getType('TransactionSearchRequestType');

$trans_search->setStartDate(date('Y-m-d') . 'T00:00:00-0700');

$response = $caller->TransactionSearch($trans_search);

$ack = $response->getAck();

echo "RESULT: $ack\n";
var_dump($response);

?>

