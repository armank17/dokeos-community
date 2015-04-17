<?php //$id: $
/* For licensing terms, see /dokeos_license.txt */
/**
==============================================================================
* GOAL : Dokeos installation
* As seen from the user, the installation proceeds in 6 steps.
* The user is presented with several webpages where he/she has to make choices
* and/or fill in data.
*
* The aim is, as always, to have good default settings and suggestions.
*
* @todo	reduce high level of duplication in this code
* @todo (busy) organise code into functions
* @package dokeos.install
==============================================================================
*/

/*
==============================================================================
		PHP VERSION CHECK & MBSTRING EXTENSION CHECK
==============================================================================
*/
error_reporting(E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR);
if ( !function_exists('version_compare') || version_compare( phpversion(), '5', '<' )) {
	$error_message_php_version = <<<EOM
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
		<head>
			<title>Wrong PHP version!</title>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<style type="text/css" media="screen, projection"> 
	/*<![CDATA[*/
	@import "../css/dokeos2_black_tablet/default.css";
	/*]]>*/
	</style> 
	<style type="text/css" media="print"> 
	/*<![CDATA[*/
	@import "../css/dokeos2_black_tablet/print.css";
	/*]]>*/
	</style> 	
		</head>
		<body>
		
		
		<div id="header">
			<div id="header1">
					<div class="headerinner">
						<div id="top_corner"></div> 
						<div id="languageselector">
						</div>
						<div id="institution">
							<?php echo get_lang('DokeosInstallation').' &mdash; '.get_lang('Version_').' '.$new_version; ?><?php if($installType == 'new') echo ' &ndash; '.get_lang('NewInstallation'); else if($installType == 'update') echo ' &ndash; '.get_lang('UpdateFromDokeosVersion').(is_array($update_from_version)?implode('|',$update_from_version):''); ?>
						</div>
					</div>
			</div>
			<div id="header2">
				<div class="headerinner">
					<ul id="dokeostabs"> 
                                                
						<li id="current"><span><a href="#"> <div>Installation</div></a></span></li>
						<li><span><a href="../../documentation/installation_guide.php">Installation guide</a></span></li>
					</ul>
					<div style="clear: both;" class="clear"> </div>
				</div>
			</div>
		</div>
                
                <div id="wrapper">
		<div id="main">
			<div id="content" class="maxcontent">

				<div style="text-align: center;"><br /><br />
						The version of scripting language on your server is wrong. Your server has to support PHP 5.x.x .<br />
						<a href="../../documentation/installation_guide.php" target="_blank">Read the installation guide</a><br /><br />
				</div>
			</div>
		</div>
		<div id="push"></div>
		</div>

			<div id="footer">
				<div class="copyright">Platform <a href="http://www.dokeos.com" target="_blank"> Dokeos </a> &copy; 2009 </div>
				&nbsp;
			</div>

		</body>
</html>
EOM;
	header('Content-Type: text/html; charset=UTF-8');
	die($error_message_php_version);
}

session_start();

// Including necessary files
@include '../inc/installedVersion.inc.php';
require '../inc/lib/main_api.lib.php';
require_once 'install_functions.inc.php';

// language setting
require '../lang/english/trad4all.inc.php';
require '../lang/english/install.inc.php';

if (!empty($_POST['language_list'])) {
	// check if it is a valid language
	$language_list = get_language_folder_list();
	if (array_key_exists($_POST['language_list'],$language_list)){
		$install_language = $_POST['language_list'];
	} else {
		$install_language = 'english';
	}
	include_once "../lang/$install_language/trad4all.inc.php";
	include_once "../lang/$install_language/install.inc.php";
	api_session_register('install_language');
} elseif ( isset($_SESSION['install_language']) && $_SESSION['install_language'] ) {
	$install_language = $_SESSION['install_language'];
	include_once "../lang/$install_language/trad4all.inc.php";
	include_once "../lang/$install_language/install.inc.php";
}
else
{
	$install_language = 'english';
}

// These global variables must be set for proper working of the function get_lang(...) during the installation.
$language_interface = $install_language;
$language_interface_initial_value = $install_language;

// Character set during installation: ISO-8859-15 for Latin 1 languages, UTF-8 for other languages.
$charset = 'UTF-8';
if (isset($install_language)) {
	if (strpos($install_language, 'unicode') === false && api_is_latin1_compatible($install_language))
	{
		// TODO: This is for backward compatibility. Actually, all the languages may use UTF-8.
		$charset = 'UTF-8';
	}
}
header('Content-Type: text/html; charset='. $charset);
// the cache-control header defines how long the page is valid for.
header('Cache-Control: max-age=0, no-cache, must-revalidate');
header('Pragma: no-cache');
// Initialization of the internationalization library.
api_initialize_internationalization();
// Initialization of the default encoding that will be used by the multibyte string routines in the internationalization library.
api_set_internationalization_default_encoding($charset);

