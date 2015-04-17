<?php
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/CatalogueInterface.php';
require_once api_get_path( SYS_PATH ) . 'main/core/dao/ecommerce/CatalogueDao.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/CatalogueModel.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/Currency.php';

class CatalogueCourseModel extends CatalogueModel implements CatalogueInterface
{
    public static function create()
    {
        return new CatalogueCourseModel();
    }
    
    public function registerItemsCoursesFree(array $session, $userId){
        $sessionIds = array_keys($_SESSION['shopping_cart']['items']);
        foreach( $sessionIds as $sessionId)
        {
            $sessionIdF = $sessionId;
        }
        CourseManager::add_user_to_course($userId,$sessionIdF);
    }
    
    public function getCourseByCode( $courseCode )
    {
        $tableMainCourse = Database::get_main_table( TABLE_MAIN_COURSE );
        $tableMainEcommerceItems = Database::get_main_table( TABLE_MAIN_ECOMMERCE_ITEMS );
        
        $response = null;
        
        if ( trim( $courseCode ) != '' )
        {
            $sql = "SELECT c.code, c.title,c.visibility,ce.cost,ce.status,ce.duration,ce.duration_type,ce.date_start,ce.date_end,ce.image,ce.description FROM $tableMainCourse AS c
            JOIN $tableMainEcommerceItems AS ce ON c.code = ce.code WHERE ce.code = '$courseCode' AND item_type = '" . CatalogueCourseModel::TYPE_COURSE . "'LIMIT 1;";
            
            $result = Database::query( $sql, __FILE__, __LINE__ );
            
            $row = true;
            while ( $row )
            {
                $row = Database::fetch_object( $result );
                if ( $row !== FALSE )
                {
                    $response = $row;
                }
            }
        }
        return $response;
    }
    
    public function getCourseEcommerceData( $from = 0, $number_of_items = 100, $column = 1, $direction = ' ASC ', $get = array() , $payment = 1)
    {
        global $_configuration;
        $courses = array();
        
        $tableMainCourse = Database::get_main_table( TABLE_MAIN_COURSE );
        $tableEcommerceItems = Database::get_main_table( TABLE_MAIN_ECOMMERCE_ITEMS );
        $tableEcommerceCategory = Database::get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY);
                
        $sql = "SELECT c.code as col0, c.visual_code as col2, c.title  as col3, ce.cost as col4,
                ce.duration as col5, ce.status  as col6, ec.chr_category as col1, ce.duration_type as col7, ce.chr_type_cost as col8, ce.cost_ttc as col9, ce.sort as col10 
                FROM $tableMainCourse AS c
                INNER JOIN $tableEcommerceItems AS ce ON c.code = ce.code 
                LEFT JOIN $tableEcommerceCategory AS ec ON ec.id_category = ce.id_category";
        
        if ($_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $access_url_id = api_get_current_access_url_id();
            $access_url_rel_course_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
            $sql.= " INNER JOIN $access_url_rel_course_table url_rel_course ON (c.code = url_rel_course.course_code) AND url_rel_course.access_url_id = $access_url_id ";
        }        
        
        $sql .= " WHERE ce.item_type = '" . CatalogueModel::TYPE_COURSE . "'";
        
        if ( isset( $get['keyword'] ) )
        {
            $keyword = Database::escape_string( $get['keyword'] );
            $sql .= " AND  (title LIKE '%" . $keyword . "%' OR code LIKE '%" . $keyword . "%' OR visual_code LIKE '%" . $keyword . "%')";
        } elseif ( isset( $get['keyword_code'] ) )
        {
            $keyword_code = Database::escape_string( $get['keyword_code'] );
            $keyword_title = Database::escape_string( $get['keyword_title'] );
            $keyword_category = Database::escape_string( $get['keyword_category'] );
            $keyword_language = Database::escape_string( $get['keyword_language'] );
            $keyword_visibility = Database::escape_string( $get['keyword_visibility'] );
            $keyword_subscribe = Database::escape_string( $get['keyword_subscribe'] );
            $keyword_unsubscribe = Database::escape_string( $get['keyword_unsubscribe'] );
            $sql .= " AND (code LIKE '%" . $keyword_code . "%' OR visual_code LIKE '%" . $keyword_code . "%') AND title LIKE '%" . $keyword_title . "%' AND category_code LIKE '%" . $keyword_category . "%'  AND course_language LIKE '%" . $keyword_language . "%'   AND visibility LIKE '%" . $keyword_visibility . "%'    AND subscribe LIKE '" . $keyword_subscribe . "'AND unsubscribe LIKE '" . $keyword_unsubscribe . "'";
        }
        $sql .= " AND payment = $payment ";
        
        if(!isset($_GET['courses_ecommerce_column']) && empty($_GET['courses_ecommerce_column'])){
            $sql .= " ORDER BY sort ASC";
        }else{        
        $sql .= " ORDER BY col$column $direction ";
        }
        
