<?php
// including the widgets language file
$language_file = array ('widgets');

// the language file
$language_file = array ('courses', 'index');

// include the global Dokeos file
include_once('../../inc/global.inc.php');

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		user_get_information();
		break;
	case 'get_widget_content':
		user_get_content();
		break;
}
switch ($_GET['action']) {
	case 'get_widget_information':
		user_get_information();
		break;
	case 'get_widget_content':
		user_get_content();
		break;
	case 'get_widget_title':
		user_get_title();
		break;
}

/**
 * This function determines if the widget can be used inside a course, outside a course or both
 *
 * @return array
 * @version Dokeos 1.9
 * @since January 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function user_get_scope(){
	return array('platform');
}

/**
 * This function displays the content of the user widget
 *
 *
 *
 *
 */
function user_get_content(){
	// the user is not registered so we need to display the login form
	if (api_is_anonymous())
	{
		include_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
		$form = new FormValidator('formLogin');
		$form->addElement('text', 'login', get_lang('UserName'), array('size' => 17));
		$form->addElement('password', 'password', get_lang('Pass'), array('size' => 17));
		$form->addElement('style_submit_button','submitAuth', get_lang('langEnter'), array('class' => 'login'));
		$renderer =& $form->defaultRenderer();
		$renderer->setElementTemplate('<div><label>{label}&nbsp;</label></div><div>{element}</div>');
		$form->display();
		if (api_get_setting('openid_authentication') == 'true') {
			include_once 'main/auth/openid/login.php';
			echo '<div>'.openid_form().'</div>';
		}

		handle_login_failed();

		if (api_get_setting('allow_registration') <> 'false') {
			echo '<div><a href="main/auth/inscription.php">'.get_lang('Reg').'</a></div>';
		}
		if (api_get_setting('allow_lostpassword') == 'true') {
			echo "<div><a href=\"main/auth/lostPassword.php\">".get_lang("LostPassword")."</a></div>";
		}
	} else {
	// the user is registered and logged in so we display the profile picture name and email and a logout button
		global $_user;

		require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';

		$image_path = UserManager::get_user_picture_path_by_id(api_get_user_id(), 'web', false, true);
		$image_dir = $image_path['dir'];
		$image = $image_path['file'];
		$image_file = $image_dir.$image;
		$img_attributes = 'src="'.$image_file.'?rand='.time().'" '.'alt="'.api_get_person_name($user_data['firstname'], $user_data['lastname']).'" '.'style="margin-top:0px;padding:5px;" ';
		if ($image_size[0] > 300) {
			//limit display width to 300px
			$img_attributes .= 'width="300" ';
		}

		echo '<div style="text-align: center;">';
		echo '<div>';
		echo '<a href="'.api_get_path ( WEB_CODE_PATH ).'auth/profile.php">';
		if ($image == 'unknown.jpg') {
			echo '<img '.$img_attributes.' />';
		} else {
			echo '<input type="image" '.$img_attributes.'"/>';
		}
		echo '</a>';
		echo '</div>';
		echo '<div>'.$_user['firstName'].' '.$_user['lastName'].'</div>';
		echo '<div>'.$_user['firstName'].' '.$_user['lastName'].'</div>';
		echo '<div><a href="mailto:'.$_user['mail'].'">'.$_user['mail'].'</a></div>';
		echo '<div style="margin: 10px;"><a href="'.api_get_path ( WEB_PATH ).'index.php?logout=true" class="button buttonlogout">'.get_lang('Logout').'</a></div>';
		echo '</div>';


	}
}

function user_get_title($param1, $original_title=false) {
	$config_title = api_get_setting('user', 'title');
	if (!empty($config_title) AND $original_title==false){
		return $config_title;
	} else {
		return get_lang('User');
	}
}

function user_get_information(){
	echo get_lang('UserWidgetInformation');
}
function user_settings_form(){

}

/**
*	Reacts on a failed login:
*	displays an explanation with
*	a link to the registration form.
*
*	@version 1.0.1
*/
function handle_login_failed() {
	if (isset($_GET['error'])) {
		switch ($_GET['error']) {
			case '':
				$message = get_lang('InvalidId');
				if (api_is_self_registration_allowed()) {
					$message = get_lang('InvalidForSelfRegistration');
				}
				break;
			case 'account_expired':
				$message = get_lang('AccountExpired');
				break;
			case 'account_inactive':
				$message = get_lang('AccountInactive');
				break;
			case 'user_password_incorrect':
				$message = get_lang('InvalidId');
				break;
			case 'access_url_inactive':
				$message = get_lang('AccountURLInactive');
				break;
		}
		echo "<div id=\"login_fail\">".$message."</div>";
	}

}
?>
