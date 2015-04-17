<?php

class application_ecommerce_models_ModelEcommerce {

    public function belongModuleCourse($id_item, $course_code, $id_module) {
        $sql = "SELECT lp_module_lp_module_id FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS) .
                " WHERE ecommerce_items_id='" . $id_item . "' AND lp_module_course_code='" . $course_code . "' AND lp_module_lp_module_id='" . $id_module . "'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetOne($sql);
        return $returnValue;
    }

    public function listSessionOuter($arrayIdSessionItem) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_SESSION) . " ";
        if (is_array($arrayIdSessionItem)) {
            if (count($arrayIdSessionItem) > 0) {
                $id_session = implode(",", $arrayIdSessionItem);
                $sql.=" WHERE id NOT IN (" . $id_session . ")";
            }
        }

        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetAll($sql);
        return $returnValue;
    }

    public function saveSession($id_session) {
        try {
            $objSession = $this->getSessionById($id_session);
            $arraySessionToSave = $this->convertArraySessionEcommerce($objSession);
            $table = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS));
            $sql = "INSERT INTO " . $table . " (`id`,`code`,`cost`,`item_type`,`status`,`currency`,`date_start`,`date_end`,`duration`,`duration_type`,`image`,`description`,`id_category`,`id_session`) VALUES (NULL,'" . $arraySessionToSave['code'] . "','" . $arraySessionToSave['cost'] . "','" . $arraySessionToSave['item_type'] . "','" . $arraySessionToSave['status'] . "','" . $arraySessionToSave['currency'] . "','" . $arraySessionToSave['date_start'] . "','" . $arraySessionToSave['date_end'] . "','" . $arraySessionToSave['duration'] . "','" . $arraySessionToSave['duration_type'] . "','" . $arraySessionToSave['image'] . "','" . $arraySessionToSave['description'] . "',NULL,'" . $arraySessionToSave['id_session'] . "')";
            Database::query($sql);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function convertArraySessionEcommerce($arraySession) {
        $arraySessionEcommerce['code'] = '-';
        $arraySessionEcommerce['cost'] = $arraySession->cost;
        $arraySessionEcommerce['item_type'] = 3; //type session
        $arraySessionEcommerce['status'] = $arraySession->visibility;
        $arraySessionEcommerce['currency'] = 0; //moneda
        $arraySessionEcommerce['date_start'] = $arraySession->date_start;
        $arraySessionEcommerce['date_end'] = $arraySession->date_end;
        $arraySessionEcommerce['duration'] = $arraySession->duration;
        $arraySessionEcommerce['duration_type'] = $arraySession->duration_type;
        $arraySessionEcommerce['image'] = $arraySession->image;
        $arraySessionEcommerce['description'] = $arraySession->description;
        $arraySessionEcommerce['id_session'] = $arraySession->id;
        return $arraySessionEcommerce;
    }

    public function getSessionById($id_session) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_SESSION) . " WHERE id = '" . $id_session . "'";
        $result = Database::query($sql);
        while ($objSession = Database::fetch_object($result))
            return $objSession;
    }

    public function getSession($id_session) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id_session = '" . $id_session . "'";

        $result = Database::query($sql);
        while ($objSession = Database::fetch_object($result))
            return $objSession;
    }

    public function getTitleSession($id_session) {
        $sql = "SELECT name FROM " . Database::get_main_table(TABLE_MAIN_SESSION) . " WHERE id = '" . $id_session . "'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetOne($sql);
        return $returnValue;
    }

    public function saveCategory($objCategoryBeans) {
        try {
            $arrayform = (array) $objCategoryBeans;
            $table = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY));
            //appcore_db_DB::conn()->debug = 1;
            appcore_db_DB::conn()->StartTrans();
            if ($arrayform['id_category'] == '0') {
                unset($arrayform['id_category']);
                appcore_db_DB::conn()->AutoExecute($table, $arrayform, 'INSERT');
            }
            else
                appcore_db_DB::conn()->AutoExecute($table, $arrayform, 'UPDATE', "id_category = $objCategoryBeans->id_category");
            appcore_db_DB::conn()->CompleteTrans();
            $objCategoryBeans->result = true;
            $objCategoryBeans->message = get_lang('SucessfulOperation');
        } catch (Exception $e) {
            $objCategoryBeans->result = false;
            $objCategoryBeans->message = $e->getMessage();
        }

        return $objCategoryBeans;
    }

    public function updateActive($form, $id_category) {
        try {
            $table = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY));
            //appcore_db_DB::conn()->debug = 1;
            appcore_db_DB::conn()->StartTrans();
            appcore_db_DB::conn()->AutoExecute($table, $form, 'UPDATE', "id_category = $id_category");
            appcore_db_DB::conn()->CompleteTrans();
        } catch (Exception $e) {
            
        }
    }

    public function updatePayment($course_code) {
        try {
            $table = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_COURSE));
            $form['payment'] = '1';
            //appcore_db_DB::conn()->debug = 1;
            appcore_db_DB::conn()->StartTrans();
            appcore_db_DB::conn()->AutoExecute($table, $form, 'UPDATE', "code = '" . $course_code . "'");
            appcore_db_DB::conn()->CompleteTrans();
        } catch (Exception $e) {
            
        }
    }

    public function updateActiveItem($form, $id_item) {
        try {
            $table = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS));
            //appcore_db_DB::conn()->debug = 1;
            appcore_db_DB::conn()->StartTrans();
            appcore_db_DB::conn()->AutoExecute($table, $form, 'UPDATE', "id = $id_item");
            appcore_db_DB::conn()->CompleteTrans();
        } catch (Exception $e) {
            
        }
    }

    public function deleteCategory($id_category) {
        $sql = "DELETE FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY) . " WHERE id_category = " . $id_category;
        $conexion = appcore_db_DB::conn();
        $conexion->Execute($sql);
    }

    public function deleteItemBySession($id_session) {
        $sql = "DELETE FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id_session = " . $id_session;
        $conexion = appcore_db_DB::conn();
        $conexion->Execute($sql);
    }

    public function deleteAllCategory($arrayCategory) {
        $sql = "DELETE FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY) . " WHERE id_category IN (" . $arrayCategory . ")";
        $conexion = appcore_db_DB::conn();
        $conexion->Execute($sql);
    }

    public function deleteAllSession($arrayItem) {
        $sql = "DELETE FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id IN (" . $arrayItem . ")";
        $conexion = appcore_db_DB::conn();
        $conexion->Execute($sql);
    }

    public function deleteAllModule($arrayItem) {
        $sql = "DELETE FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id IN (" . $arrayItem . ")";
        $conexion = appcore_db_DB::conn();
        $conexion->Execute($sql);
    }

    public function getCourseEcommerce($cod_course) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE code like '" . $cod_course . "'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetRow($sql);
        return $returnValue;
    }

    public function getListCategory($parameters = null) {
//        $item_type;
//        switch ($this->getEcommerceCatalogType()) {
//            case 1: $item_type=" AND item_type = '3' ";
//                break;
//            case 2: $item_type=" AND item_type = '2' ";
//                break;
//            case 3: $item_type=" AND item_type = '1' ";
//                break;
//        }
        //echo "SELECT *, (SELECT count(*) FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE status=1 ".$item_type." AND id_category = c.id_category) as amount FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY) . " c ";
        $sql = "SELECT *, (SELECT count(*) FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE  id_category = c.id_category) as amount FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY) . " c ";
        if ($parameters != null) {
            $sql.=" ORDER BY " . $parameters['orderby'] . " " . $parameters['order'];
        }
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetAll($sql);
        return $returnValue;
    }

    public function getCourse($code) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_COURSE) . " WHERE code like  '" . $code . "'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetRow($sql);
        return $returnValue;
    }

    public function getItemEcommerce($id_item) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id = '" . $id_item . "'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetRow($sql);
        return $returnValue;
    }

    public function getItem($id_item) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id = '" . $id_item . "'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetRow($sql);
        $returnValue['title'] = $this->getNameCourse($returnValue);
        if (empty($returnValue['description'])) {
            $returnValue['description'] = get_lang("NoSummaryForThisProduct");
        }
        if ($returnValue['chr_type_cost'] == '0') {
            if ($returnValue['code'] == '-')
                $returnValue['code'] = $id_item;
            $returnValue['button'] = '<div class="clear"></div></div><a class="addToCartCourseFree addToCartCourseFreeButton" href="javascript:void(0);" id="' . $returnValue['code'] . '" ><span style="line-height:30px;">' . get_lang('StartNow') . '</span></a>';
        } else {
            $returnValue['button'] = '';
        }
        return $returnValue;
    }

    public function getCountCategory($id_category) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id_category = '" . $id_category . "'";
        switch ($this->getEcommerceCatalogType()) {
            case 1: $sql.=" AND item_type = '3' ";
                break;
            case 2: $sql.=" AND item_type = '2' ";
                break;
            case 3: $sql.=" AND item_type = '1' ";
                break;
        }
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetAll($sql);
        return count($returnValue);
    }

    public function getCategory($id_category) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY) . " WHERE id_category =  '" . $id_category . "'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetRow($sql);
        if (count($returnValue) > 0)
            return (object) $returnValue;
        else
            return null;
    }

    public function getCoursePaginator($id_category = 0) {
        global $_configuration;

        $tableEcommerceItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $sql = "SELECT ce.* FROM $tableEcommerceItems AS ce ";

        if ($_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $access_url_id = api_get_current_access_url_id();

            switch ($this->getEcommerceCatalogType()) {
                case ECOMMERCE_ITEM_TYPE_COURSE:
                    $access_url_rel_course_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
                    $tableMainCourse = Database::get_main_table(TABLE_MAIN_COURSE);

                    $sql.= " INNER JOIN $tableMainCourse AS c ON(c.code = ce.code) " .
                            " INNER JOIN $access_url_rel_course_table url_rel_course ON (c.code = url_rel_course.course_code AND url_rel_course.access_url_id = $access_url_id) ";
                    break;
                case 1:
                    $table_session = Database::get_main_table(TABLE_MAIN_SESSION);
                    $table_access_url_rel_session = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_SESSION);
                    $sql.= " INNER JOIN $table_session s ON (s.id = ce.id_session) " .
                            " INNER JOIN $table_access_url_rel_session ar ON (ar.session_id = s.id AND ar.access_url_id = $access_url_id) ";
                    break;
            }
        }

        $sql .= " WHERE ce.status = 1 ";
        if ($id_category > 0) {
            $sql .= " AND ce.id_category = $id_category ";
        }

