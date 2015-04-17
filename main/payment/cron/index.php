<?php
require_once '/var/www/www-dila/main/inc/global.inc.php';
// including additional libraries
require_once '/var/www/www-dila/main/inc/lib/mail.lib.inc.php';
$recipient_nameapi_mail_html = "Michele";
$mailid = "jacques.archimede@dila.gouv.fr";
//$mailid = "isaac.flores@dokeos.com";
$time = strtotime("-1 day");
$curr_date = date('Y-m-d H:i:s', $time);
$emailSubject = "Harmony CSV file to ".$curr_date;
$mail_body = "See attachment file.";
$sender_name = "Dokeos task system";
$sender_email = api_get_setting('emailAdministrator');

 
$curr_date = date('d.m.Y', $time);
//$curr_date = date('d.m.Y');

$filename = 'learning.cb_valides_'.$curr_date;
$data_file_path = api_get_path(SYS_CODE_PATH).'payment/csv/'.$filename;
if (!is_file($data_file_path)) {
  $mail_body = "There is not attachment file";
}

$data_file = array('path' => $data_file_path,
                   'filename' => $filename);
$header = array ('Cc'=>'isaac.flores@dokeos.com;gmoestar@dokeos.com');
api_mail_html($recipient_nameapi_mail_html, $mailid, stripslashes($emailSubject), $mail_body, $sender_name, $sender_email, $header, $data_file);
?>
