<?php //$id:$
/* For licensing terms, see /dokeos_license.txt */
//error_log(__FILE__);
/**
*	This file generates the ActionScript variables code used by the HotSpot .swf
*	@package dokeos.exercise
* 	@author Toon Keppens
* 	@version $Id: admin.php 10680 2007-01-11 21:26:23Z pcool $
*/




include_once 'exercise.class.php';
include_once 'question.class.php';
include_once 'answer.class.php';
include_once '../inc/global.inc.php';
// set vars
$questionId    = (int)$_GET['modifyAnswers'];
$picturePath   = $documentPath.'/images';

if ($questionId == 0) {
 return;
}

global $_course;

$objQuestion = Question::read($questionId);

$pictureName   = $objQuestion->selectPicture();
$pictureSize   = getimagesize($picturePath.'/'.$objQuestion->selectPicture());
$pictureWidth  = $pictureSize[0];
$pictureHeight = $pictureSize[1];

$courseLang = !empty($_course['language']) ? $_course['language'] : $_SESSION['language'];
$courseCode = !empty($_course['sysCode']) ? $_course['sysCode'] : $_SESSION['sysCode'];
$coursePath = !empty($_course['path']) ? $_course['path'] : $_SESSION['path'];


// Query db for answers
//$sql = "SELECT id, answer, hotspot_coordinates, hotspot_type, ponderation FROM $TBL_ANSWERS WHERE question_id = '$questionId' ORDER BY id";
//$result = api_sql_query($sql,__FILE__,__LINE__);

// Init
$output = "hotspot_lang=$courseLang&hotspot_image=$pictureName&hotspot_image_width=$pictureWidth&hotspot_image_height=$pictureHeight&courseCode=$coursePath";

$i = 0;
$nmbrTries = 0;


$answers=$_SESSION['tmp_answers'];
$nbrAnswers = count($answers['answer']);

for($i=1;$i <= $nbrAnswers;$i++)
{
   	$output .= "&hotspot_".$i."=true";
	$output .= "&hotspot_".$i."_answer=".$answers['answer'][$i];

	// Square or rectancle
	if ($answers['hotspot_type'][$i] == 'square' )
	{
		$output .= "&hotspot_".$i."_type=square";
	}

	// Circle or ovale
	if ($answers['hotspot_type'][$i] == 'circle')
	{
		$output .= "&hotspot_".$i."_type=circle";
	}

	// Polygon
	if ($answers['hotspot_type'][$i] == 'poly')
	{
		$output .= "&hotspot_".$i."_type=poly";
	}
	// Delineation
	if ($answers['hotspot_type'][$i] == 'delineation')
	{
		$output .= "&hotspot_".$i."_type=delineation";
	}

	// This is a good answer, count + 1 for nmbr of clicks
	if ($answers['weighting'][$i] > 0)
	{
		$nmbrTries++;
	}

	$output .= "&hotspot_".$i."_coord=".$answers['hotspot_coordinates'][$i]."";
}

// Generate empty
$i++;
for ($i; $i <= 12; $i++)
{
	$output .= "&hotspot_".$i."=false";
}
// Output
echo $output."&nmbrTries=".$nmbrTries."&done=done";
?>