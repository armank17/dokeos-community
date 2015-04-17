<?php
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceInterface.php';

abstract class EcommerceAbstract implements EcommerceInterface
{
    const E_COMMERCE_LIB_GATEWAY_NONE = 0;
    const E_COMMERCE_LIB_GATEWAY_ATOS = 1;
    const E_COMMERCE_LIB_GATEWAY_PAYPAL = 2;
    
    const E_COMMERCE_LIB_SETTING_NAME = 'e_commerce';
    
    protected $_gateway = NULL;
    protected $_enabled = FALSE; // boolean, to check if e_commerce is enabled
    protected $_gatewayData = NULL; // array containing all credentials for
    protected $_gatewayDetailValues= NULL;    
    
    public function __construct()
    {
        $this->getSettingsFromDb();
    }
    
    /**
     *
     * @return the $_gateway
     */
    public function getGateway()
    {
        if ( is_null( $this->_gateway ) )
        {
            $this->getSettingsFromDb();
        }
        
        return $this->_gateway;
    }
    
    /**
     *
     * @param $_gateway string           
     */
    public function setGateway( $_gateway )
    {
        $this->_gateway = $_gateway;
    }
    
    /**
     *
     * @return the $_enabled
     */
    public function isEnabled()
    {
        $this->getGateway();
        return $this->_enabled;
    }
    
    /**
     *
     * @param $_enabled boolean           
     */
    public function setEnabled( $_enabled )
    {
        $this->_enabled = $_enabled;
    }
    
    /**
     *
     * @return the $_gatewayData
     */
    public function getGatewayData()
    {
        return $this->_gatewayData;
    }
    
    public function getFormUrlPayment(){
       $url = "";
       if($this->_gateway == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_ATOS) {
          $url =  api_get_path(WEB_PATH)."main/payment/atos-sips/call_request.php";
       } else if($this->_gateway == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_PAYPAL){
          $url =  api_get_path(WEB_PATH)."main/payment/checkout_4_payment_data.php";
          
       } else if($this->_gateway == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_NONE){
          
       }
       return $url;
    }
    
    public function getUrlPayment(){
       $url = "";
       if($this->_gateway == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_ATOS) {
          $url =  api_get_path(WEB_PATH)."main/payment/checkout_3_registration.php?next=3&pay=atos&id=".$_REQUEST["id"];
       } else if($this->_gateway == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_PAYPAL){
          $url =  api_get_path(WEB_PATH)."main/admin/payment_options.php?iden=".$_SESSION['iden']."&wish=".$_SESSION['wish']."&id=".$_SESSION['cat_id']."&next=5";
          
       } else if($this->_gateway == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_NONE){
          
       }
       return $url;
    }
    /**
     *
     * @param $_gatewayData NULL           
     */
    public function setGatewayData( $_gatewayData )
    {
        $this->_gatewayData = $_gatewayData;
    }

	public function getSettingsFromDb()
    {
        $this->setGateway( intval( api_get_setting( EcommerceAbstract::E_COMMERCE_LIB_SETTING_NAME ), 10 ) );
        $gatewayData = api_get_settings_options( EcommerceAbstract::E_COMMERCE_LIB_SETTING_NAME, $this->getGateway() );
        $this->setGatewayData( $gatewayData [0] );
        $isEnabled = ($this->getGateway() == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_NONE) ? FALSE : TRUE;
        $this->setEnabled( $isEnabled );
    }
    
    public function saveDataPaymentGateway( array $values )
    {

        $position = 1;
        foreach ( $values as $key => $value )
        {
            $this->saveGatewaySettingsInDb( $this->getGateway(), $key, $value ,$position);
            $position++;
        }
    }
    public function getGatewaySettings()
    {
        $gateway = intval ( $this->getGateway() , 10 );
        $table_payment_settings = Database::get_main_table( TABLE_MAIN_PAYMENT_SETTINGS );
        $sql = "SELECT * FROM $table_payment_settings WHERE gateway_id= '$gateway'";
        $result = Database::query( $sql, __FILE__, __LINE__ );
        $this->_gatewayDetailValues = array();
        while ( $row = Database::fetch_object( $result ) )
        {
            $this->_gatewayDetailValues[$row->name] = $row ;
        }
        
        return $this->_gatewayDetailValues ;
    }
    
    protected function saveGatewaySettingsInDb( $gateway, $valueName, $value , $position )
    {        
        $table_payment_settings = Database::get_main_table( TABLE_MAIN_PAYMENT_SETTINGS );
        $sql = "SELECT * FROM $table_payment_settings WHERE name='$valueName' and gateway_id= '$gateway' LIMIT 1";
        
        $result = Database::query( $sql, __FILE__, __LINE__ );
        
        $row = Database::fetch_row( $result );
        
        if ( $row === FALSE )
        {
            $sql = "INSERT INTO $table_payment_settings(name,value, position,gateway_id) VALUES('$valueName','$value','$position' ,'$gateway')";
        }
        else
        {
            $sql = "UPDATE $table_payment_settings SET value = '$value', position = '$position' WHERE name = '$valueName' AND gateway_id = '$gateway' ";
        }
        Database::query( $sql, __FILE__, __LINE__ );
        return Database::affected_rows();
    }
    
    public function urlFeedBack(){
       $url = "";
       if($this->_gateway == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_ATOS) {
          $url =  "";
       } else if($this->_gateway == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_PAYPAL){
          $url =  api_get_path( WEB_PATH ) . 'main/payment/checkout_5_payment_feedback.php';
          
       } else if($this->_gateway == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_NONE){
          
       }
       return $url;
    }

}