require_once 'install_upgrade.lib.php'; //also defines constants


// Some constants
define('DOKEOS_INSTALL',1);
define('MAX_COURSE_TRANSFER',100);
define('INSTALL_TYPE_UPDATE', 'update');
define('FORM_FIELD_DISPLAY_LENGTH', 40);
define('DATABASE_FORM_FIELD_DISPLAY_LENGTH', 25);
define('MAX_FORM_FIELD_LENGTH', 80);

// setting the error reporting
error_reporting(E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR);

// overriding the timelimit (for large campusses that have to be migrated)
@set_time_limit(0);

//upgrading from any subversion of 1.6 is just like upgrading from 1.6.5
$update_from_version_6=array('1.6','1.6.1','1.6.2','1.6.3','1.6.4','1.6.5');
//upgrading from any subversion of 1.8 avoids the additional step of upgrading from 1.6
$update_from_version_8=array('1.8','1.8.2','1.8.3','1.8.4','1.8.5','1.8.6','1.8.6.1');
//upgrading from any subversion of 2.0 avoids the additional step of upgrading from 1.6
$update_from_version_20 = array('2.0','2.1','2.2');
$my_old_version = '';
$tmp_version = get_config_param('dokeos_version');
if(!empty($_POST['old_version'])) {
	$my_old_version = $_POST['old_version'];
} elseif(!empty($tmp_version)) {
    $my_old_version = $tmp_version;
}
elseif(!empty($dokeos_version)) //variable coming from installedVersion, normally
{
	$my_old_version = $dokeos_version;
}

$new_version = '3.0';
$new_version_stable = true;
$new_version_major = true;
$new_version_package = 'pro';
/*
==============================================================================
		STEP 1 : INITIALIZES FORM VARIABLES IF IT IS THE FIRST VISIT
==============================================================================
*/

//Is valid request
$is_valid_request=$_REQUEST['is_executable'];
foreach ($_POST as $request_index=>$request_value) {
	if (substr($request_index,0,4)=='step') {
		if ($request_index<>$is_valid_request) {
			unset($_POST[$request_index]);
		}
	}
}
$badUpdatePath=false;
$emptyUpdatePath=true;
$proposedUpdatePath = '';
if(!empty($_POST['updatePath']))
{
	$proposedUpdatePath = $_POST['updatePath'];
}

if ($_POST['step2_install'] || $_POST['step2_update_8'] || $_POST['step2_update_6'] || $_POST['step2_update_20']) {
	if ($_POST['step2_install']) {
		$installType='new';

		$_POST['step2']=1;
	} else {
		$installType='update';
		if($_POST['step2_update_8']) {
			$emptyUpdatePath = false;
			if(empty($_POST['updatePath'])) {
				$proposedUpdatePath = $_SERVER['DOCUMENT_ROOT'];
			} else {
				$proposedUpdatePath = $_POST['updatePath'];
			}
			if(substr($proposedUpdatePath,-1) != '/') {
				$proposedUpdatePath.='/';
			}
			if(file_exists($proposedUpdatePath)) {
				if(in_array($my_old_version,$update_from_version_8)) {
					$_POST['step2']=1;
				} else {
					$badUpdatePath=true;
				}
			} else {
				$badUpdatePath=true;
			}
		}elseif($_POST['step2_update_20']) {
			$emptyUpdatePath = false;
			if(empty($_POST['updatePath'])) {
				$proposedUpdatePath = $_SERVER['DOCUMENT_ROOT'];
			} else {
				$proposedUpdatePath = $_POST['updatePath'];
			}
			if(substr($proposedUpdatePath,-1) != '/') {
				$proposedUpdatePath.='/';
			}
			if(file_exists($proposedUpdatePath)) {
				if(in_array($my_old_version,$update_from_version_20)) {
					$_POST['step2']=1;
				} else {
					$badUpdatePath=true;
				}
			} else {
				$badUpdatePath=true;
			}
		} else { //step2_update_6, presumably
			if(empty($_POST['updatePath']))
			{
				$_POST['step1']=1;
			}
			else
			{
				$emptyUpdatePath = false;
				if(substr($_POST['updatePath'],-1) != '/')
				{
					$_POST['updatePath'].='/';
				}

				if(file_exists($_POST['updatePath']))
				{
					//1.6.x
					$my_old_version = get_config_param('clarolineVersion',$_POST['updatePath']);
					if(in_array($my_old_version,$update_from_version_6))
					{
						$_POST['step2']=1;
						$proposedUpdatePath = $_POST['updatePath'];
					}
					else
					{
						$badUpdatePath=true;
					}
				}
				else
				{
					$badUpdatePath=true;
				}
			}
		}
	}
}
elseif($_POST['step1'])
{
	$_POST['updatePath']='';
	$installType='';
	$updateFromConfigFile='';
	unset($_GET['running']);
}
else
{
	$installType=$_GET['installType'];
	$updateFromConfigFile=$_GET['updateFromConfigFile'];
}
$current_configuration_file = '../inc/conf/configuration.php';
if (file_exists($current_configuration_file)) {
  include_once '../inc/conf/configuration.php';
  global $_configuration;
  $my_old_version = $_configuration['dokeos_version'];
}

