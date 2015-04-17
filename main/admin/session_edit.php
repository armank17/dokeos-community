<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * @package dokeos.admin
 */
// name of the language file that needs to be included
$language_file = 'admin';
$cidReset = true;
include('../inc/global.inc.php');
include('../inc/lib/timezone.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'sessionmanager.lib.php');
require_once api_get_path(LIBRARY_PATH) . 'certificatemanager.lib.php';

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

api_protect_admin_script(true);

$id = intval($_GET['id']);

$formSent = 0;
$errorMsg = '';

api_set_crop_token();
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_PATH) . 'main/document/NiceUpload.js"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="' . api_get_path(WEB_CODE_PATH) . 'course_home/css/home.css">';

// Database Table Definitions
$tbl_user = Database::get_main_table(TABLE_MAIN_USER);
$tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
$tbl_session_rel_course = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);

$tool_name = get_lang('EditSession');

$interbreadcrumb[] = array('url' => 'index.php', "name" => get_lang('PlatformAdmin'));
$interbreadcrumb[] = array('url' => "session_list.php", "name" => get_lang('SessionList'));

$result = Database::query("SELECT * FROM $tbl_session WHERE id='$id'", __FILE__, __LINE__);

if (!$infos = Database::fetch_array($result)) {
    header('Location: session_list.php');
    exit();
}

/* if( strtotime(date("Y-m-d", $infos['date_start'])) > 0 ) {
  $infos['date_start'] = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $infos['date_start'], "Y-m-d" );
  $infos['date_end']   = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $infos['date_end'], "Y-m-d" );
  } */

list($year_start, $month_start, $day_start) = explode('-', $infos['date_start']);
list($year_end, $month_end, $day_end) = explode('-', $infos['date_end']);

if (!api_is_platform_admin() && $infos['session_admin_id'] != $_user['user_id']) {
    api_not_allowed(true);
}