        $sql .= " LIMIT $from,$number_of_items";
        $res = Database::query( $sql, __FILE__, __LINE__ );
        
        $course = TRUE;
        while ( $course )
        {
            $course = Database::fetch_row( $res );
            
            if ( $course !== FALSE )
            {
                 
                if ($course[5] == 1) {
                    $course[5] = "<a class=\"make_visible_and_invisible_product\" id=\"link_".$course[0]."\" href=\"".api_get_self()."?action=makeunavailable&id=".$course['id']."\">".Display::return_icon('pixel.gif', get_lang('MakeUnavailable'),array('class'=>'actionplaceholdericon actionvisible','id'=>'img_link_'.$course[0]))."</a>";
                } else {
                    $course[5] = "<a class=\"make_visible_and_invisible_product\" id=\"link_".$course[0]."\" href=\"".api_get_self()."?action=makeavailable&id=".$course['id']."\">".Display::return_icon('pixel.gif', get_lang('MakeAvailable'),array('class'=>'actionplaceholdericon actioninvisible','id'=>'img_link_'.$course[0]))."</a>";
                }
                $category_name = $course[6];
                /*if (!empty($course[6])) {					
					$category_name = $this->getNameCategory($course[6]);
				}*/
                                
                $duration =  $course[4].' '.get_lang(ucfirst($course[7]));
                  
                $prixHT = $course[3];
                /*if($course[8] == 'TTC') {
                    $prixHT = $course[9];
                }*/
                $move = Display::return_icon('pixel.gif', get_lang('Move'),array("class"=>"actionplaceholdericon actionsdraganddrop"));
                $course_rem = array (
                        $course[0],$move, $category_name, $course[1], $course[2], '<center>'.api_number_format($prixHT).'</center>', '<center>'.$duration.'</center>', '<center>'.$course[5].'</center>', $course[0]
                );                                                        
                $courses[] = $course_rem;
            }
        }
        
