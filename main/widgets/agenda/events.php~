<?php
// include the global Dokeos file
include_once('../../../inc/global.inc.php');
require (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');

// Database table definition
$table_agenda 		= Database::get_course_table(TABLE_AGENDA);
$table_item_property	= Database::get_course_table(TABLE_ITEM_PROPERTY);

// get all the group memberships of the user
$group_memberships	= GroupManager::get_group_ids($_course['dbName'],$_user['user_id']);
$group_memberships[]	= 0;

$sql="SELECT
	agenda.*, toolitemproperties.*, UNIX_TIMESTAMP(start_date) as start, UNIX_TIMESTAMP(end_date) as end
	FROM ".$table_agenda ." agenda, ".$table_item_property." toolitemproperties
	WHERE agenda.id = toolitemproperties.ref 
	AND toolitemproperties.tool='".TOOL_CALENDAR_EVENT."'
	AND	( toolitemproperties.to_user_id=".Database::escape_string($_user['user_id'])." OR toolitemproperties.to_group_id IN (".implode(", ", $group_memberships).") )
	AND toolitemproperties.visibility='1'
	AND start_date >= FROM_UNIXTIME(".$_GET['start'].")
	AND start_date <= FROM_UNIXTIME(".$_GET['end'].")";
$result=Database::query($sql,__FILE__,__LINE__) or die(Database::error());
while ($row=Database::fetch_array($result))
{
	$events[] = array('id'=>$row['id'], 'title'=>$row['title'], 'start'=>date('c',$row['start']), 'end'=>date('c',$row['end']), "allDay"=>false);
}
echo json_encode($events);
?>
