<?php
require_once('../inc/global.inc.php');

//TODO check permissions
echo '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'flowplayer/flowplayer-3.2.4.min.js"></script>';
$path = Security::remove_XSS(urldecode($_GET['path']));

?>
<table width="100%" height="100%">
	<tr>
		<td align="center" valign="middle" width="100%" height="100%">
			<a href="<?php echo $path ?>" id="player" style="display:block;width:800px;height:450px"></a>
		</td>
	</tr>
</table>

<script type="text/javascript">flowplayer("player", "<?php echo api_get_path(WEB_LIBRARY_PATH) ?>flowplayer/flowplayer-3.2.5.swf");</script>