if(($installType=='update' && in_array($my_old_version,$update_from_version_8)) || ($installType=='update' && in_array($my_old_version,$update_from_version_20)))
{
	include_once '../inc/conf/configuration.php';
}


if(!isset($_GET['running'])) {
	$dbHostForm='localhost';
	$dbUsernameForm='root';
	$dbPassForm='';
 	$dbPrefixForm='';
	$dbNameForm='dokeos_main';
	$dbStatsForm='dokeos_stats';
	$dbScormForm='dokeos_scorm';
	$dbUserForm='dokeos_user';

	// extract the path to append to the url if Dokeos is not installed on the web root directory
	$urlAppendPath=str_replace('/main/install/index.php','',api_get_self());
  	$urlForm='http://'.$_SERVER['HTTP_HOST'].$urlAppendPath.'/';
	$pathForm=str_replace('\\','/',realpath('../..')).'/';

/*	$emailForm=$_SERVER['SERVER_ADMIN'];
	$email_parts = explode('@',$emailForm);
	if($email_parts[1] == 'localhost')
	{
		$emailForm .= '.localdomain';
	}*/
	$emailForm='newportal@dokeos.com';
	$adminLastName='Doe';
	$adminFirstName='John';
	$loginForm='admin';
	//$passForm=api_generate_password();
	$passForm='admin';

	//$campusForm='My campus';
	$campusForm='Training';
	$educationForm='Albert Einstein';
	$adminPhoneForm='(000) 001 02 03';
	//$institutionForm='My Organisation';
	$institutionForm='Company';
	$institutionUrlForm='http://www.dokeos.com';

	$languageForm='english';

	$checkEmailByHashSent=0;
	$ShowEmailnotcheckedToStudent=1;
	$userMailCanBeEmpty=1;
	$allowSelfReg=1;
	$allowSelfRegProf=1;
	$enableTrackingForm=1;
	$singleDbForm=1;
	$encryptPassForm='md5';
	$session_lifetime=360000;
}
else
{
	foreach($_POST as $key=>$val)
	{
		$magic_quotes_gpc=ini_get('magic_quotes_gpc')?true:false;

		if(is_string($val))
		{
			if($magic_quotes_gpc)
			{
				$val=stripslashes($val);
			}

			$val=trim($val);

			$_POST[$key]=$val;
		}
		elseif(is_array($val))
		{
			foreach($val as $key2=>$val2)
			{
				if($magic_quotes_gpc)
				{
					$val2=stripslashes($val2);
				}

				$val2=trim($val2);

				$_POST[$key][$key2]=$val2;
			}
		}

		$GLOBALS[$key]=$_POST[$key];
	}
}

// The Steps
$total_steps=7;
if (!$_POST)
{
	$current_step=1;
}
elseif (!empty($_POST['language_list']) or !empty($_POST['step1']) or ((!empty($_POST['step2_update_8']) or (!empty($_POST['step2_update_6'])) or (!empty($_POST['step2_update_20'])))  && ($emptyUpdatePath or $badUpdatePath)))
{
	$current_step=2;
}
elseif (!empty($_POST['step2']) or (!empty($_POST['step2_update_8']) or (!empty($_POST['step2_update_6'])) or (!empty($_POST['step2_update_20']))))
{
	$current_step=3;
}
elseif (!empty($_POST['step3']))
{
	$current_step=4;
}
elseif (!empty($_POST['step4']))
{
	$current_step=5;
}
elseif (!empty($_POST['step5']))
{
	$current_step=6;
}


// Managing the $encryptPassForm
if ($encryptPassForm=='1' ) {
	$encryptPassForm = 'md5';
} elseif ($encryptPassForm=='0') {
	$encryptPassForm = 'none';
}

