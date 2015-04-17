<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Learning Path
 * @package dokeos.learnpath
 */

// include the global Dokeos files
require_once '../inc/global.inc.php';

// include additional libraries
require_once('learnpathList.class.php');
require_once('learnpath.class.php');

$action = $_GET['action'];
$order = $_GET['order'];
$lp_table = Database::get_course_table(TABLE_LP_MAIN);

if ($action == "sortlp"){

	$listingCounter = 1;
	$disp = explode(",",$order);
	$cntdispid = sizeof($disp);
	for($i=0;$i<$cntdispid;$i++)	{

		$dispid = substr($disp[$i],8,strlen($disp[$i]));
		$query = "UPDATE $lp_table SET display_order = " . $listingCounter . " WHERE id = " . $dispid;
		$result = api_sql_query($query, __FILE__, __LINE__);
		$listingCounter = $listingCounter + 1;
	}
}

		$list = new LearnpathList(api_get_user_id());
		$flat_list = $list->get_flat_list();
		if (is_array($flat_list) && !empty($flat_list))
		{
			echo '<div id="GalleryContainer">';
			foreach ($flat_list as $id => $details)
			{
				$name = Security::remove_XSS($details['lp_name']);
				$progress_bar = learnpath::get_db_progress($id,api_get_user_id());

				if(strlen($name) > 100)
				{
				$display_name = substr($name,0,100).'...';
				}
				else
				{
				$display_name = $name;
				}
 				$html = "<div style='width:100%;border:1px solid #EC690F;height:18px;'><div  style='width:$progress_bar;background:url(\"../img/navigation/bg_progress_bar.gif\") repeat-x 0 0;height:20px;'></div></div>";

				echo '<div class="imageBox" id="imageBox'.$id.'">
		<div class="imageBox_theImage"><a href="lp_controller.php?'.api_get_cidReq().'&amp;action=view&amp;lp_id='.$id.'"><div class="quiz_content_actions" style="width:200px;height:80%;">';
		echo '<table width="100%">';
				echo '<tr style="height:50px;"><td colspan="2" align="center">'.$display_name.'</td></tr>';
				echo '<tr><td>&nbsp;</td></tr>';
				echo '<tr><td width="80%" valign="top">'.$html.'</td><td align="center"><img src="../img/exaile_old22.png"></td></tr>';
				echo '</table>';
				echo '</div></a>';
				if (api_is_allowed_to_edit()) {
				echo '<div class="imageBox_label" style="width:220px;text-align:right;"><a href="'.api_get_self().'?'.api_get_cidReq().'&amp;action=add_item&amp;type=step&amp;lp_id='.$id.'"><img src="../img/edit_link.png" ></a></div><br />';
				}
				echo '</div></div>';
			}
			echo '</div>
		<div id="insertionMarker">
		<img src="../img/marker_top.gif" alt="&nbsp;" />
		<img src="../img/marker_middle.gif" alt="&nbsp;" id="insertionMarkerLine" />
		<img src="../img/marker_bottom.gif" alt="&nbsp;" />
		</div>
		<div id="dragDropContent">
		</div><div id="debug" style="clear:both">
		</div>';
		}
?>