if ($_POST['formSent']) {
    $formSent = 1;
    $name = $_POST['name'];
    $max_seats = (html_entity_decode($_POST['max_seats']) == '&infin;' ? '-1' : intval($_POST['max_seats']));
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
    $cost = $_POST['cost'];
    $duration = $_POST['duration'];

    $duration_type = $_POST['duration_type'];
    $nolimit = $_POST['nolimit'];
    $id_coach = $_POST['id_coach'];
    $id_session_category = $_POST['session_category'];

    // preparing params of certificate in session
    if (api_get_setting('enable_certificate') == 'true') {
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

    $sql = "SELECT image FROM $tbl_session WHERE id={$id} LIMIT 1";
    $result = Database::query($sql, __FILE__, __LINE__);
    $row = Database::fetch_row($result);
    $image = $row[0];

    $updir = api_get_path(SYS_PATH) . 'home/default_platform_document/ecommerce_thumb/'; //directory path to upload
    // If there are a file uploaded
    /*
      if (!empty($_FILES['picture']['tmp_name'])) {
      $img_pic = replace_dangerous_char($_FILES['picture']['name'], 'strict');
      $img_tmp = $_FILES['picture']['tmp_name'];

      // If exist a file called with the same name then change the name
      if (file_exists($updir . $img_pic)) {
      $exp_pic = explode(".", $img_pic);
      $img_pic = $exp_pic[0] . $id . '.' . $exp_pic[1];
      }
      // Remove the last image
      @unlink($updir . $row[0]);

      // Set the dimmensions what will have the image
      $width_img = 130;
      $height_img = 160;

      // Use the function for resize the image
      api_resize_images($updir, $img_tmp, $img_pic, $width_img, $height_img);

      $image = $img_pic;
      }
     */
    $imageFileName = $_POST["picture_name"];
    if ($imageFileName != '') {

        $image_sys_path_temp = api_get_path(SYS_PATH) . 'home/default_platform_document/ecommerce_thumb/';
        $filename_temp = $image_sys_path_temp . $imageFileName;

        $image_sys_path = api_get_path(SYS_PATH) . 'home/default_platform_document/ecommerce_thumb/';
        $name_image = str_replace('tempo_session_logo', 'logo_session_' . api_get_crop_token(), $imageFileName);

        $filename = $image_sys_path . $name_image;

        rename($filename_temp, $filename);

        $image = $name_image;
    }

    $return = SessionManager::edit_session($id, $name, $description, $year_start, $month_start, $day_start, $year_end, $month_end, $day_end, $nolimit, $id_coach, $id_session_category, $cost, $certif_template, $certif_tool, $certif_min_score, $certif_min_progress, $duration, $duration_type, $image);
    if ($return == strval(intval($return))) {
        header('Location: resume_session.php?id_session=' . $return);
        exit();
    }
}

$order_clause = api_sort_by_first_name() ? ' ORDER BY firstname, lastname, username' : ' ORDER BY lastname, firstname, username';
$sql = "SELECT user_id,lastname,firstname,username FROM $tbl_user WHERE status='1'" . $order_clause;

if ($_configuration['multiple_access_urls'] == true) {
    $table_access_url_rel_user = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
    $access_url_id = api_get_current_access_url_id();
    if ($access_url_id != -1) {
        $sql = "SELECT DISTINCT u.user_id,lastname,firstname,username FROM $tbl_user u INNER JOIN $table_access_url_rel_user url_rel_user
			ON (url_rel_user.user_id = u.user_id)
			WHERE status='1' AND access_url_id = '$access_url_id' $order_clause";
    }
}

$result = Database::query($sql, __FILE__, __LINE__);

$Coaches = Database::store_result($result);
$thisYear = date('Y');

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
            ' . ($formSent && $certif_template ? 'display:block' : (!empty($infos['certif_template']) ? 'display:block' : 'display:none')) . '
        }
        .certificate-module-eval-type {
            ' . (($formSent && $certif_tool == 'quiz') || $infos['certif_tool'] == 'quiz' ? 'display:none' : 'display:block') . '
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
                    url: "' . api_get_path(WEB_CODE_PATH) . 'exercice/exercise.ajax.php?' . api_get_cidReq() . '&action=displayCertPicture&tpl_id="+tpl_id,
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


// display the header
Display::display_header($tool_name);

echo '<div class="actions">';
/* ?echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/catalogue_management.php">' . Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('Catalogue') . '</a>';
  echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/programme_list.php">' . Display :: return_icon('pixel.gif', get_lang('Programmes'),array('class' => 'toolactionplaceholdericon toolactionprogramme')) . get_lang('Programmes') . '</a>'; */
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_list.php">' . Display :: return_icon('pixel.gif', get_lang('SessionList'), array('class' => 'toolactionplaceholdericon toolactionsession')) . get_lang('SessionList') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_export.php">' . Display::return_icon('pixel.gif', get_lang('ExportSessionListXMLCSV'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')) . get_lang('ExportSessionListXMLCSV') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_import.php">' . Display::return_icon('pixel.gif', get_lang('ImportSessionListXMLCSV'), array('class' => 'toolactionplaceholdericon toolactionimportcourse')) . get_lang('ImportSessionListXMLCSV') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'coursecopy/copy_course_session.php">' . Display::return_icon('pixel.gif', get_lang('CopyFromCourseInSessionToAnotherSession'), array('class' => 'toolactionplaceholdericon toolsettings')) . get_lang('CopyFromCourseInSessionToAnotherSession') . '</a>';
echo '</div>';
echo '<div id="content">';

if (!empty($return)) {
    Display::display_error_message($return, false, true);
}
?>
<script>
    $(document).ready(function() {
        $('#picture_name').hide();
        var path_img = 'home/default_platform_document/ecommerce_thumb/';
        var url = '<?php echo api_get_path(WEB_CODE_PATH) . "index.php?module=courseInfo&cmd=InfoAjax&func=uploadImageCrop"; ?>';
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
            done: function(e, data) {
                var ext = data.files[0].name;
                ext = (ext.substring(ext.lastIndexOf('.'))).toLowerCase();
                InfoModel.showActionDialogCrop(path_img, 185, 140, false, true, 'tempo_session_logo', 'tempo_session_logo', ext);
                $('#progress_session').hide();
            },
            progress: function(e, data) {
                $('#progress_session').show();
                $('#picture_name').val('');
                $('#imgPreviewNice').hide();
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress_session').css({width : progress + '%', background : 'skyblue'});
                $('#progress_session').html(progress + '%');
            }
        });
    });