        return $courses;
    }
    
    public function getNameCategory($id_category)
    {
        $sql ="SELECT * FROM ".Database::get_main_table( TABLE_MAIN_ECOMMERCE_CATEGORY )." WHERE id_category='".$id_category."'";
        $result = Database::query( $sql, __FILE__, __LINE__ );
        $row = Database::fetch_row( $result );
        return $row[1];
    }
    
    public function saveItemEcommerce(array $catalog)
    {
        $tblEcommerceItems = Database::get_main_table( TABLE_MAIN_ECOMMERCE_ITEMS );
        $courseCode = (isset( $catalog['wanted_code'] )) ? $catalog['wanted_code'] : ((isset( $catalog['course_code'] )) ? $catalog['course_code'] : '');
        $duration = (isset( $catalog['duration'] )) ? $catalog['duration'] : '1';
        $duration_type = (isset( $catalog['duration_type'] )) ? $catalog['duration_type'] : 'day';
        $dateStart = (isset( $catalog['date_start'] )) ? $catalog['date_start'] : '';
        $dateEnd = (isset( $catalog['date_end'] )) ? $catalog['date_end'] : '';
        $status = (isset( $catalog['status'] )) ? $catalog['status'] : '0';
        $cost = (isset( $catalog['cost'] )) ? $catalog['cost'] : '0';
        $visibility = (isset( $catalog['visibility'] )) ? $catalog['visibility'] : '0';
        $description = (isset( $catalog['description'] )) ? $catalog['description'] : '';
        $remove_img = (isset( $catalog['remove_img'] )) ? intval($catalog['remove_img']) : '';
        // e_commerce_catalog_type 2 Courses
        
        $sql = "SELECT * FROM $tblEcommerceItems WHERE code='$courseCode' AND item_type = '" . CatalogueModel::TYPE_COURSE . "' LIMIT 1";
        
        $result = Database::query( $sql, __FILE__, __LINE__ );
        
        $row = Database::fetch_row( $result );
        $updir = api_get_path(SYS_PATH). 'home/default_platform_document/ecommerce_thumb/'; //directory path to upload
        
        // If there are a file uploaded
        if(!empty($_FILES['picture']['tmp_name'])){
                $img_pic = replace_dangerous_char($_FILES['picture']['name'], 'strict');		
                $img_tmp = $_FILES['picture']['tmp_name'];

                // If exist a file called with the same name then change the name
                if(file_exists($updir.$img_pic)){
                    $exp_pic = explode(".",$img_pic);
                    $img_pic = $exp_pic[0].$row['id'].'.'.$exp_pic[1];
                }                        
                // Remove the last image
                @unlink($updir.$row['10']);                          

                // Set the dimmensions what will have the image
                $width_img = 130;
                $height_img = 160;

                // Use the function for resize the image
                api_resize_images($updir,$img_tmp,$img_pic,$width_img,$height_img);	
                
                $image = $img_pic;

        }else{
                $image = $row['10'];
        }
  
        if ($remove_img == 1)
            $image = 'no-image';
        if ( $row == FALSE )
        {
            $sql = "INSERT INTO $tblEcommerceItems ( `code`,`cost`,`item_type`,`status`,
            `currency`, `date_start`, `date_end`, `image`) VALUES (
            '$courseCode','0.00', '" . CatalogueModel::TYPE_COURSE . "', 0 ,
            0,now(),$dateEnd, '$image')";
        } else
        {
            $sql = "UPDATE $tblEcommerceItems SET `status` = '$status',
            `duration` = $duration, `duration_type` = '$duration_type', 
            `date_start` = '$dateStart' , `date_end` = '$dateEnd', `cost` = '$cost',
            `image` = '$image',`description` = '$description'
            WHERE  code='$courseCode' AND item_type =  '" . CatalogueModel::TYPE_COURSE . "' ";
        }
        
        Database::query( $sql, __FILE__, __LINE__ );
        
        return Database::affected_rows();
    }
    
    /**
     *@todo implement inherited method
     */
    public function getListForStudentPortal()
    {
    }
    
    /* (non-PHPdoc)
     * @see CatalogueInterface::registerItemsForUser()
    */
    public function registerItemsForUser( array $session, array $transactionResult, $userId )
    {
//        $courseCodes = array_keys($session['shopping_cart']['items']);
        $courseCodes = array_keys($session['items_paid']);

        foreach( $courseCodes as $courseCode)
        {
            /** @var $objCourse EcommerceCourse */
            
            $objItem = EcommerceItemsDao::create()->getByCourseCode($courseCode);
            
            
            $params = array();
            $params['user_id'] =  $userId; 
            $params['ecommerce_items_id'] = $objItem->id;
            $params['role'] = '5';
            $params['group_id'] = '';
            $params['tutor_id'] = '';
            $params['sort'] = $objItem->id;
            $params['user_course_cat'] = '';
            
            EcommerceUserPrivilegesDao::create()->save($params);
        }
                
        
        return true;

    }
    public function registerItemsIntoUser(array $session, $userId) {
//        $courseCodes = array_keys($session['shopping_cart']['items']);
        $courseCodes = array_keys($session['items_paid']);
        foreach ($courseCodes as $courseCode) {
            $status = 5;
            if (CourseManager::add_user_to_course($userId, $courseCode, $status)) {
                $send = api_get_course_setting('email_alert_to_teacher_on_new_user_in_course', $courseCode);
                if ($send == 1) {
                    CourseManager::email_to_tutor($userId, $courseCode, $send_to_tutor_also = false);
                } else if ($send == 2) {
                    CourseManager::email_to_tutor($userId, $courseCode, $send_to_tutor_also = true);
                }
            }
        }
    }

    /* (non-PHPdoc)
     * @see CatalogueInterface::getShoppingCartList()
     */
    public function getShoppingCartList()
    {
        $response = '';
        $courses = array ();
        
        $courses = EcommerceCourseDao::create()->getListOfEcommerceCourses( EcommerceCourse::ECOMMERCECOURSE_STATUS_ACTIVE );
        $catalog = api_get_payment_setting('catalog');
        $response .= '<div class="items_catalogue_box">';
        $response .= '<div class="section" id="items_catalogue_list" rel="'.count($courses).'">';
        $response .= '<div class="row"><div class="form_header">' . $catalog . '</div></div>';
        
        $response .= '<div><ul id="shoppingCartCatalog" class="jcarousel-skin-tango" rel="course">';
        
        $langTime = get_lang( 'Duration' );
        $langPrice = get_lang( 'Price' );
        $langAddToCart = get_lang( 'AddToCartHome' );
        
        $currency = $_SESSION['shopping_cart']['currency'];
        foreach ($courses as $course)
        {
            $courseFull = $course->getCourseFull();
            $duration = $course->duration .' '. get_lang($course->duration_type);
            if($course->image){
                $image = $course->image;
            }else{
                $image = "thumb_dokeos.jpg";
            }
            
            $img_path =  api_get_path(WEB_PATH).'home/default_platform_document/ecommerce_thumb/'.$image;
            $img_html = '<img width="130" height=160" src="'.$img_path.'"><br/>';
            
            if($image == 'no-image')
                $img_html = '';
            
            $price = ($currency['code'] == 'USD')?$currency['symbol'].' '.$course->cost:$course->cost.' '.$currency['symbol'];
            $response .= '<li>';
            $response .= <<<EOF
<div class="course_catalog_container" rel="{$courseFull->code}">
    <p><a href="main/catalogue/course_details.php?course_code={$courseFull->code}">
        {$img_html}
        <font size="2"><b>{$courseFull->title}</b></font>
    </a></p>
    <p><div id="duration_catalogue">{$langTime}: {$duration}</div></p>
    <p><div id="price_catalogue">{$price}</div></p>
    
    <p><a href="" class="addToCartCourse addToCartCourseClick"><span>{$langAddToCart}</span></a></p>        
</div>
EOF;
            $response .= '</li>';
        }
        $response .= '</ul></div>';
        $response .= '</div></div>';
        
        return $response;
    }
    
   

    
    

}
