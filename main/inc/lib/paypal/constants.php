<?php
/*
 * Called by CallerService.php. This file overrides the constants for PAYPAL
 */

require_once dirname( __FILE__ ) . '/../../global.inc.php';
require_once api_get_path( LIBRARY_PATH ) . 'sessionmanager.lib.php';

require_once api_get_path( SYS_PATH ) . 'main/core/controller/ecommerce/EcommerceController.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceAbstract.php';

$selectedGateway = intval( api_get_setting( 'e_commerce' ), 10 );
$signature = api_get_payment_setting('email');

if ( $selectedGateway == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_PAYPAL )
{
    $objEcommerce = new EcommercePaypal();
    
    $gateway = $objEcommerce->getGatewaySettings();
    $userName = trim( $gateway['mail']->value );
    $userName = trim( $gateway['username']->value );
    $password = trim( $gateway['password']->value );
    $signature = trim( $gateway['signature']->value );
    $isDefault = trim( $gateway['workspace']->value );
        
    if( $userName == '' || $password == '' || $signature == '')
    
    {       
        $userName = 'platfo_1255077030_biz_api1.gmail.com';
        $password = '1255077037';
        $signature = 'Abg0gYcQyxQvnf2HDJkKtA-p6pqhA1k-KTYE0Gcy1diujFio4io5Vqjf';
    }
    define( 'API_SELLER', $seller );
    define( 'API_USERNAME', $userName );
    define( 'API_PASSWORD', $password );
    define( 'API_SIGNATURE', $signature );
    
  
    
    /*
     * Define the PayPal URL. This is the URL that the buyer is first sent to to
     * authorize payment with their paypal account change the URL depending if
     * you are testing on the sandbox or going to the live PayPal site For the
     * sandbox, the URL is
     * https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token= For
     * the live site, the URL is
     * https://www.paypal.com/webscr&cmd=_express-checkout&token= CHECKS IF
     * CONTAINS DEFAULT VALUES. IF VALUES ARE SET, USE THE LIVE VERSION,
     * OTHERWISE USE THE TESTING VERSION
     */
    
    if ( !$isDefault )
    {
        define( 'PAYPAL_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr' );
    } else
    {
        define( 'PAYPAL_URL', 'https://www.paypal.com/webscr' );
    }
    
    /**
     * # Endpoint: this is the server URL which you have to connect for
     * submitting
     * your API request.
     */
    
    define( 'API_ENDPOINT', 'https://api-3t.sandbox.paypal.com/nvp' );
    
    /*
     * # Third party Email address that you granted permission to make api call.
     */
    define( 'SUBJECT', '' );
    /*
     * for permission APIs ->token, signature, timestamp are needed
     * define('AUTH_TOKEN',"4oSymRbHLgXZVIvtZuQziRVVxcxaiRpOeOEmQw");
     * define('AUTH_SIGNATURE',"+q1PggENX0u+6vj+49tLiw9CLpA=");
     * define('AUTH_TIMESTAMP',"1284959128");
     */
    
    /**
     * USE_PROXY: Set this variable to TRUE to route all the API requests
     * through
     * proxy.
     * like define('USE_PROXY',TRUE);
     */
    define( 'USE_PROXY', FALSE );
    /**
     * PROXY_HOST: Set the host name or the IP address of proxy server.
     * PROXY_PORT: Set proxy port.
     *
     * PROXY_HOST and PROXY_PORT will be read only if USE_PROXY is set to TRUE
     */
    define( 'PROXY_HOST', '127.0.0.1' );
    define( 'PROXY_PORT', '808' );
    
    /**
     * # Version: this is the API version in the request.
     * # It is a mandatory parameter for each API request.
     * # The only supported value at this time is 2.3
     */
    
    define( 'VERSION', '65.1' );
    
    // Ack related constants
    define( 'ACK_SUCCESS', 'SUCCESS' );
    define( 'ACK_SUCCESS_WITH_WARNING', 'SUCCESSWITHWARNING' );
}

