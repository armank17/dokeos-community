<?php


// include the global Dokeos file
require("../inc/global.inc.php");

// including additional libraries
require_once 'invoice.lib.php';

//$session = array();
//InvoiceManager::generate_invoice($session);

InvoiceManager::generate_pdf_invoice();
?>