</script>

<form method="post" enctype="multipart/form-data" id="form" name="form" action="<?php echo api_get_self(); ?>?page=<?php echo Security::remove_XSS($_GET['page']) ?>&id=<?php echo $id; ?>" style="margin:0px;">
    <input type="hidden" name="formSent" value="1">
    <div class="row"><div class="form_header"><?php echo $tool_name; ?></div></div>
    <table border="0" cellpadding="5" cellspacing="0" width="650">
        <tr>
            <td width="30%"><?php echo get_lang('SessionName') ?>&nbsp;&nbsp;</td>
            <td width="70%"><input type="text" name="name" size="50" maxlength="50" value="<?php
                if ($formSent)
                    echo api_htmlentities($name, ENT_QUOTES, $charset);
                else
                    echo api_htmlentities($infos['name'], ENT_QUOTES, $charset);
                ?>"></td>
        </tr>
        <tr>
            <td width="10%"><?php echo get_lang('AddPicture'); ?></td>
            <td width="90%">
                <input type="file" name="picture" id="session_picture" accept="image/jpeg, image/png, image/gif" >
            </td>
        </tr>
        <?php
        $image = "default-sessions.png";
        if ($infos['image'])
            $image = $infos['image'];

        $img_path = api_get_path(WEB_PATH) . 'home/default_platform_document/ecommerce_thumb/' . $image; //directory path to upload
        ?>
        <tr>
            <td width="10%"><?php echo get_lang('Preview'); ?></td>
            <td width="90%"><div id="divImgPreview"><img id="divImgPreviewed" src="<?php echo $img_path . "?t=" . time(); ?>"/></div></td>
            <input type="hidden" name="picture_name" id="picture_name">
        </tr>
        <tr>
            <td width="10%" valign="top"><?php echo get_lang('Description') ?>&nbsp;&nbsp;</td>
            <td width="90%">
                <?php
                if ($formSent)
                    $description = $description;
                else
                    $description = $infos['description'];
                echo api_return_html_area('description', $description, '', '', null, array('ToolbarSet' => 'Survey', 'Width' => '550', 'Height' => '180'))
                ?>
            <!--<textarea name="description" class="focus" rows="5" cols="37"><!--?php if($formSent) echo api_htmlentities($description,ENT_QUOTES,$charset); else echo api_htmlentities($infos['description'],ENT_QUOTES,$charset); ?></textarea>-->
            </td>
        </tr>
        <!--<tr>
          <td width="40%"><!--?php echo get_lang('MaxSeats') ?></td>
          <td width="60%"><input type="text" name="max_seats" size="5" maxlength="6" value="<!--?php echo ($infos['max_seats'] == '-1' ? '&infin;' : $infos['max_seats']); ?>"></td>
        </tr>-->
        <!--tr>
          <td width="40%"><!--?php echo get_lang('Price') ?></td>
          <td width="60%"><input type="text" name="cost" size="10" maxlength="10" value="<!--?php echo $infos['cost']; ?>"></td>
        </tr-->
        <!--tr>
          <td width="10%"><!--?php echo get_lang('Duration') ?></td>
          <td width="90%">
              <input type="text" name="duration" size="6" maxlength="10" value="<!--?php echo $infos['duration'];?>">
              <select name="duration_type">
                  <option value="day" <!--?php echo ($infos['duration_type']=='day')?'selected="selected"':'';?>><!--?php echo get_lang('day')?></option>
                  <option value="week" <!--?php echo ($infos['duration_type']=='week')?'selected="selected"':'';?>><!--?php echo get_lang('week')?></option>
                  <option value="month" <!--?php echo ($infos['duration_type']=='month')?'selected="selected"':'';?>><!--?php echo get_lang('month')?></option>
            </select>
          </td>
        </tr-->
        <tr>
            <td width="10%"><?php echo get_lang('CoachName') ?>&nbsp;&nbsp;</td>
            <td width="90%"><select name="id_coach" style="width:250px;">
                    <option value="">----- <?php echo get_lang('Choose') ?> -----</option>
                    <?php
                    foreach ($Coaches as $enreg) {
                        ?>
                        <option value="<?php echo $enreg['user_id']; ?>" <?php if ((!$sent && $enreg['user_id'] == $infos['id_coach']) || ($sent && $enreg['user_id'] == $id_coach)) echo 'selected="selected"'; ?>><?php echo api_get_person_name($enreg['firstname'], $enreg['lastname']) . ' (' . $enreg['username'] . ')'; ?></option>

                        <?php
                    }
                    unset($Coaches);
                    ?>
                </select></td>
        </tr>
        <?php
        $tbl_session_category = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
        //$access_url_id = api_get_current_access_url_id();
        $sql = 'SELECT id, name FROM ' . $tbl_session_category . ' ORDER BY name ASC';
        $result = Database::query($sql, __FILE__, __LINE__);
        $Categories = Database::store_result($result);
        ?>
        <tr>
            <td width="10%"><?php echo get_lang('SessionCategory') ?></td>
            <td width="90%">
                <select name="session_category" value="true" style="width:250px;">
                    <option value="0"><?php get_lang('None'); ?></option>
                    <?php foreach ($Categories as $Rows): ?>
                        <option value="<?php echo $Rows['id']; ?>" <?php if ($Rows['id'] == $infos['session_category_id']) echo 'selected="selected"'; ?>><?php echo $Rows['name']; ?></option>
