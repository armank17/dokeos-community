<?php
//* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.booking
*       An overview with a list of upcoming reservations where
*       the user has subscribed to (may also be viewable in the agenda)
*
*       Later: links to m_item & m_reservation for every item your group (class) owns and
*       the possibility (links) for adding new items or reservations
==============================================================================
*/

require_once('rsys.php');

Rsys::protect_script('mysubscriptions');

// tool name
$tool_name = get_lang('Booking');

/**
    ---------------------------------------------------------------------
 */

/**
 *  Filter to display the modify-buttons
 */
/**
 * filter for the modify column of the sortable table 
 *
 * @param unknown_type $id
 * @param unknown_type $get
 * @param unknown_type $row
 * @return html code
 */
function modify_filter($id,$get,$row)
{
     return ' <a href="mysubscriptions.php?action=delete&amp;reservation_id='.substr($id,0,strpos($id,'-')).'&amp;dummy='.$row['0'].'" title="'.get_lang("DeleteSubscription").'" onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang("ConfirmDeleteSubscription")))."'".')) return false;">'.Display::return_icon('delete.png',get_lang('Delete')).'</a>';        
}

/**
 * Filter for the accepted column of the sortable table
 *
 * @param unknown_type $accept
 * @return unknown
 */
function accept_filter($accept)
{
	if ($accept == 1)
	{
		return get_lang('Yes');
	}
	else 
	{
		return get_lang('No');
	}
}

// Actions handling
switch ($_GET['action']) {
    case 'delete' :
        Rsys :: delete_subscription($_GET['reservation_id'],$_GET['dummy']);
        ob_start();
        Display :: display_normal_message(Rsys::get_return_msg(get_lang('SubscriptionDeleted'),"mysubscriptions.php",$tool_name),false);
        $msg=ob_get_contents();
		ob_end_clean();
    default :
        $NoSearchResults=get_lang('NoReservations');
        
        // header
        Display :: display_header($tool_name);

	// actions
	echo '<div class="actions">';
	echo '<div style="float: right;"><a href="reservation.php">'.Display::return_icon('sessions.gif',get_lang('BookingCalendarView')).'&nbsp;'.get_lang('GoToCalendarView').'</a></div>';
	echo '<a href="m_item.php?view=list">'.Display::return_icon('cube.png',get_lang('Resources')).'&nbsp;'.get_lang('Resources').'</a>';
	echo '<a href="m_reservation.php?view=list">'.Display::return_icon('calendar_day.gif',get_lang('BookingPeriods')).'&nbsp;'.get_lang('BookingPeriods').'</a>';
	echo '<a href="m_reservation.php?action=add&amp;view=list">'.Display::return_icon('calendar_add.gif',get_lang('BookIt')).'&nbsp;'.get_lang('BookIt').'</a>';	
	echo '<a href="mysubscriptions.php">'.Display::return_icon('file_txt.gif',get_lang('BookingListView')).get_lang('BookingListView').'</a>';
	echo '<a href="reservation.php?action=makereservation">'.Display::return_icon('calendar_add.gif',get_lang('BookIt')).get_lang('BookIt').'</a>';
	echo '</div>';

	// start the content div
	echo '<div id="content">';

        if (isset ($_POST['action'])) {
            switch ($_POST['action']) {
                case 'delete_subscriptions' :
                    $ids = $_POST['subscriptions'];
                    if (count($ids) > 0) {
                        foreach ($ids as $id)
                            Rsys :: delete_subscription(substr($id,0,strpos($id,'-')),substr($id,strrpos($id,'-')+1));
                    }
                    break;
            }
        }

        $table = new SortableTable('subscription', array('Rsys','get_num_subscriptions'),array('Rsys','get_table_subscriptions'),2);
        $table->set_header(0, '', false,array('style'=>'width:10px'));
        $table->set_header(1, get_lang('ResourceName'), true);
        $table->set_header(2, get_lang('StartDate'), true);
        $table->set_header(3, get_lang('EndDate'), true);
		$table->set_header(4, get_lang('Accept'), true);
        $table->set_header(5, get_lang('Modify'), false,array('style'=>'width:50px;'));
        $table->set_column_filter(5, 'modify_filter');
        $table->set_column_filter(4, 'accept_filter');
        $table->set_form_actions(array ('delete_subscriptions' => get_lang('DeleteSelectedSubscriptions')),'subscriptions');
        $table->display();

	// close the content div
	echo '</div>';
}

// footer
Display :: display_footer();
?>