//        if($idtype=='ALLCAT'){
//            $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id_category IS NOT NULL ";
//        }
        switch ($this->getEcommerceCatalogType()) {
            case 1: $sql.=" AND item_type = '3' ";
                break;
            case 2: $sql.=" AND item_type = '2' ";
                break;
            case 3: $sql.=" AND item_type = '1' ";
                break;
        }
        $sql.=" ORDER BY sort ASC";
        $showOnlyTTC = 1;
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetAll($sql);
        if (count($returnValue) > 0) {
            foreach ($returnValue as $index => $arrayItem) {

                $arrayItem['title'] = $this->getNameCourse($arrayItem);
                $show_imgs = api_get_setting('display_categories_on_homepage');

                if ($show_imgs == 'false') {
                    $arrayItem['image'] = '../../../main/img/pixel.gif';
                } else {
                    if ($arrayItem['item_type'] == '3') {
                        $image = $this->getImageSession($arrayItem);
                        
                        $sys_image = api_get_path(SYS_PATH) . 'home/default_platform_document/ecommerce_thumb/' . $image;
                        $web_image = api_get_path(WEB_PATH) . 'home/default_platform_document/ecommerce_thumb/' . $image;
                        if (!is_file($sys_image) || $image == 'default-sessions.png')
                            $web_image = api_get_path(WEB_PATH) . "main/application/ecommerce/assets/images/course_logo_default.jpg";

                        //$sys_image = api_get_path(SYS_PATH) . 'courses/t/ecommerce_thumb/' . $image;
                        //$web_image = api_get_path(WEB_PATH) . 'courses/default_platform_document/ecommerce_thumb/' . $image;
                        //if (!is_file($sys_image))
                        //    $web_image = api_get_path(WEB_PATH) . 'main/application/ecommerce/assets/images/course_logo_default.jpg';

                        $arrayItem['image'] = $web_image;
                        $arrayItem['w'] = '185px';
                        $arrayItem['h'] = '140px';
                        /*
                          if($image == 'default-sessions.png'){
                          $arrayItem['image'] = api_get_path(WEB_PATH).'home/default_platform_document/ecommerce_thumb/default-sessions.png';
                          $arrayItem['w'] = '185px';
                          $arrayItem['h'] = '140px';
                          }else{
                          unset($arrayItem['image']);
                          $arrayItem['image'] = api_get_path(WEB_PATH).'home/default_platform_document/ecommerce_thumb/'.$image;
                          $arrayItem['w'] = '185px';
                          $arrayItem['h'] = '140px';
                          }
                         */
                    } else {

                        $courseInfo = CourseManager::get_course_information($arrayItem['code']);

                        $sys_image = api_get_path(SYS_PATH) . 'courses/' . $courseInfo['directory'] . '/course_logo.';
                        $web_image = api_get_path(WEB_PATH) . "main/application/ecommerce/assets/images/course_logo_default.jpg";
                        $exts = array("jpg", "jpeg", "gif", "png");
                        foreach ($exts as $ext) {
                            $img_path = $sys_image . $ext;
                            if (is_file($img_path)) {
                                $web_image = api_get_path(WEB_PATH) . 'courses/' . $courseInfo['directory'] . '/course_logo.' . $ext;
                                break;
                            }
                        }

                        //$sys_image = api_get_path(SYS_PATH) . 'courses/' . $courseInfo['directory'] . '/' . $arrayItem['image'];
                        //$web_image = api_get_path(WEB_PATH) . 'courses/' . $courseInfo['directory'] . '/' . $arrayItem['image'];
                        //if (!is_file($sys_image))
                        //    $web_image = api_get_path(WEB_PATH) . "main/application/ecommerce/assets/images/course_logo_default.jpg";

                        $arrayItem['image'] = $web_image;
                        $arrayItem['w'] = '185px';
                        $arrayItem['h'] = '140px';

                        /*
                          if(empty($arrayItem['image'])){
                          $arrayItem['image'] = '../main/application/ecommerce/assets/images/default-courses.png';
                          $arrayItem['w'] = '185px';
                          $arrayItem['h'] = '140px';
                          }else{
                          $arrayItem['image'] = '../main/application/ecommerce/assets/images/'.$arrayItem['image'];
                          $arrayItem['w'] = '185px';
                          $arrayItem['h'] = '140px';
                          }
                         */
                    }
                }





                $content = strip_tags($arrayItem['description']);
                $summary = strip_tags($arrayItem['summary']);
                $arrayItem['summary'] = substr($summary, 0, 246);
                $arrayItem['description'] = substr($content, 0, 246);
                $arrayItem['seemore'] = get_lang('EcommerceLearnMore');
                $arrayItem['addtocart'] = get_lang('EcommerceAddToCart');
                $arrayItem['addtocartfree'] = get_lang('StartNow');
                $arrayItem['langprice'] = get_lang('Price');
                $arrayItem['langduration'] = get_lang('Duration');
                switch ($arrayItem['item_type']) {
                    case 3: $arrayItem['type'] = 'session';
                        $arrayItem['codeitem'] = $arrayItem['id'];
                        break;
                    case 2: $arrayItem['type'] = 'course';
                        $arrayItem['codeitem'] = $arrayItem['code'];
                        break;
                    case 1: $arrayItem['type'] = 'module';
                        $arrayItem['codeitem'] = $arrayItem['id'];
                        break;
                }
                $arrayItem['priceinfo'] = ($arrayItem['chr_type_cost'] == 'TTC' ? api_number_format(api_get_price_ttc($arrayItem['cost'])) : api_number_format($arrayItem['cost_ttc'])) . ' ' . (api_get_setting('e_commerce_catalog_currency') == '978' ? '&euro;' : '$') . ' ' . (($showOnlyTTC) ? 'TTC' : $arrayItem['chr_type_cost']);
                $arrayItem['durationinfo'] = $arrayItem['duration'] . '    ' . ( ($arrayItem['duration'] <= 1) ? ucfirst(get_lang($arrayItem['duration_type'])) : ucfirst(get_lang($arrayItem['duration_type'])) );
                $returnValue[$index] = $arrayItem;
            }
        }
        return $returnValue;
    }

    public function getEcommerceCatalogType() {
//        247  e_commerce_catalog_type  1       Sessions    
//        221  e_commerce_catalog_type  2       Courses     
//        248  e_commerce_catalog_type  3       Modules          
        $sql = "SELECT selected_value FROM " . Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT) . " WHERE variable like 'e_commerce_catalog_type'";
        $res = Database::query($sql);
        while ($settings = Database::fetch_row($res))
            return $settings[0];
    }

    public function getNameCourse($arrayItem) {
        $returnValue = '';
        switch ($arrayItem['item_type']) {
            case 1:
                $sql = "SELECT code FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id = '" . $arrayItem['id'] . "'";
                $conexion = appcore_db_DB::conn();
                $return = $conexion->GetOne($sql);
                $returnValue = $return;
                break;
            case 2:
                $sql = "SELECT title FROM " . Database::get_main_table(TABLE_MAIN_COURSE) . " WHERE code like '" . $arrayItem['code'] . "'";
                $conexion = appcore_db_DB::conn();
                $return = $conexion->GetOne($sql);
                $returnValue = $return;
                break;
            case 3:
                $sql = "SELECT name FROM " . Database::get_main_table(TABLE_MAIN_SESSION) . " WHERE id = '" . $arrayItem['id_session'] . "'";
                $conexion = appcore_db_DB::conn();
                $return = $conexion->GetOne($sql);
                $returnValue = $return;
                break;
        }
        return $returnValue;
    }

    public function getImageSession($objItem) {
        $returnValue = '';

        switch ($objItem['item_type']) {
            case 3:
                $sql = "SELECT image FROM " . Database::get_main_table(TABLE_MAIN_SESSION) . " WHERE id = '" . $objItem['id_session'] . "'";
                $res = Database::query($sql);
                $image = Database::fetch_row($res);
                $returnValue = $image[0];
                break;
        }
        return $returnValue;
    }

    public function updateImageBySession($objCourseBean, $id_item_commerce) {
        $sql = "UPDATE   " . Database::get_main_table(TABLE_MAIN_SESSION) . " set image ='" . $objCourseBean->image . "'  WHERE id = '" . $objCourseBean->id_session . "'";
        $res = Database::query($sql);
    }

    public function updateCourse($objCourseBean, $id_item_commerce) {
        try {
            $description = $objCourseBean->description;
            $arrayform = (array) $objCourseBean;
            $arrayform['description'] = $description;
            $table = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS));
            //appcore_db_DB::conn()->debug = 1;
            appcore_db_DB::conn()->StartTrans();
            appcore_db_DB::conn()->AutoExecute($table, $arrayform, 'UPDATE', "id = $id_item_commerce");
            appcore_db_DB::conn()->CompleteTrans();
            $objCourseBean->result = true;
            $objCourseBean->message = get_lang('SucessfulOperation');
        } catch (Exception $e) {
            $objCourseBean->result = false;
            $objCourseBean->message = $e->getMessage();
        }

        return $objCourseBean;
    }

    public function updateCourseTrainer2($objCourseBean, $id_item_commerce) {
        try {
            $description = $objCourseBean->description;
            $arrayform = (array) $objCourseBean;
            $arrayform['description'] = $description;

            $table = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS));
            //appcore_db_DB::conn()->debug = 1;
            appcore_db_DB::conn()->StartTrans();
            appcore_db_DB::conn()->AutoExecute($table, $arrayform, 'UPDATE', "id = $id_item_commerce");
            appcore_db_DB::conn()->CompleteTrans();
            $objCourseBean->result = true;
            $objCourseBean->message = get_lang('SucessfulOperation');
        } catch (Exception $e) {
            $objCourseBean->result = false;
            $objCourseBean->message = $e->getMessage();
        }

        return $objCourseBean;
    }

    public function getCatalogSettings() {
        $table_main_settings = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
        $table_main_settings_options = Database::get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);

        $sql = "SELECT sc.variable,sc.type,sc.category,sc.selected_value, sc.title, sc.comment, so.display_text
        FROM $table_main_settings as sc
        JOIN $table_main_settings_options as so
        ON sc.variable = so.variable AND sc.selected_value = so.value
        WHERE sc.variable = 'e_commerce_catalog_type' LIMIT 1";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetRow($sql);
        return (object) $returnValue;
    }

    public function getSessionData($from = 0, $number_of_items = 100, $column = 1, $direction = ' ASC ') {
        global $_configuration;

        $tbl_ecom_items = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
        $tbl_ecom_category = Database::get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY);
        $tbl_access_url_rel_session = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_SESSION);


        if ($_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $access_url_id = api_get_current_access_url_id();
            $access_url = " INNER JOIN $tbl_access_url_rel_session ar ON (ar.session_id = s.id AND ar.access_url_id = $access_url_id) ";
        }

        $sql = " SELECT ei.id as col2, 
                        s.name as col3, 
                        ec.chr_category as col4, 
                        ei.cost as col5, 
                        ei.duration as col6, 
                        ei.status as col7, 
                        ei.duration_type as col8,
                        ei.id_session as col9,
                        ei.chr_type_cost as col10,
                        ei.cost_ttc as col11,
                        ei.sort as col12
                 FROM $tbl_ecom_items ei
                 INNER JOIN $tbl_session s ON s.id = ei.id_session
                 $access_url
                 LEFT JOIN $tbl_ecom_category ec ON ec.id_category = ei.id_category
                 WHERE ei.item_type = '3'";
        $sql.= " ORDER BY col$column $direction ";
        $sql.= " LIMIT $from,$number_of_items";
        $rs = Database::query($sql);

        $data = array();
        if (Database::num_rows($rs) > 0) {
            while ($row = Database::fetch_row($rs)) {
                if ($row[5] == 1) {
                    $row[5] = '<div id="div_' . $row[0] . '" style="text-align:center"><span class="ecommerce_link" onclick="xajax_active(0,' . $row[0] . ')"><img title="' . get_lang('langVisible') . '" class="actionplaceholdericon actionvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span></div>';
                } else {
                    $row[5] = '<div  id="div_' . $row[0] . '" style="text-align:center"><span class="ecommerce_link" onclick="xajax_active(1,' . $row[0] . ')"><img title="' . get_lang('langInvisible') . '" class="actionplaceholdericon actioninvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span></div>';
                }
                $duration = '<center>' . $row[4] . ' ' . get_lang(ucfirst($row[6])) . '</center>';

                $prixHT = $row[3];
                if ($row[8] == 'TTC') {
                    $prixHT = $row[9];
                }
                $move = Display::return_icon('pixel.gif', get_lang('Move'), array("class" => "actionplaceholdericon actionsdraganddrop"));
                $data[] = array($row[0], $move, $row[0], $row[1], $row[2], '<center>' . api_number_format($prixHT, false) . '</center>', $duration, $row[5], $row[7]);
            }
        }
        return $data;
    }

    public function getListSession() {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE item_type = '3'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetAll($sql);
        return $returnValue;
    }

    public function getCountSessions() {
        return count($this->getListSession());
    }

    public function getListModule() {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE item_type = '1'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetAll($sql);
        return $returnValue;
    }

    public function getListCourse() {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_COURSE);
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetAll($sql);
        return $returnValue;
    }

    public function getModulesByCourse($code) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_LP_MODULE) . " WHERE course_code like '" . $code . "'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetAll($sql);
        return $returnValue;
    }

    public function saveModule($objBeanModule, $form) {
        try {
            $description = $objBeanModule->description;
            $arrayform = (array) $objBeanModule;
            $arrayform['description'] = $description;
            $table = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS));
            //appcore_db_DB::conn()->debug = 1;
            appcore_db_DB::conn()->StartTrans();
            if ($form['id_item_commerce'] > 0) {
                $id_item = $form['id_item_commerce'];
                appcore_db_DB::conn()->AutoExecute($table, $arrayform, 'UPDATE', "id = $id_item");
                $collectionCourse = $this->getListCourse();
                if (count($collectionCourse) > 0) {
                    $this->clearModules($id_item);
                    foreach ($collectionCourse as $index => $arrayCourse) {
                        if (isset($form[$arrayCourse['code']])) {
                            $array_modules = $form[$arrayCourse['code']];
                            if (count($array_modules) > 0) {
                                foreach ($array_modules as $indice => $id_module) {
                                    $table_lp_pack = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS));
                                    $array_lp_module['ecommerce_items_id'] = $id_item;
                                    $array_lp_module['lp_module_lp_module_id'] = $id_module;
                                    $array_lp_module['lp_module_course_code'] = $arrayCourse['code'];
                                    appcore_db_DB::conn()->AutoExecute($table_lp_pack, $array_lp_module, 'INSERT');
                                }
                            }
                        }
                    }
                }
            } else {
                appcore_db_DB::conn()->AutoExecute($table, $arrayform, 'INSERT');
                $objBeanModule->id = appcore_db_DB::conn()->Insert_ID();
                $collectionCourse = $this->getListCourse();
                if (count($collectionCourse) > 0) {
                    foreach ($collectionCourse as $index => $arrayCourse) {
                        if (isset($form[$arrayCourse['code']])) {
                            $array_modules = $form[$arrayCourse['code']];
                            if (count($array_modules) > 0) {
                                foreach ($array_modules as $indice => $id_module) {
                                    $table_lp_pack = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS));
                                    $array_lp_module['ecommerce_items_id'] = $objBeanModule->id;
                                    $array_lp_module['lp_module_lp_module_id'] = $id_module;
                                    $array_lp_module['lp_module_course_code'] = $arrayCourse['code'];
                                    appcore_db_DB::conn()->AutoExecute($table_lp_pack, $array_lp_module, 'INSERT');
                                }
                            }
                        }
                    }
                }
            }
            appcore_db_DB::conn()->CompleteTrans();
            $objBeanModule->result = true;
            $objBeanModule->message = get_lang('SucessfulOperation');
        } catch (Exception $e) {
            $objBeanModule->result = false;
            $objBeanModule->message = $e->getMessage();
        }

        return $objBeanModule;
    }

    public function clearModules($id_item) {
        $sql = "DELETE FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS) . " WHERE ecommerce_items_id IN (" . $id_item . ")";
        $conexion = appcore_db_DB::conn();
        $conexion->Execute($sql);
    }

    public function deleteItem($id_item) {
        $sql = "DELETE FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id = '" . $id_item . "'";
        $conexion = appcore_db_DB::conn();
        $conexion->Execute($sql);
    }

    public function loadSession($id_session) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_SESSION) . " WHERE id = '" . $id_session . "'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetRow($sql);
        return $returnValue;
    }

    public function saveItem($arrayItem) {
        try {
            $table = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS));
            //appcore_db_DB::conn()->debug = 1;
            appcore_db_DB::conn()->StartTrans();
            appcore_db_DB::conn()->AutoExecute($table, $arrayItem, 'INSERT');
            appcore_db_DB::conn()->CompleteTrans();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function hasModule($code_course) {
        $return = false;
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_LP_MODULE) . " WHERE course_code like '" . $code_course . "'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetAll($sql);
        if (count($returnValue) > 0)
            $return = true;
        return $return;
    }

    public function getItemByCourse($course_code) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE code like  '" . $course_code . "'";
        $conexion = appcore_db_DB::conn();
        $returnValue = $conexion->GetRow($sql);
        return $returnValue;
    }

    public function updateImageById($image = '', $id_item_commerce) {
        try {
            $arrayform['image'] = $image;
            $table = str_replace('`', '', Database :: get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS));
            //appcore_db_DB::conn()->debug = 1;
            appcore_db_DB::conn()->StartTrans();
            appcore_db_DB::conn()->AutoExecute($table, $arrayform, 'UPDATE', "id = $id_item_commerce");
            appcore_db_DB::conn()->CompleteTrans();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}
