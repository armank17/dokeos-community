<?php
require_once api_get_path( SYS_PATH ) . 'main/core/dao/ecommerce/EcommerceCourseDao.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/course/CourseModel.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceCatalog.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/CatalogueModel.php';
require_once api_get_path( SYS_PATH ) . 'main/core/dao/ecommerce/LpModulePackDao.php';

class EcommerceCatalogModules extends EcommerceCatalog
{
    
    public $currentValue = '';
    public $optionValues = array ();
    
    public static function create()
    {
        return new EcommerceCatalogModules();
    }
    public function getItemCatalogVisibility($code, $url_params, $row){
       $visibility = intval($row['3'],10);
       $code = intval($row['0'],10);
       $response = "";
       if($visibility == 1) {
          $response = "<a href='".  api_get_self()."?idEcommerceItem=" . $code . "&status=0'>".Display::return_icon('pixel.gif',get_lang('Deactivate'), array('class' =>'actionplaceholdericon actionvisible'))."</a>";
       } else {
          $response = "<a href='".  api_get_self()."?idEcommerceItem=" . $code . "&status=1'>".Display::return_icon('pixel.gif',get_lang('Activate'), array('class' =>'actionplaceholdericon actionvisible invisible'))."</a>";
       }
       return $response;
    }
    public function getItemCatalogButtonList( $code, $url_params, $row )
    {
        $response = '<a href="ecommerce_module_edit.php?idEcommerceItem=' . $code . '">' . Display::return_icon( 'pixel.gif', get_lang( 'Edit' ), array (
            'class' => 'actionplaceholdericon actionedit' ) ) . '</a>&nbsp;&nbsp;' . '<a href="ecommerce_module_packs.php?action=delete&idEcommerceItem=' . $code . '"  onclick="javascript:if(!confirm(' . "'" . addslashes( api_htmlentities( get_lang( "ConfirmYourChoice" ), ENT_QUOTES, $charset ) ) . "'" . ')) return false;">' . Display::return_icon( 'pixel.gif', get_lang( 'Delete' ), array (
            'class' => 'actionplaceholdericon actiondelete' ) ) . '</a>';
        
        return $response;
    
    }
    
    public function getCourseEcommerceData( $from = 0, $number_of_items = 100, $column = 1, $direction = ' ASC ', $get = array() )
    {
        // Database table definition
        $tableEcommerceItems = Database::get_main_table( TABLE_MAIN_ECOMMERCE_ITEMS );
        $currentItemType = intval( get_setting( 'e_commerce_catalog_type' ), 10 );
        
        $response = array ();
        $response = CatalogueModuleModel::create()->getCourseEcommerceData( $from, $number_of_items, $column, $direction, $get );
        
        return $response;
    }
    
    public function getFormForItemEcommerceByCode( $code )
    {
        
        $objEcommerceCourse = $this->getCourseByCode( $code );
        
        if ( is_null( $objEcommerceCourse ) )
        {
            return null;
        }
        
        $form = new FormValidator( 'frmEditEcommerceCourse', 'post', 'ecommerce_course_edit.php?code=' . $code );
        
        if ( $form->validate() )
        {
            $submitted = $form->getSubmitValues();
            
            if ( is_array( $submitted['date_start'] ) && (isset( $submitted['date_start']['d'] ) && isset( $submitted['date_start']['M'] ) && isset( $submitted['date_start']['Y'] )) )
            {
                $submitted['date_start'] = $submitted['date_start']['Y'] . '-' . $submitted['date_start']['M'] . '-' . $submitted['date_start']['d'];
            }
            if ( is_array( $submitted['date_end'] ) && (isset( $submitted['date_end']['d'] ) && isset( $submitted['date_end']['M'] ) && isset( $submitted['date_end']['Y'] )) )
            {
                $submitted['date_end'] = $submitted['date_end']['Y'] . '-' . $submitted['date_end']['M'] . '-' . $submitted['date_end']['d'];
            }
            
            $currentCatalogType = intval( get_setting( 'e_commerce_catalog_type' ), 10 );
            
            $response = array ();
            
            CatalogueModuleModel::create()->saveItemEcommerce( $submitted );
            $objEcommerceCourse = CatalogueCourseModel::create()->getCourseByCode( $code );
        
        }
        
        $form->addElement( 'hidden', 'course_code' );
        $form->addElement( 'text', 'txtTitle', get_lang( 'Name' ), array (
            'size' => 40, 'readonly' => 'readonly', 'class' => 'grayBg' ) );
        
        $form->addElement( 'text', 'cost', get_lang( 'Cost' ), array (
            'size' => 10 ) );
        
        $form->addElement( 'radio', 'status', get_lang( 'Status' ) . ':', get_lang( 'Active' ), 1 );
        $form->addElement( 'radio', 'status', null, get_lang( 'Inactive' ), 0 );
        
        $form->addElement( 'date', 'date_start', get_lang( 'StartDate' ) );
        $form->addElement( 'date', 'date_end', get_lang( 'EndDate' ) );
        
        $form->addRule( 'txtTitle', get_lang( 'ThisFieldIsRequired' ), 'required' );
        $form->addElement( 'style_submit_button', 'submit', get_lang( 'Save' ), array (
            'class' => 'save' ) );
        
        $defaults = array ();
        $defaults['txtTitle'] = $objEcommerceCourse->title;
        $defaults['course_code'] = $objEcommerceCourse->code;
        $defaults['cost'] = $objEcommerceCourse->cost;
        $defaults['visibility'] = $objEcommerceCourse->visibility;
        $defaults['status'] = $objEcommerceCourse->status;
        $defaults['date_start'] = $objEcommerceCourse->date_start;
        $defaults['date_end'] = $objEcommerceCourse->date_end;
        
        $form->setDefaults( $defaults );
        
        return $form;
    }
    
