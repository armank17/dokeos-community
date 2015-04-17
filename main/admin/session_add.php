<?php
/* For licensing terms, see /dokeos_license.txt */
// including the global Dokeos file
require_once '../inc/global.inc.php';


/**
 * @author Bart Mollet
 * @package dokeos.admin
 */
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/1.8.2/ui/i18n/jquery.ui.datepicker-' . api_get_language_isocode() . '.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_PATH) . 'main/document/NiceUpload.js"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="' . api_get_path(WEB_CODE_PATH) . 'course_home/css/home.css">';
$htmlHeadXtra[] = "<script>
        $(function() {
            $('#picture_name').hide();
            var path_img = 'home/default_platform_document/ecommerce_thumb/';
            var url = '" . api_get_path(WEB_CODE_PATH) . "index.php?module=courseInfo&cmd=InfoAjax&func=uploadImageCrop';
            $('#session_picture').NiceInputUpload(185, 185, 'left', 290, true, 'progress_session');
            $('#session_picture').fileupload({
                url: url,
                dropZone: $(this),
                formData: {
                    path: path_img,
                    name: 'tempo_session_logo',
                    min_width: 185,
                    min_height: 140,
                    max_width: 530,
                    max_height: 500
                },
                done: function (e, data) {
                    var ext = data.files[0].name;
                    ext = (ext.substring(ext.lastIndexOf('.'))).toLowerCase();
                    InfoModel.showActionDialogCrop(path_img, 185, 140, false, true, 'tempo_session_logo', 'tempo_session_logo', ext);
                    $('#progress_session').hide();
                },
                progress: function (e, data) {
                    $('#progress_session').show();
                    $('#picture_name').val('');
                    $('#imgPreviewNice').hide();
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress_session').css({width : progress + '%', background : 'skyblue'});
                    $('#progress_session').html(progress + '%');
                }
            });
    });
</script>";

$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function(){
       $("#btn-search").click(function() {
            if ($("#search").css("display") == "none") {
                $("#keyword").val("");
                $("#search").show();
                $("#keyword_name").focus();
            } else {
                $("#search").hide();
            }
       });
    });
</script>';
// name of the language file that needs to be included
$language_file = array('registration', 'admin');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationsessionadd';



include_once ('../inc/lib/timezone.lib.php');

// including additional libraries
require_once api_get_path(SYS_PATH) . 'main/application/ecommerce/models/ModelEcommerce.php';
require_once api_get_path(LIBRARY_PATH) . 'sessionmanager.lib.php';
require_once '../inc/lib/xajax/xajax.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'certificatemanager.lib.php';

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);

// setting breadcrumbs
$interbreadcrumb[] = array('url' => 'index.php', "name" => get_lang('PlatformAdmin'));
$interbreadcrumb[] = array('url' => "session_list.php", "name" => get_lang('SessionList'));

// Database Table Definitions
$tbl_user = Database::get_main_table(TABLE_MAIN_USER);
$tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);

// additional javascript, css, ...
$xajax = new xajax();
//$xajax->debugOn();
$xajax->registerFunction('search_coachs');
$htmlHeadXtra[] = $xajax->getJavascript('../inc/lib/xajax/');
$htmlHeadXtra[] = '
<script type="text/javascript">
function fill_coach_field (username) {
	document.getElementById("coach_username").value = username;
	document.getElementById("ajax_list_coachs").innerHTML = "";
}
</script>';

api_set_crop_token();

$formSent = 0;
$errorMsg = '';
$xajax->processRequests();


