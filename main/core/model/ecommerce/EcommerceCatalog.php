<?php

require_once api_get_path(SYS_PATH) . 'main/core/dao/ecommerce/EcommerceCourseDao.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/course/CourseModel.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/CatalogueManager.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/CatalogueFactory.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/CatalogueCourseModel.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/CatalogueSessionModel.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/CatalogueModel.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/CatalogueModuleModel.php';

class EcommerceCatalog {

    public $currentValue = '';
    public $optionValues = array();

    public static function create() {
        return new EcommerceCatalog();
    }

    public function deleteCourseEcommerce($courseCode) {
        $tblCourseEcommerce = Database::get_main_table(TABLE_MAIN_COURSE_ECOMMERCE);
        $courseCode = trim($courseCode);

        if ($courseCode == '') {
            return 0;
        }
        $tbl_ecommerce = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $sql1 = "UPDATE {$tbl_ecommerce} SET status = 0 WHERE code = '$courseCode'";
        Database::query($sql1, __FILE__, __LINE__);

        $sql = "DELETE FROM $tblCourseEcommerce WHERE course_code = '$courseCode';";

        Database::query($sql, __FILE__, __LINE__);

        return Database::affected_rows();
    }

    public function getCatalogSettings() {
        $table_main_settings = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
        $table_main_settings_options = Database::get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);

        $sql = "SELECT sc.variable,sc.type,sc.category,sc.selected_value, sc.title, sc.comment, so.display_text
        FROM $table_main_settings as sc
        JOIN $table_main_settings_options as so
        ON sc.variable = so.variable AND sc.selected_value = so.value
        WHERE sc.variable = 'e_commerce_catalog_type' LIMIT 1";

        $result = Database::query($sql, __FILE__, __LINE__);
        $this->currentValue = Database::fetch_object($result);

        return $this->currentValue;
    }

    public function getCatalogTypeOptions() {
        $this->optionValues = array();

        $table_main_settings = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
        $table_main_settings_options = Database::get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);

        $sql = "SELECT * FROM $table_main_settings_options WHERE variable = 'e_commerce_catalog_type'";

        $result = Database::query($sql, __FILE__, __LINE__);

        $row = true;
        while ($row) {
            $row = Database::fetch_object($result);
            if ($row !== FALSE) {
                $this->optionValues[$row->value] = $row;
            }
        }

        return $this->optionValues;
    }

    public function getTitleSession($id_session) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_SESSION) . " WHERE id =" . $id_session;
        $result = Database::query($sql, __FILE__, __LINE__);
        while ($object = Database::fetch_object($result))
            $objItem = $object;
        return $objItem->name;
    }

    public function getItemById($id_session) {
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id =" . $id_session;
        $result = Database::query($sql, __FILE__, __LINE__);
        while ($object = Database::fetch_object($result))
            $objItemSession = $object;
        switch ($objItemSession->item_type) {
            case 3:
                $objItemSession->title = $this->getTitleSession($objItemSession->id_session);

                break;
            default : $objItemSession->title = 'default';
                break;
        }
        return $objItemSession;
    }

    public function getCourseByCode($courseCode) {
        $currentCatalogType = intval(get_setting('e_commerce_catalog_type'), 10);
        $response = array();

        switch ($currentCatalogType) {
            case CatalogueModel::TYPE_COURSE :
                $response = CatalogueCourseModel::create()->getCourseByCode($courseCode);
                break;
            case CatalogueModel::TYPE_MODULES :
                $response = CatalogueModuleModel::create()->getById($courseCode);
                break;
            case CatalogueModel::TYPE_SESSION :
                $response = CatalogueSessionModel::create()->getProgrammeByCode($courseCode);
                break;
        }
        return $response;
    }

    public function getCourseByCodeObj($courseCode) {
        return EcommerceCourseDao::create()->getEcommerceCourseByCourseCode($courseCode);
    }

    public function getModuleByCodeObj($moduleCode) {
        return EcommerceCourseDao::create()->getEcommerceModuleByModuleCode($moduleCode);
    }

    public function getCourseCatalogButtonList($code, $url_params, $row) {
        return
                //javascript:if(!confirm(' . "'" . addslashes(api_htmlentities(get_lang("ConfirmYourChoice"), ENT_QUOTES, $charset)) . "'" . ')) return false;
                //ecommerce_courses.php?action=delete&course_code=' . $code . '
                '<a href="ecommerce_course_edit.php?course_code=' . $code . '">' . Display::return_icon('pixel.gif', get_lang('Edit'), array(
                    'class' => 'actionplaceholdericon actionedit')) . '</a>&nbsp;&nbsp;' . '<a href="javascript:void(0);"  onclick="Alert_Confim_Delete(\'ecommerce_courses.php?action=delete&course_code=' . $code . '\',\''.get_lang("ConfirmationDialog").'\',\''.get_lang("ConfirmYourChoice").'\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array(
                    'class' => 'actionplaceholdericon actiondelete')) . '</a>';