    public function getProductList()
    {
        return "";
    }
    
    public function getTotalNumberCourseEcommerce()
    {
        global $_configuration;
        $tableEcommerceItems = Database::get_main_table( TABLE_MAIN_ECOMMERCE_ITEMS );
        $tableCourse = Database::get_main_table(TABLE_MAIN_COURSE);
        $currentItemType = intval(get_setting( 'e_commerce_catalog_type'), 10);
        $sql = "SELECT COUNT(*) AS total FROM $tableEcommerceItems c"; 
        
        if ($_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $access_url_id = api_get_current_access_url_id();
            $access_url_rel_course_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
            $sql.= " INNER JOIN $access_url_rel_course_table url_rel_course ON (c.code = url_rel_course.course_code) AND url_rel_course.access_url_id = $access_url_id";
        }
        $sql.= " INNER JOIN ".$tableCourse." co ON co.code = c.code";
        $sql .= " WHERE c.item_type = '$currentItemType'";
        $res = Database::query( $sql, __FILE__, __LINE__ );
        $course = Database::fetch_row( $res );
        return intval( $course[0], 10 );
    }
    
    public function getEcommerceItemData( $from = 0, $number_of_items = 100, $column = 1, $direction = ' ASC ', $get = array() )
    {
        return CatalogueModuleModel::create()->getCourseEcommerceData( $from, $number_of_items, $column, $direction, $get );
    }
    
    public function getModulesByCourseCode( $courseCode )
    {
        $response = array ();
        $tableLpModules = Database::get_main_table( TABLE_MAIN_ECOMMERCE_LP_MODULE );
        $sql = "SELECT * FROM  $tableLpModules WHERE course_code = '$courseCode' ";
        
        $rsModules = Database::query( $sql );
        $row = TRUE;
        while ( $row )
        {
            $row = Database::fetch_object( $rsModules, 'CatalogueModuleModel' );
            if ( $row !== FALSE )
            {
                $response[] = $row;
            }
        }
        
        return $response;
    }
    
