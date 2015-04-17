<?php
require ('../inc/global.inc.php');
$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
$sql = "SELECT id, name FROM $session_table";
$res = Database::query($sql, __FILE__, __LINE__);
while($row = Database::fetch_array($res)) {
	echo "<option value=".$row['id'].">".$row['name']."</option>";
}
?>