//                '<a href="ecommerce_course_edit.php?course_code=' . $code . '">' . Display::return_icon('pixel.gif', get_lang('Edit'), array(
//                    'class' => 'actionplaceholdericon actionedit')) . '</a>&nbsp;&nbsp;' . '<a href="ecommerce_courses.php?action=delete&course_code=' . $code . '"  onclick="javascript:if(!confirm(' . "'" . addslashes(api_htmlentities(get_lang("ConfirmYourChoice"), ENT_QUOTES, $charset)) . "'" . ')) return false;">' . Display::return_icon('pixel.gif', get_lang('Delete'), array(
//                    'class' => 'actionplaceholdericon actiondelete')) . '</a>';
    }

    public function getCourseEcommerceData($from = 0, $number_of_items = 100, $column = 1, $direction = ' ASC ', $get = array()) {
        // Database table definition
        $tableEcommerceItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $currentItemType = intval(get_setting('e_commerce_catalog_type'), 10);

        $response = array();
        switch ($currentItemType) {
            case CatalogueModel::TYPE_COURSE :
                $response = CatalogueCourseModel::create()->getCourseEcommerceData($from, $number_of_items, $column, $direction, $get);
                break;
            /**
             *
             * @todo Implement methods for other types!
             */
        }

        return $response;
    }

    public function getCoursesList() {
        global $_configuration;

        $tableMainCourse = Database::get_main_table(TABLE_MAIN_COURSE);
        $tableMainCourseEcommerce = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);

        $response = array();

        $courseType = CatalogueModel::TYPE_COURSE;

        $sql = "SELECT c.code, c.title,c.visibility,ce.cost,ce.status,ce.date_start,ce.date_end FROM $tableMainCourse AS c
                 LEFT JOIN $tableMainCourseEcommerce AS ce ON c.code = ce.code AND ce.item_type = '$courseType' ";

        if ($_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $access_url_id = api_get_current_access_url_id();
            $access_url_rel_course_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
            $sql.= " INNER JOIN $access_url_rel_course_table url_rel_course ON (c.code = url_rel_course.course_code) AND url_rel_course.access_url_id = $access_url_id";
        }

        $result = Database::query($sql, __FILE__, __LINE__);

        $row = true;
        while ($row) {
            $row = Database::fetch_object($result);
            if ($row !== FALSE) {
                $response[] = $row;
            }
        }
        return $response;
    }

    public function getCurrency() {
        $response = NULL;
    }

    public function getFormForCourseEcommerceByCode($courseCode) {

        $objEcommerceCourse = $this->getCourseByCode($courseCode);

        if (is_null($objEcommerceCourse)) {
            return null;
        }

        $form = new FormValidator('frmEditEcommerceCourse', 'post', 'ecommerce_course_edit.php?course_code=' . $courseCode);

        if ($form->validate()) {
            $submitted = $form->getSubmitValues();

            if (is_array($submitted['date_start']) && (isset($submitted['date_start']['d']) && isset($submitted['date_start']['M']) && isset($submitted['date_start']['Y']))) {
                $submitted['date_start'] = $submitted['date_start']['Y'] . '-' . $submitted['date_start']['M'] . '-' . $submitted['date_start']['d'];
            }
            if (is_array($submitted['date_end']) && (isset($submitted['date_end']['d']) && isset($submitted['date_end']['M']) && isset($submitted['date_end']['Y']))) {
                $submitted['date_end'] = $submitted['date_end']['Y'] . '-' . $submitted['date_end']['M'] . '-' . $submitted['date_end']['d'];
            }

            $currentCatalogType = intval(get_setting('e_commerce_catalog_type'), 10);

            $response = array();

            switch ($currentCatalogType) {
                case CatalogueModel::TYPE_COURSE :
                    CatalogueCourseModel::create()->saveItemEcommerce($submitted);
                    $objEcommerceCourse = CatalogueCourseModel::create()->getCourseByCode($courseCode);
                    break;

                /**
                 *
                 * @todo implement other types
                 *       THIS SHOULD BE USED I
                 */
            }
        }

        if ($objEcommerceCourse->image) {
            $image = $objEcommerceCourse->image;
        } else {
            $image = "thumb_dokeos.jpg";
        }

        $img_path = '';
        if ($image != 'no-image')
            $img_path = api_get_path(WEB_PATH) . 'home/default_platform_document/ecommerce_thumb/' . $image; //directory path to upload






            
//        $thumb_path =  api_get_path(SYS_PATH).'home/default_platform_document/ecommerce_thumb/'.$image; 

        $form->addElement('hidden', 'course_code');

        $form->addElement('file', 'picture', get_lang('AddPicture'));
        $form->addElement('static', 'imagesize', '', get_lang('PNGorJPG'));
        if (!empty($img_path))
            $form->addElement('static', 'thumbimage', get_lang('Preview'), '<img src="' . $img_path . '">');
        $form->addElement('checkbox', 'remove_img', null, get_lang('RemoveImage') . ' / ' . get_lang('WithoutImage'), 1);
        $allowed_picture_types = array('jpg', 'jpeg', 'png', 'gif');
        $form->addRule('picture', get_lang('OnlyImagesAllowed') . ' (' . implode(',', $allowed_picture_types) . ')', 'filetype', $allowed_picture_types);

        $form->addElement('text', 'txtTitle', get_lang('Name'), array(
            'size' => 40, 'readonly' => 'readonly', 'class' => 'grayBg'));

        $form->addElement('html_editor', 'description', get_lang('Description'), null, array('ToolbarSet' => 'Catalogue', 'Width' => '90%', 'Height' => '100'));

        $form->addElement('text', 'cost', get_lang('Cost'), array(
            'size' => 10));

        $form->addElement('radio', 'status', get_lang('Catalog') . ':', get_lang('Yes'), 1);
        $form->addElement('radio', 'status', null, get_lang('No'), 0);

        $group = array();
        $duration_type = array(
            'week' => get_lang('week'),
            'month' => get_lang('month'),
            'year' => get_lang('year')
        );
        $group[] = $form->createElement('text', 'duration', get_lang('Duration'), array('size' => 5));
        $group[] = $form->createElement('select', 'duration_type', null, $duration_type);
        $form->addGroup($group, null, get_lang('Duration'), ' ');

        $form->addElement('date', 'date_start', get_lang('StartDate'));
        $form->addElement('date', 'date_end', get_lang('EndDate'));

        $form->addRule('txtTitle', get_lang('ThisFieldIsRequired'), 'required');
        $form->addElement('style_submit_button', 'submit', get_lang('Save'), array(
            'class' => 'save'));

        $defaults = array();
        $defaults['txtTitle'] = $objEcommerceCourse->title;
        $defaults['description'] = $objEcommerceCourse->description;
        $defaults['course_code'] = $objEcommerceCourse->code;
        $defaults['cost'] = $objEcommerceCourse->cost;
        $defaults['visibility'] = $objEcommerceCourse->visibility;
        $defaults['status'] = $objEcommerceCourse->status;
        $defaults['duration'] = $objEcommerceCourse->duration;
        $defaults['duration_type'] = $objEcommerceCourse->duration_type;
        $defaults['date_start'] = $objEcommerceCourse->date_start;
        $defaults['date_end'] = $objEcommerceCourse->date_end;
        $defaults['picture'] = $objEcommerceCourse->image;
        if ('no-image' == $objEcommerceCourse->image)
            $defaults['remove_img'] = 1;

        $form->setDefaults($defaults);

        return $form;
    }

    public function getItemEcommerceData($ecommerceItemId) {
        $ecommerceItemId = intval($ecommerceItemId, 10);

        $tableEcommerceItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $sql = "SELECT * FROM $tableEcommerceItems WHERE id = '$ecommerceItemId' LIMIT 1;";
        $res = Database::query($sql, __FILE__, __LINE__);
        $ecommerceItem = Database::fetch_object($res, 'EcommerceModel');
    }

    public function getProductList() {

        //sets the currentValue (of the catalog to be used in the next switch)
        $this->getCatalogSettings();

        $response = '';

        $objCatalogue = CatalogueFactory::getObject();
        $response = $objCatalogue->getShoppingCartList();

        return $response;
    }

    public function getCourseListUncategorized() {
        /*
          $array_list = array();
          $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE status = 1 ";
          switch ($this->getEcommerceCatalogType()) {
          case 1: $sql.=" AND item_type = '3' ";
          break;
          case 2: $sql.=" AND item_type = '2' ";
          break;
          case 3: $sql.=" AND item_type = '1' ";
          break;
          }
          $sql.=" ORDER BY sort ASC";
          if ($limit > 0)
          $sql.=" LIMIT " . $limit;
          $res = Database::query($sql);
          while ($course = Database::fetch_object($res)) {
          array_push($array_list, $course);
          }
          return $array_list;
         */
        $array_list = array();
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE status=1 and id_category IS NULL";
        $res = Database::query($sql);
        while ($course = Database::fetch_object($res)) {
            array_push($array_list, $course);
        }
        return $array_list;
    }

    /**
     * get the list of categories in html.
     * @author Johnny Huamani <johnny1402@gmail.com>
     * @return string
     */
    public function getListCategoryHtml() {
        $returnValue = '';
        $returnValue.='<div class="box-right-e" style="">
                <h2 class="getTitleCategory">' . $this->getTitleCategory() . '</h2>';
        $click1 = (count($this->getCourseEcommerce()) > 0) ? ('style="cursor:pointer;background-image:none !important;" onClick="return listAll();"') : ('');
        //$click2 = (count($this->getCourseListUncategorized()) > 0) ? ('style="cursor:pointer;background-image:none !important;" onClick="return CourseListUncategorized();"') : ('');

        $returnValue.='<ul class="allcategory"><li ' . $click1 . ' class="ecommerce_text_category" id="cat-0">' . get_lang('AllCategories') . '(' . count($this->getCourseEcommerce()) . ')</li></ul>';
        $objCollectionCategories = $this->getCategory();

        $returnValue.= '<ul class="categories">';

        foreach ($objCollectionCategories as $objCategory) {
            if (count($this->getCourseEcommerceByCategories(NULL, $objCategory->id_category)) > 0) {
                if (count($this->getCourseEcommerceByCategories(NULL, $objCategory->id_category)) > 0) {
                    $returnValue.='<li style="cursor:pointer;background-image:none !important;" onClick="return listCategory(0, ' . $objCategory->id_category . ', ' . ceil(count($this->getCourseEcommerceByCategories(NULL, $objCategory->id_category)) / 10) . ')"><span class="ecommerce_text_category" id="cat-' . $objCategory->id_category . '">' . $objCategory->chr_category . '(' . count($this->getCourseEcommerceByCategories(NULL, $objCategory->id_category)) . ')</span></li>';
                } else {
                    $returnValue.='<li style="background-image:none !important;" class="ecommerce_text_category">' . $objCategory->chr_category . '(' . count($this->getCourseEcommerceByCategories(NULL, $objCategory->id_category)) . ')</li>';
                }
            }
        }
        $returnValue.= '</ul>';
        // $returnValue.= '<ul class="allcategory"><li ' . $click2 . ' class="ecommerce_text_category" id="cat-0">' . get_lang('CoursesUncategorized') . '(' . count($this->getCourseListUncategorized()) . ')</li></ul>';
        $returnValue.='</div>';
        return $returnValue;
    }

    /**
     * get html for list item ecommerce
     * @author Johnny Huamani <johnny1402@gmail.com>
     */
    public function getHtmlCatalog() {
        $showOnlyTTC = 1;
        $html = '<style>';
        $html.='.catalog_title{height:50px;}' . PHP_EOL;
        $html.='#ElistCatalog{width:740px; height:100%; }' . PHP_EOL;
        $html.='.catalog_list{width:80%x; }' . PHP_EOL;
        $html.='.catalog_department{border-left: 1px solid #EAEAEA;
                height: 100%;
                min-height: 130px;
                padding: 5px;
                vertical-align: top;
                width: 20%;}' . PHP_EOL;
        //$html.='.ecommerce_text_category{padding-left:8px;}'.PHP_EOL;
        $html.='.course_catalog_container{background: url("main/img/blank.gif") no-repeat scroll 0 -3px transparent;}' . PHP_EOL;
        $html.= '</style>';
        $html.='<div id="divDialog"></div>';
        $html.='<table width="100%">';
        $html.='<tr>';
        $html.='<td colspan="2" class="catalog_title"><h1>' . $this->getTitleCatalog() . '</h1>';
        $html.='</td>';
        $html.='</tr>';
        $html.='<tr>';
        $html.='<td class="catalog_list"><div id="ElistCatalog">';
        $objCollectionCourse = $this->getCourseEcommerce(10);
        if (count($objCollectionCourse) > 0) {
            foreach ($objCollectionCourse as $index => $objCourse) {
                $html.='<table class="back_table_catalog">';
                $html.='<tr>';
                $show_imgs = api_get_setting('display_categories_on_homepage');
                $classShowImg = '';
                if ($show_imgs == 'true') {
                    $html.='<td class="box_catalog" style="text-align:center;padding:0px; width:200px;height:150px; overflow: hidden;">';
                    //$html.='<td class="box_catalog" style="text-align:center;padding:0px; width:139px;height:160px;">';
                    //if (strlen(trim($objCourse->image)) > 0)
//                    $url_image = "main/application/ecommerce/assets/images/" . $objCourse->image ;                                                            

                    if ($objCourse->item_type == 3) {
                        $image = $this->getImageSession($objCourse);
                        //$html.='<img style="height: 148px; padding-top:6px; width: 127px;" src="'.  api_get_path(WEB_PATH).'home/default_platform_document/ecommerce_thumb/' . $image . '" />';//                        
                        $sys_image = api_get_path(SYS_PATH) . 'home/default_platform_document/ecommerce_thumb/' . $image;
                        $web_image = api_get_path(WEB_PATH) . 'home/default_platform_document/ecommerce_thumb/' . $image;
                        if (!is_file($sys_image) || $image == 'default-sessions.png')
                            $web_image = api_get_path(WEB_PATH) . "main/application/ecommerce/assets/images/course_logo_default.jpg";
                        $html.='<a href="javascript:void(0);" id="' . $objCourse->id . '" onClick="return viewDetail(this);">';
                        $html.='<img style="height: 140px; padding-top:6px; width: 185px;" src="' . $web_image . '" />';
                        $html.='</a>';
                    } else {
                        $courseInfo = CourseManager::get_course_information($objCourse->code);
                        //$sys_image = api_get_path(SYS_PATH) . 'courses/' . $courseInfo['directory'] . '/' . $objCourse->image;
                        //$web_image = api_get_path(WEB_PATH) . 'courses/' . $courseInfo['directory'] . '/' . $objCourse->image;
                        
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
                        //if (!is_file($sys_image))
                            //$web_image = api_get_path(WEB_PATH) . "main/application/ecommerce/assets/images/default-courses.png";
                        $html.='<a href="javascript:void(0);" id="' . $objCourse->id . '" onClick="return viewDetail(this);">';
                        $html.='<img style="height: 140px; padding-top:6px; max-width: 185px;" src="' . $web_image . '" />';
                        $html.='</a>';
                    }
                    $html.='</td>';
                    //$classShowImg = 'style="width:74.5%!important; margin-left:10px!important;"';
                    $classShowImg = 'style="height:130px; width:66.5%!important; margin-left:10px!important;"';
                }
                $html.='<td class="box_catalog2" ' . $classShowImg . '>';
                $html.='<table width="100%">';
                $html.='<tr style="height:70px">';
                $html.='<td style="font-size: 12px; padding-left: 10px;vertical-align: top; padding-top: 5px;width: 400px;">';
                $html.='<strong style="font-zise:12px;">' . $this->getNameCourse($objCourse) . '</strong><br />';
                if ($objCourse->item_type == 3)
                    $content = $this->getTitleSession($objCourse->code); //strip_tags($objCourse->description);
                else
                    $content = strip_tags($objCourse->summary);
                $content = substr($content, 0, 246);
                $html.=$content;
                $html.='</td>';
                $html.='</tr>';
                //$html .= '<tr style="height:30px"><td style="vertical-align:top; padding-left:10px;width:400px;color:#000;"><strong>' . get_lang('PriceLabel') . ': </strong>' . ($objCourse->chr_type_cost == 'TTC' ? api_number_format(api_get_price_ttc($objCourse->cost)) : api_number_format($objCourse->cost_ttc)) . ' ' . (api_get_setting('e_commerce_catalog_currency') == '978' ? '&euro;' : '$') . ' ' . (($showOnlyTTC) ? 'TTC' : $objCourse->chr_type_cost) . '&nbsp;&nbsp;&nbsp;<strong>' . get_lang('Duration') . ': </strong>' . $objCourse->duration . ' ' . (($objCourse->duration <= 1 ) ? ucfirst(get_lang($objCourse->duration_type)) : ucfirst(get_lang($objCourse->duration_type)) ) . '</td></tr>';
                switch ($objCourse->chr_type_cost) {
                    case '0' : $typeTT = '<tr style="height:30px"><td style="vertical-align:top; padding-left:10px;width:400px;color:#000; margin-top:10px !important; float:left; display:block"><strong>' . get_lang('Price') . ': </strong>' . get_lang('Free') . ' &nbsp;&nbsp;&nbsp;<strong>' . get_lang('Access') . ': </strong>' . $objCourse->duration . ' ' . (($objCourse->duration <= 1 ) ? ucfirst(get_lang($objCourse->duration_type)) : ucfirst(get_lang($objCourse->duration_type)) ) . '</td></tr>';
                        break;
                    default : $typeTT = '<tr style="height:30px"><td style="vertical-align:top; padding-left:10px;width:400px;color:#000;  margin-top:10px !important; float:left; display:block"><strong>' . get_lang('Price') . ': </strong>' . ($objCourse->chr_type_cost == 'TTC' ? api_number_format(api_get_price_ttc($objCourse->cost)) : api_number_format($objCourse->cost_ttc)) . ' ' . (api_get_setting('e_commerce_catalog_currency') == '978' ? '&euro;' : '$') . ' ' . (($showOnlyTTC) ? 'TTC' : $objCourse->chr_type_cost) . '&nbsp;&nbsp;&nbsp;<strong>' . get_lang('CourseAccess') . ': </strong>' . $objCourse->duration . ' ' . (($objCourse->duration <= 1 ) ? ucfirst(get_lang($objCourse->duration_type)) : ucfirst(get_lang($objCourse->duration_type)) ) . '</td></tr>';
                }
                $html .= $typeTT;
                $html.='<tr  style="height:20px;">';
                $html.='<td style="text-align:right;">';
                switch ($objCourse->item_type) {
                    case '1': $type = 'module';
                        $code = $objCourse->id;
                        break;
                    case '2': $type = 'course';
                        $code = $objCourse->code;
                        break;
                    case '3': $type = 'session';
                        $code = $objCourse->id;
                        break;
                }
                $html.='<div style=" float:right; margin-top:-15px;"><table rel="' . $type . '" id="shoppingCartCatalog"><tr><td>';
                //$html.='<a href="#" class="addToCartCourse" id="' . $objCourse->id . '" onClick="return viewDetail(this);"><span>' . get_lang('EcommerceLearnMore') . '</span></a>';
                switch ($objCourse->chr_type_cost) {
                    case '0' : $htmlF = '<a href="#" class="" style="text-decoration: underline;" id="' . $objCourse->id . '" onClick="return viewDetail(this);"><span>' . get_lang('EcommerceLearnMore') . '</span></a>';
                        break;
                    case 'B' : $htmlF = '<a href="#" class="addToCartCourse" id="' . $objCourse->id . '" onClick="return viewDetail(this);"><span>' . get_lang('EcommerceLearnMore') . '</span></a>';
			break;
                    default : $htmlF = '<a href="#" class="" style="text-decoration: underline;" id="' . $objCourse->id . '" onClick="return viewDetail(this);"><span>' . get_lang('EcommerceLearnMore') . '</span></a>';
                }
                $html .= $htmlF;
                //$html.='<a href="#" class="" style="text-decoration: underline;" id="' . $objCourse->id . '" onClick="return viewDetail(this);"><span>' . get_lang('EcommerceLearnMore') . '</span></a>';
                switch ($objCourse->chr_type_cost) {
                    case '0' : $typeTB = str_repeat('&nbsp;', 5) . '<span class="course_catalog_container" rel="' . $code . '">
                                                    <a class="addToCartCourseFree" style="margin-top: -15px;padding: 15px;" href="main/payment/checkout_2_registration.php?id='.$objCourse->id.'&prev=2&chr_type=0" ><span>' . get_lang('StartNow') . '</span></a>
                                                    </span>';
                                                  //. 'main/payment/checkout_2_registration.php?id=' .$_SESSION['cat_id']. '&prev=2'
                        break;
                    default : $typeTB = str_repeat('&nbsp;', 5) . '<span class="course_catalog_container" rel="' . $code . '">
                                                    <a class="addToCartCourse addToCartCourseClick" style="margin-top: -15px;padding: 15px; background:-moz-linear-gradient(center top , #4F9D00, #3e7900);background: -webkit-gradient(linear, left top, left bottom, from(#4F9D00), to(#3e7900));background-image:-o-linear-gradient(top,  #4F9D00, #3e7900);" href="#"><span>' . get_lang('EcommerceAddToCart') . '</span></a>
                                                    </span>';
                }
                $html.= $typeTB;
                $html.= '</td></tr></table></div>';
                $html.='</td>';
                $html.='</tr>';
                $html.='</table>';
                $html.='</td>';
                $html.='</tr>';
                $html.='</table>';
            }
        } else {
            $html.='<div style="width:525px;"></div>';
        }
        $html.='</div></td>';
        $html.='</tr>';
        $html.='</table>';
        $paginate = $this->getCourseEcommerce(11);
        $html.= (count($paginate) > 10) ? '<div id="divPaginator"></div>' : '';
        return $html;
    }

    public function getTitleCatalog() {
        $catalog = api_get_settings_options('catalogName');
        return $catalog[0]['value'];
    }

    public function getTitleCategory() {
        $category = api_get_settings_options('categoryName');
        return $category[0]['value'];
    }

    public function getCourseEcommerceByCategories($id_item = NULL, $id_category) {
        global $_configuration;
        $array_list = array();
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " ce ";

        if ($_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $access_url_id = api_get_current_access_url_id();

            switch ($this->getEcommerceCatalogType()) {
                case ECOMMERCE_ITEM_TYPE_COURSE:
                    $access_url_rel_course_table = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
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

        $sql .= " WHERE ce.status = 1 AND ce.id_category = $id_category";

        switch ($this->getEcommerceCatalogType()) {
            case 1: $sql.=" AND ce.item_type = '3' ";
                break;
            case 2: $sql.=" AND ce.item_type = '2' ";
                break;
            case 3: $sql.=" AND ce.item_type = '1' ";
                break;
        }
        $res = Database::query($sql);
        while ($item = Database::fetch_object($res))
            array_push($array_list, $item);
        return $array_list;
    }

    public function getNameCourse($objItem) {
        $returnValue = '';
        switch ($objItem->item_type) {
            case 2:
                $sql = "SELECT title FROM " . Database::get_main_table(TABLE_MAIN_COURSE) . " WHERE code = '" . $objItem->code . "'";
                $res = Database::query($sql);
                $title = Database::fetch_row($res);
                $returnValue = $title[0];
                break;
            case 3:
                $sql = "SELECT name FROM " . Database::get_main_table(TABLE_MAIN_SESSION) . " WHERE id = '" . $objItem->id_session . "'";
                $res = Database::query($sql);
                $title = Database::fetch_row($res);
                $returnValue = $title[0];
                break;
            case 1:
                $sql = "SELECT code FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . " WHERE id = '" . $objItem->id . "'";
                $res = Database::query($sql);
                $title = Database::fetch_row($res);
                $returnValue = $title[0];
                break;
        }
        return $returnValue;
    }

    public function getImageSession($objItem) {
        $returnValue = '';
        switch ($objItem->item_type) {
            case 3:
                $sql = "SELECT image FROM " . Database::get_main_table(TABLE_MAIN_SESSION) . " WHERE id = '" . $objItem->id_session . "'";
                $res = Database::query($sql);
                $image = Database::fetch_row($res);
                $returnValue = $image[0];
                break;
        }
        return $returnValue;
    }

    public function getCourseEcommerce($limit = '') {
        global $_configuration;
        $array_list = array();

        $tableMainCourse = Database::get_main_table(TABLE_MAIN_COURSE);
        $tableEcommerceItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        //$tableEcommerceCategory = Database::get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY);

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

        switch ($this->getEcommerceCatalogType()) {
            case 1: $sql.=" AND item_type = '3' ";
                break;
            case 2: $sql.=" AND item_type = '2' ";
                break;
            case 3: $sql.=" AND item_type = '1' ";
                break;
        }
        $sql.=" ORDER BY sort ASC";
        if ($limit > 0)
            $sql.=" LIMIT " . $limit;

        $res = Database::query($sql);
        while ($course = Database::fetch_object($res)) {
            array_push($array_list, $course);
        }
        return $array_list;
    }

    public function getEcommerceCatalogType() {
        $sql = "SELECT selected_value FROM " . Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT) . " WHERE variable like 'e_commerce_catalog_type'";
        $res = Database::query($sql);
        while ($settings = Database::fetch_row($res))
            return $settings[0];
    }

    public function getCategory() {
        $array_list = array();
        $sql = "SELECT * FROM " . Database::get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY) . " WHERE bool_active = 1";
        $res = Database::query($sql);
        while ($course = Database::fetch_object($res)) {
            array_push($array_list, $course);
        }
        return $array_list;
    }

    public function getTotalNumberCourseEcommerce() {
        global $_configuration;
        $tableEcommerceItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $tableCourse = Database::get_main_table(TABLE_MAIN_COURSE);
        $currentItemType = intval(get_setting('e_commerce_catalog_type'), 10);
        $sql = "SELECT COUNT(*) AS total FROM $tableEcommerceItems c ";

        if ($_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $access_url_id = api_get_current_access_url_id();
            $access_url_rel_course_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
            $sql.= " INNER JOIN $access_url_rel_course_table url_rel_course ON (c.code = url_rel_course.course_code) AND url_rel_course.access_url_id = $access_url_id";
        }
        $sql.= " INNER JOIN " . $tableCourse . " co ON co.code = c.code";
        $sql .= " WHERE c.item_type = '$currentItemType' AND payment=1";
        $res = Database::query($sql, __FILE__, __LINE__);
        $course = Database::fetch_row($res);
        return intval($course[0], 10);
    }

    public function getValidCatalogCurrencyOptions() {
        $objCatalogueModel = CatalogueFactory::getObject();
        $valid = $objCatalogueModel->getValidCatalogCurrencyOptions();
        return $valid;
    }

    public function updateCurrentCatalogTypeValue($currentValue) {
        $currentValue = intval($currentValue, 10);
        $table_main_settings = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
        $sql = "UPDATE $table_main_settings SET selected_value = $currentValue WHERE variable = 'e_commerce_catalog_type' LIMIT 1";
        $result = Database::query($sql, __FILE__, __LINE__);
        return Database::affected_rows();
    }

    protected function _getListCoursesCatalog() {
        
    }

    protected function _getListModuleCatalog() {
        
    }

    public function saveCatalog($catalogue) {
        $catalogue_table = Database::get_main_table(TABLE_MAIN_CATALOGUE);
        $objCatalog = $this->getCatalogue();

        if ($objCatalog === false) {
            $sql = "INSERT INTO " . $catalogue_table . " SET " . "title	= '" . Database::escape_string($catalogue['title']) . "',
            economic_model		= '" . Database::escape_string($catalogue['economic_model']) . "',
            visible				= '" . Database::escape_string($catalogue['visible']) . "',
            company_logo 	= '" . $catalogue['company_logo'] . "',
            payment 				= '" . implode(',', $catalogue['payments']) . "',
            company_address	= '" . Database::escape_string($catalogue['company_address']) . "',
            cheque_message	= '" . Database::escape_string($catalogue['cheque_message']) . "',
            cc_payment_message	= '" . Database::escape_string($catalogue['cc_payment_message']) . "',
            installment_payment_message	= '" . Database::escape_string($catalogue['install_payment_message']) . "',
            options_selection	= '" . Database::escape_string($catalogue['opt_selection_text']) . "',
            terms_conditions	= '" . Database::escape_string($catalogue['termsconditions']) . "',
            tva_description	= '" . Database::escape_string($catalogue['tvadescription']) . "',
            bank_details	= '" . Database::escape_string($catalogue['bank_details']) . "' ,
            currency	= '" . $catalogue['catalog_currency'] . "' ";
        } else {

            $sql = "UPDATE " . $catalogue_table . " SET " . " 
            title				= '" . Database::escape_string($catalogue['title']) . "',
            economic_model		= '" . Database::escape_string($catalogue['economic_model']) . "',
            visible				= '" . Database::escape_string($catalogue['visible']) . "',
            payment 				= '" . implode(',', $catalogue['payments']) . "',
            company_address	= '" . Database::escape_string($catalogue['company_address']) . "',
            cheque_message	= '" . Database::escape_string($catalogue['cheque_message']) . "',
            cc_payment_message	= '" . Database::escape_string($catalogue['cc_payment_message']) . "',
            installment_payment_message	= '" . Database::escape_string($catalogue['install_payment_message']) . "',
            options_selection	= '" . Database::escape_string($catalogue['opt_selection_text']) . "',
            terms_conditions	= '" . Database::escape_string($catalogue['termsconditions']) . "',
            tva_description	= '" . Database::escape_string($catalogue['tvadescription']) . "',";
            if (!empty($_FILES['file']['tmp_name'])) {
                $sql .= " company_logo 	= '" . $catalogue['company_logo'] . "',";
            }
            $sql .= " currency	= '" . $catalogue['catalog_currency'] . "', 
                bank_details	= '" . Database::escape_string($catalogue['bank_details']) . "' WHERE id = " . $objCatalog['id'];
        }
        Database::query($sql, __FILE__, __LINE__);

        return true;
    }

    public function getCatalogue() {
        $catalogue_table = Database :: get_main_table(TABLE_MAIN_CATALOGUE);
        $sql = "SELECT * FROM $catalogue_table";
        $res = Database::query($sql, __FILE__, __LINE__);
        $num_rows = Database::num_rows($res);
        $row = Database::fetch_array($res);
        return $row;
    }

    public function getCatalogForm(FormValidator $form) {
        $objCatalog = new EcommerceCatalog();
        $catalogRow = $objCatalog->getCatalogue();

        $form->addElement('header', '', get_lang('CatalogueManagement'));
        $form->addElement('text', 'title', get_lang('Title'), 'class="focus";style="width:300px;"');

        $radios_economic_model[] = FormValidator :: createElement('radio', null, null, get_lang('Commercial'), '1');
        $radios_economic_model[] = FormValidator :: createElement('radio', null, null, get_lang('NonCommercial'), '0');
        $form->addGroup($radios_economic_model, 'economic_model', get_lang('EconomicModel'));

        $radios_visible[] = FormValidator :: createElement('radio', null, null, get_lang('Onhomepage'), '1');
        $radios_visible[] = FormValidator :: createElement('radio', null, null, get_lang('NotVisible'), '0');
        $form->addGroup($radios_visible, 'visible', get_lang('Visible'));

        $form->addElement('html', '</table>');
        $form->addElement('html', '<table width="100%" border="0"><tr><td><div class="row"><div class="label">' . get_lang('PaymentMethods') . '</div><div class="formw"><input name="payment_methods[]" type="checkbox" value="Online"');

        if (strpos($catalogRow['payment'], "Online") !== false) {
            $form->addElement('html', ' checked ');
        }

        $form->addElement('html', '>' . get_lang('Online') . '<input name="payment_methods[]" type="checkbox" value="Cheque"');
        if (strpos($catalogRow['payment'], "Cheque") !== false) {
            $form->addElement('html', ' checked ');
        }

        $form->addElement('html', '>' . get_lang('Cheque'));

        $form->addElement('html', '</div></div></td></tr>');
        $form->addElement('html', '</table>');

        // adding currency
        $form->addElement('header', '', get_lang('EcommerceCatalogCurrency'));
        $arCurrencyOptions = $objCatalog->getValidCatalogCurrencyOptions();

        $radiosCurrencyOptions = array();

        foreach ($arCurrencyOptions['options'] as $currencyOptions) {
            $radiosCurrencyOptions[] = FormValidator :: createElement('radio', null, null, get_lang($currencyOptions->display_text), $currencyOptions->value);
        }

        $form->addGroup($radiosCurrencyOptions, 'catalog_currency', get_lang('Currency'));
        $defaults['catalog_currency'] = intval($arCurrencyOptions['selected'], 10);

        $form->addElement('header', '', get_lang('InvoicingInformation'));
        $form->addElement('file', 'file', get_lang('CompanyLogo'), 'size="40"');

        $form->addElement('textarea', 'company_address', get_lang('CompanyAddress'), array('rows' => '3', 'cols' => '60'));
        $form->addElement('textarea', 'bank_details', get_lang('BankDetails'), array('rows' => '3', 'cols' => '60'));

        $editor_config = array('ToolbarSet' => 'Catalogue', 'Width' => '100%', 'Height' => '180');

        // Option selection message
        $form->addElement('html', '<div class="HideFCKEditor" id="HiddenFCKexerciseDescription" >');
        $form->add_html_editor('opt_selection_text', get_lang('OptionsSelectionText'), false, false, $editor_config);
        $form->addElement('html', '</div>');

        // Payment Messages
        $form->addElement('header', '', get_lang('PaymentMethodsMessage'));
        $form->addElement('html', '<div class="HideFCKEditor" id="HiddenFCKexerciseDescription" >');
        $form->add_html_editor('cc_payment_message', get_lang('CreditCartPaymentMessage'), false, false, $editor_config);
        $form->addElement('html', '</div>');

        // Cheque Payment
        $form->addElement('html', '<div class="HideFCKEditor" id="HiddenFCKexerciseDescription" >');
        $form->add_html_editor('cheque_message', get_lang('ChequeMessage'), false, false, $editor_config);
        $form->addElement('html', '</div>');
        $form->addElement('header', '', get_lang('InformationPages'));
        $editor_config = array('ToolbarSet' => 'Catalogue', 'Width' => '100%', 'Height' => '180');
        $form->addElement('html', '<div class="HideFCKEditor" id="HiddenFCKexerciseDescription" >');
        $form->add_html_editor('termsconditions', get_lang('TermsAndConditions'), false, false, $editor_config);
        $form->addElement('html', '</div>');
        $form->addElement('html', '<div class="HideFCKEditor" id="HiddenFCKexerciseDescription" >');
        $form->add_html_editor('tvadescription', get_lang('TvaDescription'), false, false, $editor_config);
        $form->addElement('html', '</div>');
        $form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');

        if ($catalogRow === false) {
            $defaults['economic_model'] = 1;
            $defaults['visible'] = 1;
        } else {
            $defaults['title'] = $catalogRow['title'];
            $defaults['economic_model'] = $catalogRow['economic_model'];
            $defaults['visible'] = $catalogRow['visible'];
            $defaults['termsconditions'] = $catalogRow['terms_conditions'];
            $defaults['tvadescription'] = $catalogRow['tva_description'];
            $defaults['company_address'] = $catalogRow['company_address'];
            $defaults['bank_details'] = $catalogRow['bank_details'];
            $defaults['opt_selection_text'] = $catalogRow['options_selection'];
            $defaults['cc_payment_message'] = $catalogRow['cc_payment_message'];
            $defaults['cheque_message'] = $catalogRow['cheque_message'];
        }

        $form->setDefaults($defaults);
        return $form;
    }

}
