<?php
//* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.booking
==============================================================================
*/

// the section (for the tabs)
$this_section = SECTION_COURSES;


require_once('rsys.php');

Rsys :: protect_script('reservation');

// tool name
$tool_name = get_lang('Booking');

// breadcrumbs
//$interbreadcrumb[] = array ("url" => 'reservation.php', "name" => get_lang('Booking'));

// Header
Display :: display_header($tool_name);

// Tool title
// api_display_tool_title($tool_name);


if (isset($_GET['cat'])) {
	$category_id = Security::remove_XSS($_GET['cat']);
}
if (isset($_GET['item'])) {
	$item_id = Security::remove_XSS($_GET['item']);
}


// actions
echo '<div class="actions">';
echo '<a href="mysubscriptions.php">'.Display::return_icon('file_txt.gif',get_lang('BookingListView')).get_lang('BookingListView').'</a>';
echo '<a href="reservation.php?action=makereservation">'.Display::return_icon('calendar_add.gif',get_lang('BookIt')).get_lang('BookIt').'</a>';
echo '</div>';

// start content div
echo '<div id="content">';

/**
 * Enter description here...
 *
 * @param unknown_type $color
 * @return html code
 */
function getBlock($color) {
	return '<img src="../img/px_'.$color.'.gif" alt="" style="border:1px solid #000;height: 10px;width: 10px;vertical-align:top;margin-left:10px" />';
}

$gogogo=false;
// Get resolution of user
if((empty($_SESSION['swidth'])||empty($_SESSION['sheight']))&&(empty($_GET['swidth'])||empty($_GET['sheight']))) {
	?>
	<script type="text/javascript">
	window.location.href='reservation.php?sheight='+screen. height+'&swidth='+screen.width;
	</script>
<?php
}
elseif((empty($_SESSION['swidth']))) {
    $_SESSION['swidth']=$_GET['swidth'];
    $_SESSION['sheight']=$_GET['sheight'];
    $gogogo=true;
}
else{
	$gogogo=true;
}


if ($_GET['action'] == 'makereservation')
{
	echo '<form id="cat_form" action="reservation.php?action=makereservation" method="get">';
	echo '	<input type="hidden" name="action" value="makereservation" />';
	echo '	<input type="hidden" name="cat" value="'.$category_id.'" />';
	
	// Step 1: Selecting the resource type (category)
	echo '	<div class="row">
				<div class="form_header">'.get_lang('Step1SelectResourceType').'</div>
			</div>';
	echo '	<div class="row">
				<div class="label">
					<span class="form_required">*</span> '.get_lang('ResourceType').'
				</div>
				<div class="formw">';
	echo '			<select name="cat" onchange="this.form.submit();">';
	echo '				<option value="0">'.get_lang('Select').'</option>';
	// adding all the resource categories to the form
	$cats = Rsys :: get_category_with_items();
	if(count($cats)>0){
	    foreach ($cats as $cat) {
		   echo '		<option value="'.$cat['id'].'"'. ($cat['id'] == $category_id ? ' selected="selected"' : '').'>'.$cat['name'].'</option>';
	    }
	}
	echo '			</select>';
	echo '		</div>
			</div>';	
	echo '</form>';

	if ($gogogo&&!empty($category_id)) {
		// getting all the resources for the selected category
		$itemlist = Rsys :: get_cat_items($category_id);

		// Step 2: Selecting the resource
	if (count($itemlist) != 0) {
			echo '<form id="item_form" action="reservation.php?cat='.$category_id.'&amp;item=" method="get">';
			echo '	<input type="hidden" name="action" value="makereservation" />';
			echo '	<input type="hidden" name="cat" value="'.$category_id.'" />';
			echo '	<div class="row">
						<div class="form_header">'.get_lang('Step2SelectResource').'</div>
					</div>';
			echo '	<div class="row">
						<div class="label">
							<span class="form_required">*</span> '.get_lang('Resource').'
						</div>
						<div class="formw">';		
			
			echo '			<select name="item" onchange="this.form.submit();">
								<option value="0">'.get_lang('Select').'</option>';
			// adding all the resources to the form
			foreach ($itemlist as $id => $item){
				echo '			<option value="'.$id.'"'. ($id == $_GET['item'] ? ' selected="selected"' : '').'>'.$item.'</option>';
			}
			echo '			</select>';
			echo '		</div>
					</div>';			
			echo '</form>';
	}else{
			echo get_lang('NoItemsReservation');	
	}
		
		
		// Step 3: Selecting booking period for the resource
	if(!empty($_GET['item'])) {
			
			echo '	<div class="row">
				<div class="form_header">'.get_lang('Step3SelectBookingPeriod').'</div>
			</div>';
			
		$calendar = new rCalendar();
		$time=Rsys::mysql_datetime_to_array($_GET['date'].' 00:00:00');
		ob_start();
			
			// The mini month calendar (for navigation to the next/previous weeks/months)
        echo '<div style="float: left; margin-right: 10px">';
		if(isset($_GET['changemonth'])) {
				echo $calendar->get_mini_month(intval($time['month']),intval($time['year']),"&amp;action=makereservation&amp;cat=".$category_id."&amp;item=".$_GET['item']."&amp;changemonth=yes",$_GET['item']);
		}
		else
				echo $calendar->get_mini_month(date('m'),date('Y'),"&amp;action=makereservation&amp;cat=".$category_id."&amp;item=".$_GET['item'],$_GET['item']);
	        echo '</div>';

	        // The week calendar (for selecting the booking periods)
	        echo '<div style="float: left" >';   
        switch($_SESSION['swidth']) {
            case '640': $week_scale= 170;break;
            case '1024': $week_scale=130;break;
            case '1152': $week_scale=110;break;
            case '1280': $week_scale=94;break;
            case '1600': $week_scale=70;break;
            case '1792': $week_scale=60;break;
            case '1800': $week_scale=50;break;
            case '1920': $week_scale=40;break;
            case '2048': $week_scale=30;break;
            default: $week_scale= 150; // 800x600
        }
		if(isset($_GET['date'])){
			echo $calendar->get_week_view(intval($time['day']),intval($time['month']), $time['year'],$_GET['item'], $week_scale,$category_id);
		}else
			echo $calendar->get_week_view(intval(date('d')), intval(date('m')), intval(date('Y')), $_GET['item'], $week_scale,$category_id);
		echo '</div>';
       $buffer=ob_get_contents();
       ob_end_clean();

			// The legend
	       $legend = 	getBlock('green').' '.api_ucfirst(get_lang('OpenBooking')).' '.
	       				getBlock('blue').' '.get_lang('TimePicker').' '.
	       				getBlock('orange').' '.get_lang('OutPeriod').' '.
	       				getBlock('red').' '.get_lang('Reserved').' '.
	       				getBlock('grey').' '.get_lang('NoReservations').' '.
	       				getBlock('black').' '.get_lang('Blackout');

	       echo '<div style="text-align:right; border-bottom: 2px dotted #666; margin: 0 0 0.2em 0; padding: 0.2em;clear:both;font-family: Verdana,sans-serif;font-size: 1.2em;color:#666;font-weight:bold">'.$GLOBALS['weekstart'].' - '.$GLOBALS['weekend'].'</div>'.$buffer.'<div style="clear:both;">&nbsp;</div><div style="background-color:#EEE;padding: 0.5em;font-family:Verdana;sans-serif;font-size:10px;text-align:center">'.$legend.'</div>';
		}
	}
}

// close content div
echo '</div>';

// footer
Display :: display_footer();
?>
