<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Learning Path
 * Script that handles the saving of item status
 * @package dokeos.learnpath
 * @author Yannick Warnier
 */

$msg = $_SESSION['oLP']->get_message();
$charset = 'ISO-8859-15'; //not taken into account here as we don't include a header
error_log('New LP - Loaded lp_save : '.$_SERVER['REQUEST_URI'].' from '.$_SERVER['HTTP_REFERER'],0);
?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<script language='javascript'>
<?php
if($_SESSION['oLP']->mode != 'fullscreen'){
}
?>
</script>

</head>
<body>
</body></html>
