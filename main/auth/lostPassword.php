<?php
/* For licensing terms, see /dokeos_license.txt */
/**
==============================================================================
 * SCRIPT PURPOSE :
 *
 * This script allows users to retrieve the password of their profile(s)
 * on the basis of their e-mail address. The password is send via email
 * to the user.
 *
 * Special case : If the password are encrypted in the database, we have
 * to generate a new one.
*
*	@todo refactor, move relevant functions to code libraries
*
*	@package dokeos.auth
==============================================================================
*/
// name of the language file that needs to be included
$language_file = 'registration';

// resetting the course id
$cidReset=true;

require '../inc/global.inc.php';
require_once 'lost_password.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'mail.lib.inc.php';

// Load jquery library
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
//$htmlHeadXtra[] = '<script type="text/javascript">
//  $(document).ready(function (){
//     $("div.formw").attr("style","width: 88%;");
//  });
//</script>';
$tool_name = get_lang('LostPassword');
// the section (for the tabs)
$this_section = SECTION_CAMPUS;
Display :: display_header($tool_name);


$tool_name = get_lang('LostPass');

// Forbidden to retrieve the lost password
if (api_get_setting('allow_lostpassword') == 'false') {
	api_not_allowed();
}
?>

<style type="text/css">
    #lost_password .row{
        float: left;
        width: auto;
        clear: none;
        margin-left: 7px;
    }
    #lost_password .row div.label{
        float: left;
        width: auto;
    }
    #lost_password .row div.formw{
        float: left;
        width: 65%;
    }
    #lost_password .row div.formw button{
        margin-left: 30px;
        margin-top: -7px;
    }
</style>
<?php
echo '<div id="content">';

echo '<h3 class="orange">';
echo $tool_name;
echo '</h3>';

if (isset ($_GET['reset']) && isset ($_GET['id'])) {
	$reset_message =  reset_password($_GET["reset"], $_GET["id"], true);
        Display::display_confirmation_message($reset_message, true, true);
} else {

	$form = new FormValidator('lost_password');
	//$form->addElement('text', 'user', get_lang('User'), array('style'=>'width:370px'));
	$form->addElement('text', 'email', get_lang('Email'), array('style'=>'width:400px'));

	$form->applyFilter('email','strtolower');
	$form->addElement('style_submit_button', 'submit', get_lang('Send'),'class="save"');

	// setting the rules
	//$form->addRule('user', '<div class="required">'.get_lang('ThisFieldIsRequired'), 'required');

	if ($form->validate()) {
		$values = $form->exportValues();
		$user = $values['user'];
		$email = $values['email'];

		$condition = '';
		/*if (!empty($email)) {
			$condition = " AND LOWER(email) = '".Database::escape_string($email)."' ";
		}*/
                if (!empty($email)) {
			$condition = "LOWER(email) = '".Database::escape_string($email)."' ";
		}
		$tbl_user = Database :: get_main_table(TABLE_MAIN_USER);
		/*$query = " SELECT user_id AS uid, lastname AS lastName, firstname AS firstName,
					username AS loginName, password, email, status AS status,
					official_code, phone, picture_uri, creator_id
					FROM ".$tbl_user."
					WHERE ( username = '".Database::escape_string($user)."' $condition ) ";
*/
                $query = " SELECT user_id AS uid, lastname AS lastName, firstname AS firstName,
					username AS loginName, password, email, status AS status,
					official_code, phone, picture_uri, creator_id
					FROM ".$tbl_user."
					WHERE $condition";

		$result = Database::query($query, __FILE__, __LINE__);
		$num_rows = Database::num_rows($result);

		if ($result && $num_rows > 0) {
			if ($num_rows > 1) {
				$by_username = false; // more than one user
				while ($data = Database::fetch_array($result)) {
					$user[] = $data;
				}
			} else {
				$by_username = true; // single user (valid user + email)
				$user = Database::fetch_array($result);
			}
			if ($userPasswordCrypted != 'none') {
				handle_encrypted_password($user, $by_username);
			} else {
				send_password_to_user($user, $by_username);
			}
		} else {
			Display::display_confirmation_message(get_lang('NoUserAccountWithThisEmailAddress'), true, true);
		}

	} else {

		echo '<p>';
		echo '&nbsp;&nbsp;'.get_lang('EnterEmailUserAndWellSendYouPassword');
		echo '</p>';
  		echo '<div>';
		$form->display();
  		echo '</div>';
	}
}

echo '</div>';
/*
echo '<div class="actions">';
echo '&nbsp;';
echo '</div>';*/

Display :: display_footer();
