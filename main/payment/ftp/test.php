<?php
require_once '/var/www/www-dila/main/inc/global.inc.php';
require_once '/var/www/www-dila/main/inc/lib/ftp_manager.lib.php';

$host = api_get_setting('ftp_server');
$username = api_get_setting('ftp_user');
$password = api_get_setting('ftp_password');
$port = 21;
$pasv = true;
$ftp_stream = FtpManager::connect($host, $port);
$rs_l = FtpManager::login($ftp_stream, $username, $password);
FtpManager::pasv_connection($ftp_stream, $pasv);
//$remote_path = FtpManager::get_ftp_path($ftp_stream);
$curr_date = date('Y-m-d');
$filename = 'harmony_'.$curr_date;
$local_file = api_get_path(SYS_CODE_PATH).'payment/csv/'.$filename.'.csv';

//$local_file = api_get_path(SYS_PATH).'main/payment/csv/harmony_2011-07-25.csv';
$remote_file = basename($local_file);
$rs_p = FtpManager::put_file($ftp_stream, $local_file, $remote_file);
