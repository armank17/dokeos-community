<?php
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
class EcommerceInvoice {

    public $currentValue = '';
    public $optionValues = array();

    public static function create() {
        return new EcommerceInvoice();
    }

    public function getInvoiceEcommerceData($from = 0, $number_of_items = 100, $column = 4, $direction = ' DESC ', $get = array()) {
        global $_configuration;        
        $invoices = array();

        $tableInvoice = Database::get_main_table(TABLE_MAIN_INVOICE);
        $tableUser = Database::get_main_table(TABLE_MAIN_USER);

        $sql = "SELECT i.id AS col0, i.id AS col1, u.firstname AS col2, u.lastname AS col3, i.products AS col4, i.date AS col5, i.invoice AS col6, i.full_path AS col7, u.user_id as col8 
                FROM {$tableInvoice} AS i  
                INNER JOIN {$tableUser} AS u ON (i.user_id = u.user_id)";
        $sql .= " ORDER BY col{$column} {$direction} ";
        $sql .= " LIMIT {$from}, {$number_of_items} ";

        $res = Database::query($sql, __FILE__, __LINE__);

        $invoice = TRUE;
        while ($invoice) {
            $invoice = Database::fetch_row($res);

            if ($invoice !== FALSE) {
				$extra = UserManager::get_extra_user_data($invoice[8]);								
                $invoice[1] = $extra['organization'];
                $invoice[5] = date('d-m-Y', strtotime($invoice[5]));
                $invoice[6] = '<a target="_blank" href="' . $invoice[7] . '">'.Display::return_icon('pixel.gif', get_lang('Download'), array('class' => 'fckactionplaceholdericon actiondocpdf')).'</a>';
                $invoice_rem = array(
                    $invoice[0], $invoice[1], $invoice[2], $invoice[3], $invoice[4], $invoice[5], $invoice[6]);

                $invoices[] = $invoice_rem;
            }
        }

        return $invoices;
    }

    public function getTotalNumberInvoicerEcommerce() {
        global $_configuration;
        $tableInvoice = Database::get_main_table(TABLE_MAIN_INVOICE);

        $sql = "SELECT COUNT(*) AS total FROM $tableInvoice i ";

        $res = Database::query($sql, __FILE__, __LINE__);
        $invoice = Database::fetch_row($res);
        return intval($invoice[0], 10);
    }

}
