<?php
require_once('../inc/global.inc.php');

//TODO check permissions

echo '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'flowplayer/flowplayer-3.2.4.min.js"></script>';


$path = Security::remove_XSS(urldecode($_GET['path']));
?>
<table width="100%" height="100%">
	<tr>
		<td align="center" valign="left" width="100%" height="100%">
			<a href="<?php echo $path ?>" id="player" style="display:block;width:500px;height:250px"></a>
		</td>
	</tr>
</table>

<script type="text/javascript">
 // setup player
$f("player", "<?php echo api_get_path(WEB_LIBRARY_PATH).'flowplayer/flowplayer-3.2.5.swf';?>", {

	// common clip: these properties are common to each clip in the playlist
	clip: {
		// by default clip lasts 10 seconds
		duration: 10
	},

	// playlist with four entries
	playlist: [

		// another image. works as splash for the audio clip
		/*{url: '/main/img/media_flash.png', duration: 0},*/

		// audio, requires audio plugin. custom duration
		{url: "<?php echo $path; ?>", duration: 0}

	],

	// show playlist buttons in controlbar
	plugins:  {
		controls: {
			playlist: true
		}
	}
});

</script>