if ($_POST['formSent']) {
    $formSent = 1;
    $name = $_POST['name'];
    $cost = $_POST['cost'];
    $description = $_POST['description'];
    $dateStart = $_POST['dateStart'];
    $dateStart = explode("/", $dateStart);
    $year_start = $dateStart[2];
    $month_start = $dateStart[1];
    $day_start = $dateStart[0];
    $dateEnd = $_POST['dateEnd'];
    $dateEnd = explode("/", $dateEnd);
    $year_end = $dateEnd[2];
    $month_end = $dateEnd[1];
    $day_end = $dateEnd[0];
    $nolimit = $_POST['nolimit'];
    $coach_username = $_POST['coach_username'];
    $id_session_category = $_POST['session_category'];    
    $duration = '1';
    $duration_type = 'day';
    $image = 'default-sessions.png';
    $updir = api_get_path(SYS_PATH) . 'home/default_platform_document/ecommerce_thumb/'; //directory path to upload
    // If there are a file uploaded
    /*
    if (!empty($_FILES['picture']['tmp_name'])) {
        $img_pic = replace_dangerous_char($_FILES['picture']['name'], 'strict');
        $img_tmp = $_FILES['picture']['tmp_name'];

        // If exist a file called with the same name then change the name
        if (file_exists($updir . $img_pic)) {
            $exp_pic = explode(".", $img_pic);
            $img_pic = $exp_pic[0] . $row['id'] . '.' . $exp_pic[1];
        }

        // Set the dimmensions what will have the image
        $width_img = 130;
        $height_img = 160;

        // Use the function for resize the image
        api_resize_images($updir, $img_tmp, $img_pic, $width_img, $height_img);

        $image = $img_pic;
    }
	*/
					
    $imageFileName = $_POST['picture_name'];
    if ($imageFileName != '') {
		
        $image_sys_path_temp = api_get_path(SYS_PATH) . 'home/default_platform_document/ecommerce_thumb/';
        $filename_temp = $image_sys_path_temp . $imageFileName;
		
        $image_sys_path = api_get_path(SYS_PATH) . 'home/default_platform_document/ecommerce_thumb/';
        $name_image = str_replace('tempo_session_logo', 'logo_session_' . api_get_crop_token(), $imageFileName);
		
        $filename = $image_sys_path . $name_image;
	
        rename($filename_temp, $filename);
		
        $image = $name_image;
	}
	
    if (api_get_setting('enable_certificate') == 'true') {
        // preparing params of certificate in session
        $certif_template = 0;
        $certif_tool = '';
        $certif_min_score = $certif_min_progress = 0.00;
        if (!empty($_POST['certificate_template'])) {
            $certif_template = intval($_POST['certificate_template']);
            $certif_tool = $_POST['certificate_tool'];
            if ($_POST['certif_evaluation_type'] == 2 || $_POST['certificate_tool'] == 'quiz') {
                // score
                $certif_min_score = $_POST['certificate_min_score'];
                $certif_min_progress = 0.00;
            } else {
                // progress
                $certif_min_progress = $_POST['certificate_min_score'];
                $certif_min_score = 0.00;
            }
        }
    }    
   
    $return = SessionManager::create_session($name, $description, $year_start, $month_start, $day_start, $year_end, $month_end, $day_end, $nolimit, $coach_username, $id_session_category, $cost, $certif_template, $certif_tool, $certif_min_score, $certif_min_progress, $duration, $duration_type, $image);
    //save in table ecommerce 
    $objModelEcommerce = new application_ecommerce_models_ModelEcommerce();
    $objModelEcommerce->saveSession($return);
    // end save
    if ($return == strval(intval($return))) {
        // integer => no error on session creation
        global $_configuration;
        require_once (api_get_path(LIBRARY_PATH) . 'urlmanager.lib.php');
        if ($_configuration['multiple_access_urls'] == true) {
            $tbl_user_rel_access_url = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
            $access_url_id = api_get_current_access_url_id();
            UrlManager::add_session_to_url($return, $access_url_id);
        } else {
            // we are filling by default the access_url_rel_session table
            UrlManager::add_session_to_url($return, 1);
        }
        header('Location: add_courses_to_session.php?id_session=' . $return . '&add=true&msg=');
        exit();
    }
}



$nb_days_acess_before = 0;
$nb_days_acess_after = 0;

