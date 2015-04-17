<?php

class CatalogueManager
{
    /**
     * 
     * @var CatalogueInterface
     */
    private $_catalogObj = NULL;
    
    
    public static function create()
    {
        return new CatalogueManager( CatalogueFactory::getObject() );
    }
    
    public function __construct(CatalogueInterface $objCatalog)
    {
        $this->_catalogObj = $objCatalog;
    }

    public function getListForStudentPortal()
    {        
        return $this->_catalogObj->getListForStudentPortal();
    }
    
    public function getObject()
    {
        return $this->_catalogObj;
    }
    
    public function registerUserIntoCourses(array $session , array  $transactionResult ){
   
    }
    
    
}
