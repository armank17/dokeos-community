<?php
// resetting the course id

require_once dirname( __FILE__ ) . '/../../../../main/inc/global.inc.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/CatalogueInterface.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/CatalogueSessionModel.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/CatalogueModuleModel.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/CatalogueSessionModel.php';

class CatalogueFactory
{
    const SESSION = 1;
    const COURSE = 2;
    const MODULE = 3;
    
    public static function getObject()
    {
        $type = intval( api_get_setting( 'e_commerce_catalog_type' ), 10 );
        if (empty($type)) { $type = 2;}
        switch ( $type )
        {
            case CatalogueFactory::SESSION :               
                return new CatalogueSessionModel();
            
            case CatalogueFactory::COURSE :
                return new CatalogueCourseModel();
            
            case CatalogueFactory::MODULE :
                return new CatalogueModuleModel();
        }
    }
}