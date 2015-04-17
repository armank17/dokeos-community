<?php

$cidReset = TRUE;
require_once dirname(__FILE__) . '/../../../../main/inc/global.inc.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceFactory.php';
require_once api_get_path(SYS_PATH) . 'main/core/dao/ecommerce/EcommerceCourseDao.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/ShoppingCartModel.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/CatalogueModel.php';

$request = $_REQUEST;

$scController = new ShoppingCartController();

switch ($request['action']) {
    case 'addItem' :
        $scController->addItemToShoppingCart($request);
        echo $scController->getShoppingCartHtml();
        break;
    case 'getShoppingCartHtml' :
        $scController->getShoppingCartHtml();
        break;
    case 'removeItem' :
        $scController->removeItemShoppingCartHtml($request);
        echo $scController->getShoppingCartHtml();
        break;
}

class CatalogueController {

    public static function create() {
        return new CatalogueController();
    }

    public function getActiveCatalogPaymentOptions() {
        $response = '';
        $payment_method = api_get_setting('e_commerce_payment_method');
        if ($_SESSION['shopping_cart']['chr_type'] != '0') {
            if (!empty($payment_method)) {
                if ($payment_method['online'] == 'true') {
                    $response .= '<div class="payment-methods"><div class="credit-card">' . Display::return_icon('credit_card.png', '', array(
                                'onclick' => 'callPayment(1)', 'style' => 'cursor:pointer;')) . '</div><br />' . get_lang('CreditCardPayment') . '</div>';
                }
                if ($payment_method['cheque'] == 'true') {

                    $response .= '<div class="payment-methods">' . Display::return_icon('cheque.png', '', array(
                                'onclick' => 'callPayment(2)', 'style' => 'cursor:pointer;')) . '<br />' . get_lang('Cheque') . '</div>';
                }/*
                  if ($payment_method['installment']=='true')
                  {
                  $response .= '<div class="payment-methods">' . Display::return_icon( 'installments.png', '', array (
                  'onclick' => 'callPayment(3)', 'style' => 'cursor:pointer;' ) ) . '<br />' . get_lang( 'TransferIn3Installments' ) . '</div>';
                  } */
            }
        } else {
            $personal_data = api_get_user_info(api_get_user_id());
            $response .= '<div class="" style="height:auto;margin-top:-40px;width:400px;margin-left:5px;">';
            $response .= '<br>';
            $response .= '<p style="text-align:left;"><strong>' . get_lang("FirstName") . ' : </strong>' . $personal_data['firstname'] . '</p>';
            $response .= '<p style="text-align:left;"><strong>' . get_lang("LastName") . ' : </strong>' . $personal_data['lastname'] . '</p>';
            $response .= '<p style="text-align:left;"><strong>' . get_lang("LoginName") . ' : </strong>' . $personal_data['username'] . '</p>';
            $response .= '<p style="text-align:left;"><strong>' . get_lang("email") . ' : </strong>' . $personal_data['mail'] . '</p>';
            $response .= '</div>';
            $response .= '<table class="data_table">
                <tr>
                    <th>' . get_lang(ucfirst($_SESSION['shopping_cart']['item_type'])) . '</th>
                    <th width="75px">' . get_lang('Access') . '</th>
                </tr>';
            foreach ($_SESSION['shopping_cart']['items'] as $item) {
                $_SESSION['IdShopCourse'] = $item['code'];
                $response .= '<trclass="row_odd">
                        <td style="text-align:left;padding-left:8px;">' . $item['name'] . '</td>
                        <td style="text-align:center;">' . $item['duration'] . '  ' . ucfirst(($item['duration'] <= 1) ? (get_lang($item['duration_type'])) : (get_lang($item['duration_type']))) . '</td>
                    </tr>';
            }
            $response .= '</table>';
        }
//        $response .= '<div class="payment-methods">'.Display::return_icon('cancel.png','', array('id'=>'cancell_button_id','onclick' => 'callPayment(4)', 'style'=>'cursor:pointer;')).'<br />'.get_lang('CancelOrder').'</div>';//
        $response .= '<div class="pull-bottom"><div style=""><button class="save" >' . get_lang('Submit') . '</button></div>';
        $response .= '<div style=""><button class="freshbutton-blue" onclick="callPayment(4)">' . get_lang('CancelOrder') . '</button></div>';
        $response .= '</div></div>';

        return $response;
    }

}