?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>&mdash; <?php echo get_lang('DokeosInstallation').' &mdash; '.get_lang('Version_').' '.$new_version; ?></title>

	<style type="text/css" media="screen, projection"> 
	/*<![CDATA[*/
	@import "../css/dokeos2_black_tablet/default.css";
	/*]]>*/
	</style> 
	<style type="text/css" media="print"> 
	/*<![CDATA[*/
	@import "../css/dokeos2_black_tablet/print.css";
	/*]]>*/
	</style> 	
	<script type="text/javascript" src="../inc/lib/javascript/jquery-1.4.2.min.js" language="javascript"></script>
	<script type="text/javascript" >
		  function check_mysql_connection()
		  {

			  try {
				  if ($("#singleDbForm_id").val() == 0) {
                                        get_database_mode = 1; // upgrade simple database
				  } else {
                                        get_database_mode = 2; // upgrade multiple database
				  }
			  } catch (e) {}
                                      get_database_mode = 2; // Default is multiple database, we need more checks before final release here
                                      if ($('#singleDb1').is(":checked")==false) {
                                            get_database_mode = 2;
                                      } else if($('#singleDb1').is(":checked")==true){
                                            get_database_mode = 1;
			              }
                                        $.post("mysql-check.php", {
		  				connection_test : true,
						database_host	: $("#dbHostForm").val(),
						database_user	: $("#dbUsernameForm").val(),
						database_pass	: $("#dbPassForm").val(),
						database_prefix : $("#dbPrefixForm").val(),
                                                database_mode   : get_database_mode,
                                                main_database   : $("#dbNameForm").val()
		  			},
		  			function (data) {
						// display the feedback data of the mysql check
		  				$("#connection_feedback").html(data);

						// if the feedback message contains a div with class confirmation-message then we may continue
						// else the next button needs to be disabled
						if (data.indexOf('class="warning-message-install"') > 1){
							$("#submitbuttonstep4").removeAttr('disabled');
						} else {
							$("#submitbuttonstep4").attr('disabled','disabled');
						}
		  			}
		  		);
		  }
		$(document).ready( function() {
          
			 //checked
			if ($('#singleDb1').attr('checked')==false) {
					$('#dbStatsForm').removeAttr('disabled');
					$('#dbUserForm').removeAttr('disabled');
					$('#dbStatsForm').attr('value','dokeos_stats');
					$('#dbUserForm').attr('value','dokeos_user');
			} else if($('#singleDb1').attr('checked')==true){
					$('#dbStatsForm').attr('disabled','disabled');
					$('#dbUserForm').attr('disabled','disabled');
					$('#dbStatsForm').attr('value','dokeos_main');
					$('#dbUserForm').attr('value','dokeos_main');
			}
			//Allow dokeos install in IE
			$("button").click(function() {
				$("#is_executable").attr("value",$(this).attr("name"));
			});

			// show the header
			$("#header1, #header2").show();

			// Expand or collapse the help
			$('#help-link').click(function () {
				$('#help-content').slideToggle('fast', function() {
					if ( $(this).hasClass('help-open') ) {
						$('#help a').css({'backgroundImage':'url("<?php echo api_get_path(WEB_PATH); ?>main/img/screen-options-right.gif")'});
						$(this).removeClass('contextual-help-open');
					} else {
						$('#help a').css({'backgroundImage':'url("<?php echo api_get_path(WEB_PATH); ?>main/img/screen-options-right-up.gif")'});
						$(this).addClass('help-open');
					}
				});
				return false;
			});
	 	} );


		function show_hide_tracking_and_user_db (my_option) {
			if (my_option=='singleDb1') {
				$('#dbStatsForm').attr('disabled','true');
				$('#dbUserForm').attr('disabled','true');
				$('#dbStatsForm').attr('value','dokeos_main');
				$('#dbUserForm').attr('value','dokeos_main');
                                $('#optional_param2').hide();
                                $('#optional_param4').hide();
			} else if (my_option=='singleDb0') {
				$('#dbStatsForm').removeAttr('disabled');
				$('#dbUserForm').removeAttr('disabled');
				$('#dbStatsForm').attr('value','dokeos_stats');
				$('#dbUserForm').attr('value','dokeos_user');
                                $('#optional_param2').show();
                                $('#optional_param4').show();
			}
		}


		init_visibility=0;
		function show_hide_option() {
			if(init_visibility == 0) {
				document.getElementById('optional_param1').style.display = '';
				document.getElementById('optional_param2').style.display = '';
				if(document.getElementById('optional_param3'))
				{
					document.getElementById('optional_param3').style.display = '';
				}
				document.getElementById('optional_param4').style.display = '';
				document.getElementById('optional_param5').style.display = '';
				document.getElementById('optional_param6').style.display = '';
                                
                                <?php if ($singleDbForm == 1): ?>
                                    document.getElementById('optional_param2').style.display = 'none';
                                    document.getElementById('optional_param4').style.display = 'none';
                                <?php endif; ?>
				init_visibility = 1;
			document.getElementById('optionalparameters').innerHTML='<img style="vertical-align:middle;" src="../img/div_hide.gif" alt="" /> <?php echo get_lang('OptionalParameters'); ?>';
			} else {
				document.getElementById('optional_param1').style.display = 'none';
				document.getElementById('optional_param2').style.display = 'none';
				if(document.getElementById('optional_param3')) {
					document.getElementById('optional_param3').style.display = 'none';
				}
				document.getElementById('optional_param4').style.display = 'none';
				document.getElementById('optional_param5').style.display = 'none';
				document.getElementById('optional_param6').style.display = 'none';
			document.getElementById('optionalparameters').innerHTML='<img style="vertical-align:middle;" src="../img/div_show.gif" alt="" /> <?php echo get_lang('OptionalParameters'); ?>';
				init_visibility = 0;
			}
		}
	</script>

