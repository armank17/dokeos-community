<?php

require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/CatalogueInterface.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/CatalogueModel.php';
require_once api_get_path(SYS_PATH) . 'main/core/dao/ecommerce/CatalogueDao.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/Currency.php';

class CatalogueSessionModel extends CatalogueModel implements CatalogueInterface {

    public static function create() {
        return new CatalogueSessionModel();
    }

    public function getProgrammeByCode($sessionCode) {
        $response = null;
        if (trim($sessionCode) != '') {
            $sql = 'SELECT s.id,s.name,e.cost,e.duration,e.duration_type FROM ' . Database::get_main_table(TABLE_MAIN_SESSION) . ' s ' .
                   'INNER JOIN ' . Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS) . ' e ON e.id_session=s.id ' .
                   'WHERE e.id=' . $sessionCode . '';
            $result = Database::query($sql, __FILE__, __LINE__);
            while ($row = Database::fetch_array($result)) {
                $response = $row;
            }
        }
        return $response;
    }

    public function getListOfEcommerceItems($status = NULL) {
        global $_configuration;
        $response = array();
        $tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);

        $sql = "SELECT * FROM $tbl_session s";

        if ($_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $access_url_id = api_get_current_access_url_id();
            $access_url_rel_session_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_SESSION);
            $sql.= " INNER JOIN $access_url_rel_session_table url_rel_sess ON (s.id = url_rel_sess.session_id) AND url_rel_sess.access_url_id = $access_url_id";
        }

        if (!is_null($status)) {
            $status = intval($status, 10);
            $sql .= " WHERE visibility = '$status' ";
        }

        $rs_programmes = Database::query($sql, __FILE__, __LINE__);

        $row = TRUE;

        while ($row) {
            $row = Database::fetch_object($rs_programmes);
            if ($row !== FALSE) {
                $response[] = $row;
            }
        }

        return $response;
    }

    public function getListForStudentPortal() {
        $response = '';


        /**
         * second part
         */
        //if (api_get_setting('catalogue_category_session') == 'true') {
        $tbl_session_category = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
        $tbl_session_cat_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_USER);
        $tbl_session_rel_category = Database::get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);
        $tbl_session_rel_course = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
        $tbl_session_rel_course_user = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

        // get category users
        $sql = "SELECT DISTINCT(uc.category_id), c.name, c.date_start, c.date_end FROM $tbl_session_cat_rel_user uc "
                . "INNER JOIN $tbl_session_category c ON  uc.category_id = c.id  WHERE uc.user_id=" . api_get_user_id();

        $rs_cat_users = Database::query($sql);

        if (Database::num_rows($rs_cat_users)) {
            while ($row_cat_users = Database::fetch_array($rs_cat_users)) {
                $date_start = $row_cat_users['date_start'];
                $date_end = $row_cat_users['date_end'];

                $today = strtotime("now");
                $start_date = strtotime($date_start);
                $end_date = strtotime($date_end);

                $datetime = explode(" ", $date_start);
                $dateparts = explode("-", $datetime[0]);
                $date_start = $dateparts[2] . '-' . $dateparts[1] . '-' . $dateparts[0];

                $datetime = explode(" ", $date_end);
                $dateparts = explode("-", $datetime[0]);
                $date_end = $dateparts[2] . '-' . $dateparts[1] . '-' . $dateparts[0];

                $response .= '<div class="catalogue-session-category" >' . PHP_EOL;
                $response .= '<h2>' . $row_cat_users['name'] . '</h2>' . PHP_EOL;
                if (($start_date > $today) || ($end_date < $today)) {
                    $response .= get_lang('Programme') . ' ' . get_lang('AvailableFrom') . ' ' . $date_start . ' ' . get_lang('To') . ' ' . $date_end;
                    $programme_status = 'N';
                }
                //	$ids_session = array();
                // session in the category
                $rs_sess = Database::query("SELECT DISTINCT(session_id) FROM $tbl_session_rel_category WHERE category_id = {$row_cat_users['category_id']}");
                if (Database::num_rows($rs_sess)) {
                    while ($row_sess = Database::fetch_array($rs_sess, ASSOC)) {
                        //$rs_sess_cat = Database::query("SELECT course_code FROM $tbl_session_rel_course_user WHERE id_session = {$row_sess['session_id']} AND id_user = ".api_get_user_id());
                        $rs_sess_cat = Database::query("SELECT course_code FROM $tbl_session_rel_course_user WHERE id_session = {$row_sess['session_id']} AND id_user = " . api_get_user_id() . " AND course_code IN(SELECT course_code FROM $tbl_session_cat_rel_user WHERE session_id = {$row_sess['session_id']} AND user_id = " . api_get_user_id() . " AND category_id = {$row_cat_users['category_id']})");
                        if (Database::num_rows($rs_sess_cat)) {
                            $response .= '<div class="session-category-courses">' . PHP_EOL;
                            while ($row_sess_cat = Database::fetch_object($rs_sess_cat)) {
                                // if user in course session
                                $my_course = api_get_course_info($row_sess_cat->course_code);
                                $my_course['k'] = $my_course['id'];
                                $my_course['db'] = $my_course['dbName'];
                                $my_course['c'] = $my_course['official_code'];
                                $my_course['i'] = $my_course['name'];
                                $my_course['d'] = $my_course['path'];
                                $my_course['t'] = $my_course['titular'];
                                $my_course['id_session'] = $row_sess['session_id'];

                                $response .= '<div class="session-category-course-item">' . Display::return_icon('miscellaneous22x22.png', $my_course['name'], array('style' => 'vertical-align:middle')) . ' <strong>' . PHP_EOL;

                                if ($programme_status == 'N') {
                                    $response .= $my_course['name'] . '</strong>';
                                } else {
                                    $response .= '<a href="' . api_get_path(WEB_COURSE_PATH) . $my_course['path'] . '/?id_session=' . $my_course['id_session'] . '">';
                                    $response .= $my_course['name'] . '</a></strong>' . show_notification($my_course);
                                }
                                $response .= '</div>';
                            }
                            $response .= '</div>';
                        }
                    }
                }
                $response .= '</div>';
            }
        } else {

            if (is_array($courses_tree)) {

                foreach ($courses_tree as $key => $category) {


                    if ($key == 0) {

                        // sessions and courses that are not in a session category
                        if (!isset($_GET['history'])) { // check if it's not history trainnign session list
                            // independent courses
                            $userdefined_categories = get_user_course_categories();
                            //ksort($userdefined_categories);
                            foreach ($userdefined_categories as $index => $my_category) {
                                if (count($category['courses']) > 0) {
                                    $total_courses_in_category = CourseManager::get_total_of_courses_in_user_category($index);
                                    if ($total_courses_in_category > 0) {
                                        $response .= '<div class="course_list_category" >' . $my_category . '</div>';
                                    }
                                    $response .= '<ul class="courseslist">';
                                }
                                foreach ($category['courses'] as $course) {
                                    if ($course['category_id'] == $index) {
                                        $c = get_logged_user_course_html($course, 0, 'independent_course_item');
                                        $response .= '<li>' . $c[1] . '</li>';
                                    }
                                }

                                if (count($category['courses']) > 0) {
                                    $response .= '</ul>';
                                }
                            }
                        }

                        //independent sessions
                        foreach ($category['sessions'] as $session) {
                            //don't show empty sessions
                            if (count($session['courses']) < 1) {
                                continue;
                            }
                            //	echo '<div class="section">';
                            //	echo '	<div class="sectiontitle" id="session_'.$session['details']['id'].'" >';
                            //	echo Display::return_icon('div_show.gif', get_lang('Expand'), array('align' => 'absmiddle', 'id' => 'session_img_'.$session['details']['id'])) . ' ';
                            //	if(!in_array($session['details']['id'],$ids_session)){
                            $s = get_session_title_box($session['details']['id']);
                            $response .= '<div class="catalogue-session-category" >';
                            $response .= '<h2>' . $s['title'] . '</h2>';

                            //	echo get_lang('SessionName') . ': ' . $s['title']. ' - '.(!empty($s['coach'])?$s['coach'].' - ':'').$s['dates'];
                            //	echo '	</div>';
                            //courses inside the current session
                            //	echo '	<div class="sectioncontent">';
                            $response .= '<div class="session-category-courses">';
                            foreach ($session['courses'] as $course) {
                                $c = get_logged_user_course_html($course, $session['details']['id'], 'session_course_item');
                                //	echo $c[1];
                                $response .= '<div class="session-category-course-item">' . $c[1] . '</div>';
                            }
                            $response .= '	</div>';
                            $response .= '</div>';
                            //	}
                        }
                    } else {

                        // all sessions included in
                        if (!empty($category['details'])) {
                            //changes made as we have for DILA
                            $response .= '<div class="catalogue-session-category" >';
                            $response .= '<h2>' . $category['details']['name'] . '</h2>';
                            foreach ($category['sessions'] as $session) {
                                if (count($session['courses']) < 1) {
                                    continue;
                                }
                                $response .= '<div class="session-category-courses">';
                                foreach ($session['courses'] as $course) {
                                    $c = get_logged_user_course_html($course, $session['details']['id'], 'session_course_item');
                                    //var_dump($c);
                                    $response .= $c[1];
                                }
                                $response .= '</div>';
                            }
                            $response .= '</div>';
                        } // end condition outstanding
                    }
                }
            }
        }

        return $response;
    }

    public function registerItemsIntoUser(array $session, $userId) {
        $sessionIds = array_keys($_SESSION['shopping_cart']['items']);
        if (!empty($sessionIds)) {
            foreach ($sessionIds as $sessionId) {
                SessionManager::suscribe_users_to_session($sessionId, array(0 => $userId));
            }
        }
    }

    public function registerSessionIntoUser(array $session, $userId){
        $sessionIds = array_keys($_SESSION['shopping_cart']['items']);
        SessionManager::register_shop_session_course($sessionIds,$userId);
    }

    public function registerItemsForUser(array $session, array $transactionResult, $userId) {
//       $courseCodes = array_keys($session['shopping_cart']['items']);
        $courseCodes = array_keys($session['items_paid']);

        foreach ($courseCodes as $courseCode) {
            /** @var $objCourse EcommerceCourse */
            $objItem = EcommerceItemsDao::create()->getByCourseCode($courseCode);


            $params = array();
            $params['user_id'] = $userId;
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

    /* (non-PHPdoc)
     * @see CatalogueInterface::getShoppingCartList()
     */

    public function getShoppingCartList() {
        $response = '';
        $programmes = array();
        $programmes = CatalogueSessionModel::create()->getListOfEcommerceItems(EcommerceCourse::ECOMMERCECOURSE_STATUS_ACTIVE);
        $catalog = api_get_payment_setting('catalog');
        $response .= '<div class="items_catalogue_box"><div class="section" id="items_catalogue_list" rel="' . count($programmes) . '">';
        $response .= '<div class="row"><div class="form_header">' . $catalog . '</div></div>';

        $response .= '<div><ul id="shoppingCartCatalog" class="jcarousel-skin-tango" rel="session">';

        $langTime = get_lang('Duration');
        $langPrice = get_lang('Price');
        $langAddToCart = get_lang('AddToCartHome');

        $currency = $_SESSION['shopping_cart']['currency'];
        foreach ($programmes as $programme) {
            $duration = $programme->duration . ' ' . get_lang($programme->duration_type);
            if ($programme->image) {
                $image = $programme->image;
            } else {
                $image = "thumb_dokeos.jpg";
            }
            $img_path = api_get_path(WEB_PATH) . 'home/default_platform_document/ecommerce_thumb/' . $image;

            $price = ($currency['code'] == 'USD') ? $currency['symbol'] . ' ' . $programme->cost : $programme->cost . ' ' . $currency['symbol'];
            $response .= '<li>';
            $response .= <<<EOF
<div class="course_catalog_container" rel="{$programme->id}">
    <p><a href="main/catalogue/course_details.php?course_code={$programme->id}">
        <img width="130" height=160" src="{$img_path}"><br/>
        <font size="2"><b>{$programme->name}</b></font>
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