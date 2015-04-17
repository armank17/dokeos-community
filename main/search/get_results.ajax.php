<?php
require_once('../inc/global.inc.php');

require_once(api_get_path(LIBRARY_PATH).'search/DokeosQuery.php');
require_once api_get_path(LIBRARY_PATH).'course.lib.php';

$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.js"></script>'; 
$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.css" type="text/css" media="screen" />';

// Get resource only of trainings where user is registered
$course_id_list = CourseManager::get_course_id_list_by_user_id(api_get_user_id());
$training_list_query = array();
foreach ($course_id_list as $course_code) {
	$training_list_query[] = dokeos_get_boolean_query(XAPIAN_PREFIX_COURSEID . $course_code);
}
//$training_query = xapian_get_boolean_query(XAPIAN_PREFIX_COURSEID . api_get_course_id());
$my_training_list_query = dokeos_join_queries($training_list_query, null, 'OR');
$query_rs = dokeos_query_query($_GET['input'], 0, 100000, $my_training_list_query);
if ($query_rs) {
	$i = 0;
	foreach($query_rs[1] as $document) {
                $target = "";
		$thickbox = '';
		$i++;
		
		$extension = strtolower(pathinfo($document['url'], PATHINFO_EXTENSION));
		$fullFilename = $shortFilename = pathinfo($document['url'], PATHINFO_FILENAME);
		if(strlen($fullFilename)>20 & $document['toolid'] == TOOL_DOCUMENT) {
                        $complete_name = $fullFilename;
			$shortFilename = substr($fullFilename, 0, 20).'...';
		} else {
			$shortFilename = $document['title'];
                        $complete_name = $shortFilename;
			if (strlen($shortFilename)>20) {
			  $shortFilename = substr($document['title'], 0, 20).'...';
			}
		}

		$thumb_sys_path = api_get_path(SYS_COURSE_PATH).api_get_course_path($document['courseid']).'/temp/thumb_'.pathinfo($document['url'], PATHINFO_FILENAME).'.png';
		$thumb_web_path = api_get_path(WEB_COURSE_PATH).api_get_course_path($document['courseid']).'/temp/thumb_'.pathinfo($document['url'], PATHINFO_FILENAME).'.png';
		$thumb_width = 200;
		$thumb_height = 115;	
		$margin = '';
                $play_clip = '';

		// create video thumbnail
		if(in_array($extension, array('wmv','mpg','mpeg','mov','avi','flv','mp4'))) {
                        $play_clip = '<img align="center" style="position:absolute;margin-left:75px;margin-top:35px;" src="'.api_get_path(WEB_IMG_PATH).'media_playback_start_black_32.png" />';
			// check if the ffmpeg extension is available
			$extension_soname = 'ffmpeg'.PHP_SHLIB_SUFFIX;
			// load extension
			if(!extension_loaded('ffmpeg')) {
			    dl($extension_soname);
			}

			if(!file_exists($thumb_sys_path)) {
				$url = str_replace(api_get_path(WEB_PATH), api_get_path(SYS_PATH), $document['url']);
				$movie = new ffmpeg_movie($url, false);
				if(!$movie)continue;
				$frame = $movie->getFrame($movie->getFrameCount()/2);
				if(!$frame)continue;
				$width = $frame->getWidth();
				$height = $frame->getHeight();
				$img_src = $frame->toGDImage();
				$img_dest = imagecreatetruecolor($thumb_width, $thumb_height);
				imagecopyresampled($img_dest, $img_src, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
				imagepng($img_dest, $thumb_sys_path);
			}
			$document['thumbnail'] = $thumb_web_path;
			if($extension == 'flv' || $extension == 'mp4') {
				$document['url'] = api_get_path(WEB_PATH).'main/search/view_video.php?path='.urlencode($document['url']).'&amp;width=800&amp;height=500';
				$thickbox = 'thickbox';
                                $target = '_blank';
			}
		}
		
		// create image thumbnail
		else if(in_array($extension, array('png','jpg','bmp','gif')))
		{
			$img_sys_path = str_replace(api_get_path(WEB_COURSE_PATH), api_get_path(SYS_COURSE_PATH), $document['url']);
			switch($extension)
			{
				case 'png' :
					$img_src = imagecreatefrompng($img_sys_path);
					break;
				case 'jpg' :
					$img_src = imagecreatefromjpeg($img_sys_path);
					break;
				case 'gif' :
					$img_src = imagecreatefromgif($img_sys_path);
					break;
				case 'bmp' :
					$img_src = imagecreatefromwbmp($img_sys_path);
					break;
                                case 'jpeg' :
                                $img_src = imagecreatefromjpeg($img_sys_path);
                                break;
			}
			list($src_width, $src_weight) = getimagesize($document['url']);
			
			$img_dest = imagecreatetruecolor($thumb_width, $thumb_height);
			imagecopyresampled($img_dest, $img_src, 0, 0, 0, 0, $thumb_width, $thumb_height, $src_width, $src_weight);
			imagepng($img_dest, $thumb_sys_path);
			$document['thumbnail'] = $thumb_web_path;
			$document['url'] .= '?width=800&amp;height=450';
                        $target = '_blank';
			$thickbox = 'thickbox';
		} else if(in_array($extension, array('swf'))) {
			$document['url'] = api_get_path(WEB_PATH).'main/search/view_flash.php?path='.urlencode($document['url']).'&amp;width=800&amp;height=600';
			$document['thumbnail'] = api_get_path(WEB_IMG_PATH).'48x48file_flash.png';
			$thickbox = 'thickbox';
			$margin = 'margin-top:20px;';
		} else if($extension == 'pdf') {
			$document['thumbnail'] = api_get_path(WEB_IMG_PATH).'48x48file_pdf.png';
			$margin = 'margin-top:20px;';
		} else if(in_array($extension, array('doc','docx','odt'))) {
			$document['thumbnail'] = api_get_path(WEB_IMG_PATH).'48x48file_writer.png';
			$margin = 'margin-top:20px;';
		} else if(in_array($extension, array('xls','xlsx','ods'))) {
			$document['thumbnail'] = api_get_path(WEB_IMG_PATH).'48x48file_spreadsheet.png';
			$margin = 'margin-top:20px;';
		} else if(in_array($extension, array('ppt','pptx','odp'))) {
			$document['thumbnail'] = api_get_path(WEB_IMG_PATH).'48x48file_presentation.png';
			$margin = 'margin-top:20px;';
		}  else if($extension == 'mp3') {
                        $play_clip = '<img align="center" style="position:absolute;margin-left:22px;margin-top:32px;" src="'.api_get_path(WEB_IMG_PATH).'media_playback_start_black_32.png" />';
                        $document['thumbnail'] = api_get_path(WEB_IMG_PATH).'alsaconf';
                        $document['url'] = api_get_path(WEB_PATH).'main/search/view_mp3.php?path='.urlencode($document['url']).'&amp;width=500&amp;height=300';
                        $thickbox = 'thickbox';
                        $margin = 'margin-top:15px;';
                        $target = '_blank';
                } else {
                    $info = pathinfo($document['thumbnail']);
                    if ($info['basename'] == 'link.gif') {
                        $document['thumbnail'] = str_replace('link.gif','link_48.png',$document['thumbnail']);
                        $margin = 'margin-top:20px;';
                    } elseif ($info['basename'] == 'quiz.gif') {
                        $document['thumbnail'] = str_replace('quiz.gif','quiz_48.png',$document['thumbnail']);
                        $margin = 'margin-top:20px;';
                    } elseif ($info['basename'] == 'defaut.gif') {
                        $document['thumbnail'] = str_replace('defaut.gif','default_48.png',$document['thumbnail']);
                        $margin = 'margin-top:20px;';
                    } elseif ($info['basename'] == 'file_zip.gif') {
                        $document['thumbnail'] = str_replace('file_zip.gif','application_zip_64.png',$document['thumbnail']);
                        $margin = 'margin-top:10px;';
                    } else if ($info['basename'] == 'actiondoctext') {
                        $document['thumbnail'] = str_replace('actiondoctext','default_48.png',$document['thumbnail']);
                        $margin = 'margin-top:10px;';
                    } else {;
                        // Get last 3 characters
                        $ext = substr(strrchr($info['basename'],'.'),1);
                        $ext = strtolower($ext);
                        if (empty($ext)) {
                          $document['thumbnail'] = str_replace($info['basename'],'default_48.png',$document['thumbnail']);
                        }
                        $margin = 'margin-top:0px;'; 
                    }
                }
	

                if (empty($thickbox)) {
                    $target = '_blank';
                }

                if ($width >= 300 && $height >= 200) {
                    $real_width = ' width = "200" ';
                    $real_height = ' height = "115" ';
                }

                // clean empties contents
                if (empty($document['thumbnail'])) {
                    continue;
                }

		echo '
		<a href="'.$document['url'].'" target="'.$target.'" class="'.$thickbox.'" title="'.$complete_name.'">
			<div id="thumb_'.$i.'" class="big_button four_buttons rounded grey_border button_notext">
   '.$play_clip.'
				<img align="center" '.$real_width.' '.$real_height.' style="'.$margin.'" src="'.$document['thumbnail'].'" />
				<p style="width:100%; height:20px;left:0px; padding-top:5px; background-color:#ddd; position: absolute; bottom: 0px; margin:0px;">
					'.$shortFilename.'
				</p>
			</div>
		</a>';

		
	}
}
else
{
	echo get_lang('NoResults');
}