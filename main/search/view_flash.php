<?php
require_once('../inc/global.inc.php');

//TODO check permissions

echo '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'swfobject/swfobject.js"></script>';

$path = Security::remove_XSS(urldecode($_GET['path']));
$dat = Security::remove_XSS(urldecode($_REQUEST['dat']));
?>
<table width="100%" height="100%">
	<tr>
		<td align="center" valign="middle" width="100%" height="100%">
			<!-- <div id="flashcontent"> -->
                        <div id="<?php echo $dat ?>">
			text replaces
			</div>
		</td>
	</tr>
</table>


<script type="text/javascript">

var so = new SWFObject("<?php echo $path ?>", "flashmovie", "100%", "580", "8", "");
//so.write("flashcontent");
so.write("<?php echo $dat ?>");

</script>