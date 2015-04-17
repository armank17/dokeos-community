<?php

api_protect_course_script(true);

//Activate audiorecorder - 1 to activate - 0 to deactivate
$audiorecorder = 1;

//Time limit of the recording
$time_limit = 120;
 $url = parse_url(api_get_path(WEB_PATH));

//Tool to convert flv file into mp3
$ffmpeg = '/usr/bin/ffmpeg -i ';

//Source directory of the FLV files repository
$dir_audio_source = '/usr/share/red5/webapps/oflaDemo/streams/';

//Target directory of the MP3 files into Dokeos course structure
$dir_audio_target = $_configuration['root_sys'].'courses/'.$_SESSION['_cid'].'/document/audio/';

?>
