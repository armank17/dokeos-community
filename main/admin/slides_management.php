<?php

/* For licensing terms, see /dokeos_license.txt */

/**
 * @package dokeos.admin
 * @todo correct typo: language instead of langauge !!!!
 * @todo do NOT use $_REQUEST but $_POST or $_GET
 * @todo the update statement does not have a WHERE id = ... part so all the entries in the database will be updated. Is this the desired behaviour? 
 * @todo why ob_start()... why output buffering? I don't think this is needed here
 * @todo why include sortabletable.class.php there is no sortable table on this page. 
 */
// name of the language file that needs to be included
$language_file = array('registration', 'admin');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationslidesmanagement';

// including the global Dokeos file
require ('../inc/global.inc.php');
require_once api_get_path(LIBRARY_PATH) . 'sublanguagemanager.lib.php';
// including additional libraries
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');

//lang
global $language_interface;
$language_interface = Security::remove_XSS($_REQUEST['language']);

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

api_set_crop_token();

// Access restrictions
api_protect_admin_script(true);

(isset($_GET['language'])) ? $language = $_GET['language'] : $language = api_get_interface_language();
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_PATH) . 'main/document/NiceUpload.js"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="' . api_get_path(WEB_CODE_PATH) . 'course_home/css/home.css">';
$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready(function() {
	$(function() {
            $("#picture_name").hide();
            var path_img = "home/default_platform_document/";
            var url = "' . api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=uploadImageCrop"
            $("#slide_picture").NiceInputUpload(170, 720, "left", 313, true, "progress_slide");
            $("#slide_picture").fileupload({
                url: url,
                dropZone: $(this),
                formData: {
                    path: path_img,
                    name: "tempo_picture_slide",
                    min_width: 720,
                    min_height: 240,
                    max_width: 740,
                    max_height: 540
                },
            done: function (e, data) {
                var ext = data.files[0].name;
                ext = (ext.substring(ext.lastIndexOf("."))).toLowerCase();
                InfoModel.showActionDialogCrop(path_img, 720, 240, false, true, "tempo_picture_slide", "tempo_picture_slide", ext);
                $("#progress_slide").hide();
            },
            progress: function (e, data) {
                $("#progress_slide").show();
                $("#picture_name").val("");
                $("#imgPreviewNice").hide();
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $("#progress_slide").css({width: progress + "%", background: "skyblue"});
                $("#progress_slide").html(progress + "%");
            }
        });

		$(".dragdrop").sortable({ opacity: 0.6,cursor: "move",cancel: ".nodrag", update: function(event, ui) {
                var current_lp_id = ui.item.attr("id");
                var current_lp_data = current_lp_id.split("lp_row_");
                var Lp_id = current_lp_data[1]; // This is the current LP ID of the selected row

                var sorted_list = $(this).sortable("serialize");
                    sorted_list = sorted_list.replace(/&/g,"");
                var sorted_data = sorted_list.split("lp_row[]=");

               // get new order of this lp
                var newOrder = 0;
                for(var i=0; i<sorted_data.length; i++){
                  if(sorted_data[i] == Lp_id){
                    newOrder = i;
                  }
                }
		  // call ajax to save new position
		  $.ajax({
			   type: "GET",
                           url: "' . api_get_path(WEB_AJAX_PATH) . 'slides.ajax.php?action=change_lp_position&lp_id="+Lp_id+"&new_order="+newOrder+"&language=' . $language . '",
			   success: function(response){
                                document.location="slides_management.php?language=' . $language . '";
                           }
                        })
                    }
		});
	});
});
</script> ';

$tool_name = get_lang('AddSlides');

$slides_table = Database :: get_main_table(TABLE_MAIN_SLIDES);
$slides_management_table = Database :: get_main_table(TABLE_MAIN_SLIDES_MANAGEMENT);

