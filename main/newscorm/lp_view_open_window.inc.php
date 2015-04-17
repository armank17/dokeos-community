<?php
$htmlHeadXtra[] = '<script type="text/javascript">window.open("'.$src.'","content_id","toolbar=0,location=0,status=0,scrollbars=1,resizable=1");</script>';
include_once('../inc/reduced_header.inc.php');
//check if audio recorder needs to be in studentview
$course_id=$_SESSION["_course"]["id"];
if ($_SESSION["status"][$course_id]==5) {
    $audio_recorder_studentview = true;
} else {
    $audio_recorder_studentview = false;
}
//set flag to ensure lp_header.php is loaded by this script (flag is unset in lp_header.php)
$_SESSION['loaded_lp_view'] = true;
?>
<body>
<div align="center"  style="margin-left:auto;margin-right:auto;<?php echo $full?'width:99%;height:100%;':'width:1024px;height:768px;'; ?>">
<!-- New Header Dokeos 2.0-->
<div id="courseHeaderFullScreen">
	<?php
    // Display the home icon and the tittle
    if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
      ?>
      <div align="left"><a id="modulohomeicon" onclick="window.parent.API.save_asset();" href="lp_controller.php?<?php echo api_get_cidreq(); ?>&amp;action=return_to_course_homepage"><?php echo Display::return_icon('spacer.gif', get_lang('Home'), array('style'=>'vertical-align:middle')).'&nbsp;&nbsp;<span style="color:#ffffff;">'.get_lang('Home').'</span>'?></a></div>
      <?php
    } else {
      ?>
      <div align="left"><a id="modulohomeicon" onclick="window.parent.API.save_asset();" href="lp_controller.php?<?php echo api_get_cidreq(); ?>&amp;action=return_to_course_homepage"><div style="float:left;"><?php echo Display::return_icon('spacer.gif', get_lang('Home'), array('style'=>'vertical-align:middle')).'<div style="float:right;margin-left:5px;margin-top:3px;">'.get_lang('Home').'</div>'?></div></a></div>
    <?php
    }
    echo '<div align="right" style="margin-right:10px;">';
    echo  '<a class="next_button" href="'.api_get_path(WEB_CODE_PATH).'newscorm/lp_controller.php?'.api_get_cidreq().'&amp;action=view&amp;lp_id='.intval($_GET['lp_id']).'">
                   <span style="color:#ffffff;float:right;margin-rigth:5px;margin-top:3px;cursor:pointer;">'.get_lang('ReloadPage').'</span>
               </a>';

    echo '</div>';
	?>
</div>

