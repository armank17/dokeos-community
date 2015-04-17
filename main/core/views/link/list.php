<?php	
	global $_user, $charset, $view;
	//if($no_of_links <> 0 ){
		echo '<table class="data_table">';
		if (api_is_allowed_to_edit ()) {
			echo "<tr><th width='6%' align='center' style='padding-right: 0px;'>" . get_lang('Move') . "</th>";
		    echo "<th width='54%' align='left' style='padding-right: 0px;padding-left: 5px;'>" . get_lang('Link') . "</th>";
		    echo "<th width='10%' style='padding-right: 0px;'>" . get_lang('Modify') . "</th>";
		    echo "<th width='10%' style='padding-right: 0px;'>" . get_lang('Delete') . "</th>";
		    echo "<th width='10%' style='padding-right: 0px;'>" . get_lang('Visible') . "</th>";
		    /*// deprecated since 3.0
		    echo "<th width='10%' style='padding-right: 0px;'>" . get_lang('Module') . "</th>";
		    */// 
		}
		else {
		    echo "<tr><th width='100%' align='left'>" . get_lang('Links') . "</th>";
		}
		echo "</tr>";
		echo '</table>';
		if(!empty($zeroCategoryLinks )){
			echo $zeroCategoryLinks;
		}
	//}
	/*else {
		echo '<table class="data_table"><tr><th>&nbsp;</th></tr></table>';
	}*/
	
	if(!empty($linkCategories )){
		echo $linkCategories;
	}
	
?>
