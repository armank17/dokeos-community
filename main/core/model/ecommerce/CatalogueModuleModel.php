<?php

require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/CatalogueInterface.php';
require_once api_get_path(SYS_PATH) . 'main/core/dao/ecommerce/CatalogueDao.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/CatalogueModel.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/Currency.php';

class CatalogueModuleModel extends CatalogueModel implements CatalogueInterface {

    public static function create() {
        return new CatalogueModuleModel();
    }

    public function getCourseByCode($courseCode) {
        $tableMainCourse = Database::get_main_table(TABLE_MAIN_COURSE);
        $tableMainEcommerceItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);

        $response = null;

        if (trim($courseCode) != '') {
            $sql = "SELECT c.code, c.title,c.visibility,ce.cost,ce.status,ce.date_start,ce.date_end FROM $tableMainCourse AS c
            JOIN $tableMainEcommerceItems AS ce ON c.code = ce.code WHERE ce.code = '$courseCode' AND item_type = '" . CatalogueCourseModel::TYPE_COURSE . "'LIMIT 1;";

            $result = Database::query($sql, __FILE__, __LINE__);

            $row = true;
            while ($row) {
                $row = Database::fetch_object($result);
                if ($row !== FALSE) {
                    $response = $row[0];
                }
            }
        }
        return $response;
    }

    public function getCourseEcommerceData($from = 0, $number_of_items = 100, $column = 1, $direction = ' ASC ', $get = array()) {
        global $_configuration;
        $courses = array();

        $tableEcommerceItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);

        $sql = "SELECT c.id as col0,c.code as col1, c.cost as col2, c.status as col3,c.date_start as col4, c.date_end as col5 FROM $tableEcommerceItems  AS c ";

        if ($_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $access_url_id = api_get_current_access_url_id();
            $access_url_rel_course_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
            $sql.= " INNER JOIN $access_url_rel_course_table url_rel_course ON (c.code = url_rel_course.course_code) AND url_rel_course.access_url_id = $access_url_id";
        }

        $sql .= " WHERE c.item_type = '" . CatalogueModel::TYPE_MODULES . "'";

        if (isset($get['keyword'])) {
            $keyword = Database::escape_string($get['keyword']);
            $sql .= " AND  (title LIKE '%" . $keyword . "%' OR code LIKE '%" . $keyword . "%' OR visual_code LIKE '%" . $keyword . "%')";
        } elseif (isset($get['keyword_code'])) {
            $keyword_code = Database::escape_string($get['keyword_code']);
            $keyword_title = Database::escape_string($get['keyword_title']);
            $keyword_category = Database::escape_string($get['keyword_category']);
            $keyword_language = Database::escape_string($get['keyword_language']);
            $keyword_visibility = Database::escape_string($get['keyword_visibility']);
            $keyword_subscribe = Database::escape_string($get['keyword_subscribe']);
            $keyword_unsubscribe = Database::escape_string($get['keyword_unsubscribe']);
            $sql .= " AND (code LIKE '%" . $keyword_code . "%' OR visual_code LIKE '%" . $keyword_code . "%') AND title LIKE '%" . $keyword_title . "%' AND category_code LIKE '%" . $keyword_category . "%'  AND course_language LIKE '%" . $keyword_language . "%'   AND visibility LIKE '%" . $keyword_visibility . "%'    AND subscribe LIKE '" . $keyword_subscribe . "'AND unsubscribe LIKE '" . $keyword_unsubscribe . "'";
        }

        $sql .= " ORDER BY col$column $direction ";
        $sql .= " LIMIT $from,$number_of_items";

        $res = Database::query($sql, __FILE__, __LINE__);

        $course = TRUE;
        while ($course) {
            $course = Database::fetch_row($res);

            if ($course !== FALSE) {
                $course_rem = array(
                    $course[0], $course[0], $course[1], $course[3], $course[2], $course[4], $course[5], $course[0]);
                $courses[] = $course_rem;
            }
        }

        return $courses;
    }

    public function saveItemEcommerce(array $module) {
        $tblEcommerceItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $tblEcommerceLpModulePack = Database::get_main_table(TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS);

        $ecommerceItemId = (isset($module['id'])) ? $module['id'] : '';
        $lpNameBlockName = (isset($module['txtTitle'])) ? $module['txtTitle'] : '';

        $lpModuleIds = (isset($module['cboModules'])) ? $module['cboModules'] : array();

        $courseCode = (isset($module['code'])) ? $module['code'] : ((isset($module['course_code'])) ? $module['course_code'] : '');

        $dateStart = (isset($module['date_start'])) ? $module['date_start'] : '';
        $dateEnd = (isset($module['date_end'])) ? $module['date_end'] : '';

        $status = (isset($module['status'])) ? $module['status'] : '0';
        $cost = (isset($module['cost'])) ? $module['cost'] : '0.0';

        $duration = (isset($module['duration'])) ? $module['duration'] : '1';
        $duration_type = (isset($module['duration_type'])) ? $module['duration_type'] : 'day';

        $courseCode = (isset($module['code_course'])) ? $module['code_course'] : '';
        // e_commerce_catalog_type 2 Courses

        $sql = "SELECT * FROM $tblEcommerceItems WHERE `id` = '$ecommerceItemId' LIMIT 1";

        $result = Database::query($sql, __FILE__, __LINE__);

        $row = Database::fetch_row($result);

        $updir = api_get_path(SYS_PATH) . 'home/default_platform_document/ecommerce_thumb/'; //directory path to upload
        // If there are a file uploaded
        if (!empty($_FILES['picture']['tmp_name'])) {
            $img_pic = replace_dangerous_char($_FILES['picture']['name'], 'strict');
            $img_tmp = $_FILES['picture']['tmp_name'];

            // If exist a file called with the same name then change the name
            if (file_exists($updir . $img_pic)) {
                $exp_pic = explode(".", $img_pic);
                $img_pic = $exp_pic[0] . $row['id'] . '.' . $exp_pic[1];
            }
            // Remove the last image
            @unlink($updir . $row['10']);

            // Set the dimmensions what will have the image
            $width_img = 130;
            $height_img = 160;

            // Use the function for resize the image
            api_resize_images($updir, $img_tmp, $img_pic, $width_img, $height_img);

            $image = $img_pic;
        } else {
            $image = $row['10'];
        }

        if ($row === FALSE) {
            $sql = "INSERT INTO $tblEcommerceItems ( `code`,`cost`,`item_type`,`status`,`currency`, `date_start`, `date_end`, `duration`, `duration_type`. `image`)
             VALUES ( '$lpNameBlockName','$cost', '" . CatalogueModel::TYPE_MODULES . "', $status ,0,'$dateStart','$dateEnd', $duration, '$duration_type', '$image')";
            Database::query($sql, __FILE__, __LINE__);
            $idEcommerceItems = Database::insert_id();

            if (count($lpModuleIds) > 0) {
                foreach ($lpModuleIds as $newLpModule) {
                    $sql = "INSERT INTO $tblEcommerceLpModulePack ( `ecommerce_items_id`,`lp_module_lp_module_id`,`lp_module_course_code`) VALUES (
                    '$idEcommerceItems','$newLpModule', '$courseCode') ";
                    Database::query($sql, __FILE__, __LINE__);
                }
            }
        } else {
            $sql = "UPDATE $tblEcommerceItems SET `code` = '$lpNameBlockName',`status` = '$status', 
            `date_start` = '$dateStart', `date_end` = '$dateEnd', `cost` = '$cost', `duration` = $duration, `duration_type` = '$duration_type', `image` = '$image'  WHERE `id` = '$ecommerceItemId'";
            Database::query($sql, __FILE__, __LINE__);

            $sql = "DELETE from $tblEcommerceLpModulePack WHERE `ecommerce_items_id` = '$ecommerceItemId' ";
            Database::query($sql, __FILE__, __LINE__);

            if (count($lpModuleIds) > 0) {
                foreach ($lpModuleIds as $newLpModule) {
                    $sql = "INSERT INTO $tblEcommerceLpModulePack ( `ecommerce_items_id`,`lp_module_lp_module_id`,`lp_module_course_code`) VALUES (
                    '$ecommerceItemId','$newLpModule', '$courseCode') ";
                    Database::query($sql, __FILE__, __LINE__);
                }
            }
        }
        return Database::affected_rows();
    }

    public function saveEcommerceLpModule(array $catalog) {

        $tblEcommerceItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $tblEcommerceLpModule = Database::get_main_table(TABLE_MAIN_ECOMMERCE_LP_MODULE);

        $lpId = (isset($catalog['lp_id'])) ? $catalog['lp_id'] : '';
        $lpName = (isset($catalog['title'])) ? $catalog['title'] : '';
        $lpDescription = (isset($catalog['description'])) ? $catalog['description'] : '';

        $courseCode = (isset($catalog['course_code'])) ? $catalog['course_code'] : ((isset($catalog['course_code'])) ? $catalog['course_code'] : '');
        $dateStart = (isset($catalog['date_start'])) ? $catalog['date_start'] : '';
        $dateEnd = (isset($catalog['date_end'])) ? $catalog['date_end'] : '';
        $status = (isset($catalog['status'])) ? $catalog['status'] : '0';
        $cost = (isset($catalog['cost'])) ? $catalog['cost'] : '0';
        $visibility = (isset($catalog['visibility'])) ? $catalog['visibility'] : '0';

        $sql = "SELECT * FROM $tblEcommerceLpModule WHERE `course_code`='$courseCode' AND lp_module_id = '$lpId' LIMIT 1";

        $result = Database::query($sql, __FILE__, __LINE__);

        $row = Database::fetch_row($result);

        if ($row === FALSE) {

            $sql = "INSERT INTO $tblEcommerceLpModule (`lp_module_id`, `course_code`, `lp_title`, `lp_description`)
            VALUES ( '$lpId', '$courseCode', '$lpName', '$lpDescription' )";
        } else {
            $sql = "UPDATE $tblEcommerceLpModule SET `lp_title` = '$lpName', `lp_description` =  '$lpDescription' WHERE lp_module_id = '$lpId'";
        }

        Database::query($sql, __FILE__, __LINE__);

        return Database::affected_rows();
    }

    public function getById($idItemEcommerce) {
        $tableMainEcommerceItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $response = null;
        $idItemEcommerce = intval($idItemEcommerce, 10);

        //$sql = "SELECT ei.id, ei.code, ei.cost, ei.item_type, ei.status, ei.currency, ei.date_start, ei.date_end, ei.duration, ei.duration_type, ei.image "
        //        . " FROM $tableMainEcommerceItems AS ei WHERE ei.id = '$idItemEcommerce' AND item_type = '" . CatalogueCourseModel::TYPE_MODULES . "' LIMIT 1";

        $sql = "SELECT e.id,e.code,e.cost,e.item_type,e.status,e.currency,e.date_start,e.date_end,e.duration,e.duration_type,e.image "
                . " FROM $tableMainEcommerceItems e WHERE e.id = '$idItemEcommerce' AND e.item_type=1 LIMIT 1";

        $result = Database::query($sql, __FILE__, __LINE__);
        while ($row = Database::fetch_array($result)) {
            $response = $row;
        }
        return $response;
    }

    public function getListOfEcommerceItems($status = NULL) {
        $response = array();
        $tblEcommerceItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $sql = "SELECT * FROM $tblEcommerceItems WHERE item_type = " . CatalogueModel::TYPE_MODULES;
        if (!is_null($status)) {
            $status = intval($status, 10);
            $sql .= " AND status = '$status' ";
        }

        $rsCourses = Database::query($sql);
        $row = TRUE;
        while ($row) {
            $row = Database::fetch_object($rsCourses, 'EcommerceCatalogModules');
            if ($row !== FALSE) {
                $response[] = $row;
            }
        }
        return $response;
    }

    /**
     * @todo implement inherited method
     */
    public function getListForStudentPortal() {
        
    }

    /* (non-PHPdoc)
     * @see CatalogueInterface::registerItemsForUser()
     */

    public function registerItemsForUser(array $session, array $transactionResult, $userId) {
//        $modules = array_keys($session['shopping_cart']['items']);
        $modules = array_keys($session['items_paid']);
        foreach ($modules as $module) {
            $params = array();
            $params['user_id'] = $userId;
            $params['ecommerce_items_id'] = $module;
            $params['role'] = '5';
            $params['group_id'] = '';
            $params['tutor_id'] = '';
            $params['sort'] = 0;
            $params['user_course_cat'] = '';

            EcommerceUserPrivilegesDao::create()->save($params);
        }


        return true;
    }

    public function registerItemsIntoUser(array $session, $userId) {
//       $modules = array_keys($session['shopping_cart']['items']);
        $modules = array_keys($session['items_paid']);
        $courseCodes = array();
        foreach ($modules as $module) {
            $tblEcommerceLpModulePack = Database::get_main_table(TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS);
            $sql = "SELECT * FROM $tblEcommerceLpModulePack WHERE ecommerce_items_id = " . $module;
            $res = Database::query($sql);
            if (Database::num_rows($res) > 0) {
                $objcourse = Database::fetch_object($res);
                if (!in_array($objcourse->lp_module_course_code, $courseCodes)) {
                    $courseCodes[] = $objcourse->lp_module_course_code;
                }
            }
        }
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

    public function getShoppingCartList() {
        $response = '';
        $modules = array();

        $modules = CatalogueModuleModel::create()->getListOfEcommerceItems(EcommerceCourse::ECOMMERCECOURSE_STATUS_ACTIVE);
        $catalog = api_get_payment_setting('catalog');
        $response .= '<div class="items_catalogue_box"><div class="section" id="items_catalogue_list" rel="' . count($modules) . '">';
        $response .= '<div class="row"><div class="form_header">' . $catalog . '</div></div>';

        $response .= '<div><ul id="shoppingCartCatalog" class="jcarousel-skin-tango" rel="module">';

        $langTime = get_lang('Duration');
        $langPrice = get_lang('Price');
        $langAddToCart = get_lang('AddToCartHome');

        $currency = $_SESSION['shopping_cart']['currency'];
        foreach ($modules as $module) {
//            $courseFull = $module->getCourseFull();
            $duration = $module->duration . ' ' . get_lang($module->duration_type);
            if ($module->image) {
                $image = $module->image;
            } else {
                $image = "thumb_dokeos.jpg";
            }
            $img_path = api_get_path(WEB_PATH) . 'home/default_platform_document/ecommerce_thumb/' . $image;

            $price = ($currency['code'] == 'USD') ? $currency['symbol'] . ' ' . $courseFull->cost : $courseFull->cost . ' ' . $currency['symbol'];
            $response .= '<li>';
            $response .= <<<EOF
<div class="course_catalog_container" rel="{$courseFull->code}">
    <p><a href="main/catalogue/module_pack_details.php?id={$module->id}">
        <img width="130" height=160" src="{$img_path}"><br/>
        <font size="2"><b>{$courseFull->code}</b></font>
    </a></p>
    <p><div id="duration_catalogue">{$langTime}: {$duration}</div></p>
    <p><div id="price_catalogue">{$price}</div></p>
    <p><a href="" class="addToCartCourse addToCartCourseClick"><span>{$langAddToCart}</span></a></p>        
</div>
EOF;
            $response .= '</li>';
        }
        $response .= '</ul></div>';
        /** /
          $response .= '<div><table width="100%" id="shoppingCartCatalog" rel="module">';

          $i = 0;

          //        / *
          //         * @var $course EcommerceCourse
          //         * /
          foreach ( $modules as $module )
          {

          if ( $i % 3 == 0 )
          {
          $response .= '<tr>';
          }

          // $courseFull = $module->getCourseFull();
          $langTime = get_lang( 'Duration' );
          $langPrice = get_lang( 'Price' );
          $langAddToCart = get_lang( 'AddToCart' );

          $currencyIsoCode = CatalogueFactory::getObject()->getDefaultCatalogue();
          $currency = Currency::create()->getCurrencyByIsoCode( $currencyIsoCode->currency );
          $currency = $currency['symbol'];

          $duration = $module->duration .' '. get_lang($module->duration_type);
          $response .= <<<EOF
          <td width="33%" valign="top">
          <div class="course_catalog_container" rel="{$module->id}">
          <a href="main/catalogue/module_pack_details.php?id={$module->id}">
          <font size="2"><b>{$module->code}</b></font>
          </a>
          <p>{$langTime}: {$duration}</p>
          <p>{$langPrice}: {$currency} {$module->cost}</p>
          <p><a href="" class="addToCartCourse addToCartCourseClick"><span>{$langAddToCart}</span></a></p>
          </div>
          </td>
          EOF;

          if ( $i % 6 == 0 && $i != 0)
          {
          $response .= '</tr>';
          }

          $i ++;
          }
          $response .= '</table></div>';
          /* */
        $response .= '</div></div>';

        return $response;
    }

}