<div id="content_with_secondary_actions"  style="<?php echo $full?'width:100%;padding:0px;margin:0px;':'width:1004px'; ?>;height:100%;" >
    <div id="learning_path_left_zone" style="display:none;float:left;width:280px;height:100%">

		<!-- header -->
		<div id="header">
	        <div id="learning_path_header" style="font-size:14px;">
	            <table>
	                <tr>
	                    <td>
	                        <a href="lp_controller.php?<?php echo api_get_cidreq(); ?>&amp;action=return_to_course_homepage" target="_self" onclick="window.parent.API.save_asset();"><img src="../img/lp_arrow.gif" /></a>
	                    </td>
	                    <td>
	                        <a class="link" href="lp_controller.php?<?php echo api_get_cidreq(); ?>&amp;action=return_to_course_homepage" target="_self" onclick="window.parent.API.save_asset();">
	                        <?php echo api_convert_encoding(get_lang('CourseHomepageLink'), $charset, api_get_system_encoding()); ?></a>
	                    </td>
	                </tr>
	            </table>
	        </div>
		</div>
		<!-- end header -->

        <!-- Image preview Layout -->

			<div id="author_image" class="lp_author_image" style="height:15%; width:100%;margin-left:5px;">
		<?php $image = '../img/lp_author_background.gif'; ?>

			<div id="preview_image" style="padding:5px;background-image: url('../img/lp_author_background.gif');background-repeat:no-repeat;height:110px">

		       	<div style="width:100px; float:left;height:105px;margin:5px">
		       		<span style="width:104px; height:96px; float:left; vertical-align:bottom;">
			        <center>
			        <?php
			        if ($_SESSION['oLP']->get_preview_image()!='') {
			        	$picture = getimagesize(api_get_path(SYS_COURSE_PATH).api_get_course_path().'/upload/learning_path/images/'.$_SESSION['oLP']->get_preview_image());
			        	if($picture['1'] < 96) { $style = ' style="padding-top:'.((94 -$picture['1'])/2).'px;" '; }
			        	$size = ($picture['0'] > 104 && $picture['1'] > 96 )? ' width="104" height="96" ': $style;
			        	$my_path = api_get_path(WEB_COURSE_PATH).api_get_course_path().'/upload/learning_path/images/'.$_SESSION['oLP']->get_preview_image();
			        	echo '<img '.$size.' src="'.$my_path.'">';
			        } else {
						echo Display :: display_icon('unknown_250_100.jpg', ' ');
					}
					?>
				    </center>
				    </span>
		       	</div>

				<div id="nav_id" name="nav_name" class="lp_nav" style="margin-left:105px;height:90px">
			        <?php
						$display_mode = $_SESSION['oLP']->mode;
						$scorm_css_header = true;
						$lp_theme_css = $_SESSION['oLP']->get_theme();

						//Setting up the CSS theme if exists
						if (!empty ($lp_theme_css) && !empty ($mycourselptheme) && $mycourselptheme != -1 && $mycourselptheme == 1) {
							global $lp_theme_css;
						} else {
							$lp_theme_css = $my_style;
						}

						$progress_bar = $_SESSION['oLP']->get_progress_bar('', -1, '', true);
						$navigation_bar = $_SESSION['oLP']->get_navigation_bar();
						$mediaplayer = $_SESSION['oLP']->get_mediaplayer($autostart);

						$tbl_lp_item	= Database::get_course_table(TABLE_LP_ITEM);
						$show_audioplayer = false;
						// getting all the information about the item
						$sql = "SELECT audio FROM " . $tbl_lp_item . " WHERE lp_id = '" . Database::escape_string($_SESSION['oLP']->lp_id)."'";
						$res_media= Database::query($sql, __FILE__, __LINE__);

						if (Database::num_rows($res_media) > 0) {
							while ($row_media= Database::fetch_array($res_media)) {
							     if (!empty($row_media['audio'])) {$show_audioplayer = true; break;}
							}
						}
					?>

					<div id="lp_navigation_elem" class="lp_navigation_elem" style="padding-left:130px;margin-top:9px;">
						<div style="padding-top:15px;padding-bottom:50px;" ><?php echo $navigation_bar; ?></div>
						<div style="height:20px"><?php echo $progress_bar; ?></div>
					</div>
				</div>
    		</div>
	   </div>
	   <!-- end image preview Layout -->
		<div id="author_name" style="position:relative;top:2px;left:0px;margin:0;padding:0;text-align:center;width:100%">
			<?php echo $_SESSION['oLP']->get_author() ?>
		</div>

		<!-- media player layaout -->
		<?php $style_media = (($show_audioplayer)?' style= "position:relative;top:10px;left:10px;margin:8px;font-size:32pt;height:20px;"':'style="height:15px"'); ?>
		<div id="media"  <?php echo $style_media ?>>
			<?php echo (!empty($mediaplayer))?$mediaplayer:'&nbsp;' ?>
		</div>
		<!-- end media player layaout -->

		<!-- toc layout -->
		<div id="toc_id"  style="overflow: auto; padding:0;margin-top:20px;height:60%;width:100%">
			<div id="learning_path_toc" style="font-size:9pt;margin:0;"><?php echo $_SESSION['oLP']->get_html_toc(); ?>

    	<?php if (!empty($_SESSION['oLP']->scorm_debug)) { //only show log ?>
	        <!-- log message layout -->
			<div id="lp_log_name" name="lp_log_name" class="lp_log" style="height:150px;overflow:auto;margin:4px">
				<div id="log_content"></div>
				<div id="log_content_cleaner" style="color: white;">.</div>
			</div>
	        <!-- end log message layout -->
	   <?php } ?>
			</div>
		</div>
		<!-- end toc layout -->
	</div>
    <!-- end left Zone -->

    <!-- right Zone -->
	<div id="learning_path_right_zone" style="height:700px;padding-top:20px;">
		<iframe id="content_id" name="content_name" class="course_view" width="100%" height="700px" frameborder="0" ></iframe>
	</div>
    <!-- end right Zone -->
</div>
</div>
<script language="JavaScript" type="text/javascript">
// now we load the content after havin loaded the dom, so that we are sure that scorm_api is loaded
window.onload = function () {
    <?php if ($current_item_type == 'certificate') : ?>
                $.ajax({
                    type: "POST",
                    url: "lp_ajax.php?action=display_certificate&lp_id="+olms.lms_lp_id,
                    success: function(data) {
                        $('#content_id').contents().find('html').html(data);
                    }
                 });
        <?php endif; ?>
};
</script>
</body>
