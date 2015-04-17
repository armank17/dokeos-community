<?php
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceInterface.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceAbstract.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/user/UserModel.php';
require_once api_get_path( SYS_PATH ) . 'main/inc/lib/paypal/PaypalModel.php';


if ( ! class_exists( 'EcommercePaypal' ) )
{    
    class EcommercePaypal extends EcommerceAbstract implements EcommerceInterface
    {
        /*
         * (non-PHPdoc) @see EcommerceInterface::getForm()
         */
        public function getForm()
        {
            $form = new FormValidator( 'frmEcommerce' );
            
            $type_url = array();
            $type_url[] = FormValidator::createElement('radio', null, null, get_lang('PaypalProduction'), '1');
            $type_url[] = FormValidator::createElement('radio', null, null, get_lang('PaypalTest'), '0');
            $form->addGroup($type_url, 'radType', get_lang('Workspace'));
            $form->addElement('text', 'txtEmail', get_lang( 'PaypalEmail' ), array ('size' => 40 ));
            $form->addElement('text', 'txtCatalog', get_lang( 'CatalogName' ), array ('size' => 40 ));
            $form->addElement('textarea', 'txtSuccessBuy', get_lang('txtSuccessBuy'),array('cols'=>52,'rows'=>6));
            $form->addElement('textarea', 'txtSuccessBuyUser', get_lang('txtSuccessBuyUser'),array('cols'=>52,'rows'=>6));
            $form->addElement('textarea', 'txtUnsuccessBuyUser', get_lang('txtUnsuccessBuy'),array('cols'=>52,'rows'=>6));

//            $form->addElement( 'text', 'txtUserName', 'API ' . get_lang( 'UserName' ), array (
//                'size' => 40 ) );
//            $form->addElement( 'text', 'txtPassword', 'API ' . get_lang( 'Password' ), array (
//                'size' => 40 ) );
//            $form->addElement( 'text', 'txtSignature', 'API ' . get_lang( 'Signature' ), array (
//                'size' => 40 ) );
//            $form->addRule( 'txtUserName', get_lang( 'ThisFieldIsRequired' ), 'required' );
//            $form->addRule( 'txtPassword', get_lang( 'ThisFieldIsRequired' ), 'required' );
            $form->addElement( 'style_submit_button', 'submit', get_lang( 'Save' ), array (
                'class' => 'save' ) );
            
            $this->_gatewayDetailValues = $this->getGatewaySettings();
            
            $defaults['radType'] = intval($this->_gatewayDetailValues['workspace']->value);
            $defaults['txtEmail'] = $this->_gatewayDetailValues['email']->value;
            $defaults['txtCatalog'] = $this->_gatewayDetailValues['catalog']->value;
            $defaults['txtSuccessBuy'] = $this->_gatewayDetailValues['successbuy']->value;
            $defaults['txtSuccessBuyUser'] = $this->_gatewayDetailValues['successbuyuser']->value;
            $defaults['txtUnsuccessBuyUser'] = $this->_gatewayDetailValues['unsuccessbuy']->value;
//            $defaults['txtUserName'] = $this->_gatewayDetailValues['username']->value;
//            $defaults['txtPassword'] = $this->_gatewayDetailValues['password']->value;
//            $defaults['txtSignature'] = $this->_gatewayDetailValues['signature']->value;
            $form->setDefaults( $defaults );
            
            return $form;
        }
        
        /*
         * (non-PHPdoc) @see EcommerceInterface::save()
         */
        public function save( array $post, array $files )
        {
            $paypalValues['workspace'] = intval( $post['radType'] );
            $paypalValues['email'] = trim( $post['txtEmail'] );
            $paypalValues['catalog'] = trim( $post['txtCatalog'] );
            $paypalValues['successbuy'] = trim( $post['txtSuccessBuy'] );
            $paypalValues['successbuyuser'] = trim( $post['txtSuccessBuyUser'] );
            $paypalValues['unsuccessbuy'] = trim( $post['txtUnsuccessBuyUser'] );
//            $paypalValues['username'] = trim( $post['txtUserName'] );
//            $paypalValues['password'] = trim( $post['txtPassword'] );
//            $paypalValues['signature'] = trim( $post['txtSignature'] );
            $idGateway = parent::getGateway();
            
            parent::saveDataPaymentGateway( $paypalValues );
        }
        
        public function proccessPayment( $request )
        {
            $response = array ();            
            $objPaypal = new PaypalModel();
            $product = count( $_SESSION['shopping_cart']['items'] );
            $objPaypal->setProduct( $product );            
            $response = $objPaypal->processForm( $_SESSION, $request );
            
            return $response;
        }    
    }
}