<?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="10%"><?php echo get_lang('NoTimeLimits') ?></td>
            <td width="90%">
                <input type="checkbox" name="nolimit" id="nolimit" onChange="update_status()" <?php if ($year_start == "0000") echo "checked"; ?>/>
            </td>
        <tr>
        <tr>
            <td width="30%"><?php echo get_lang('DateStartSession') ?>&nbsp;&nbsp;</td>
            <td width="70%">
                <input <?php echo $disabled; ?>  type="text" id="dateStart" name="dateStart" value="<?php echo $day_start . '/' . $month_start . '/' . $year_start ?>" />
            </td>
        </tr>
        <tr>
            <td width="30%"><?php echo get_lang('DateEndSession') ?>&nbsp;&nbsp;</td>
            <td width="70%">
                <input <?php echo $disabled; ?> type="text" id="dateEnd" name="dateEnd" value="<?php echo $day_end . '/' . $month_end . '/' . $year_end ?>" />
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
                <button id="modify" class="save" type="submit" value="<?php echo get_lang('ModifyThisSession') ?>"><?php echo get_lang('ModifyThisSession') ?></button>
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
    $(document).ready(function() {
        if ($("#nolimit").is(':checked')) {
            $("#dateStart").datepicker("disable").attr("disabled", "disabled").css('color', '#F9F9F8');
            $("#dateEnd").datepicker("disable").attr("disabled", "disabled").css('color', '#F9F9F8');
        } else {
            $("#dateStart").datepicker("enable").attr("enable", "enable").css('color', '#333333');
            $("#dateEnd").datepicker("enable").attr("enable", "enable").css('color', '#333333');
        }
    });

    $("#nolimit").click(function() {
        if ($("#nolimit").is(':checked')) {
            $("#dateStart").datepicker("disable").attr("disabled", "disabled").css('color', '#F9F9F8');
            $("#dateEnd").datepicker("disable").attr("disabled", "disabled").css('color', '#F9F9F8');
        } else {
            $("#dateStart").datepicker("enable").attr("enable", "enable").css('color', '#333333');
            $("#dateEnd").datepicker("enable").attr("enable", "enable").css('color', '#333333');
        }
    });

</script>
<?php
// End content
echo '</div>';

Display::display_footer();
?>
