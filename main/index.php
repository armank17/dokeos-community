<?php

// incluimos el autoload para las instancias
require_once dirname(__FILE__) . '/appcore/require/autoload.php';
//////////////////////////////////////////////
require_once dirname(__FILE__) . '/appcore/library/adodb5/adodb.inc.php';
require_once dirname(__FILE__) . '/appcore/library/adodb5/adodb-active-record.inc.php';
require_once dirname(__FILE__) . '/appcore/library/adodb5/adodb-exceptions.inc.php';
//constantes
require_once dirname(__FILE__) . '/appcore/require/constants.php';
require_once dirname(__FILE__) . '/inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'image.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'mail.lib.inc.php';

$connection = appcore_db_DB::conn();
$connection->debug = 0;
appcore_controller_Controller::run();