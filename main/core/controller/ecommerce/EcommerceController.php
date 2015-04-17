<?php
$cidReset= TRUE;
require_once dirname(__FILE__) .'/../../../inc/global.inc.php';

require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceFactory.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommercePaypal.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceCatalogModules.php';

$request = $_REQUEST;

if ( ! empty( $request ) )
{
    $ecController = new EcommerceController();

    switch ( $request['action'] )
    {
        case 'getCourseModulesList' :
            $itemId = (isset($request['itemId'])) ? $request['itemId'] : '';
            echo $ecController->getCourseModulesList( $request['course'] , $itemId);
            break;
    }
}


class EcommerceController
{
    
    public $selectedGateway = 0;
    public $objEcommerce = null;
    public $gatewayData = array ();
    
    public function __construct()
    {
        $this->selectedGateway = intval( api_get_setting( 'e_commerce' ), 10 );
                
        $this->objEcommerce = EcommerceFactory::getEcommerceObject( $this->selectedGateway );        
        $this->gatewayData = $this->objEcommerce->getGatewayData();
    }
    
    public function getForm()
    {
        
        return $this->_getFormFromEcommerce( $this->objEcommerce );
    }
    
    public function save( array $post , array $files )
    {
        $this->objEcommerce->save( $post , $files );
    }
    
    public function getCourseModulesList( $course, $itemId = 0)
    {
        
        $itemId = intval($itemId, 10);

        $selectedItems = array();
        $hasSelectedItems = false;
        
        if( $itemId > 0 )
        {            
           $lpModulePack = LpModulePackModel::create()->getLpModulesByItemId($itemId,$course);
           
           foreach($lpModulePack  as $lpModule)
           {               
               $selectedItems['course'] = $lpModule->lp_module_course_code;
               $selectedItems['lpIds'][] = $lpModule->lp_module_lp_module_id;
           }
           
           $hasSelectedItems = (count( $selectedItems ) > 0 ) ;           
        }
        
        $response = '<select name="cboModules[]" id="cboModules" multiple="multiple">'. PHP_EOL;;
        
        foreach( EcommerceCatalogModules::create()->getModulesByCourseCode( $course ) as $module )
        {
            $isSelected ='';
            if($hasSelectedItems )
            {
                if( in_array($module->lp_module_id, $selectedItems['lpIds']) )
                {
                    $isSelected =' selected="selected" ';
                }
                
            }
            
            $response .= '<option '.$isSelected.'value="'.$module->lp_module_id .'">' . $module->lp_title .'</option>' . PHP_EOL;
        }        
        $response .= '</select>'. PHP_EOL;;
        $response .= '<input type="hidden" name="code_course" id="code_course"  value="'.$module->course_code .'"/>'. PHP_EOL;;
        
        
        $langSelectAll = get_lang('SelectAll');
        $langUnselectAll = get_lang('UnSelectAll');
        $langSelectEpisodes = get_lang('SelectEpisodes');
        $langSelectedEpisodes = get_lang('SelectedEpisodes'); 
        $response .= <<<EOF
        <script>
        	$(document).ready(function(){
                $("#cboModules").multiselect({ 
                checkAllText: "{$langSelectAll}",
                uncheckAllText: "{$langUnselectAll}",
                noneSelectedText: "{$langSelectEpisodes}",
                selectedText: "{$langSelectedEpisodes}",
                autoOpen: false,
                height: 120,
                width: 300
                
    }).multiselectfilter();

        $("#cboModules").multiselect('refresh');
    });
        
        </script>
EOF;

        if ( api_is_xml_http_request() )
        {
            $response = api_utf8_encode($response);
        }
        return $response;
    }
    
    protected function _getFormFromEcommerce( EcommerceInterface $obj )
    {
        
        return $obj->getForm();
    }
    
    
}