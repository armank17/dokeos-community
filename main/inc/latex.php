<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>LaTeX Code</title>
</head>

<body>
<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	This script displays a form for registering new users.
*	@package	 dokeos.include
==============================================================================
*/

// include the global Dokeos file
include("../inc/global.inc.php");

// cleaning URL parameters
$code 		= Security::remove_XSS($_GET['code']);
$filename	= Security::remove_XSS($_GET['filename']);

echo '<div id="latex_code">';
echo '<h3>'.get_lang('LatexCode').'</h3>';
echo stripslashes($code);
echo '</div>';



echo '<div id="latex_image">';
echo '<h3>'.get_lang('LatexFormula').'</h3>';
echo '<img src="'.api_get_path(WEB_COURSE_PATH).$_course['path'].'/temp/'.$filename.'" alt="'.get_lang('LatexCode').'"/>';
echo '</div>';
?>
</body>
</html>
