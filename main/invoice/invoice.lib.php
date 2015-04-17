<?php

//require_once api_get_path(LIBRARY_PATH).'ezpdf/Cezpdf.php';
require_once api_get_path(LIBRARY_PATH) . 'html2pdf/html2pdf.class.php';

Class InvoiceManager {

    public static function generate_pdf_invoice() {
        $path_archive_invoice = api_get_path(SYS_PATH) . 'main/upload/invoice/';
        $url_archive_invoice = api_get_path(WEB_PATH) . 'main/upload/invoice/';
        if (api_get_setting('enable_invoice') === 'true') {
            $p_invoice = $_SESSION['paypal_invoice'];
            $invoice_table = Database::get_main_table(TABLE_MAIN_INVOICE);

            $user_info = api_get_user_info();
            if (empty($user_info))
                $user_info = api_get_user_info_by_email($_SESSION['student_info']['email']);

            $user_info_extra = api_get_user_info_extra($user_info['user_id']);
            if (empty($user_info_extra['company']))
                $user_info_extra['company'] = $_SESSION['student_info']['company'];
            if (empty($user_info_extra['extra_tva_id']))
                $user_info_extra['extra_tva_id'] = $_SESSION['student_info']['extra_tva_id'];
            if (!opendir(api_get_path(SYS_PATH) . 'archive/invoice/'))
                if (!opendir($path_archive_invoice))
                    mkdir($path_archive_invoice, 0777);
            $curr_date = date('YmdHi');
            $filename = 'invoice_' . $user_info['user_id'] . '_' . $curr_date . '.pdf';
            $data_file_path = api_get_path(SYS_PATH) . 'archive/invoice/' . $filename;
            $full_path = api_get_path(WEB_PATH) . 'archive/invoice/' . $filename;
            $data_file_path = $path_archive_invoice . $filename;
            $full_path = $url_archive_invoice . $filename;

            if ($user_info['email'] !== $p_invoice['payer_email'])
                $user_info['email'] = $p_invoice['payer_email'] . "<br>({$user_info['email']})";

            $web_path = api_get_path(WEB_PATH) . 'archive/invoice/';
            $web_path = $url_archive_invoice;
            $logo = api_get_settings_options('invoiceLogo');
//            $company = api_get_settings_options('companyName');
            $company_address = api_get_settings_options('companyAddress');
            $bank = api_get_settings_options('invoiceBank');
            $aditionalInfo = api_get_settings_options('invoiceAdditionalInfo');
            $image = $web_path . $logo[0]['value'];
            $tax = api_get_setting('e_commerce_catalog_tax');

            $total_invoice = 0;
            $total_invoice_tax = 0;
            $items_paid = array();
            for ($i = 1; $i <= $p_invoice['num_cart_items']; $i++) {
                $items_paid[] = $p_invoice['item_number' . $i];
            }

            $items = '';
            $total_invoice_qty = 0;
            $total_invoice_tax_qty = 0;
            foreach ($_SESSION['items_paid'] as $item) {
                $total_invoice += $item['price'];
                $total_invoice_qty += $item['price'] * $item['quantity'];
                $total_invoice_tax += $item['price'] * (1 + ($tax / 100));
                $total_invoice_tax_qty += $item['price'] * (1 + ($tax / 100) * $item['quantity']);
                $items .= '
                        <tr>
                            <td>' . htmlentities($item['name'], ENT_QUOTES, 'ISO-8859-15', false) . '</td>
                            <td>' . $item['duration'] . ' ' . get_lang($item['duration_type'], 'DLTT', 'french') . '</td>';
                $items .= '<td align="center">' . $item['quantity'] . '</td>
                            <td align="center">' . api_number_format($tax) . ' %</td>
                            <td align="right">' . api_number_format($item['price'] * $item['quantity']) . '</td>
                            <td class="list_lr" align="right">' . api_number_format($item['price'] * (1 + ($tax / 100)) * $item['quantity'] * $item['quantity']) . '</td>
                        </tr>';
                $items_str .= $item['name'] . ', ';
            }
            $items_str = substr($items_str, 0, -2);
            $sql = "SELECT id FROM {$invoice_table} AS i ORDER BY id DESC LIMIT 1";
            $res = Database::query($sql);
            $row = Database::fetch_row($res);
            $number = date('Ymd') . '-' . str_pad((int) ($row[0]) + 1, 5, "0", STR_PAD_LEFT);
            $sql = "INSERT INTO {$invoice_table} SET user_id = {$user_info['user_id']}, invoice = '{$filename}', full_path = '{$full_path}', products = '{$items_str}', number = '{$number}'";
            //$sql = "INSERT INTO `invoice`(`id`, `user_id`, `number`, `invoice`, `products`, `full_path`, `date`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5],[value-6],[value-7])";
            Database::query($sql, __FILE__, __LINE__);
            ob_start();
            include(dirname(__FILE__) . '/invoice_board_pdf.inc.php');
            $contents = ob_get_clean();
            ob_end_clean();
            try {
                $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                $html2pdf->writeHTML($contents);
                $html2pdf->Output($data_file_path, 'F');
            } catch (HTML2PDF_exception $e) {
                echo $e;
            }
        }
        return true;
    }

}

?>
