<?php

class LpModulePackModel
{
    public static function create()
    {
        return new LpModulePackModel();
    } 
    
    public function getLpModulesByItemId( $itemId,$course = null)
    {
        $lpModulePack = LpModulePackDao::create()->getByEcommerceItemId($itemId,$course);

        return $lpModulePack;
    }

}