<?php if(!empty($charset)){ ?>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset ?>" />
<?php } ?>
</head>
<body dir="<?php echo $text_dir ?>">



<div id="header">
	<div id="header1">
			<div class="headerinner">
				<div id="top_corner"></div> 
				<div id="languageselector">
				</div>
				<div id="institution">
					<?php echo get_lang('DokeosInstallation').' &mdash; '.get_lang('Version_').' '.$new_version; ?><?php if($installType == 'new') echo ' &ndash; '.get_lang('NewInstallation'); else if($installType == 'update') echo ' &ndash; '.get_lang('UpdateFromDokeosVersion').(is_array($update_from_version)?implode('|',$update_from_version):''); ?>
				</div>
			</div>
	</div>
	<div id="header2">
		<div class="headerinner">
			<ul id="dokeostabs">
                            <li class="install" id="current"><a href="/main/install/index.php">Installation</a></li>
			    <li class="guide_na"> <a href="../../documentation/installation_guide.php" target="_blank">Installation guide</a></span></li>
			</ul>
			<div style="clear: both;" class="clear"> </div>
		</div>
	</div>

</div>
    
<div id="wrapper">
<div id="main">
	<div id="content" class="maxcontent">
		<div id="content_with_menu" style="z-index:10;">
		<form style="padding: 0px; margin: 0px;" method="post" action="<?php echo api_get_self(); ?>?running=1&amp;installType=<?php echo $installType; ?>&amp;updateFromConfigFile=<?php echo urlencode($updateFromConfigFile); ?>">
