<?php
/**
==============================================================================
*	This script displays the Dokeos header up to the </head> tag
*   IT IS A COPY OF header.inc.php EXCEPT that it doesn't start the body
*   output.
*
*	@package dokeos.include
==============================================================================
*/

/*----------------------------------------
              HEADERS SECTION
  --------------------------------------*/

/*
 * HTTP HEADER
 */

//Give a default value to $charset. Should change to UTF-8 some time in the future.
//This parameter should be set in the platform configuration interface in time.
if(empty($charset))
{
	$charset = 'UTF-8';
}

//header('Content-Type: text/html; charset='. $charset)
//	or die ("WARNING : it remains some characters before &lt;?php bracket or after ?&gt end");

header('Content-Type: text/html; charset='. $charset);
if ( isset($httpHeadXtra) && $httpHeadXtra )
{
	foreach($httpHeadXtra as $thisHttpHead)
	{
		header($thisHttpHead);
	}
}

// Get language iso-code for this page - ignore errors
// The error ignorance is due to the non compatibility of function_exists()
// with the object syntax of Database::get_language_isocode()
@$document_language = Database::get_language_isocode($language_interface);
if(empty($document_language))
{
  //if there was no valid iso-code, use the english one
  $document_language = 'en';
}

/*
 * HTML HEADER
 */
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $document_language; ?>" lang="<?php echo $document_language; ?>">
<head>
    <meta charset="utf-8"/>
<title>
<?php
if(!empty($nameTools))
{
	echo $nameTools.' - ';
}

if(!empty($_course['official_code']))
{
	echo $_course['official_code'].' - ';
}

echo api_get_setting('siteName');
?>
</title>
<?php

/*
 * Choose CSS style platform's, user's, course's, or Learning path CSS
 */

$platform_theme = api_get_setting('stylesheets'); 	// plataform's css
$my_style=$platform_theme;
if(api_get_setting('user_selected_theme') == 'true')
{
	$useri = api_get_user_info();
	$user_theme = $useri['theme'];
	if(!empty($user_theme) && $user_theme != $my_style)
	{
		$my_style = $user_theme;					// user's css
	}
}
$mycourseid = api_get_course_id();
if (!empty($mycourseid) && $mycourseid != -1)
{
	if (api_get_setting('allow_course_theme') == 'true')
	{
		$mycoursetheme=api_get_course_setting('course_theme');
		if (!empty($mycoursetheme) && $mycoursetheme!=-1)
		{
			if(!empty($mycoursetheme) && $mycoursetheme != $my_style)
			{
				$my_style = $mycoursetheme;		// course's css
			}
		}

		$mycourselptheme=api_get_course_setting('allow_learning_path_theme');
		if (!empty($mycourselptheme) && $mycourselptheme!=-1 && $mycourselptheme== 1)
		{
			global $lp_theme_css; //  it comes from the lp_controller.php
			global $lp_theme_config; // it comes from the lp_controller.php

			if (!empty($lp_theme_css))
				{
					$theme=$lp_theme_css;
					if(!empty($theme) && $theme != $my_style)
					{
						$my_style = $theme;	 // LP's css
					}
				}

		}
	}
}

if (!empty($lp_theme_log)){
	$my_style=$platform_theme;
}


if($my_style!='')
{
?>
<style type="text/css" media="screen, projection">
/*<![CDATA[*/
@import "<?php echo api_get_path(WEB_CODE_PATH); ?>css/<?php echo $my_style;?>/default.css";
/*]]>*/
</style>
<?php
}
?>

<link rel="courses" href="<?php echo api_get_path(WEB_CODE_PATH) ?>auth/courses.php" title="<?php echo api_htmlentities(get_lang('OtherCourses'),ENT_QUOTES,$charset); ?>" />
<link rel="profil" href="<?php echo api_get_path(WEB_CODE_PATH) ?>auth/profile.php" title="<?php echo api_htmlentities(get_lang('ModifyProfile'),ENT_QUOTES,$charset); ?>" />
<link href="http://www.dokeos.com/documentation.php" rel="Help" />
<link href="http://www.dokeos.com" rel="Copyright" />
<link rel="shortcut icon" href="<?php echo api_get_path(WEB_PATH); ?>favicon.ico" type="image/x-icon" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset ?>" />

<script type="text/javascript">
//<![CDATA[
// This is a patch for the "__flash__removeCallback" bug, see FS#4378.
if ( ( navigator.userAgent.toLowerCase().indexOf('msie') != -1 ) && ( navigator.userAgent.toLowerCase().indexOf( 'opera' ) == -1 ) )
{
	window.attachEvent( 'onunload', function()
		{
			window['__flash__removeCallback'] = function ( instance, name )
			{
				try
				{
					if ( instance )
					{
						instance[name] = null ;
					}
				}
				catch ( flashEx )
				{

				}
			} ;
		}
	) ;
}
//]]>
</script>
<script type="text/javascript">
        function initJQuery() {    
                //if the jQuery object isn't available
                if (typeof(jQuery) == 'undefined') {      
                        if (typeof initJQuery.jQueryScriptOutputted == 'undefined') {
                                //only output the script once..
                                initJQuery.jQueryScriptOutputted = true;            
                                //output the script (load it from google api)
                                document.write("<scr" + "ipt type=\"text/javascript\" src=\"<?php echo api_get_path(WEB_LIBRARY_PATH); ?>javascript/jquery-1.4.2.min.js\"></scr" + "ipt>");
                        }
                        setTimeout("initJQuery()", 50);
                }
        }
        initJQuery();
    </script>
<?php
global $_configuration;

if ($_configuration['multiple_access_urls'] == true) {
$current_url_path = substr(api_get_path(WEB_PATH),0,-1);
$main_url_path = $_configuration['root_web'];
$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function (){
      $("a[href]").attr("href", function(index, href) {
       var href_info = new Array();
       var n = href.indexOf("/main/");

       if (n != -1) {
         href_info = href.split("/main/");
         hardcoded_url = href_info[0];
          if(new RegExp("[a-zA-Z0-9]+://([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?").test(hardcoded_url)) {
            href = href.replace(hardcoded_url,"'.$current_url_path.'");
          }
        }
        return href;
      });
    });
  </script>';
$current_url_path = api_get_path(WEB_PATH);
$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function (){
      $("img[src]").attr("src", function(index, src) {
        src = src.replace("'.$main_url_path.'","'.$current_url_path.'");
        return src;
      });
    });
  </script>';
}
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js"></script>';

$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.qtip/jquery.qtip.min.css" />';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.qtip/jquery.qtip.min.js"></script>';

$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH).'javascript/dokeos.js.php" language="javascript"></script>';
if ( isset($htmlHeadXtra) && $htmlHeadXtra )
{
	foreach($htmlHeadXtra as $this_html_head)
	{
		echo($this_html_head);
	}
}
?>
</head>
<body dir="<?php echo  $text_dir ?>" <?php
if(defined('DOKEOS_QUIZGALLERY') && DOKEOS_QUIZGALLERY)
echo 'onload="javascript:if(document.question_admin_form) { document.question_admin_form.questionName.focus(); }"';?>>