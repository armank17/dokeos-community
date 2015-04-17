<?php

// $Id: quota.php 21106 2009-05-30 16:25:16Z iflorespaz $

/* For licensing terms, see /dokeos_license.txt */

/**
  ==============================================================================
 * 	This script displays info about the course disk use and quota:
 * 	how large (in megabytes) is the documents area of the course,
 * 	what is the maximum allowed for this course...
 *
 * 	@author Roan Embrechts
 * 	@package dokeos.document
  ==============================================================================
 */
// name of the language file that needs to be included
$language_file = 'document';

// including the global dokeos file
require_once "../inc/global.inc.php";

// including additional libraries
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';

// some constants and variables
$courseDir = $_course['path'] . "/document";
$maxFilledSpace = DEFAULT_DOCUMENT_QUOTA;

// getting the course quota
$course_quota = DocumentManager::get_course_quota();

// calculating the total space
$already_consumed_space = documents_total_space($_course);

if ($_REQUEST['action'] == 'diskspace') {
    echo floatval($course_quota) - floatval($already_consumed_space);
    exit;
}

// breadcrumbs
$interbreadcrumb[] = array("url" => "document.php", "name" => get_lang('Document'));

// title of the page
$nameTools = get_lang("DocumentQuota");

// display the header
Display::display_tool_header($nameTools, "Doc");
Display::display_introduction_section(TOOL_DOCUMENT);
// actions
echo '<div class="actions">';
// link back to the documents overview
echo '<a href="document.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('Documents'), array('style' => 'vertical-align:middle;', 'class' => 'toolactionplaceholdericon toolactionback')) . '&nbsp;&nbsp;' . get_lang('Documents') . '</a>';
echo '</div>';

echo '<div id="content">';
/*
  ==============================================================================
  FUNCTIONS
  ==============================================================================
 */

/**
 * 	Here we count 1 kilobyte = 1000 byte, 12 megabyte = 1000 kilobyte.
 */
function display_quota($course_quota, $already_consumed_space) {
    $course_quota_m = round($course_quota / 1000000);
    $already_consumed_space_m = round($already_consumed_space / 1000000);
    $message = get_lang("CourseCurrentlyUses") . " <strong>" . $already_consumed_space_m . " megabyte</strong>.<br/>" . get_lang("MaximumAllowedQuota") . " <strong>$course_quota_m megabyte</strong>.<br/>";

    $percentage = $already_consumed_space / $course_quota * 100;
    $percentage = round($percentage);

    if ($percentage < 100)
        $other_percentage = 100 - $percentage;
    else
        $other_percentage = 0;

    //decide where to place percentage in graph
    if ($percentage >= 50) {
        $text_in_filled = "&nbsp;$other_percentage%" .
                $text_in_unfilled = "";
    } else {
        $text_in_unfilled = "&nbsp;$other_percentage%" .
                $text_in_filled = "";
    }

    //decide the background colour of the graph
    if ($percentage < 65)
        $colour = "#E9E9E9"; //safe - grey
    else if ($percentage < 90)
        $colour = "#B0AEAE"; //filling up - yelloworange
    else
        $colour = "#DD0000"; //full - red

        
//this is used for the table width: a table of only 100 pixels looks too small
    $visual_percentage = 4 * $percentage;
    $visual_other_percentage = 4 * $other_percentage;

    $message .= get_lang("PercentageQuotaInUse") . ": <strong>$percentage%</strong>.<br/>" .
            get_lang("PercentageQuotaFree") . ": <strong>$other_percentage%</strong>.<br>";

    $message .= "<br/><table cellpadding=\"\" cellspacing=\"0\" height=\"40\"><tr>
				<td bgcolor=\"$colour\" width=\"$visual_percentage\"></td>
				<td bgcolor=\"#E0E0E0\" width=\"$visual_other_percentage\">&nbsp;$other_percentage%</td>
				</tr></table>";

    echo $message;
}

// setting the full path
$full_path = $baseWorkDir . $courseDir;

// displaying the quota
display_quota($course_quota, $already_consumed_space);
echo '</div>';
// display the footer
Display::display_footer();
?>
