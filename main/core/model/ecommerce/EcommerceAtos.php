<?php

require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceInterface.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceAbstract.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/user/UserModel.php';
require_once api_get_path(SYS_PATH) . 'main/inc/lib/atos/AtosModel.php';

class EcommerceAtos extends EcommerceAbstract implements EcommerceInterface {
    /*
     * (non-PHPdoc) @see EcommerceInterface::getForm()
     */

    public function getForm() {
        $form = new FormValidator('frmEcommerce');

        $form->add_textfield('api', get_lang('NumApi'), false);
        $form->addElement('file', 'flCertificate', 'API ' . get_lang('File'), array(
            'size' => 40));
        $form->addElement('checkbox', 'api_default', get_lang('UseApiDefault'), get_lang('Yes'));
        $form->addElement('style_submit_button', 'submit', get_lang('Save'), array(
            'class' => 'save'));
        $this->_gatewayDetailValues = $this->getGatewaySettings();
        $defaults['api'] = $this->_gatewayDetailValues['keyapi']->value;
        $defaults['api_default'] = 0;
        $form->setDefaults($defaults);
        return $form;
    }

    public function getFormHtml() {
        $html.='
        <div class="label divAtos" style="margin: 2px 5px 0pt 0pt;">' . get_lang('NumApi') . '</div>
        <div class="formw divAtos"><input type="text" name="api" size="30" value=""/></div>
        <div class="clear divAtos"></div>
        <div class="label divAtos" style="margin: 2px 5px 0pt 0pt;">' . get_lang('File') . '</div>
        <div class="formw divAtos"><input type="file" name="flCertificate" /></div>
        <div class="clear divAtos"></div>
        <div class="label" style="margin: 2px 5px 0pt 0pt;">' . get_lang('UseApiDefault') . '</div>
        <div class="formw"><input type="checkbox" id="check_api_default" name="api_default" value="1" /></div>
        <div class="clear"></div>';
        return $html;
    }

    /*
     * (non-PHPdoc) @see EcommerceInterface::save()
     */

    public function save(array $post, array $files) {
        if ($files['flCertificate']['size'] > 0 && !isset($post['api_default'])) {
            $uploadCertificateDir = api_get_path(SYS_CODE_PATH) . 'payment/atos-sips/param/';

            if (!isset($post['api_default'])) {
                $api_default = $post['api'];
                $file_tmp = $files['flCertificate']['tmp_name'];
                $destination = $uploadCertificateDir . $files['flCertificate']['name'];
            }/* else {
                $api_default = '011223344551111';
                $file_tmp = api_get_path(SYS_CODE_PATH) . 'payment/atos-sips/default/certif.fr.011223344551111';
                $destination = $uploadCertificateDir . 'certif.fr.011223344551111';
            }*/
            move_uploaded_file($file_tmp, $destination);

            $paypalValues['keyapi'] = Database::escape_string($api_default);
            $paypalValues['keyfile'] = Database::escape_string($destination);

            //copy file parcom
            copy($uploadCertificateDir . 'parmcom.defaut', $uploadCertificateDir . 'parmcom.' . $paypalValues['keyapi']);

            //delete old pathfile
            unlink($uploadCertificateDir . 'pathfile');
            //generate pathfile
            //copy($uploadCertificateDir.'pathfile.defaut', $uploadCertificateDir.'pathfile');
            $content = file_get_contents($uploadCertificateDir . 'pathfile.defaut');
            $content = str_replace('D_LOGO!!', 'D_LOGO!' . api_get_path(WEB_CODE_PATH) . 'payment/atos-sips/logo/!', $content);
            $content = str_replace('F_DEFAULT!!', 'F_DEFAULT!' . $uploadCertificateDir . 'parmcom.defaut!', $content);
            $content = str_replace('F_PARAM!!', 'F_PARAM!' . $uploadCertificateDir . 'parmcom!', $content);
            $content = str_replace('F_CERTIFICATE!!', 'F_CERTIFICATE!' . $uploadCertificateDir . 'certif!', $content);
            $fp = fopen($uploadCertificateDir . 'pathfile', 'w');
            fwrite($fp, $content);
            fclose($fp);

            $idGateway = parent::getGateway();
            parent::setGateway($post['e_commerce']);
            parent::saveDataPaymentGateway($paypalValues);
        } else {
            $uploadCertificateDir = api_get_path(SYS_CODE_PATH) . 'payment/atos-sips/param/';
            $api_default = '011223344551111';
            //$file_tmp = api_get_path(SYS_CODE_PATH) . 'payment/atos-sips/default/certif.fr.011223344551111';
            $destination = $uploadCertificateDir . 'certif.fr.011223344551111';

            //move_uploaded_file($file_tmp, $destination);

            $paypalValues['keyapi'] = Database::escape_string($api_default);
            $paypalValues['keyfile'] = Database::escape_string($destination);

            //copy file parcom
            copy($uploadCertificateDir . 'parmcom.defaut', $uploadCertificateDir . 'parmcom.' . $paypalValues['keyapi']);

            //delete old pathfile
            unlink($uploadCertificateDir . 'pathfile');
            //generate pathfile
            //copy($uploadCertificateDir.'pathfile.defaut', $uploadCertificateDir.'pathfile');
            $content = file_get_contents($uploadCertificateDir . 'pathfile.defaut');
            $content = str_replace('D_LOGO!!', 'D_LOGO!' . api_get_path(WEB_CODE_PATH) . 'payment/atos-sips/logo/!', $content);
            $content = str_replace('F_DEFAULT!!', 'F_DEFAULT!' . $uploadCertificateDir . 'parmcom.defaut!', $content);
            $content = str_replace('F_PARAM!!', 'F_PARAM!' . $uploadCertificateDir . 'parmcom!', $content);
            $content = str_replace('F_CERTIFICATE!!', 'F_CERTIFICATE!' . $uploadCertificateDir . 'certif!', $content);
            $fp = fopen($uploadCertificateDir . 'pathfile', 'w');
            fwrite($fp, $content);
            fclose($fp);

            //$idGateway = parent::getGateway();
            parent::setGateway($post['e_commerce']);
            parent::saveDataPaymentGateway($paypalValues);
        }
    }

    /*
     * (non-PHPdoc) @see EcommerceInterface::getData()
     */

    public function getData() {
        // TODO Auto-generated method stub
    }

    /* (non-PHPdoc)
     * @see EcommerceInterface::proccessPayment()
     */

    public function proccessPayment($request) {
        // TODO Auto-generated method stub
        $response = array();
        $objAtos = new AtosModel();
        $product = count($_SESSION['shopping_cart']['items']);
        $objAtos->setProduct($product);
        $response = $objAtos->processForm($_SESSION, $request);

        return $response;
    }

    public function processResponse($request) {
        // TODO Auto-generated method stub
        $response = array();
        $objAtos = new AtosModel();
        $product = count($_SESSION['shopping_cart']['items']);
        $objAtos->setProduct($product);
        $response = $objAtos->processResponse($request);

        return $response;
    }

}