    public function getFormForModulePack( FormValidator $form, $idEcommerceItem = null )
    {
        $form->addElement( 'hidden', 'id' );
        $form->addElement( 'text', 'txtTitle', get_lang( 'Name' ), array (
            'size' => 40, 'class' => 'grayBg' ) );
        
        
        $image = "thumb_dokeos.jpg";
        if (!is_null( $idEcommerceItem ) && intval( $idEcommerceItem, 10 ) > 0)
        {
            $objEcommerceItem = CatalogueModuleModel::create()->getById( $idEcommerceItem );
            if($objEcommerceItem->image){
                $image = $objEcommerceItem->image;
            }
        }

        $img_path = api_get_path(WEB_PATH). 'home/default_platform_document/ecommerce_thumb/'.$image; //directory path to upload
        $form->addElement('file', 'picture', get_lang('AddPicture'));
        $form->addElement('static','imagesize','',get_lang('PNGorJPG'));
        $form->addElement('static','thumbimage',get_lang('Preview'),'<img src="'.$img_path.'">');	
        $allowed_picture_types = array ('jpg', 'jpeg', 'png', 'gif');
        $form->addRule('picture', get_lang('OnlyImagesAllowed').' ('.implode(',', $allowed_picture_types).')', 'filetype', $allowed_picture_types);
        
        $form->addElement( 'text', 'cost', get_lang( 'Cost' ), array (
            'size' => 10 ) );
        
        $group = array();
        $duration_type = array(
            'week' => get_lang('week'),
            'month' => get_lang('month'),
            'year' => get_lang('year')
        );
        $group[] = $form->createElement( 'text', 'duration', get_lang( 'Duration' ), array( 'size'=>5 ) );
        $group[] = $form->createElement( 'select', 'duration_type', null, $duration_type );
        $form->addGroup($group, null, get_lang( 'Duration' ), ' ');
        
        $form->addElement( 'radio', 'status', get_lang( 'Status' ) . ':', get_lang( 'Active' ), 1 );
        $form->addElement( 'radio', 'status', null, get_lang( 'Inactive' ), 0 );
        
        $form->addElement( 'date', 'date_start', get_lang( 'StartDate' ) );
        $form->addElement( 'date', 'date_end', get_lang( 'EndDate' ) );
        
        $form->addRule( 'txtTitle', get_lang( 'ThisFieldIsRequired' ), 'required' );
        $form->addElement( 'style_submit_button', 'submit', get_lang( 'Save' ), array (
            'class' => 'save' ) );
        
        $defaults = array ();
        
        if ( ! is_null( $idEcommerceItem ) && intval( $idEcommerceItem, 10 ) > 0 )
        {
            if ( is_object( $objEcommerceItem ) )
            {
                $defaults['id'] = $objEcommerceItem->id;
                $defaults['txtTitle'] = $objEcommerceItem->code;
                $defaults['code'] = $objEcommerceItem->code;
                $defaults['cost'] = $objEcommerceItem->cost;
                $defaults['status'] = $objEcommerceItem->status;
                $defaults['date_start'] = $objEcommerceItem->date_start;
                $defaults['date_end'] = $objEcommerceItem->date_end;
                $defaults['duration'] = $objEcommerceItem->duration;
                $defaults['duration_type'] = $objEcommerceItem->duration_type;
                
            }
        } else {
           $defaults['status'] = 1;
           $defaults['date_start'] = date('d-M-Y');
           $defaults['date_end'] = date('d-M-Y');
        }
        $form->setDefaults( $defaults );
        
        return $form;
    }
    
    public function getCourseCodeByObj( $ecommerceItem )
    {
        $response = '';
        $idItem = intval( $ecommerceItem->id, 10 );
        
        $lpmodulepack = LpModulePackDao::create()->getByEcommerceItemId( $idItem );
        if ( isset( $lpmodulepack[0] ) )
        {
            $response = $lpmodulepack[0]->lp_module_course_code;
        }
        
        return $response;
    }
    public function setEcommerceVisibility($ecommerceItemId, $status){
       $ecommerceItemId = intval($ecommerceItemId, 10);
       $tblEcommerceItems = Database::get_main_table( TABLE_MAIN_ECOMMERCE_ITEMS );
       $sql = "UPDATE $tblEcommerceItems SET status=$status WHERE id=$ecommerceItemId";
       
       Database::query( $sql, __FILE__, __LINE__ );
       return Database::affected_rows();    
    }
    
    public function deleteEcommerceItem( $ecommerceItemId )
    {
        $tblEcommerceItems = Database::get_main_table( TABLE_MAIN_ECOMMERCE_ITEMS );
        $tblEcommerceLpModulePack = Database::get_main_table( TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS );
        
        $sql = "DELETE from $tblEcommerceLpModulePack WHERE `ecommerce_items_id` = '$ecommerceItemId' ";
        Database::query( $sql, __FILE__, __LINE__ );
        
        $sql = "DELETE from $tblEcommerceItems WHERE `id` = '$ecommerceItemId' ";
        Database::query( $sql, __FILE__, __LINE__ );
        
         return Database::affected_rows();    
    }
}