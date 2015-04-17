<?php
// resetting the course id
require_once dirname( __FILE__ ) . '/../../../../main/inc/global.inc.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceNone.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceAtos.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommercePaypal.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceAbstract.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceInterface.php';


//

class EcommerceFactory
{
    const NONE = 0;
    const ATOS  = 1;
    const PAYPAL = 2;
    
    public static function getEcommerceObject( $type = NULL)
    {       
        switch( $type )
        {
            case EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_PAYPAL:                
                return new EcommercePaypal();
                break;
            case EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_ATOS:
                return new EcommerceAtos();
                break;
            default:
            case EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_NONE:
                return new EcommerceNone();
                break;
        }
    }
}