if (isset($_GET['action']) && $_GET['action'] == 'add') {
    //Create form
    $form = new FormValidator('add_slide', 'post', api_get_self() . '?action=add');
    $form->addElement('header', '', $tool_name);
    
    $form->addElement('file', 'picture', get_lang('UpdatePicture'), array('id' => 'slide_picture', 'accept' => 'image/jpeg, image/png, image/gif'));
    //$form->addElement('html', '<div class="row"><div class="label"></div><div class="formw" style="max-width: 34%;"><div id="progress_slide" style="height:30px; margin-bottom:10px; border-radius:5px; text-align:center; line-height:28px; font-weight:bold; font-size:14px;"></div></div></div>');
    //$form->addElement('html', '<div class="row"><div class="label"></div><img id="imgPreviewNice" src=""></div>');
    $form->addElement('static', 'imagesize', '', get_lang('Size') . ' 720x240');
    //$allowed_picture_types = array('jpg', 'jpeg', 'png', 'gif');
    //$form->addRule('picture', get_lang('OnlyImagesAllowed') . ' (' . implode(',', $allowed_picture_types) . ')', 'filetype', $allowed_picture_types);
    //$form->addRule('picture', get_lang('ThisFieldIsRequired'), 'required');
    $form->addElement('text', 'picture_name', '', 'id="picture_name"');
    $form->addElement('text', 'title', get_lang('Title'), 'class="focus" style="width:300px;"');
    $form->addElement('text', 'alttext', get_lang('AltText'), 'class="focus" style="width:300px;"');
    $form->addElement('text', 'caption', get_lang('Caption'), 'class="focus" style="width:300px;"');
    $form->addElement('text', 'link', get_lang('Link'), 'class="focus" style="width:300px;"');
    $form->addElement('select_language', 'language', get_lang('Language'));
    $form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');

    // Validate form
    if ($form->validate()) {
        $slides = $form->exportValues();

        $title = $slides['title'];
        $alttext = $slides['alttext'];
        $caption = $slides['caption'];
        $link = $slides['link'];
        $language = $slides['language'];
        $imageFileName = $slides['picture_name'];

        $updir = api_get_path(SYS_PATH) . 'home/default_platform_document/'; //directory path to upload

        if ($imageFileName != '') {

            $sql_display_order = "SELECT max(display_order) AS max_order FROM $slides_table WHERE language = '" . Database::escape_string($language) . "'";
            $rs_display_order = Database::query($sql_display_order, __FILE__, __LINE__);
            while ($row = Database::fetch_array($rs_display_order)) {
                $display_order = $row['max_order'] + 1;
            }

            if (empty($display_order)) {
                $display_order = 1;
            }

            $slide_pic = str_replace('tempo_picture_slide', 'picture_slide_' . api_get_crop_token(), $imageFileName);

            // Insert into the slide tables
            $sql = "INSERT INTO " . $slides_table . " SET " .
                    "title			= '" . Database::escape_string($slides['title']) . "',
                                                            alternate_text		= '" . Database::escape_string($slides['alttext']) . "',
                                                            link				= '" . Database::escape_string($slides['link']) . "',
                                                            caption 			= '" . Database::escape_string($slides['caption']) . "',							   										  
                                                            image 			= '" . Database::escape_string($slide_pic) . "',										   
                                                            language			= '" . Database::escape_string($slides['language']) . "',	
                                                            display_order		= " . Database::escape_string($display_order);
            echo $display_order;

            Database::query($sql, __FILE__, __LINE__);

            // Get the id of last slide inserted                
            $last_id = Database::get_last_insert_id();

            // If exist a file called with the same name then change the name
            //if (file_exists($updir . $slide_pic)) {
            //   $exp_pic = explode(".", $slide_pic);
            //   $slide_pic = $exp_pic[0] . $last_id . '.' . $exp_pic[1];
            // Update the slide table 
            //   $sql = "UPDATE " . $slides_table . " SET image = '" . Database::escape_string($slide_pic) . "' WHERE id= '" . intval($last_id) . "'";
            //   Database::query($sql, __FILE__, __LINE__);
            //}
            // Set the dimmensions what will have the image
            //$width_slide = 720;
            //$height_slide = 240;
            // Use the function for resize the image
            //api_resize_images($updir, $slide_tmp, $slide_pic, $width_slide, $height_slide);


            $image_sys_path_temp = api_get_path(SYS_PATH) . 'home/default_platform_document/';
            $filename_temp = $image_sys_path_temp . $imageFileName;
            
            $image_sys_path = api_get_path(SYS_PATH) . 'home/default_platform_document/';
            $name_image = $slide_pic;
            
            $image_sys_path_thumb = api_get_path(SYS_PATH) . 'home/default_platform_document/template_thumb';
            $name_image_thumb = 'thumb_' . $name_image;

            $filename = $image_sys_path . $name_image;
            
            $filename_thumb = $image_sys_path_thumb . $name_image_thumb;

            rename($filename_temp, $filename);

            create_thumbnail($image_sys_path, $name_image, 150);

            header('Location: slides_management.php?language=' . Security::remove_XSS($slides['language']));
            exit();
        }
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'edit') {

    $sql = "SELECT * FROM $slides_table WHERE id = " . Database::escape_string($_GET['id']);
    $res = Database::query($sql, __FILE__, __LINE__);
    while ($row = Database::fetch_array($res)) {
        $title = $row['title'];
        $alternate_text = $row['alternate_text'];
        $link = $row['link'];
        $caption = $row['caption'];
        $image = $row['image'];
        $language = $row['language'];
    }
    $img_path = api_get_path(WEB_PATH) . 'home/default_platform_document/' . $image; //directory path to upload
    $thumb_path = api_get_path(SYS_PATH) . 'home/default_platform_document/' . $image;

    $form = new FormValidator('add_slide', 'post', api_get_self() . '?action=edit&amp;id=' . $_GET['id']);
    $form->addElement('header', '', get_lang('EditSlide'));
    
    $form->addElement('file', 'picture', get_lang('UpdatePicture'), array('id' => 'slide_picture', 'accept' => 'image/jpeg, image/png, image/gif'));
    //$form->addElement('html', '<div class="row"><div class="label"></div><div class="formw" style="max-width: 34%;"><div id="progress_slide" style="height:30px; margin-bottom:10px; border-radius:5px; text-align:center; line-height:28px; font-weight:bold; font-size:14px;"></div></div></div>');
    //$form->addElement('html', '<div class="row"><div class="label"></div><img id="imgPreviewNice" src=""></div>');
    $form->addElement('static', 'imagesize', '', get_lang('Size') . ' 720x240');
    //$form->addElement('static', 'thumbimage', get_lang('Preview'),'<img src="' . $img_path . '">');
    $form->addElement('html', '<div class="row" ><div class="label">' . get_lang('Preview') . '</div><div class="formw" id="divImgPreview"><img id="divImgPreviewed" src="' . $img_path . '"></div></div>');
    //$allowed_picture_types = array('jpg', 'jpeg', 'png', 'gif');
    //$form->addRule('picture', get_lang('OnlyImagesAllowed') . ' (' . implode(',', $allowed_picture_types) . ')', 'filetype', $allowed_picture_types);

    $form->addElement('text', 'picture_name', '', 'id="picture_name"');
    $form->addElement('text', 'title', get_lang('Title'), 'class="focus" style="width:300px;"');
    $form->addElement('text', 'alttext', get_lang('AltText'), 'class="focus" style="width:300px;"');
    $form->addElement('text', 'caption', get_lang('Caption'), 'class="focus" style="width:300px;"');
    $form->addElement('text', 'link', get_lang('Link'), 'class="focus" style="width:300px;"');
    $form->addElement('select_language', 'language', get_lang('Language'));
    $form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');

    $defaults['title'] = $title;
    $defaults['alttext'] = $alternate_text;
    $defaults['caption'] = $caption;
    $defaults['link'] = $link;
    $defaults['language'] = $language;
    $form->setDefaults($defaults);

    // Validate form
    if ($form->validate()) {
        $slides = $form->exportValues();

        $title = $slides['title'];
        $alttext = $slides['alttext'];
        $caption = $slides['caption'];
        $link = $slides['link'];
        $language = $slides['language'];
        $imageFileName = $slides['picture_name'];

        $updir = api_get_path(SYS_PATH) . 'home/default_platform_document/'; //directory path to upload
        // If there are a file uploaded


        /*
          if (!empty($_FILES['picture']['tmp_name'])) {
          $slide_pic = replace_dangerous_char($_FILES['picture']['name'], 'strict');
          $slide_tmp = $_FILES['picture']['tmp_name'];

          // If exist a file called with the same name then change the name
          if (file_exists($updir . $slide_pic)) {
          $exp_pic = explode(".", $slide_pic);
          $slide_pic = $exp_pic[0] . $_GET['id'] . '.' . $exp_pic[1];
          }
          // Remove the last image
          @unlink($updir . $image);
          // Remove the last thumb of image
          @unlink($thumb_path);

          // Set the dimmensions what will have the image
          $width_slide = 720;
          $height_slide = 240;

          // Use the function for resize the image
          api_resize_images($updir, $slide_tmp, $slide_pic, $width_slide, $height_slide);

          $thumbWidth = "150";
          create_thumbnail($updir, $slide_pic, $thumbWidth);
          }
         */

        if ($imageFileName != '') {
            
            $slide_pic = str_replace('tempo_picture_slide', 'picture_slide_' . api_get_crop_token(), $imageFileName);

            $image_sys_path_temp = api_get_path(SYS_PATH) . 'home/default_platform_document/';
            $filename_temp = $image_sys_path_temp . $imageFileName;
            
            $image_sys_path = api_get_path(SYS_PATH) . 'home/default_platform_document/';
            $name_image = $slide_pic;
            
            $image_sys_path_thumb = api_get_path(SYS_PATH) . 'home/default_platform_document/template_thumb';
            $name_image_thumb = 'thumb_' . $name_image;

            $filename = $image_sys_path . $name_image;
            
            $filename_thumb = $image_sys_path_thumb . $name_image_thumb;

            rename($filename_temp, $filename);
            
            create_thumbnail($image_sys_path, $name_image, 150);
        }



        // Update the values of the slide
        $sql = "UPDATE " . $slides_table . " SET 
						title		= '" . Database::escape_string($slides['title']) . "',
						alternate_text	= '" . Database::escape_string($slides['alttext']) . "',
						link		= '" . Database::escape_string($slides['link']) . "',
						caption		= '" . Database::escape_string($slides['caption']) . "',";
        //if (!empty($_FILES['picture']['tmp_name'])) {
        if ($imageFileName != '') {
            $sql .= "   image 	= '" . Database::escape_string($slide_pic) . "',";
        }
        $sql .= " language	= '" . Database::escape_string($slides['language']) . "' WHERE id = " . $_GET['id'];

        Database::query($sql, __FILE__, __LINE__);
        header('Location: slides_management.php?language=' . $slides['language']);
        exit();
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'modify') {
    $sql = "SELECT * FROM $slides_management_table LIMIT 1";
    $rs = Database::query($sql, __FILE__, __LINE__);
    while ($row = Database::fetch_array($rs)) {
        $show_slide = $row['show_slide'];
        $speed = $row['slide_speed'];
    }

    $form = new FormValidator('slide_timer', 'post', api_get_self() . '?action=modify');
    $form->addElement('header', '', get_lang('SlideScenario'));
    $form->addElement('radio', 'show_slide', get_lang('ShowSlide'), get_lang('Yes'), 1);
    $form->addElement('radio', 'show_slide', '', get_lang('No'), 0);
    $slide_speed = range(0, 20);
    $form->addElement('select', 'speed', get_lang('SlideSpeed'), $slide_speed);
    $form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');
    $defaults['speed'] = $speed;
    $defaults['show_slide'] = $show_slide;
    $form->setDefaults($defaults);

    // Validate form
    if ($form->validate()) {
        $slides = $form->exportValues();

        $sql = "UPDATE " . $slides_management_table . " SET 
														show_slide = " . Database::escape_string($slides['show_slide']) . ",
													    slide_speed	= " . Database::escape_string($slides['speed']);

        Database::query($sql, __FILE__, __LINE__);
        header('Location: slides_management.php');
        exit();
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'delete') {

    $sql = "SELECT language,image FROM $slides_table WHERE id = " . Database::escape_string(Security::remove_XSS($_GET['id']));
    $rs = Database::query($sql, __FILE__, __LINE__);
    while ($row = Database::fetch_array($rs)) {
        $language = $row['language'];
        $image = $row['image'];
    }

    $sql = "DELETE FROM $slides_table WHERE id = " . Database::escape_string(Security::remove_XSS($_GET['id']));
    Database::query($sql, __FILE__, __LINE__);

    $updir = api_get_path(SYS_PATH) . 'home/default_platform_document/'; //directory path to upload    
    $thumb_path = api_get_path(SYS_PATH) . 'home/default_platform_document/template_thumb/thumb_' . $image;

    // Remove the image
    @unlink($updir . $image);
    // Remove the thumb of image
    @unlink($thumb_path);

    header('Location: slides_management.php?language=' . $language);
    exit();
}

function create_thumbnail($updir, $slide_pic, $thumbWidth) {
    $filename = api_get_path(SYS_PATH) . 'home/default_platform_document/template_thumb/';
    $img_name = $updir . $slide_pic;

    $info = pathinfo($img_name);
    $ext = $info['extension'];

    if (!strcmp("png", $ext))
        $src_img = imagecreatefrompng($img_name);

    if (!strcmp("jpg", $ext) || !strcmp("jpeg", $ext))
        $src_img = imagecreatefromjpeg($img_name);

    if (!strcmp("gif", $ext))
        $src_img = imagecreatefromgif($img_name);

    //gets the dimmensions of the image
    $width = imageSX($src_img);
    $height = imageSY($src_img);

    $new_width = $thumbWidth;
    $new_height = floor($height * ( $thumbWidth / $width ));

    // we create a new image with the new dimmensions
    $dst_img = ImageCreateTrueColor($new_width, $new_height);

    if (!strcmp("png", $ext)) {
        imagesavealpha($dst_img, true);
        imagealphablending($dst_img, false);
        $transparent = imagecolorallocatealpha($dst_img, 0, 0, 0, 127);
        imagefill($dst_img, 0, 0, $transparent);
    }
    // resize the big image to the new created one
    imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // output the created image to the file. Now we will have the thumbnail into the file named by $filename
    if (!strcmp("png", $ext))
        imagepng($dst_img, $filename . 'thumb_' . $slide_pic);

    if (!strcmp("jpg", $ext) || !strcmp("jpeg", $ext))
        imagejpeg($dst_img, $filename . 'thumb_' . $slide_pic);

    if (!strcmp("gif", $ext))
        imagegif($dst_img, $filename . 'thumb_' . $slide_pic);

    //destroys source and destination images. 
    imagedestroy($dst_img);
    imagedestroy($src_img);
}

Display :: display_header();
if (isset($_REQUEST['language'])) {
    $language = $_REQUEST['language'];
    $add_param = '?language=' . $language;
} else {
    $language = api_get_interface_language();
    $add_param = '?language=' . $language;
}
echo '<div class="actions">';
echo '<a href="configure_homepage.php">' . Display::return_icon('pixel.gif', get_lang('HomePage'), array('class' => 'toolactionplaceholdericon toolactionhomepage')) . ' ' . get_lang('HomePage') . '</a>';
echo '<a href="' . api_get_self() . ''.$add_param.'&action=add">' . Display::return_icon('pixel.gif', get_lang('AddSlides'), array('class' => 'toolactionplaceholdericon toolallpages')) . ' ' . get_lang('AddSlides') . '</a>';
echo '<a href="' . api_get_self() . ''.$add_param.'&action=modify">' . Display::return_icon('pixel.gif', get_lang('SlideScenario'), array('class' => 'toolactionplaceholdericon toolactionscenario')) . ' ' . get_lang('SlideScenario') . '</a>';
echo '<a href="' . api_get_self() . $add_param . '">' . Display::return_icon('pixel.gif', get_lang("SlidesManagement"), array('class' => 'toolactionplaceholdericon toolallpages')) . get_lang('SlidesManagement') . '</a>';
echo '<div class="float_r">';
api_display_language_form(true, '', true);
echo '</div>';
echo '</div>';

echo '<div id="content">';

if (!isset($_GET['action'])) {
    if (isset($_GET['language'])) {
        $language = $_GET['language'];
    } else {
        $language = api_get_interface_language();
        // Check if current language has sublanguage
        $language_id = api_get_language_id($language, false);
        $sublanguages_info = SubLanguageManager::get_sublanguage_info_by_parent_id($language_id);
        if (!empty($sublanguages_info['dokeos_folder'])) {
            $language = $sublanguages_info['dokeos_folder'];
        }
    }


    $sql = "SELECT * FROM $slides_table WHERE language = '" . Database::escape_string($language) . "' ORDER BY display_order";

    $res = Database::query($sql, __FILE__, __LINE__);
    $num_rows = Database::num_rows($res);
    if ($num_rows == 0) {
        //Adding 3 default images into database
        /* $sql = "INSERT INTO $slides_table(title,alternate_text,link,caption,image,language,display_order)
          VALUES('".Database::escape_string(get_lang('YourTitle1'))."',
          '".Database::escape_string(get_lang('AltText1'))."',
          '#',
          '".Database::escape_string(get_lang('YourCaption1'))."',
          'slide01.jpg',
          '".Database::escape_string($language)."','1')";
          Database::query($sql,__FILE__,__LINE__);
          $sql = "INSERT INTO $slides_table(title,alternate_text,link,caption,image,language,display_order)
          VALUES('".Database::escape_string(get_lang('YourTitle2'))."',
          '".Database::escape_string(get_lang('AltText2'))."',
          '#',
          '".Database::escape_string(get_lang('YourCaption2'))."',
          'slide02.jpg',
          '".Database::escape_string($language)."','2')";
          Database::query($sql,__FILE__,__LINE__);
          $sql = "INSERT INTO $slides_table(title,alternate_text,link,caption,image,language,display_order)
          VALUES('".Database::escape_string(get_lang('YourTitle3'))."',
          '".Database::escape_string(get_lang('AltText3'))."',
          '#',
          '".Database::escape_string(get_lang('YourCaption3'))."',
          'slide03.jpg',
          '".Database::escape_string($language)."','3')";
          Database::query($sql,__FILE__,__LINE__); */
    }
    $sql = "SELECT * FROM $slides_table WHERE language = '" . Database::escape_string($language) . "' ORDER BY display_order";
    $res = Database::query($sql, __FILE__, __LINE__);
    
    echo '<div class="row"><div class="form_header">' . get_lang('SlidesList') . '</div></div>';

    echo '<table style="width:100%" id="slidelist" class="data_table data_table_exercise">';
    echo '<thead>';
    echo '<tr>';
    echo '<th width="8%">' . get_lang('Move') . '</th>';
    echo '<th width="35%">' . get_lang('Picture') . '</th>';
    echo '<th width="15%">' . get_lang('Title') . '</th>';
    echo '<th width="15%">' . get_lang('AltText') . '</th>';
    echo '<th width="15%">' . get_lang('Caption') . '</th>';
    echo '<th width="10%">' . get_lang('Language') . '</th>';
    echo '<th width="5%">' . get_lang('Edit') . '</th>';
    echo '<th width="5%">' . get_lang('Delete') . '</th>';
    echo '</tr>';
    echo '</thead>';

    echo '<tbody id="categories" class="dragdrop nobullets  ui-sortable">';
    while ($slide = Database::fetch_array($res)) {

        if ($i % 2 == 0) {
            $class = "row_odd";
        } else {
            $class = "row_even";
        }

        $thumbimg_dir = api_get_path(WEB_PATH) . 'home/default_platform_document/template_thumb/';
        $picture = "<div align='center'><img src='" . $thumbimg_dir . "thumb_" . $slide['image'] . "'></div>";
        $edit_link = '<center><a href="' . api_get_self() . '?&amp;action=edit&amp;id=' . $slide['id'] . '">' . Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')) . '</a></center>';
        //$delete_link = '<center><a href="' . api_get_self() . '?&amp;action=delete&amp;id=' . $slide['id'] . '" onclick="javascript:if(!confirm(\'' . get_lang('ConfirmYourChoice') . '\')) return false;">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a></center>';
        $delete_link = '<center><a href="javascript:void(0);" onclick="Alert_Confim_Delete( \''.api_get_self() . '?&amp;action=delete&amp;id=' . $slide['id'].'\',\''.get_lang("ConfirmationDialog").'\',\''.get_lang("ConfirmYourChoice").'\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a></center>';
        echo '<tr id="lp_row_' . $slide['id'] . '" class="category ' . $class . '" style="opacity: 1;">';

        echo '<td align="center" width="12%" style="cursor:pointer">' . Display::return_icon('pixel.gif', get_lang('Move'), array('class' => 'actionplaceholdericon actiondragdrop')) . '</td>
					<td width="30%">' . $picture . '</td>
					<td width="12%">' . $slide['title'] . '</td>
					<td width="14%">' . $slide['alternate_text'] . '</td>
					<td width="14%">' . $slide['caption'] . '</td>
					<td width="9%">' . $slide['language'] . '</td>
					<td width="4%">' . $edit_link . '</td>
					<td width="5%">' . $delete_link . '</td>';
        echo '</tr>';

        $i++;
    }
    echo '</tbody>';
    echo '</table>';
} else {
    $form->display();
}

echo '</div>';

//echo '<div class="actions">';
//echo '</div>';
// displaying the footer
Display :: display_footer();
?>