$thisYear = date('Y');
$thisMonth = date('m');
$thisDay = date('d');
$nextYear = $thisYear + 1;

// certificate object
$objCertificate = new CertificateManager();

$htmlHeadXtra[] = '
    <style type="text/css">     
        .media { display:none;}
        #session-certificate-thumb {
            text-align:left;
            margin-top: 0px;
        }
        #session-certificate-thumb img {
            border: 2px solid #aaa;
        }
        .session-certificate-score {
            ' . ($formSent && $certif_template ? 'display:block' : 'display:none') . ';
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function() {
         $( "#dateStart" ).datepicker({
            showOn: "button",
            buttonImage: "' . api_get_path(WEB_IMG_PATH) . 'calendar.gif",
            buttonImageOnly: true,
            dateFormat: "dd/mm/yy"
            
        });
        $( "#dateEnd" ).datepicker({
            showOn: "button",
            buttonImage: "' . api_get_path(WEB_IMG_PATH) . 'calendar.gif",
            buttonImageOnly: true,
            dateFormat: "dd/mm/yy"
        });
        
    
           // change certificate template
           $("#session-certificate").change(function(){
                var tpl_id = $(this).val();
                $(".session-certificate-score").hide();
                if (tpl_id == 0) {
                    $(".session-certificate-score input[name=\'certificate_min_score\']").val(\'\');
                    $(".session-certificate-score").hide();
                } else {
                    $(".session-certificate-score").show();
                }
                $.ajax({
                    type: "GET",
                    url: "' . api_get_path(WEB_CODE_PATH) . 'exercice/exercise.ajax.php?' . api_get_cidReq() . '&amp;action=displayCertPicture&tpl_id="+tpl_id,
                    success: function(data) {
                        $("#session-certificate-thumb").show();
                        $("#session-certificate-thumb").html(data);
                    }
                });
           });

            $(".certificate-tool-radio").click(function(){
                var check = $("#certificate-tool-radio2").is(":checked");
                $(".certificate-module-eval-type").hide();
                if (check) {
                    $(".certificate-module-eval-type").hide();
                } else {
                    $(".certificate-module-eval-type").show();
                }
            });
        });
    </script>
';

//display the header
Display::display_header(get_lang('AddSession'));

echo '<div class="actions">';
/* if(api_get_setting('show_catalogue')=='true'){
  echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/catalogue_management.php">' . Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('Catalogue') . '</a>';
  echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/programme_list.php">' . Display :: return_icon('pixel.gif', get_lang('Programmes'),array('class' => 'toolactionplaceholdericon toolactionprogramme')) . get_lang('Programmes') . '</a>';
  } */
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_list.php">' . Display :: return_icon('pixel.gif', get_lang('SessionList'), array('class' => 'toolactionplaceholdericon toolactionsession')) . get_lang('SessionList') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_category_list.php">' . Display :: return_icon('pixel.gif', get_lang('ListSessionCategory'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('ListSessionCategory') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_export.php">' . Display::return_icon('pixel.gif', get_lang('ExportSessionListXMLCSV'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')) . get_lang('ExportSessionListXMLCSV') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_import.php">' . Display::return_icon('pixel.gif', get_lang('ImportSessionListXMLCSV'), array('class' => 'toolactionplaceholdericon toolactionimportcourse')) . get_lang('ImportSessionListXMLCSV') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_users_unsubscribe.php">' . Display::return_icon('pixel.gif', get_lang('UnsubscribeSessionUsers'), array('class' => 'toolactionplaceholdericon UnsubscribeSessionUsers')) . get_lang('UnsubscribeSessionUsers') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'coursecopy/copy_course_session.php">' . Display::return_icon('pixel.gif', get_lang('CopyFromCourseInSessionToAnotherSession'), array('class' => 'toolactionplaceholdericon toolsettings')) . get_lang('CopyFromCourseInSessionToAnotherSession') . '</a>';
//echo '<a href="javascript:void(0)" id="btn-search">' . Display :: return_icon('pixel.gif', get_lang('Search'), array('class' => 'toolactionplaceholdericon toolactionsearch')) . get_lang('Search') . '</a>';
echo '</div>';

// start the content div
echo '<div id="content">';
//echo '<div class="secondary-actions" id="search" style="' . (isset($_GET['keyword_name']) ? 'display:block;' : 'display:none') . '" >';
//echo '<h2 style="font-weight:bolder; text-transform:uppercase;">'.get_lang('AdvancedSearch').'</h2>';
//echo '<div class="secondary-actions-extra">';
//echo '<form method="POST" action="session_list.php?keyword_name=' . $_GET['keyword_name'] . '"><input type="text" id="keyword_name" name="keyword_name" value="' . Security::remove_XSS($_GET['keyword_name']) . '"/>&nbsp;
//      <button class="search" type="submit" name="name" style="margin-left:-6px; margin-top:-5px; height: 30px; float:none;" value="' . get_lang('Search') . '">' . get_lang('Search') . '</button>&nbsp;
//      <a href="session_list.php?search=advanced"><span style="display:block; clear:both; margin-bottom:20px; ">' . get_lang('AdvancedSearch') . '</span></a></form>';
//echo '</div>';
//echo '</div>';

if (!empty($return)) {
    Display::display_error_message($return, false, true);
}
?>


<?php
/*
<input type="hidden" id="width" name="width" />
<input type="hidden" id="height" name="height" />
<input type="hidden" id="image" name="image" />

<a to="logo_session" deleteFn="" title="Crop" id="logo-info2" href="<?php echo api_get_path(WEB_CODE_PATH)."/index.php?module=courseInfo&cmd=Info&func=cropImage"; ?>"></a>
*/
?>
<?php /*
<form method="post" enctype="multipart/form-data" id="imageForm" name="imageForm" >
<input type="file" name="picture" id="picture" style="opacity: 0; position: absolute; top: 92px; left: 100px; z-index:10"/>
<input type="hidden" name="to" value="logo_session">
</form>    
 */ ?>

<form method="post" enctype="multipart/form-data" name="form" action="<?php echo api_get_self(); ?>" style="margin:0px;">
    <input type="hidden" name="formSent" value="1">
    
    <div class="row"><div class="form_header"><?php echo get_lang('AddSession'); ?></div></div>
    <table border="0" cellpadding="5" cellspacing="0" width="940">
        <tr>
            <td width="10%"><?php echo get_lang('Name'); ?><span class="sym-error"> * </span></td>
            <td width="90%"><input type="text" name="name" size="50" class="focus" maxlength="50" value="<?php if ($formSent) echo api_htmlentities($name, ENT_QUOTES, $charset); ?>"></td>
        <input type="hidden" name="picture_name" id="picture_name">
        </tr>
        <tr>
            <td width="10%"><?php echo get_lang('AddPicture'); ?></td>
            <td width="90%">
                <input type="file" name="picture" id="session_picture" accept="image/jpeg, image/png, image/gif" >
            </td>
        </tr>
<!--        <tr>
            <td width="10%"><!?php echo get_lang('Preview'); ?></td>
            <td width="90%"><div id="divImgPreview"><img src=""/></div></td>
        </tr>-->
        <tr>
            <td width="10%" valign="top"><?php echo get_lang('Description') ?>&nbsp;&nbsp;</td>
            <td width="90%">
                <?php
                if ($formSent)
                    $description = api_htmlentities($description, ENT_QUOTES, $charset);
                echo api_return_html_area('description', $description, '', '', null, array('ToolbarSet' => 'Survey', 'Width' => '780', 'Height' => '120'))
                ?>
            <!--<textarea name="description" class="focus" rows="5" cols="37"><!--?php if ($formSent) echo api_htmlentities($description, ENT_QUOTES, $charset); ?></textarea>-->
            </td>
        </tr>
        <?php
        $e_commerce_enabled = intval(api_get_setting("e_commerce_catalog_type"));
        $e_commerce_activated = intval(api_get_setting("e_commerce"));
        ?>
        <tr>
            <td width="10%"><?php echo get_lang('CoachName') ?>&nbsp;&nbsp;<span class="sym-error"> * </span></td>
            <td width="90%">
                <?php
                $sql = 'SELECT COUNT(1) FROM ' . $tbl_user . ' WHERE status=1';
                $rs = Database::query($sql, __FILE__, __LINE__);
                $count_users = Database::result($rs, 0, 0);

                if (intval($count_users) < 50) {
                    $order_clause = api_sort_by_first_name() ? ' ORDER BY firstname, lastname, username' : ' ORDER BY lastname, firstname, username';
                    $sql = "SELECT user_id,lastname,firstname,username FROM $tbl_user WHERE status='1'" . $order_clause;
                    global $_configuration;
                    if ($_configuration['multiple_access_urls'] == true) {
                        $tbl_user_rel_access_url = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
                        $access_url_id = api_get_current_access_url_id();
                        if ($access_url_id != -1) {
                            $sql = 'SELECT username, lastname, firstname FROM ' . $tbl_user . ' user
			INNER JOIN ' . $tbl_user_rel_access_url . ' url_user ON (url_user.user_id=user.user_id)
			WHERE access_url_id = ' . $access_url_id . '  AND status=1' . $order_clause;
                        }
                    }

                    $result = Database::query($sql, __FILE__, __LINE__);
                    $Coaches = Database::store_result($result);
                    ?>
                    <select name="coach_username" value="true" style="width:250px;">
                        <option value="0"><?php get_lang('None'); ?></option>
                        <?php foreach ($Coaches as $enreg): ?>
                            <option value="<?php echo $enreg['username']; ?>" <?php if ($sent && $enreg['user_id'] == $id_coach) echo 'selected="selected"'; ?>><?php echo api_get_person_name($enreg['firstname'], $enreg['lastname']) . ' (' . $enreg['username'] . ')'; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php
                } else {
                    ?>
                    <input type="text" name="coach_username" id="coach_username" onkeyup="xajax_search_coachs(document.getElementById('coach_username').value)" >
                    <div id="ajax_list_coachs">

                    </div>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php
        $id_session_category = '';
        $tbl_session_category = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
        $sql = 'SELECT id, name FROM ' . $tbl_session_category . ' ORDER BY name ASC';
        $result = Database::query($sql, __FILE__, __LINE__);
        $Categories = Database::store_result($result);
        ?>
        <?php if (!empty($Categories)): ?>
            <tr>
                <td width="10%"><?php echo get_lang('SessionCategory') ?></td>
                <td width="90%">
                    <select name="session_category" value="true" style="width:250px;">
                        <option value="0"><?php get_lang('None'); ?></option>
                        <?php foreach ($Categories as $Rows): ?>
                            <option value="<?php echo $Rows['id']; ?>" <?php if ($Rows['id'] == $id_session_category) echo 'selected="selected"'; ?>><?php echo $Rows['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <td width="10%"><?php echo get_lang('NoTimeLimits') ?></td>
            <td width="90%">
                <input type="checkbox" name="nolimit" id="nolimit"/>
            </td>
        <tr>
            <td width="10%"><?php echo get_lang('DateStartSession') ?>&nbsp;&nbsp;</td>
            <td width="90%">
                <input type="text" id="dateStart" name="dateStart" value="<?php echo $thisDay . '/' . $thisMonth . '/' . $thisYear ?>" />
            </td>
        </tr>
        <tr>
            <td width="10%"><?php echo get_lang('DateEndSession') ?>&nbsp;&nbsp;</td>
            <td width="90%">
                <input type="text" id="dateEnd" name="dateEnd"  value="<?php echo $thisDay . '/' . $thisMonth . '/' . $nextYear ?>" />
            </td>
        </tr>
        <tr>
            <td><div class="pull-bottom"><button class="save" type="submit" value="<?php echo get_lang('NextStep') ?>"><?php echo get_lang('NextStep') ?></button></div>
            </td>
        </tr>
        <tr>
            <td/>
            <td valign="right" align="left"><span class="form_required"> *</span><?php echo str_replace('*', '<span class="form_required"> *</span>', get_lang('FieldRequired')); ?></td>
        </tr>
    </table>
</form>
<script type="text/javascript">
                    $("#nolimit").click(function() {
                        if ($("#nolimit").is(':checked')) {
                            $("#dateStart").datepicker("disable").attr("disabled", "disabled").css('color', '#F9F9F8');
                            $("#dateEnd").datepicker("disable").attr("disabled", "disabled").css('color', '#F9F9F8');
                        } else {
                            $("#dateStart").datepicker("enable").attr("enable", "enable").css('color', '#333333');
                            $("#dateEnd").datepicker("enable").attr("enable", "enable").css('color', '#333333');
                        }
                    });
                    /*function setDisable(select){
                     document.form.day_start.disabled = (select.checked) ? true : false;
                     document.form.month_start.disabled = (select.checked) ? true : false;
                     document.form.year_start.disabled = (select.checked) ? true : false;
                     
                     document.form.day_end.disabled = (select.checked) ? true : false;
                     document.form.month_end.disabled = (select.checked) ? true : false;
                     document.form.year_end.disabled = (select.checked) ? true : false;
                     }*/
</script>
<?php
// close the content div
echo '</div>';

// display the footer
Display::display_footer();

function search_coachs($needle) {
    global $tbl_user;
    $xajax_response = new XajaxResponse();
    $return = '';

    if (!empty($needle)) {
        // xajax send utf8 datas... datas in db can be non-utf8 datas
        $charset = api_get_setting('platform_charset');
        $needle = api_convert_encoding($needle, $charset, 'utf-8');
        $order_clause = api_sort_by_first_name() ? ' ORDER BY firstname, lastname, username' : ' ORDER BY lastname, firstname, username';

        // search users where username or firstname or lastname begins likes $needle
        $sql = 'SELECT username, lastname, firstname FROM ' . $tbl_user . ' user
				WHERE (username LIKE "' . $needle . '%"
				OR firstname LIKE "' . $needle . '%"
				OR lastname LIKE "' . $needle . '%")
				AND status=1' .
                $order_clause .
                ' LIMIT 10';

        global $_configuration;
        if ($_configuration['multiple_access_urls'] == true) {
            $tbl_user_rel_access_url = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
            $access_url_id = api_get_current_access_url_id();
            if ($access_url_id != -1) {

                $sql = 'SELECT username, lastname, firstname FROM ' . $tbl_user . ' user
				INNER JOIN ' . $tbl_user_rel_access_url . ' url_user ON (url_user.user_id=user.user_id)
				WHERE access_url_id = ' . $access_url_id . '  AND (username LIKE "' . $needle . '%"
				OR firstname LIKE "' . $needle . '%"
				OR lastname LIKE "' . $needle . '%")
				AND status=1' .
                        $order_clause .
                        ' LIMIT 10';
            }
        }

        $rs = Database::query($sql, __FILE__, __LINE__);
        while ($user = Database :: fetch_array($rs)) {
            $return .= '<a href="javascript: void(0);" onclick="javascript: fill_coach_field(\'' . $user['username'] . '\')">' . api_get_person_name($user['firstname'], $user['lastname']) . ' (' . $user['username'] . ')</a><br />';
        }
        $return = '<div class="ajax_list_coachs_style">' . $return . '</div>';
    }

    $xajax_response->addAssign('ajax_list_coachs', 'innerHTML', api_utf8_encode($return));

    return $xajax_response;
}
?>
