<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.survey
*/

require_once('../inc/global.inc.php');


switch ($_GET['action']) { 
case 'updateSurvey' :
  if (api_is_allowed_to_edit ()) {
   $output = changeSurveyOrder($_GET['disporder'],$_GET['survey_id']);
  }
  break;
}

/**
 * Allow reorder the question list using Drag and drop
 * @author Breetha Mohan <breetha.mohan@dokeos.com>
 * @param array $disporder 
 * @return boolean true if success
 */
function changeSurveyOrder($disporder,$survey_id) {

$table_survey_question 			= Database :: get_course_table(TABLE_SURVEY_QUESTION);
$disparr = explode(",",$disporder);	
$len = sizeof($disparr);
$listingCounter = 1;
for($i=0;$i<sizeof($disparr);$i++)
{	
	echo $sql = "UPDATE $table_survey_question SET sort=".$listingCounter." WHERE question_id = ".$disparr[$i]." AND survey_id = ".$survey_id;
	$res = Database::query($sql,__FILE__,__LINE__);
	$listingCounter = $listingCounter + 1;	
} 

  return true;

}
