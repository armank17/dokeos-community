<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.document
==============================================================================
*/

// Language files that should be included
$language_file = array('document');

// including the global Dokeos file
require '../inc/global.inc.php';

require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'course.lib.php';

// Database table definitions
$lp_table = Database::get_course_table(TABLE_LP_MAIN);
$lp_view_table = Database::get_course_table(TABLE_LP_VIEW);
$lp_item_view_table = Database :: get_course_table(TABLE_LP_ITEM_VIEW);
$tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);
$table_users = Database::get_main_table(TABLE_MAIN_USER);


// setting the breadcrumbs
$interbreadcrumb[] = array ("url" => Security::remove_XSS('document.php?curdirpath='.$pathurl), "name" => get_lang('Documents'));

$htmlHeadXtra[] =
"<script type=\"text/javascript\">
function confirmation (name) {
	if (confirm(\" ". get_lang("AreYouSureToDelete") ." \"+ name + \" ?\"))
		{return true;}
	else
		{return false;}
}

</script>";


// Displaying the header
Display :: display_tool_header(get_lang('Documents'));

$course_code = explode("=",api_get_cidReq());

?>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->

</script>


<?php
echo '<script type="text/javascript">
function callPrint()
{
	document.getElementById("print_header").style.display = "none";
	document.getElementById("print_footer").style.display = "none";
	window.print();
}

function call()
{
	if(document.getElementById("test").style.display == "none")
	{
	document.getElementById("test").style.display = "";
	}
	else
	{
	document.getElementById("test").style.display = "none";
	}
}
</script>';


echo '<div class="actions" id="print_header" style="display:;">';
echo '<a href="lp_controller.php?'.api_get_cidReq().'">'.Display::return_icon('pixel.gif', get_lang('Author'), array('class' => 'toolactionplaceholdericon toolactionback')).' '.get_lang('Author').'</a>';
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'getcertificate')
{
echo '<a href="#" onclick="callPrint()">'.Display::return_icon('pixel.gif',get_lang('Print'),array('class'=>'toolactionplaceholdericon toolactionprint32')).' '.get_lang('Print').'</a>';
}
echo '</div>';

$form = new FormValidator('create_certificate','post',api_get_self().'?'.api_get_cidReq().'&amp;action=getcertificate');
$renderer = & $form->defaultRenderer();

if( $form->validate()) {
		$certificate = $form->exportValues();
		$user_id = $_POST['UserList'];
		$portal = $_POST['portal'];
		$portal_logo = $_FILES['portal_logo']['name'];
		$organisation = $_POST['organisation'];
		$organisation_logo = $_FILES['organisation_logo']['name'];
		$certificate_date = $_POST['date'];
		$certificate_msg = $_POST['text'];
		$template = $_POST['picktemplate'];
		$cert = $_FILES['logo']['name'];
		$courseDir   = $_course['path']."/document";
  		$sys_course_path = api_get_path(SYS_COURSE_PATH);
	    $base_work_dir = $sys_course_path.$courseDir;
	    $temp_path = $sys_course_path.$_course['path'].'/temp/';
		$cert_template = $_POST['pick_template']['template'];
		$get_certificate = $_POST['get_certificate']['certificate'];
		$generate_certificate = $_POST['generate_certificate']['htmlpdf'];

		$sql = "SELECT firstname,lastname,username FROM $table_users WHERE user_id = ".$user_id;
		$result = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($result))
		{
			$firstname = $row['firstname'];
			$lastname = $row['lastname'];
			$username = $row['username'];
		}

		if($cert_template == 1)
		{
			$cert_template = "frames1_s.png";
		}
		elseif($cert_template == 2)
		{
			$cert_template = "frames2_s.png";
		}
		elseif($cert_template == 3)
		{
			$cert_template = "frames3_s.png";
		}

		if(move_uploaded_file($_FILES['logo']['tmp_name'], $temp_path.$_FILES['logo']['name'])) {
			//echo 'image uploaded';
		}
		if(!empty($portal_logo))
		{
			move_uploaded_file($_FILES['portal_logo']['tmp_name'], $temp_path.$_FILES['portal_logo']['name']);
		}
		if(!empty($organisation_logo))
		{
			move_uploaded_file($_FILES['organisation_logo']['tmp_name'], $temp_path.$_FILES['organisation_logo']['name']);
		}

		$lp_passed = 'Y';
		if($get_certificate == 1)
		{
			$sql = "SELECT sum(lp_item.max_score) AS max_score,sum(lp_item_view.score) AS score FROM $tbl_lp_item lp_item, $lp_item_view_table lp_item_view, $lp_table lp, $lp_view_table lp_view WHERE lp.id = lp_item.lp_id AND lp_item.id = lp_item_view.lp_item_id AND lp.id = lp_view.lp_id AND lp_view.id = lp_item_view.lp_view_id AND lp_item.item_type = 'quiz' AND lp_view.user_id = ".$user_id." GROUP BY lp_item.item_type";
			$result = Database::query($sql, __FILE__, __LINE__);
			if(Database::num_rows($result) > 0)
			{
				while($row = Database::fetch_array($result))
				{
					$max_score = $row['max_score'];
					$score = $row['score'];
					$score_perc = $score/$max_score * 100;
				}
			}
			if($score_perc >= 50)
			{
				$user_passed = 'Y';
			}
			else
			{
				$user_passed = 'N';
			}
		}
		else
		{
			$sql = "SELECT lp_view.progress FROM $lp_table lp, $lp_view_table lp_view WHERE lp.id = lp_view.lp_id AND lp_view.user_id = ".$user_id;
			$result = Database::query($sql, __FILE__, __LINE__);
			if(Database::num_rows($result) > 0)
			{
				while($row = Database::fetch_array($result))
				{
					$lp_progress = $row['progress'];
					if($lp_progress > 50 && $lp_passed == 'Y')
					{
						$user_passed = 'Y';
					}
					else
					{
						$lp_passed = 'N';
						$user_passed = 'N';
					}
				}
			}
		}
		if($user_passed == 'Y')
	    {
		$s = "<div id='certcontent' style='background-image:url(../img/".$cert_template.");no-repeat;'><table width='100%' height='100%' border='0' style='padding:30px;'><tr height='50'><td style='padding:10px'>";
		if(!empty($portal_logo))
		{
			$s .= "<img src='../../courses/".$course_code[1]."/temp/".$portal_logo."'>";
		}
		else
		{
			$s .= '&nbsp;';
		}
		$s .= "</td><td style='font-size:25px;font-weight:bold;'>&nbsp;&nbsp;".$portal."</td></tr><tr height='50'><td style='padding:10px'>";
		if(!empty($organisation_logo))
		{
			$s .= "<img src='../../courses/".$course_code[1]."/temp/".$organisation_logo."'>";
		}
		else
		{
			$s .= '&nbsp;';
		}
		$s .= "</td><td style='padding-left:20px;font-size:25px;font-weight:bold;'>".$organisation."</td></tr><tr height='100'><td align='center' colspan='2'><h2>".$certificate_msg."</h2></td></tr><tr height='150'><td align='center' valign='top'><h2>".$firstname.' '.$lastname."</h2></td><td align='right'><img width='130' height='130' src='../../courses/".$course_code[1]."/temp/".$cert."'></td></tr></table></div>";
		}
		else
		{
		$s = "<div id='content'><h2>".get_lang('UserFailed')."</h2></div>";
		}
		echo $s;

		if($generate_certificate == 1)
		{
			$head = '<html><head><meta http-equiv="Content-Type" content="text/html; charset='.api_get_system_encoding().'" /><link rel="stylesheet" href="./css/frames.css" type="text/css" /><style type="text/css">
			#certcontent {
			height:535px;			/* read by IE6 as min-height */
			width: 950px;
			zoom:1;
			}
			</style>
			</head><body>';
			if ($fp = @ fopen($temp_path.$username.'.html', 'w')) {
				fputs($fp, $head);
				fputs($fp, $s);
				fputs($fp, '</body></html>');
				fclose($fp);
				$files_perm = api_get_setting('permissions_for_new_files');
				$files_perm = octdec(!empty($files_perm)?$files_perm:'0777');
				chmod($temp_path.$username.'.html',$files_perm);
			}
		}
}
else
{
// start the content div
echo '<div id="content">';

// Create a new form

$user_name = array();
$a_course_users = CourseManager :: get_user_list_from_course_code($_SESSION['_course']['id'], true, $_SESSION['id_session']);
foreach ($a_course_users as $user_id => $o_course_user) {
	$user_name[$user_id] = $o_course_user['firstname'].'&nbsp;&nbsp;'.$o_course_user['lastname'];
}
$form->addElement('select', 'UserList', get_lang('User'),$user_name,'class="focus" id="UserList" style="width:300px;"');
//$form->addElement('text','firstname',get_lang('FirstName'),'class="focus" id="firstname" style="width:300px;"');
//$form->addElement('text','lastname',get_lang('LastName'),'class="focus" id="lastname" style="width:300px;"');
$form->addElement('text','portal',get_lang('Portal'),'class="focus" id="portal" style="width:300px;"');
$form->addElement('file', 'portal_logo', get_lang('PortalLogo'));
$renderer->setElementTemplate('<div class="row"><div class="label">{label}</div><div class="formw">{element} '.get_lang('LogoSize').'</div></div>', 'portal_logo');
$form->addElement('text','organisation',get_lang('Organisation'),'class="focus" id="organisation" style="width:300px;"');
$form->addElement('file', 'organisation_logo', get_lang('OrganisationLogo'));
$renderer->setElementTemplate('<div class="row"><div class="label">{label}</div><div class="formw">{element} '.get_lang('LogoSize').'</div></div>', 'organisation_logo');
$form->addElement('datepicker', 'date', get_lang ('Date'), array ('form_name' => 'create_certificate', 'id' => 'date') );
$form->add_html_editor('text', get_lang('Message'), false, false, array('ToolbarSet' => 'Announcements', 'Width' => '80%', 'Height' => '150'));
$form->addElement('file', 'logo', get_lang('AddLogo'));
$renderer->setElementTemplate('<div class="row"><div class="label">{label}</div><div class="formw">{element} '.get_lang('SealSize').'</div></div>', 'logo');
$allowed_picture_types = array ('jpg', 'jpeg', 'png', 'gif');

$form->addRule('logo', get_lang('OnlyImagesAllowed').' ('.implode(',', $allowed_picture_types).')', 'filetype', $allowed_picture_types);

//$form->addElement('text','picktemplate',get_lang('PickTemplate'),'class="focus" id="picktemplate" style="width:300px;"');
$form->addElement('html','<div class="row"><div class="label"></div><div class="formw"><a href="#" onclick="call()">Show/Hide Templates</a></div></div>');
$form->addElement('html','<div class="row" id="test" style="display:none"><div class="formw"><table border="0" width="100%" cellpadding="10"><tr><td><div class="quiz_content_actions" align="center"><a href=certificate.php?template=one><img src="../img/frames1_s.png" width="200" height="150"></a></div></td><td><div class="quiz_content_actions" align="center"><img src="../img/frames2_s.png" width="200" height="150"></div></td><td><div class="quiz_content_actions" align="center"><img src="../img/frames3_s.png" width="200" height="150"></div></td></tr></table></div></div>');
$group = array();
$group[] =& HTML_QuickForm::createElement('radio', 'template', null, get_lang('Template1'), 1);
$group[] =& HTML_QuickForm::createElement('radio', 'template', null, get_lang('Template2'), 2);
$group[] =& HTML_QuickForm::createElement('radio', 'template', null, get_lang('Template3'), 3);
$form->addGroup($group, 'pick_template', get_lang('PickTemplate'), '&nbsp;');

$group = array();
$group[] =& HTML_QuickForm::createElement('radio', 'certificate', null, get_lang('Score'), 1);
$group[] =& HTML_QuickForm::createElement('radio', 'certificate', null, get_lang('Progress'), 2);
$form->addGroup($group, 'get_certificate', get_lang('Getcertificate'), '&nbsp;');

$group = array();
$group[] =& HTML_QuickForm::createElement('radio', 'htmlpdf', null, get_lang('Html'), 1);
$group[] =& HTML_QuickForm::createElement('radio', 'htmlpdf', null, get_lang('Pdf'), 2);
$form->addGroup($group, 'generate_certificate', get_lang('GenerateCertificate'), '&nbsp;');


$form->addElement('style_submit_button', 'submit', get_lang('CreateCertificate'), 'class="save" style="'.$margin_top.' margin-bottom:10px;"');

$current_hour = date ( 'H' );
$current_minutes = date ( 'i' );
$defaults['date'] = array ('d' => date ( 'd' ), 'F' => date ( 'm' ), 'Y' => date ( 'Y' ), 'H' => $current_hour, 'i' => $current_minutes );
$defaults['generate_certificate']['htmlpdf'] = 1;
$defaults['pick_template']['template'] = 1;
$defaults['get_certificate']['certificate'] = 1;

$form->setDefaults($defaults);
$form->display();

// close the content div
echo '</div>';
}

 // bottom actions bar
echo '<div class="actions" id="print_footer" style="display:;">';
echo '</div>';
// display footer
Display :: display_footer();
?>
