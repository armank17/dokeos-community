<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Learning Path
 * @package dokeos.learnpath
 */

require_once('back_compat.inc.php');
require_once('scorm.lib.php');
require_once('learnpath.class.php');
require_once('learnpathItem.class.php');
require_once('course_navigation_interface.inc.php');

if ($is_allowed_in_course == false) api_not_allowed();
// encoding
if (!empty($_SESSION['oLP']->encoding))		$charset = $_SESSION['oLP']->encoding;
else										$charset = api_get_system_encoding();

if (empty($charset))						$charset = 'ISO-8859-1';

?>
	<link rel="stylesheet" type="text/css" href="../css/dokeos2_orange/course_navigation.css" />
	<link rel="stylesheet" type="text/css" href="../css/dokeos_orange/author.css" />
	<link rel="stylesheet" type="text/css" href="../css/dokeos2_orange/default.css" />


<!-- Header-->
	<div id="courseHeader"><?php echo renderSimpleHeader(); ?></div>


	<div id="author_view">
<!-- top toolbar -->
		<?php
			$topItems = array();
			$topItems []= "<a href='#' class='ico_author'>[==author==]</a>";
			$topItems []= "<a href='#' class='ico_content'>[==content==]</a>";
			$topItems []= "<a href='#' class='ico_template'>[==templates==]</a>";
			$topItems []= "<a href='#' class='ico_page'>[==page==]</a>";
			echo renderToolbar($topItems, true);
		?>
		
		
<!-- main -->
	<!-- big buttons -->
		<div id="author_main" class="rounded">
			<a href="#" class='big_button four_buttons rounded grey_border new_button'>[==new==]</a>
			<a href="#" class='big_button four_buttons rounded grey_border scorm_button'>[==scorm==]</a>
			<a href="#" class='big_button four_buttons rounded grey_border word_button'>word</a>
			<a href="#" class='big_button four_buttons rounded grey_border ppt_button'>powerpoint</a>
	
		<!-- list of courses -->
			<div class="big_box rounded bg_white clear_b grey_border">
				<table class="without_th">
					<thead>
						<tr>
							<th class='bg_first'>[==edit==]</th>
							<th class='bg_continu'>[==existing course==]</th>
							<th class='bg_last'>[==delete==]</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="spacer" colspan="3"></td>
						</tr>
						<?php /* TODO foreach course */
//							$courses = array();
//							foreach($courses as $c){
						?>
						<tr>
							<td class="button"><a href="#"><img src="/dokeos/main/img/navigation/edit.png"></a></td>
							<td><a href="#" class="blue_link">Lorem ipsum que sapelorio quezac 1</a></td>
							<td class="button"><a href="#"><img src="/dokeos/main/img/navigation/delete.png"></a></td>
						</tr>
						<tr>
							<td class="button"><a href="#"><img src="/dokeos/main/img/navigation/edit.png"></a></td>
							<td><a href="#" class="blue_link">Lorem ipsum que sapelorio quezac 2</a></td>
							<td class="button"><a href="#"><img src="/dokeos/main/img/navigation/delete.png"></a></td>
						</tr>
						<tr>
							<td class="button"><a href="#"><img src="/dokeos/main/img/navigation/edit.png"></a></td>
							<td><a href="#" class="blue_link">Lorem ipsum que sapelorio quezac 3</a></td>
							<td class="button"><a href="#"><img src="/dokeos/main/img/navigation/delete.png"></a></td>
						</tr>
						<tr>
							<td class="button"><a href="#"><img src="/dokeos/main/img/navigation/edit.png"></a></td>
							<td><a href="#" class="blue_link">Lorem ipsum que sapelorio quezac 4</a></td>
							<td class="button"><a href="#"><img src="/dokeos/main/img/navigation/delete.png"></a></td>
						</tr>
						<?php
//							}
						?>
					</tbody>
				</table>
			</div>
		
			<form class="orange" method="post" enctype="multipart/form-data" action="" >
			
				<h3><?php echo "[==upload==] word";	/* TODO set language */ ?></h3>
				<img class="float_l" src="/dokeos/main/img/navigation/icon_word.png" style="margin-right:10px;"/>
				<input type="file" name="" id="" />
				<input type="submit" />
				<div id="upload_bar"></div>
				
			</form>
		
		</div>
		
<!-- bottom toolbar -->
		<?php
			$bottomItems = array();
			$bottomItems []= "<a href='#' class='ico_build'>[==build==]</a>";
			$bottomItems []= "<a href='#' class='ico_organize'>[==organize==]</a>";
			$bottomItems []= "<a href='#' class='ico_view'>[==view==]</a>";
			echo renderToolbar($bottomItems, false);
		?>

	</div>
