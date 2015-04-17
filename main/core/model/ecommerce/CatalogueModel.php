<?php
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/CatalogueInterface.php';
require_once api_get_path( SYS_PATH ) . 'main/core/dao/ecommerce/CatalogueDao.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/Currency.php';
require_once api_get_path( SYS_PATH ) . 'main/core/dao/ecommerce/EcommerceUserPrivilegesDao.php';
require_once api_get_path( SYS_PATH ) . 'main/core/dao/ecommerce/EcommerceItemsDao.php';

abstract class CatalogueModel implements CatalogueInterface
{
    const TYPE_SESSION = 1;
    const TYPE_COURSE = 2;
    const TYPE_MODULES = 3;    
        
    public function getValidCatalogCurrencyOptions()
    {
        $response = array ();
        $objCatalogueDao = new CatalogueDao();
        
        /*
         * @var $objCatalog CatalogueModel
         */
        $objCatalog = $objCatalogueDao->getFirstCatalogue();
        $response['selected'] = 0;
        $response['options'] = $objCatalogueDao->getOptionsCurrency();
        
        if ( $objCatalog !== FALSE && is_object( $objCatalog ) )
        {
            $response['selected'] = intval( $objCatalog->currency, 10 );
        }
        
        return $response;
    }
    
    public function getDefaultCatalogue()
    {
        $objCatalogueDao = new CatalogueDao();
        return $objCatalogueDao->getFirstCatalogue();
    }
    
    public function getCurrentCatalogueType()
    {
        return api_get_setting('e_commerce_catalog_type');
    }
    
    
    
    

}