<table cellpadding="6" cellspacing="0" border="0" width="90%" align="center" style="padding-left: 20px;">
<tr>
  <td>
	<input type="hidden" name="updatePath"           value="<?php if(!$badUpdatePath) echo api_htmlentities($proposedUpdatePath, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="urlAppendPath"        value="<?php echo api_htmlentities($urlAppendPath, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="pathForm"             value="<?php echo api_htmlentities($pathForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="urlForm"              value="<?php echo api_htmlentities($urlForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="dbHostForm"           value="<?php echo api_htmlentities($dbHostForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="dbUsernameForm"       value="<?php echo api_htmlentities($dbUsernameForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="dbPassForm"           value="<?php echo api_htmlentities($dbPassForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" id="singleDbForm_id" name="singleDbForm"         value="<?php echo api_htmlentities($singleDbForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="dbPrefixForm"         value="<?php echo api_htmlentities($dbPrefixForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="dbNameForm"           value="<?php echo api_htmlentities($dbNameForm, ENT_QUOTES, $charset); ?>" />
<?php
	if($installType == 'update' OR $singleDbForm == 0)
	{
?>
	<input type="hidden" name="dbStatsForm"          value="<?php echo api_htmlentities($dbStatsForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="dbScormForm"          value="<?php echo api_htmlentities($dbScormForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="dbUserForm"           value="<?php echo api_htmlentities($dbUserForm, ENT_QUOTES, $charset); ?>" />
<?php
	}
	else
	{
?>
	<input type="hidden" name="dbStatsForm"          value="<?php echo api_htmlentities($dbNameForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="dbUserForm"           value="<?php echo api_htmlentities($dbNameForm, ENT_QUOTES, $charset); ?>" />
<?php
	}
?>
	<input type="hidden" name="enableTrackingForm"   value="<?php echo api_htmlentities($enableTrackingForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="allowSelfReg"         value="<?php echo api_htmlentities($allowSelfReg, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="allowSelfRegProf"     value="<?php echo api_htmlentities($allowSelfRegProf, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="emailForm"            value="<?php echo api_htmlentities($emailForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="adminLastName"        value="<?php echo api_htmlentities($adminLastName, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="adminFirstName"       value="<?php echo api_htmlentities($adminFirstName, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="adminPhoneForm"       value="<?php echo api_htmlentities($adminPhoneForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="loginForm"            value="<?php echo api_htmlentities($loginForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="passForm"             value="<?php echo api_htmlentities($passForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="languageForm"         value="<?php echo api_htmlentities($languageForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="campusForm"           value="<?php echo api_htmlentities($campusForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="educationForm"        value="<?php echo api_htmlentities($educationForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="institutionForm"      value="<?php echo api_htmlentities($institutionForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="institutionUrlForm"   value="<?php echo api_stristr($institutionUrlForm, 'http://', false, $charset) ? api_htmlentities($institutionUrlForm, ENT_QUOTES, $charset) : api_stristr($institutionUrlForm, 'https://', false, $charset) ? api_htmlentities($institutionUrlForm, ENT_QUOTES, $charset) : 'http://'.api_htmlentities($institutionUrlForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="checkEmailByHashSent" value="<?php echo api_htmlentities($checkEmailByHashSent, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="ShowEmailnotcheckedToStudent" value="<?php echo api_htmlentities($ShowEmailnotcheckedToStudent, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="userMailCanBeEmpty"   value="<?php echo api_htmlentities($userMailCanBeEmpty, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="encryptPassForm"      value="<?php echo api_htmlentities($encryptPassForm, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="session_lifetime"  value="<?php echo api_htmlentities($session_lifetime, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="old_version"  value="<?php echo api_htmlentities($my_old_version, ENT_QUOTES, $charset); ?>" />
	<input type="hidden" name="new_version"  value="<?php echo api_htmlentities($new_version, ENT_QUOTES, $charset); ?>" />




<?php
if($_POST['step2'])
{
	//STEP 3 : LICENSE
	display_license_agreement();
}
elseif($_POST['step3'])
{
	//STEP 4 : MYSQL DATABASE SETTINGS
	display_database_settings_form($installType, $dbHostForm, $dbUsernameForm, $dbPassForm, $dbPrefixForm, $enableTrackingForm, $singleDbForm, $dbNameForm, $dbStatsForm, $dbScormForm, $dbUserForm);
}
elseif($_POST['step4'])
{
// Create logs directory
if ( ! is_dir('../install/logs') ) {
  if (! mkdir('../install/logs') ){ 
    die("Can't create log directory. main/install must be writable.");  
  }
}

	//STEP 5 : CONFIGURATION SETTINGS
	//if update, try getting settings from the database...
	if($installType == 'update')
	{
		$db_name = $dbNameForm;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'platformLanguage');
		if(!empty($tmp)) $languageForm = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'emailAdministrator');
		if(!empty($tmp)) $emailForm = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'administratorName');
		if(!empty($tmp)) $adminFirstName = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'administratorSurname');
		if(!empty($tmp)) $adminLastName = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'administratorTelephone');
		if(!empty($tmp)) $adminPhoneForm = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'siteName');
		if(!empty($tmp)) $campusForm = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'Institution');
		if(!empty($tmp)) $institutionForm = $tmp;
		$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'InstitutionUrl');
		if(!empty($tmp)) $institutionUrlForm = $tmp;
		if(in_array($my_old_version,$update_from_version_6))
		{   //for version 1.6
			$urlForm = get_config_param('rootWeb');
			$encryptPassForm = get_config_param('userPasswordCrypted');
			// Managing the $encryptPassForm
			if ($encryptPassForm=='1' ) {
				$encryptPassForm = 'md5';
			} elseif ($encryptPassForm=='0') {
				$encryptPassForm = 'none';
			}

			$allowSelfReg = get_config_param('allowSelfReg');
			$allowSelfRegProf = get_config_param('allowSelfRegProf');
		}
		else
		{   //for version 1.8
			$urlForm = $_configuration['root_web'];
			$encryptPassForm = get_config_param('userPasswordCrypted');
			// Managing the $encryptPassForm
			if ($encryptPassForm=='1' ) {
				$encryptPassForm = 'md5';
			} elseif ($encryptPassForm=='0') {
				$encryptPassForm = 'none';
			}

			$allowSelfReg = false;
			$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'allow_registration');
			if(!empty($tmp)) $allowSelfReg = $tmp;
			$allowSelfRegProf = false;
			$tmp = get_config_param_from_db($dbHostForm,$dbUsernameForm,$dbPassForm,$db_name,'allow_registration_as_teacher');
			if(!empty($tmp)) $allowSelfRegProf = $tmp;
		}
	}
	display_configuration_settings_form($installType, $urlForm, $languageForm, $emailForm, $adminFirstName, $adminLastName, $adminPhoneForm, $campusForm, $institutionForm, $institutionUrlForm, $encryptPassForm, $allowSelfReg, $allowSelfRegProf, $loginForm, $passForm);
}
elseif($_POST['step5'])
{
	//STEP 6 : LAST CHECK BEFORE INSTALL
?>

	<h2 class="warning-title"><?php echo display_step_sequence().get_lang('LastCheck'); ?></h2>

	<?php echo get_lang('HereAreTheValuesYouEntered');?>
	<br />
	<b><?php echo get_lang('PrintThisPageToRememberPassAndOthers');?></b>


	<?php echo get_lang('MainLang').' : '.$languageForm; ?><br /><br />

	<?php echo get_lang('DBHost').' : '.$dbHostForm; ?><br />
	<?php echo get_lang('DBLogin').' : '.$dbUsernameForm; ?><br />
	<?php echo get_lang('DBPassword').' : '.str_repeat('*',strlen($dbPassForm)); ?><br />
	<?php if(!empty($dbPrefixForm)) echo get_lang('DbPrefixForm').' : '.$dbPrefixForm.'<br />'; ?>
	<?php echo get_lang('MainDB').' : <b>'.$dbNameForm; ?></b><?php if($installType == 'new') echo ' ('.get_lang('ReadWarningBelow').')'; ?><br />
	<?php
	if(!$singleDbForm)
	{
		echo get_lang('StatDB').' : <b>'.$dbStatsForm.'</b>';
		if($installType == 'new')
		{
			echo ' ('.get_lang('ReadWarningBelow').')';
		}
		echo '<br />';

		echo get_lang('UserDB').' : <b>'.$dbUserForm.'</b>';
		if($installType == 'new')
		{
			echo ' ('.get_lang('ReadWarningBelow').')';
		}
		echo '<br />';
	}
	?>
	<?php echo get_lang('EnableTracking').' : '.($enableTrackingForm?get_lang('Yes'):get_lang('No')); ?><br />
	<?php echo get_lang('SingleDb').' : '.($singleDbForm?get_lang('One'):get_lang('Several')); ?><br /><br />

	<?php echo get_lang('AllowSelfReg').' : '.($allowSelfReg?get_lang('Yes'):get_lang('No')); ?><br />
	<?php echo get_lang('EncryptMethodUserPass').' : ';
  	echo $encryptPassForm;
	?><br /><br/>

	<?php echo get_lang('AdminEmail').' : '.$emailForm; ?><br />
	<?php
	if (api_is_western_name_order()) {
		echo get_lang('AdminFirstName').' : '.$adminFirstName, '<br />', get_lang('AdminLastName').' : '.$adminLastName, '<br />';
	} else {
		echo get_lang('AdminLastName').' : '.$adminLastName, '<br />', get_lang('AdminFirstName').' : '.$adminFirstName, '<br />';
	}
	?>
	<?php echo get_lang('AdminPhone').' : '.$adminPhoneForm; ?><br />

	<?php if($installType == 'new'): ?>
	<?php echo get_lang('AdminLogin').' : <b>'.$loginForm; ?></b><br />
	<?php echo get_lang('AdminPass').' : <b>'.$passForm; ?></b><br /><br />
	<?php else: ?>
	<br />
	<?php endif; ?>

	<?php echo get_lang('CampusName').' : '.$campusForm; ?><br />
	<?php echo get_lang('InstituteShortName').' : '.$institutionForm; ?><br />
	<?php echo get_lang('InstituteURL').' : '.$institutionUrlForm; ?><br />
	<?php echo get_lang('DokeosURL').' : '.$urlForm; ?><br />



	<?php if($installType == 'new'){?>
	<div class="warning-message-install">
	<p align="left"><b>
	<?php echo get_lang('Warning');?> !<br />
	<?php echo get_lang('TheInstallScriptWillEraseAllTables');?>
	</b></p>
	</div>
	<?php } ?>

	<table width="100%">
	<tr>
	  <td><button type="submit" class="new-previous-link" name="step4" value="&lt; <?php echo get_lang('Previous'); ?>" />&nbsp;&nbsp;<?php echo get_lang('Previous'); ?></button></td>
	  <td align="right"><input type="hidden" name="is_executable" id="is_executable" value="-" /><button class="save" type="submit" name="step6" value="<?php echo get_lang('InstallDokeos'); ?> &gt;" onclick="javascript:if(this.value == '<?php $msg = get_lang('PleaseWait');?>...') return false; else this.value='<?php $msg = get_lang('InstallDokeos');?>...';" ><?php echo $msg; ?></button></td>
	</tr>
	</table>

<?php
}
elseif($_POST['step6'])
{       
	//STEP 6 : INSTALLATION PROCESS
	if($installType == 'update')
	{
		if(empty($my_old_version)){$my_old_version='1.8.6';} //we guess
		$_configuration['main_database'] = $dbNameForm;
		//$urlAppendPath = get_config_param('urlAppend');
        error_log('Starting migration process from '.$my_old_version.' ('.time().')',0);

    	if ($userPasswordCrypted=='1' ) {
			$userPasswordCrypted = 'md5';
		} elseif ($userPasswordCrypted=='0') {
			$userPasswordCrypted = 'none';
		}

		switch ($my_old_version) {
                    case '1.6':
                    case '1.6.0':
                    case '1.6.1':
                    case '1.6.2':
                    case '1.6.3':
                    case '1.6.4':
                    case '1.6.5':
                        include('update-db-1.6.x-1.8.0.inc.php');
                        include('update-files-1.6.x-1.8.0.inc.php');
                        //intentionally no break to continue processing
                    case '1.8':
                    case '1.8.0':
                        include('update-db-1.8.0-1.8.2.inc.php');
                        //intentionally no break to continue processing
                    case '1.8.2':
                        include('update-db-1.8.2-1.8.3.inc.php');
                        //intentionally no break to continue processing
                    case '1.8.3':
                        include('update-db-1.8.3-1.8.4.inc.php');
                        include('update-files-1.8.3-1.8.4.inc.php');
                    case '1.8.4':
                        include('update-db-1.8.4-1.8.5.inc.php');
                        include('update-files-1.8.4-1.8.5.inc.php');
                    case '1.8.5':
                        include('update-db-1.8.5-1.8.6.inc.php');
                        include('update-files-1.8.5-1.8.6.inc.php');
                    case '1.8.6':
                        include('update-db-1.8.6-1.8.6.1.inc.php');
                        include('update-files-1.8.6-1.8.6.1.inc.php');
                    case '1.8.6.1':
                        include('update-db-1.8.6.1-2.0.inc.php');
                        include('update-files-1.8.6.1-2.0.inc.php');
                    case '2.0':
                        include('update-db-2.0-2.1.inc.php');
                        include('update-files-2.0-2.1.inc.php');
                    case '2.1':
                        include('update-db-2.1-2.2.inc.php');
                        include('update-files-2.1-2.2.inc.php');
                    case '2.2':
                        Database::query("SET storage_engine = MYISAM;");
                        Database::query("SET SESSION character_set_server='utf8';");
                        Database::query("SET SESSION collation_server='utf8_general_ci';");
                        Database::query("SET NAMES 'utf8';");
                        include('update-db-2.2-3.0.inc.php');
                        include('update-files-2.2-3.0.inc.php');
                    default:
				break;
		}
	} else {
                Database::query("SET storage_engine = MYISAM;");
                Database::query("SET SESSION character_set_server='utf8';");
                Database::query("SET SESSION collation_server='utf8_general_ci';");
                Database::query("SET NAMES 'utf8';");
		include('install_db.inc.php');
		include('install_files.inc.php');
	}

	display_after_install_message($installType, $nbr_courses);
        //include_once api_get_path(SYS_PATH).'PRO/makeitpro.php';
} elseif($_POST['step1'] || $badUpdatePath) {
	//STEP 1 : REQUIREMENTS
	//make sure that proposed path is set, shouldn't be necessary but...
	if(empty($proposedUpdatePath)){$proposedUpdatePath = $_POST['updatePath'];}
	display_requirements($installType, $badUpdatePath, $proposedUpdatePath, $update_from_version_8, $update_from_version_6, $update_from_version_20);
} else {
	//start screen
	display_language_selection();
}
?>
  </td>
</tr>
</table>
</form>
		</div>

	<div id="menu" class="menu">
	<div id="installation_steps" style="width:190px; z-index:1;" >
		<a href="http://www.dokeos.com"><img src="../img/dokeos-logo-grey.png" hspace="10" vspace="10" alt="Dokeos logo" /></a>
		<ol>
			<li <?php step_active('1'); ?>><?php echo get_lang('InstallationLanguage'); ?></li>
			<li <?php step_active('2'); ?>><?php echo get_lang('Requirements'); ?></li>
			<li <?php step_active('3'); ?>><?php echo get_lang('Licence'); ?></li>
			<li <?php step_active('4'); ?>><?php echo get_lang('DBSetting'); ?></li>
			<li <?php step_active('5'); ?>><?php echo get_lang('CfgSetting'); ?></li>
			<li <?php step_active('6'); ?>><?php echo get_lang('PrintOverview'); ?></li>
			<li <?php step_active('7'); ?>><?php echo get_lang('Installing'); ?></li>
		</ol>
	</div>
</div>
</div>

<br style="clear:both;" />

<div class="push"></div>
	</div>
</div><!-- wrapper end-->
<?php echo '<style>
    
    #footer{
        display:none;
    } 
    
    #wrapper:after{
        margin:0px !important;
    }
    
    h1{border-bottom: 1px solid #CCCCCC;
        font-size: 21px;
        margin-bottom: 15px;
        margin-top: 15px;
        padding-bottom: 5px;
    }
    
    .RequirementContent {
        margin-top:10px;
    }
    
    .warning-message-install {
        padding:10px;
    }
    .warning-message {
        padding:10px;
    }
</style>';?>
<div class="sticky-footer">
<div id="footer">
	<div class="copyright"><?php echo get_lang('Platform');?> <a href="http://www.dokeos.com" target="_blank"> Dokeos <?php echo $new_version ?></a> &copy; <?php echo date('Y'); ?> </div>
	&nbsp;
</div>
</div>
</body>
</html>
