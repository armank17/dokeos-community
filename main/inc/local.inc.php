<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.include
*/

/**
 *
 *                             SCRIPT PURPOSE
 *
 * This script initializes and manages Dokeos session information. It
 * keeps available session information up to date.
 *
 * You can request a course id. It will check if the course Id requested is the
 * same as the current one. If it isn't it will update session information from
 * the database. You can also force the course reset if you want ($cidReset).
 *
 * All the course information is stored in the $_course array.
 *
 * You can request a group id. The script will check if the group id requested is the
 * same as the current one. If it isn't it will update session information from
 * the database. You can also force the course reset if you want ($gidReset).
 *
 The course id is stored in $_cid session variable.
 * The group  id is stored in $_gid session variable.
 *
 *
 *                    VARIABLES AFFECTING THE SCRIPT BEHAVIOR
 *
 * string  $login
 * string  $password
 * boolean $logout
 *
 * string  $cidReq   : course id requested
 * boolean $cidReset : ask for a course Reset, if no $cidReq is provided in the
 *                     same time, all course informations is removed from the
 *                     current session
 *
 * int     $gidReq   : group Id requested
 * boolean $gidReset : ask for a group Reset, if no $gidReq is provided in the
 *                     same time, all group informations is removed from the
 *                     current session
 *
 *
                               SCRIPT STRUCTURE
*
* 1. The script determines if there is an authentication attempt. This part
* only chek if the login name and password are valid. Afterwards, it set the
* $_user['user_id'] (user id) and the $uidReset flag. Other user informations are retrieved
* later. It's also in this section that optional external authentication
* devices step in.
*
* 2. The script determines what other session informations have to be set or
* reset, setting correctly $cidReset (for course) and $gidReset (for group).
*
* 3. If needed, the script retrieves the other user informations (first name,
		* last name, ...) and stores them in session.
*
* 4. If needed, the script retrieves the course information and stores them
* in session
*
* 5. The script initializes the user permission status and permission for the
* course level
*
* 6. If needed, the script retrieves group informations an store them in
* session.
*
* 7. The script initializes the user status and permission for the group level.
*
*/

// verified if the username and password exists in the current session
if (isset($_SESSION['info_current_user'][1]) && isset($_SESSION['info_current_user'][2])) {
	require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
	require_once (api_get_path(LIBRARY_PATH).'legal.lib.php');
}

if (isset($_GET['token'])) {
  require_once(api_get_path(SYS_PATH).'main/auth/sso/sso.lib.php');
}

// Check LDAP access
$ldap_access = false;
if ((isset($_POST['login']) && isset($_POST['password'])) && extension_loaded('ldap')) {
require_once(api_get_path(SYS_CODE_PATH).'auth/ldap/ldap.conf.php');
require_once(api_get_path(SYS_CODE_PATH).'auth/ldap/functions.inc.php');
$ldap_user = ldap_authenticate($_POST['login'],$_POST['password']);
 if ($ldap_user !== false) {
     $ldap_access = true;
 }
}
// parameters passed via GET
$logout = isset($_GET["logout"]) ? $_GET["logout"] : '';
$gidReq = isset($_GET["gidReq"]) ? Database::escape_string($_GET["gidReq"]) : '';

// Request the course id (can be done by setting $cidReq in the script or through the $_GET['cidReq'] parameter)
$cidReq = isset($cidReq) ? Database::escape_string($cidReq) : '';
$cidReq = isset($_GET['cidReq']) ? Database::escape_string($_GET['cidReq']) : $cidReq;

// Resetting the course id (can be done by setting $cidReset in the script (for instance in the admin scripts) or through the $_GET['cidReset'] parameter)
$cidReset = isset($cidReset) ? Database::escape_string($cidReset) : '';
$cidReset = (isset($_GET['cidReq']) && ((isset($_SESSION['_cid']) && $_GET['cidReq']!=$_SESSION['_cid']) || (!isset($_SESSION['_cid'])))) ? Database::escape_string($_GET["cidReq"]) : $cidReset;

// $cDir is a special url param sent by courses/.htaccess
$cDir = (!empty($_GET['cDir']) ? $_GET['cDir'] : null);

// resetting the group id
$gidReset = isset($gidReset) ? $gidReset : '';


// parameters passed via POST
$login = isset($_POST["login"]) ? $_POST["login"] : '';

/*
   ==============================================================================
   MAIN CODE
   ==============================================================================
 */

if (!empty($_SESSION['_user']['user_id']) && ! ($login || $logout)) {
	// uid is in session => login already done, continue with this value
	$_user['user_id'] = $_SESSION['_user']['user_id'];
} else {
	if (isset($_user['user_id'])) {
		unset($_user['user_id']);
	}

	//$_SESSION['info_current_user'][1] is user name
	//$_SESSION['info_current_user'][2] is current password encrypted
	//$_SESSION['update_term_and_condition'][1] is current user id, of user in session
	if (api_get_setting('allow_terms_conditions')=='true') {
		if (isset($_POST['login']) && isset($_POST['password']) && isset($_SESSION['update_term_and_condition'][1])) {

			$user_id=$_SESSION['update_term_and_condition'][1];	// user id
			// update the terms & conditions

			//verify type of terms and conditions
			$info_legal = explode(':',$_POST['legal_info']);
			$legal_type=LegalManager::get_type_of_terms_and_conditions($info_legal[0],$info_legal[1]);

			//is necessary verify check
			if ($legal_type==1) {
				if ((isset($_POST['legal_accept']) && $_POST['legal_accept']=='1')) {
					$legal_option=true;
				} else {
					$legal_option=false;

				}
			}
			//no is check option
			if ($legal_type==0) {
				$legal_option=true;
			}

			if (isset($_POST['legal_accept_type']) && $legal_option===true) {
				$cond_array = explode(':',$_POST['legal_accept_type']);
				if (!empty($cond_array[0]) && !empty($cond_array[1])){
					$time = time();
					$condition_to_save = intval($cond_array[0]).':'.intval($cond_array[1]).':'.$time;
					UserManager::update_extra_field_value($user_id,'legal_accept',$condition_to_save);

				}
			}
		}

	}

 // SSO authentification
 $sso_login = false;
 if (isset($_GET['token'])) { // Need more checks
  $token = Security::remove_XSS($_GET['token']);
   $sso_info = get_sso_info_by_security_token($token);
   $login = '';
   $login_status = '';
   $user_id = '';
   if ($sso_info['login_status'] == 0) {
     $sso_username = $sso_info['username'];
     $login_status = $sso_info['login_status'];
     $sso_id = $sso_info['token_id'];
     $sso_login = true;
   }
 }
	//IF cas is activated and user isn't logged in	
	$cas_login=false;
	if (api_get_setting('cas_activate') == 'true' AND !isset($_user['user_id']) and !isset($_POST['login'])  && !$logout) {
            $my_cas_server = api_get_setting('cas_server');
               if (!is_null($my_cas_server) || !empty($my_cas_server)) {
		require_once(api_get_path(SYS_PATH).'main/auth/cas/authcas.php');
		$cas_login = cas_is_authenticated();
               }
	}

	if ( ( isset($_POST['login']) AND  isset($_POST['password']) ) OR ( $cas_login) OR ( $sso_login) )  {
		// $login && $password are given to log in
		if ( $cas_login  && empty($_POST['login']) ) {
			$login = $cas_login;
		} elseif ( $sso_login  && empty($_POST['login'])) {
			$login = $sso_username;
  		}else {
			$login = $_POST['login'];
			$password = $_POST['password'];
		}

		//lookup the user in the main database
		$user_table = Database::get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT user_id, username, firstname, lastname, password, auth_source, active, expiration_date, login_counter
			FROM $user_table
			WHERE username = '".Database::escape_string(trim($login))."'";

		$result = Database::query($sql,__FILE__,__LINE__);

		if (Database::num_rows($result) > 0) {
			$uData = Database::fetch_array($result);
                        if ($ldap_access == true) {
                            $uData['auth_source'] = LDAP_AUTH_SOURCE;
                        }
			if ($uData['auth_source'] == PLATFORM_AUTH_SOURCE) {
				//the authentification of this user is managed by Dokeos itself
				$password = trim($password);

				if (api_get_setting('allow_terms_conditions')=='true') {
					if (isset($_POST['password']) && isset($_SESSION['info_current_user'][2]) && $_POST['password']==$_SESSION['info_current_user'][2]) {
						$password=$_POST['password'];
					} else {
						$password = api_get_encrypted_password($password);
					}
				} else {
					$password = api_get_encrypted_password($password);
				}
				if (api_get_setting('allow_terms_conditions')=='true') {
					if ($password == $uData['password'] AND (trim($login) == $uData['username']) OR $cas_login ) {
						$temp_user_id = $uData['user_id'];
						$term_and_condition_status=api_check_term_condition($temp_user_id);//false or true
						if ($term_and_condition_status===false) {
							$_SESSION['update_term_and_condition']=array(true,$temp_user_id);
							$_SESSION['info_current_user']=array(true,$login,$password);
							header('Location: '.api_get_path(WEB_CODE_PATH).'auth/inscription.php');
							exit;
						} else {
							unset($_SESSION['update_term_and_condition']);
							unset($_SESSION['info_current_user']);
						}

					}
				}

				// check if the user is still active
				if ($uData['active'] <> '1'){
						$loginFailed = true;
						login_failed(api_get_path(WEB_PATH).'index.php?loginFailed=1&error=account_inactive');
				}

				// check the user's password
				if (($password == $uData['password'] OR $cas_login OR $sso_login) AND (trim($login) == $uData['username'])) {
					// check if the account is active (not locked)
					if ($uData['active']=='1') {
						// check if the expiration date has not been reached
						if ($uData['expiration_date']>date('Y-m-d H:i:s') OR $uData['expiration_date']=='0000-00-00 00:00:00') {
							global $_configuration;
							if (!empty($_configuration['multiple_access_urls']) && $_configuration['multiple_access_urls']==true) {
								//check the access_url configuration setting if the user is registered in the access_url_rel_user table
								//getting the current access_url_id of the platform
								$current_access_url_id = api_get_current_access_url_id();
								// my user is subscribed in these sites => $my_url_list
								$my_url_list = api_get_access_url_from_user($uData['user_id']);

								if (is_array($my_url_list) && count($my_url_list)>0 ){
									// the user have the permissions to enter at this site
									if (in_array($current_access_url_id, $my_url_list)) {
										$_user['user_id'] = $uData['user_id'];
										api_session_register('_user');
										event_login();
									} else {
										$loginFailed = true;
										login_failed(api_get_path(WEB_PATH).'index.php?loginFailed=1&error=access_url_inactive');
									}
								} else {
									$loginFailed = true;
									login_failed(api_get_path(WEB_PATH).'index.php?loginFailed=1&error=access_url_inactive');
								}
							} else {
								$_user['user_id'] = $uData['user_id'];
								api_session_register('_user');
								event_login();

							}
						} else {
							$loginFailed = true;
							login_failed(api_get_path(WEB_PATH).'index.php?loginFailed=1&error=account_expired');
						}
					} else {
						$loginFailed = true;
						login_failed(api_get_path(WEB_PATH).'index.php?loginFailed=1&error=account_inactive');
					}
				} else {
					$loginFailed = true;
					login_failed(api_get_path(WEB_PATH).'index.php?loginFailed=1&error=user_password_incorrect', true, $login);
				}

				if (isset($uData['creator_id']) && $_user['user_id'] != $uData['creator_id']) {
					//first login for a not self registred
					//e.g. registered by a teacher
					//do nothing (code may be added later)
				}
			} elseif (!empty($extAuthSource[$uData['auth_source']]['login']) && file_exists($extAuthSource[$uData['auth_source']]['login'])) {
				/*
				 * Process external authentication
				 * on the basis of the given login name
				 */
				$loginFailed = true;  // Default initialisation. It could
				// change after the external authentication
				$key = $uData['auth_source']; //'ldap','shibboleth'...
				/* >>>>>>>> External authentication modules <<<<<<<<< */
				// see configuration.php to define these
				include_once($extAuthSource[$key]['login']);
				/* >>>>>>>> External authentication modules <<<<<<<<< */
			} else // no standard Dokeos login - try external authentification
			{
				//huh... nothing to do... we shouldn't get here
				error_log('Dokeos Authentication file '. $extAuthSource[$uData['auth_source']]['login']. ' could not be found - this might prevent your system from doing the corresponding authentication process',0);
			}
                        $device_info = array();
                        $get_navigator_info = api_get_navigator();
                        $device_info = $get_navigator_info['device'];
                        $machine_type = $device_info['machinetype'];
                        // $machine_type == 'phone' OR $machine_type == 'tablet'
                        //var_dump($machine_type); var_dump($_REQUEST);die();
                        if (($machine_type == 'phone' OR $machine_type == 'tablet' OR $machine_type== NULL) && (isset($_POST['fromMobile']) && $_POST['fromMobile'] =="mobile") && isset($_REQUEST['fromMobile'])) {
                            //var_dump("testing");die();
                            header('location: '.api_get_path(WEB_VIEW_PATH).'mobile/index.php');
                            exit;
                        }
			if (!empty($_SESSION['request_uri'])) {
				$req = $_SESSION['request_uri'];
				unset($_SESSION['request_uri']);
				header('location: '.$req);
			} else {
				if (isset($param)) {
					header('location: '.api_get_path(WEB_PATH).api_get_setting('page_after_login').$param);
				} else {
					 // Update login_status for the SSO authentification
					  if ($sso_login == true && $login_status == 0) {
						update_sso_login_status_by_sso_id($sso_id);
					  }

					if ( (api_get_setting('force_password_change') <> 0 OR $uData['login_counter'] == '-1') AND (api_get_self() !='/change_password.php' AND   $uData['login_counter'] == '-1' )){                                                                                    
                                            header('location: '.api_get_path(WEB_PATH).'change_password.php');                                                                                       
					} else {
						reset_failed_login_counter($uData['user_id']);
                                                //asked for a key to handle redirects.
                                                if(isset($_POST['current_location'])){
                                                    switch($_POST['current_location']){
                                                        case 'payment':
                                                            header('location: '.$_POST['redirect_url'].'');
                                                            break;
                                                        default:
                                                            header('location: '.api_get_path(WEB_PATH).api_get_setting('page_after_login'));
                                                            break;
                                                    }
                                                }else{
                                                    // here is the main redirect of a *normal* login page in Dokeos
                                                    header('location: '.api_get_path(WEB_PATH).api_get_setting('page_after_login'));
                                                }
					}
				}


			}
		} else {
			// login failed, Database::num_rows($result) <= 0
			$loginFailed = true;  // Default initialisation. It could
			// change after the external authentication

			/*
			 * In this section:
			 * there is no entry for the $login user in the Dokeos
			 * database. This also means there is no auth_source for the user.
			 * We let all external procedures attempt to add him/her
			 * to the system.
			 *
			 * Process external login on the basis
			 * of the authentication source list
			 * provided by the configuration settings.
			 * If the login succeeds, for going further,
			 * Dokeos needs the $_user['user_id'] variable to be
			 * set and registered in the session. It's the
			 * responsability of the external login script
			 * to provide this $_user['user_id'].
			 */

			if (isset($extAuthSource) && is_array($extAuthSource)) {
				foreach($extAuthSource as $thisAuthSource) {
					if (!empty($thisAuthSource['newUser']) && file_exists($thisAuthSource['newUser'])) {
						include_once($thisAuthSource['newUser']);
					} else {
						error_log('Dokeos Authentication file '. $thisAuthSource['newUser']. ' could not be found - this might prevent your system from using the authentication process in the user creation process',0);
					}
				}
			} //end if is_array($extAuthSource)

		} //end else login failed
	} elseif(api_get_setting('sso_authentication')=='true' &&  !in_array('webservices', explode('/', $_SERVER['REQUEST_URI']))) {
		/**
		 * TODO:
		 * - Implement user interface for api_get_setting('sso_authentication')
		 *   } elseif (api_get_setting('sso_authentication')=='true') {
		 * - Work on a better validation for webservices paths. Current is very poor and exit
		 * - $master variable should be recovered from dokeos settings.
		 */
	$master = array(
			'domain' => api_get_setting('sso_authentication_domain'), 			//	'localhost/project/drupal5',
			'auth_uri' => api_get_setting('sso_authentication_auth_uri'),		//	'/?q=user',
			'deauth_uri' => api_get_setting('sso_authentication_unauth_uri'),	//	'/?q=logout',
			'protocol' => api_get_setting('sso_authentication_protocol')		//	'http://',
		       );
	$referer = $master['protocol'] . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	if (isset($_SESSION['_user']['user_id'])) {
		if ($logout) {
			// Library needed by index.php
			include_once api_get_path(LIBRARY_PATH) . 'online.inc.php';
			include_once (api_get_path(LIBRARY_PATH).'course.lib.php');
			// Prevent index.php to redirect
			global $logout_no_redirect;
			$logout_no_redirect = TRUE;
			// Make custom redirect after logout
			online_logout();
			header('Location: '. $master['protocol'] . $master['domain'] . $master['deauth_uri']);
			exit;
		}
	} elseif(!$logout) {
		$master_url = $master['domain'] . $master['auth_uri'];
		// Handle cookie comming from Master Server
		if (!isset($_GET['sso_referer']) && !isset($_GET['loginFailed'])) {
			// Target to redirect after success SSO
			$target = api_get_path(WEB_PATH);
			// Redirect to master server
			header('Location: ' . $master['protocol'] . $master_url . '&sso_referer=' . urlencode($referer) . '&sso_target=' . urlencode($target));
			exit;
		} elseif (isset($_GET['sso_cookie'])) {
			if (isset($_GET['sso_referer']) ? $_GET['sso_referer'] == $master['protocol']. $master_url : FALSE) {
				$sso = unserialize(base64_decode($_GET['sso_cookie']));
				//lookup the user in the main database
				$user_table = Database::get_main_table(TABLE_MAIN_USER);
				$sql = "SELECT user_id, username, password, auth_source, active, expiration_date
					FROM $user_table
					WHERE username = '".Database::escape_string(trim($sso['username']))."'";

				$result = Database::query($sql,__FILE__,__LINE__);

				if (Database::num_rows($result) > 0) {
					$uData = Database::fetch_array($result);
					// check the user's password
					if ($uData['auth_source'] == PLATFORM_AUTH_SOURCE) {
						// Make sure password is encrypted with md5
						if (!$userPasswordCrypted) {
							$uData['password'] = md5($uData['password']);
						}
						//the authentification of this user is managed by Dokeos itself// check the user's password
						// password hash comes into a sha1
						if ($sso['secret'] == sha1($uData['password']) && ($sso['username'] == $uData['username'])) {
							// check if the account is active (not locked)
							if ($uData['active']=='1') {
								// check if the expiration date has not been reached
								if ($uData['expiration_date']>date('Y-m-d H:i:s') OR $uData['expiration_date']=='0000-00-00 00:00:00') {
									global $_configuration;
									if ($_configuration['multiple_access_urls']==true) {
										//check the access_url configuration setting if the user is registered in the access_url_rel_user table
										//getting the current access_url_id of the platform
										$current_access_url_id = api_get_current_access_url_id();
										// my user is subscribed in these sites => $my_url_list
										$my_url_list = api_get_access_url_from_user($uData['user_id']);

										if (is_array($my_url_list) && count($my_url_list)>0 ) {
											if (in_array($current_access_url_id, $my_url_list)) {
												// the user has permission to enter at this site
												$_user['user_id'] = $uData['user_id'];
												api_session_register('_user');
												event_login();


												// Redirect to homepage
												$sso_target = isset($sso['target']) ? $sso['target'] : api_get_path(WEB_PATH) .'.index.php';
												header('Location: '. $sso_target);
											} else {
												// user does not have permission for this site
												$loginFailed = true;
												login_failed('index.php?loginFailed=1&error=no_permission_access_url');
											}
										} else {
											// there is no URL in the multiple urls list for this user
											$loginFailed = true;
											login_failed('index.php?loginFailed=1&error=no_permission_access_url');
										}
									} else {
										//single URL access
										$_user['user_id'] = $uData['user_id'];
										api_session_register('_user');

										event_login();

										// Redirect to homepage
										$sso_target = isset($sso['target']) ? $sso['target'] : api_get_path(WEB_PATH) .'.index.php';
										header('Location: '. $sso_target);
									}
								} else {
									// user account expired
									$loginFailed = true;
									login_failed('index.php?loginFailed=1&error=account_expired');
								}
							} else {
								//user not active
								$loginFailed = true;
								login_failed('index.php?loginFailed=1&error=account_inactive');
							}
						} else {
							//password is wrong
							$loginFailed = true;
							login_failed('index.php?loginFailed=1&error=user_password_incorrect', true, $login);
						}
					} else {
						//auth_source is wrong
						$loginFailed = true;
						login_failed('index.php?loginFailed=1&error=auth_source_wrong');
					}
				} else {
					//no user by that login
					$loginFailed = true;
					login_failed('index.php?loginFailed=1&error=unknown_user');
				}
			} else {
				//request comes from unknown source
				$loginFailed = true;
				login_failed('index.php?loginFailed=1&error=unknown_source');
			}
		}
	}
} elseif (api_get_setting('openid_authentication')=='true') {
	if (!empty($_POST['openid_url'])) {
		include('main/auth/openid/login.php');
		openid_begin(trim($_POST['openid_url']),api_get_path(WEB_PATH).'index.php');
		//this last function should trigger a redirect, so we can die here safely
		die('Openid login redirection should be in progress');
	} elseif (!empty($_GET['openid_identity']))
	{	//it's usual for PHP to replace '.' (dot) by '_' (underscore) in URL parameters
		include('main/auth/openid/login.php');
		$res = openid_complete($_GET);
		if ($res['status'] == 'success') {
			$id1 = $res['openid.identity'];
			//have another id with or without the final '/'
			$id2 = (substr($id1,-1,1)=='/'?substr($id1,0,-1):$id1.'/');
			//lookup the user in the main database
			$user_table = Database::get_main_table(TABLE_MAIN_USER);
			$sql = "SELECT user_id, username, password, auth_source, active, expiration_date
				FROM $user_table
				WHERE openid = '".Database::escape_string($id1)."'
				OR openid = '".Database::escape_string($id2)."' ";
			$result = Database::query($sql);
			if ($result !== false) {
				if (Database::num_rows($result)>0) {
					//$row = Database::fetch_array($res);
					$uData = Database::fetch_array($result);

					if ($uData['auth_source'] == PLATFORM_AUTH_SOURCE) {
						//the authentification of this user is managed by Dokeos itself

						// check if the account is active (not locked)
						if ($uData['active']=='1') {
							// check if the expiration date has not been reached
							if ($uData['expiration_date']>date('Y-m-d H:i:s') OR $uData['expiration_date']=='0000-00-00 00:00:00') {
								$_user['user_id'] = $uData['user_id'];
								api_session_register('_user');

								event_login();

							} else {
								$loginFailed = true;
								login_failed('index.php?loginFailed=1&error=account_expired');
							}
						} else {
							$loginFailed = true;
							login_failed('index.php?loginFailed=1&error=account_inactive');
						}

						if (isset($uData['creator_id']) && $_user['user_id'] != $uData['creator_id'])
						{
							//first login for a not self registred
							//e.g. registered by a teacher
							//do nothing (code may be added later)
						}
					}
				} else {
					//Redirect to the subscription form
					header('Location: '.api_get_path(WEB_CODE_PATH).'auth/inscription.php?username='.$res['openid.sreg.nickname'].'&email='.$res['openid.sreg.email'].'&openid='.$res['openid.identity'].'&openid_msg=idnotfound');
					//$loginFailed = true;
				}
			} else {
				$loginFailed = true;
			}
		} else {
			$loginFailed = true;
		}
	}
}

//    else {} => continue as anonymous user
$uidReset = true;

//    $cidReset = true;
//    $gidReset = true;
}

//Now check for anonymous user mode
if (isset($use_anonymous) && $use_anonymous == true) {
	//if anonymous mode is set, then try to set the current user as anonymous
	//if he doesn't have a login yet
	api_set_anonymous();
} else {
	//if anonymous mode is not set, then check if this user is anonymous. If it
	//is, clean it from being anonymous (make him a nobody :-))
	api_clear_anonymous();
}

// if there is a cDir parameter in the URL (coming from courses/.htaccess redirection)
if (!empty($cDir)) {
	require_once api_get_path(LIBRARY_PATH).'course.lib.php';
	$c = CourseManager::get_course_id_from_path($cDir);
	if ($c != false) { $cidReq = $c; }
}

// if the requested course is different from the course in session

if (!empty($cidReq) && (!isset($_SESSION['_cid']) or (isset($_SESSION['_cid']) && $cidReq != $_SESSION['_cid']))) {
	$cidReset = true;
	$gidReset = true;    // As groups depend from courses, group id is reset
}

// if the requested group is different from the group in session
$gid = isset($_SESSION['_gid'])?$_SESSION['_gid']:'';
if ($gidReq && $gidReq != $gid) {
	$gidReset = true;
}


//////////////////////////////////////////////////////////////////////////////
// USER INIT
//////////////////////////////////////////////////////////////////////////////

if (isset($uidReset) && $uidReset) // session data refresh requested
{
	$is_platformAdmin = false; $is_allowedCreateCourse = false;

	if (isset($_user['user_id']) && $_user['user_id']) // a uid is given (log in succeeded)
	{
		$user_table = Database::get_main_table(TABLE_MAIN_USER);
		$admin_table = Database::get_main_table(TABLE_MAIN_ADMIN);
		if ($_configuration['tracking_enabled']) {
			$sql = "SELECT user.*, a.user_id is_admin,
				UNIX_TIMESTAMP(login.login_date) login_date
					FROM $user_table
					LEFT JOIN $admin_table a
					ON user.user_id = a.user_id
					LEFT JOIN ".$_configuration['statistics_database'].".track_e_login login
					ON user.user_id  = login.login_user_id
					WHERE user.user_id = '".$_user['user_id']."'
					ORDER BY login.login_date DESC LIMIT 1";
		} else {
			$sql = "SELECT user.*, a.user_id is_admin
				FROM $user_table
				LEFT JOIN $admin_table a
				ON user.user_id = a.user_id
				WHERE user.user_id = '".$_user['user_id']."'";
		}

		$result = Database::query($sql,__FILE__,__LINE__);

		if (Database::num_rows($result) > 0) {
			// Extracting the user data

			$uData = Database::fetch_array($result);
			$_user						= $uData;
			$_user ['firstName'] 		= $uData['firstname' ];
			$_user ['lastName' ] 		= $uData['lastname'  ];
			$_user ['mail'     ] 		= $uData['email'     ];
			$_user ['lastLogin'] 		= $uData['login_date'];
			$_user ['official_code'] 	= $uData['official_code'];
			$_user ['picture_uri'] 		= $uData['picture_uri'];
			$_user ['user_id'] 			= $uData['user_id'];
			$_user ['language']	 		= $uData['language'];
			$_user ['auth_source'] 		= $uData['auth_source'];
			$_user ['theme']			= $uData['theme'];
			$_user ['status']			= $uData['status'];
			$_user ['password']			= ''; // unsetting for security reasons

			$is_platformAdmin        = (bool) (! is_null( $uData['is_admin']));
			$is_allowedCreateCourse  = (bool) (($uData ['status'] == 1) or (api_get_setting('drhCourseManagerRights') and $uData['status'] == 4));

			api_session_register('_user');
		} else {
			header('location:'.api_get_path(WEB_PATH));
			//exit("WARNING UNDEFINED UID !! ");
		}
	} else { // no uid => logout or Anonymous
		api_session_unregister('_user');
		api_session_unregister('_uid');
	}

	api_session_register('is_platformAdmin');
	api_session_register('is_allowedCreateCourse');
} else { // continue with the previous values
	$_user = $_SESSION['_user'];
	$is_platformAdmin = $_SESSION['is_platformAdmin'];
	$is_allowedCreateCourse = $_SESSION['is_allowedCreateCourse'];
}

//////////////////////////////////////////////////////////////////////////////
// COURSE INIT
//////////////////////////////////////////////////////////////////////////////

if (isset($cidReset) && $cidReset) { // course session data refresh requested or empty data
	if ($cidReq) {
		$course_table = Database::get_main_table(TABLE_MAIN_COURSE);
		$course_cat_table = Database::get_main_table(TABLE_MAIN_CATEGORY);
		$sql =    "SELECT course.*, course_category.code faCode, course_category.name faName
			FROM $course_table
			LEFT JOIN $course_cat_table
			ON course.category_code = course_category.code
			WHERE course.code = '$cidReq'";
		$result = Database::query($sql,__FILE__,__LINE__);

		if (Database::num_rows($result)>0) {
			$cData = Database::fetch_array($result);
			$_cid                            = $cData['code'             ];
			$_course = array();
			$_course['id'          ]         = $cData['code'             ]; //auto-assigned integer
			$_course['name'        ]         = $cData['title'         ];
			$_course['official_code']         = $cData['visual_code'        ]; // use in echo
			$_course['sysCode'     ]         = $cData['code'             ]; // use as key in db
			$_course['path'        ]         = $cData['directory'        ]; // use as key in path
			$_course['dbName'      ]         = $cData['db_name'           ]; // use as key in db list
			$_course['dbNameGlu'   ]         = $_configuration['table_prefix'] . $cData['db_name'] . $_configuration['db_glue']; // use in all queries
			$_course['titular'     ]         = $cData['tutor_name'       ];
			$_course['language'    ]         = $cData['course_language'   ];
			$_course['extLink'     ]['url' ] = $cData['department_url'    ];
			$_course['extLink'     ]['name'] = $cData['department_name'];
			$_course['categoryCode']         = $cData['faCode'           ];
			$_course['categoryName']         = $cData['faName'           ];

			$_course['visibility'  ]         = $cData['visibility'];
			$_course['subscribe_allowed']    = $cData['subscribe'];
			$_course['unubscribe_allowed']   = $cData['unsubscribe'];

			api_session_register('_cid');
			api_session_register('_course');

			if ($_configuration['tracking_enabled'] && !isset($_SESSION['login_as'])) {

				$course_tracking_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);
				$time = api_get_datetime();
				$sql="INSERT INTO $course_tracking_table 
					(course_code, user_id, login_course_date, logout_course_date, counter)" .
					"VALUES('".$_cid."', '".$_user['user_id']."', '$time', '$time', '1')";
				Database::query($sql,__FILE__,__LINE__);
			}	
			// if a session id has been given in url, we store the session
			if (api_get_setting('use_session_mode')=='true') {
				// Database Table Definitions
				$tbl_session 				= Database::get_main_table(TABLE_MAIN_SESSION);
				$tbl_user 					= Database::get_main_table(TABLE_MAIN_USER);
				$tbl_session_course 		= Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
				$tbl_session_course_user 	= Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

				if (!empty($_GET['id_session'])) {
					$_SESSION['id_session'] = Database::escape_string($_GET['id_session']);
					$sql = 'SELECT name FROM '.$tbl_session . ' WHERE id="'.intval($_SESSION['id_session']) . '"';
					$rs = Database::query($sql,__FILE__,__LINE__);
					list($_SESSION['session_name']) = Database::fetch_array($rs);
				} else {
					api_session_unregister('session_name');
					api_session_unregister('id_session');
				}
			}
		} else {
			//exit("WARNING UNDEFINED CID !! ");
			header('location:'.api_get_path(WEB_PATH));
		}
	} else {
		api_session_unregister('_cid');
		api_session_unregister('_course');
	}
} else { // continue with the previous values
	if (empty($_SESSION['_course']) OR empty($_SESSION['_cid'])) { //no previous values...
		$_cid = -1;		//set default values that will be caracteristic of being unset
		$_course = -1;
	} else {
		$_cid 		= $_SESSION['_cid'   ];
		$_course    = $_SESSION['_course'];

		// these lines are usefull for tracking. Indeed we can have lost the id_session and not the cid.
		// Moreover, if we want to track a course with another session it can be usefull
		if (!empty($_GET['id_session'])) {
			$tbl_session 				= Database::get_main_table(TABLE_MAIN_SESSION);
			$_SESSION['id_session'] = Database::escape_string($_GET['id_session']);
			$sql = 'SELECT name FROM '.$tbl_session . ' WHERE id="'.intval($_SESSION['id_session']). '"';
			$rs = Database::query($sql,__FILE__,__LINE__);
			list($_SESSION['session_name']) = Database::fetch_array($rs);
		}

		if ($_configuration['tracking_enabled'] && !isset($_SESSION['login_as'])) {
			$course_tracking_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);
			$time = api_get_datetime();
                        $course_code=$_course['sysCode'];
			//We select the last record for the current course in the course tracking table
			//$sql="SELECT course_access_id FROM $course_tracking_table WHERE user_id=".intval($_user ['user_id'])." ORDER BY login_course_date DESC LIMIT 0,1";
                        if (isset($_configuration['session_lifetime'])) {
                           $session_lifetime    = $_configuration['session_lifetime'];
                         } else {
                            $session_lifetime    = 3600;
                         }
                        $sql="SELECT course_access_id FROM $course_tracking_table
                        WHERE user_id=".intval($_user ['user_id'])."
                        AND course_code='$course_code' 
                        AND login_course_date > now() - INTERVAL $session_lifetime SECOND
                        ORDER BY login_course_date DESC LIMIT 0,1";
                        
                        $result=api_sql_query($sql,__FILE__,__LINE__);
			if (Database::num_rows($result)>0) {
				$i_course_access_id = Database::result($result,0,0);

				//We update the course tracking table
				$sql="UPDATE $course_tracking_table " .
					"SET logout_course_date = '$time', " .
					"counter = counter+1 " .
					"WHERE course_access_id=".intval($i_course_access_id);

				api_sql_query($sql,__FILE__,__LINE__);
			} else {
				$sql="INSERT INTO $course_tracking_table(course_code, user_id, login_course_date, logout_course_date, counter)" .
					"VALUES('".$_course['sysCode']."', '".$_user['user_id']."', '$time', '$time', '1')";
				api_sql_query($sql,__FILE__,__LINE__);	
			}		
		}
	}
}


//////////////////////////////////////////////////////////////////////////////
// COURSE / USER REL. INIT
//////////////////////////////////////////////////////////////////////////////
if ((isset($uidReset) && $uidReset) || (isset($cidReset) && $cidReset)) { // session data refresh requested
	if (isset($_user['user_id']) && $_user['user_id'] && isset($_cid) && $_cid) { // have keys to search data

		if (api_get_setting('use_session_mode') != 'true') {

			$course_user_table = Database::get_main_table(TABLE_MAIN_COURSE_USER);
			$sql = "SELECT * FROM $course_user_table
				WHERE user_id  = '".$_user['user_id']."'
				AND course_code = '$cidReq'";

			$result = Database::query($sql,__FILE__,__LINE__);

			if (Database::num_rows($result) > 0) { // this  user have a recorded state for this course
				$cuData = Database::fetch_array($result);

				$is_courseMember     = true;
				$is_courseTutor      = (bool) ($cuData['tutor_id' ] == 1 );
				$is_courseAdmin      = (bool) ($cuData['status'] == 1 );

				api_session_register('_courseUser');
			} else { // this user has no status related to this course
				$is_courseMember = false;
				$is_courseAdmin  = false;
				$is_courseTutor  = false;
			}

			$is_courseAdmin = (bool) ($is_courseAdmin || $is_platformAdmin);

		} else {

			$tbl_course_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);

			$sql = "SELECT * FROM ".$tbl_course_user."
				WHERE user_id  = '".$_user['user_id']."'
				AND course_code = '$cidReq'";

			$result = Database::query($sql,__FILE__,__LINE__);

			if (Database::num_rows($result) > 0) { // this  user have a recorded state for this course
				$cuData = Database::fetch_array($result);

				$_courseUser['role'] = $cuData['role'  ];
				$is_courseMember     = true;
				$is_courseTutor      = (bool) ($cuData['tutor_id' ] == 1 );
				$is_courseAdmin      = (bool) ($cuData['status'] == 1 );

				api_session_register('_courseUser');
			}

			if (empty($is_courseAdmin)) { // this user has no status related to this course
				// is it the session coach or the session admin ?
				$tbl_session = Database :: get_main_table(TABLE_MAIN_SESSION);
				$tbl_session_course = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
				$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
				$sql = " SELECT session.id_coach, session_admin_id FROM $tbl_session session,$tbl_session_course_user session_rcru
					WHERE session_rcru.id_session = session.id AND session_rcru.course_code = '$_cid' AND session_rcru.id_user='{$_user['user_id']}' AND session_rcru.status = 2";       
				$result = Database::query($sql,__FILE__,__LINE__);
				$row = Database::store_result($result);
				if (array_key_exists(0, $row) && ($row[0]['id_coach']==$_user['user_id'])) {
					$_courseUser['role'] = 'Professor';
					$is_courseMember     = true;
					$is_courseTutor      = true;
					$is_courseCoach      = true;
					$is_sessionAdmin     = false;

					if (api_get_setting('extend_rights_for_coach')=='true') {
						$is_courseAdmin = true;
					} else {
						$is_courseAdmin = false;
					}

					api_session_register('_courseUser');
				} elseif (array_key_exists(0, $row) && ($row[0]['session_admin_id']==$_user['user_id'])) {
					$_courseUser['role'] = 'Professor';
					$is_courseMember     = false;
					$is_courseTutor      = false;
					$is_courseAdmin      = false;
					$is_courseCoach      = false;
					$is_sessionAdmin     = true;
				} else {
					// Check if the current user is the course coach
					$sql = "SELECT 1
						FROM ".$tbl_session_course_user."
						WHERE course_code='$_cid'
						AND id_user = '".$_user['user_id']."'
						AND id_session = '".api_get_session_id()."'
						AND status = 2";

					$result = Database::query($sql,__FILE__,__LINE__);
					if ($row = Database::fetch_array($result)) {
						$_courseUser['role'] = 'Professor';
						$is_courseMember     = true;
						$is_courseTutor      = true;
						$is_courseCoach      = true;
						$is_sessionAdmin     = false;

						if (api_get_setting('extend_rights_for_coach')=='true') {
							$is_courseAdmin = true;
						} else {
							$is_courseAdmin = false;
						}
						api_session_register('_courseUser');
					} else {
						if (api_get_session_id() != 0) {
                                                        // check if user is a global tutor in the session
                                                        $rs = Database::query("SELECT id_coach FROM $tbl_session WHERE id = '".api_get_session_id()."' AND id_coach = '".$_user['user_id']."'");
                                                        if (Database::num_rows($rs) > 0) {
                                                            $is_courseMember     = true;
                                                            $is_courseTutor      = true;
                                                            $is_courseAdmin      = true;
                                                            $is_sessionAdmin     = false;
                                                            api_session_register('_courseUser');
                                                        }
                                                        else {
                                                            $tbl_session_category_rel_tutor = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_TUTOR);
                                                            $check_categories = Database::query("SELECT session_category_id FROM $tbl_session WHERE id = '".api_get_session_id()."'");
                                                            if (Database::num_rows($check_categories) > 0) {
                                                                $rs_tutors = Database::query("SELECT tutor_id FROM $tbl_session_category_rel_tutor WHERE session_category_id IN (SELECT session_category_id FROM $tbl_session WHERE id = '".api_get_session_id()."') AND tutor_id = '".$_user['user_id']."'");                                                                
                                                                if (Database::num_rows($rs_tutors) > 0) {
                                                                    $is_courseMember     = true;
                                                                    $is_courseTutor      = true;
                                                                    $is_courseAdmin      = true;
                                                                    $is_sessionAdmin     = false;
                                                                    api_session_register('_courseUser');
                                                                }     
                                                                else {
                                                                    // Check if the user is a student is this session
                                                                    $sql = "SELECT * FROM ".$tbl_session_course_user."
                                                                            WHERE id_user  = '".$_user['user_id']."'
                                                                            AND id_session = '".api_get_session_id()."'
                                                                            AND course_code = '$cidReq' AND status NOT IN(2)";	
                                                                            $result = Database::query($sql,__FILE__,__LINE__);	   
                                                                    if (Database::num_rows($result) > 0) { // this  user have a recorded state for this course					        
                                                                            while($row = Database::fetch_array($result)){
                                                                                    $is_courseMember     = true;
                                                            $is_courseTutor      = false;
                                                            $is_courseAdmin      = false;
                                                            $is_sessionAdmin     = false;
                                                            api_session_register('_courseUser');
                                                        }
                                                                    }
                                                                }
                                                            }
                                                        else {
                                                            // Check if the user is a student is this session
                                                            $sql = "SELECT * FROM ".$tbl_session_course_user."
                                                                    WHERE id_user  = '".$_user['user_id']."'
                                                                    AND id_session = '".api_get_session_id()."'
                                                                    AND course_code = '$cidReq' AND status NOT IN(2)";	
                                                                    $result = Database::query($sql,__FILE__,__LINE__);	   
                                                            if (Database::num_rows($result) > 0) { // this  user have a recorded state for this course					        
                                                                    while($row = Database::fetch_array($result)){
                                                                            $is_courseMember     = true;
                                                                            $is_courseTutor      = false;
                                                                            $is_courseAdmin      = false;
                                                                            $is_sessionAdmin     = false;
                                                                            api_session_register('_courseUser');
                                                                    }
                                                            }
                                                        }							
                                                        }							
						} else {
							// For test this to do this :
							// 1. Edit a training from admin portal
							// 2. Change the the course visibility to : Private access (course reachable by people on the users list only)
							// 3. remove the "if" condition below
							// 4. Now is impossible to access to course even if you are a course member
							// Bug fixed : Feature #7162 - restored 1.8 behavior for the portals migration

							if (!$is_courseMember) {// if the user is not member of the course then we are removing all the user credentials
								$is_courseMember     = false;
								$is_courseTutor      = false;
								$is_courseAdmin      = false;
								$is_sessionAdmin     = false;
								api_session_unregister('_courseUser');
							}
						}

					}
				}
			}
		}
	} else { // keys missing => not anymore in the course - user relation
		//// course
		$is_courseMember = false;
		$is_courseAdmin  = false;
		$is_courseTutor  = false;
		$is_courseCoach  = false;
		$is_sessionAdmin     = false;
		api_session_unregister('_courseUser');
	}

	//Verify course if already purchased
        //date_default_timezone_get(); //Configure TimeZone
//        $date_today = date("Y-m-d H:i:s"); //get DATETIME NOW
//        $resultPayment = Database::query("SELECT date_end FROM " . Database::get_main_table(TABLE_MAIN_PAYMENT_COURSE_REL_USER) . " WHERE user_id = '" . api_get_user_id() . "' AND course_code = '" . trim($GLOBALS['_cid']) . "'");
//        $course_payment = Database::fetch_array($resultPayment);
//        $date_end_payment = trim($course_payment['date_end']);
//        //check course has expired
//        if ($date_today >= $date_end_payment && $date_end_payment != '') {
//            $_course['visibility'] = '';
//        }
        
        $tbl_payment_session_rel_user = Database::get_main_table(TABLE_MAIN_PAYMENT_SESSION_REL_USER);
        $tbl_session_rel_course_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_REL_COURSE_REL_USER);
        $query= "SELECT  date_end  FROM $tbl_payment_session_rel_user p INNER JOIN $tbl_session_rel_course_rel_user s ON p.id_session=s.id_session
        WHERE s.id_user='" . api_get_user_id() . "' AND course_code='" . trim($GLOBALS['_cid']) . "' AND p.id_session='".  api_get_session_id() ."'";
        $res = Database::query($query);
        $result = Database::fetch_array($res);
        $date_end_payment_s = trim($result['date_end']);
        if(empty($date_end_payment_s)){
            $query= "SELECT date_end FROM " . Database::get_main_table(TABLE_MAIN_PAYMENT_COURSE_REL_USER) . " WHERE user_id = '" . api_get_user_id() . "' AND course_code = '" . trim($GLOBALS['_cid']) . "'";
            $res = Database::query($query);
            $result = Database::fetch_array($res);
            $date_end_payment_s = trim($result['date_end']);
        }
        if ($date_today >= $date_end_payment_s && $date_end_payment_s != '') {
            $_course['visibility'] = '';
        }
        
	$is_allowed_in_course = null;
	if (isset($_course)) {
		if ($_course['visibility'] == COURSE_VISIBILITY_OPEN_WORLD) {
                    $is_allowed_in_course = true;
                }
		elseif ($_course['visibility'] == COURSE_VISIBILITY_OPEN_PLATFORM && isset($_user['user_id']) && !api_is_anonymous($_user['user_id'], true)) {
                    $is_allowed_in_course = true;
                }
		elseif ($_course['visibility'] == COURSE_VISIBILITY_REGISTERED && ($is_platformAdmin || $is_courseMember)) {
                    $is_allowed_in_course = true;
                }
		elseif ($_course['visibility'] == COURSE_VISIBILITY_CLOSED && ($is_platformAdmin || $is_courseAdmin)) {
                    $is_allowed_in_course = true;
                }
		else {
                    $is_allowed_in_course = false;
                }
	}
        
	// requires testing!!!
	// check the session visibility
	if ($is_allowed_in_course) {
		$my_session_id = api_get_session_id();
		//if I'm in a session
		//var_dump($is_platformAdmin, $is_courseTutor,api_is_coach());
		if ($my_session_id!=0)
			if (!$is_platformAdmin) {
				// admin and session coach are *not* affected to the invisible session mode
				// the coach is not affected because he can log in some days after the end date of a session
				$session_visibility = api_get_session_visibility($my_session_id);
				if ($session_visibility==SESSION_INVISIBLE) {
					$is_allowed_in_course =false;
                                }
				
				$belongs = api_user_belongs_to_session($my_session_id,$_course['id'],$_user['user_id']);
				if ($belongs==false) {
					$is_allowed_in_course =false;
                                }                                                                                               
			
			}

	}

	// save the states

	api_session_register('is_courseMember');
	api_session_register('is_courseAdmin');
	//api_session_register('is_courseAllowed'); //deprecated old permission var
	api_session_register('is_courseTutor');
	api_session_register('is_allowed_in_course'); //new permission var
	api_session_register('is_courseCoach');
	api_session_register('is_sessionAdmin');
} else { // continue with the previous values

	if (isset($_SESSION ['_courseUser'])) {
		$_courseUser          = $_SESSION ['_courseUser'];
	}

	$is_courseMember      = $_SESSION ['is_courseMember' ];
	$is_courseAdmin       = $_SESSION ['is_courseAdmin'  ];
	//$is_courseAllowed     = $_SESSION ['is_courseAllowed']; //deprecated
	$is_allowed_in_course = $_SESSION ['is_allowed_in_course'];
	$is_courseTutor       = $_SESSION ['is_courseTutor'  ];
	$is_courseCoach       = $_SESSION ['is_courseCoach'  ];
}


//////////////////////////////////////////////////////////////////////////////
// GROUP INIT
//////////////////////////////////////////////////////////////////////////////

if ((isset($gidReset) && $gidReset) || (isset($cidReset) && $cidReset)) { // session data refresh requested
	if ($gidReq && $_cid ) { // have keys to search data
		$group_table = Database::get_course_table(TABLE_GROUP);
		$sql = "SELECT * FROM $group_table WHERE id = '$gidReq'";
		$result = Database::query($sql,__FILE__,__LINE__);
		if (Database::num_rows($result) > 0) { // This group has recorded status related to this course
			$gpData = Database::fetch_array($result);
			$_gid                   = $gpData ['id'             ];
			api_session_register('_gid');
		} else {
			exit("WARNING UNDEFINED GID !! ");
		}
	} elseif (isset($_SESSION['_gid']) or isset($_gid)) { // Keys missing => not anymore in the group - course relation
		api_session_unregister('_gid');
	}
} elseif (isset($_SESSION['_gid'])) { // continue with the previous values
	$_gid             = $_SESSION ['_gid'            ];
} else { //if no previous value, assign caracteristic undefined value
	$_gid = -1;
}
//set variable according to student_view_enabled choices
if (api_get_setting('student_view_enabled') == "true") {
	if (isset($_GET['isStudentView'])) {
		if ($_GET['isStudentView'] == 'true') {
			if (isset($_SESSION['studentview'])) {
				if (!empty($_SESSION['studentview'])) {
					// switching to studentview
					$_SESSION['studentview'] = 'studentview';
				}
			}
		} elseif ($_GET['isStudentView'] == 'false') {
			if (isset($_SESSION['studentview'])) {
				if (!empty($_SESSION['studentview'])) {
					// switching to teacherview
					$_SESSION['studentview'] = 'teacherview';
				}
			}
		}
	} elseif (!empty($_SESSION['studentview'])) {
		//all is fine, no change to that, obviously
	} elseif (empty($_SESSION['studentview'])) {
		// We are in teacherview here
		$_SESSION['studentview'] = 'teacherview';
	}
}

if (isset($_cid)) {
	$tbl_course = Database::get_main_table(TABLE_MAIN_COURSE);
	$time = api_get_datetime();
	$sql="UPDATE $tbl_course SET last_visit= '$time' WHERE code='$_cid'";
	Database::query($sql,__FILE__,__LINE__);
}

// force password change
if( isset($_SESSION['force_password_change']) AND $_SESSION['force_password_change'] == TRUE AND api_get_self() !='/change_password.php'    ){  
    header('location: '.api_get_path(WEB_PATH).'change_password.php');      
}

/**
 * This function increases the field dokeos_main.user.login_counter with 1 on every successfull login
 */
function update_login_counter($user_id, $value=''){
	if ($value == ''){
		$sql = "UPDATE ".Database :: get_main_table(TABLE_MAIN_USER)." SET login_counter = login_counter+1, login_failed_counter = 0 WHERE user_id='".Database::escape_string($user_id)."'";
	} else {
		$sql = "UPDATE ".Database :: get_main_table(TABLE_MAIN_USER)." SET login_counter = 0, login_failed_counter = 0 WHERE user_id='".Database::escape_string($user_id)."'";
	}
	$result = Database::query($sql, __FILE__, __LINE__);
	reset_failed_login_counter($user_id);
}

function reset_failed_login_counter($user_id){
	$sql = "UPDATE ".Database :: get_main_table(TABLE_MAIN_USER)." SET login_failed_counter = 0 WHERE user_id='".Database::escape_string($user_id)."'";
	$result = Database::query($sql, __FILE__, __LINE__);
}

/**
 * This function is called whenever the login fails for whatever reason
 * @param string The Url where user will be redirect on a login failed
 * @param boolean If true allow to increment the login_failed counter in the user table
 * @param string The username
 */
function login_failed($redirect_url, $increasefailedcounter=false, $username=''){
		$loginFailed = true;
		api_session_unregister('_uid');

		// increase the login_failed counter in the user table
		if ($increasefailedcounter == true and $username <> ''){
			if (api_get_setting('login_fail_lock') > 0){
				// get the current number of failed logins of this user
				$sql = "SELECT login_failed_counter, user_id, email, firstname, lastname FROM ".Database :: get_main_table(TABLE_MAIN_USER)." WHERE username = '".Database::escape_string($username)."'";
				$result = Database::query($sql, __FILE__, __LINE__);
				$row = Database::fetch_array($result, "ASSOC");

				// check if the user is an admin (this might indicate a hacking attempt
				$sql = "SELECT * FROM ".Database :: get_main_table(TABLE_MAIN_ADMIN)." WHERE user_id = '".Database::escape_string($row['user_id'])."'";
				$result = Database::query($sql, __FILE__, __LINE__);
                $admin = Database::num_rows($result);
                
				if ( $admin > 0 ) {
					$emailbody .= get_lang('FailedAdminAccountLogin').': '.$username;
					$emailbody .= get_lang('PleaseBeCarefullMightBeHackingAttempt');
					$emailbody .= get_lang('LastLoginIP').': '.$_SERVER['REMOTE_ADDR'];
					//@api_mail(api_get_setting('siteName'), api_get_setting('emailAdministrator'), get_lang('AccountBlocked').': '.$username, $emailbody, api_get_setting('administratorName').' '.api_get_setting('administratorSurname'), api_get_setting('emailAdministrator'));					
					header('Location: '.api_get_path(WEB_PATH).'index.php?loginFailed=1&error=AdminNotifiedWrongLogin');
					exit;
				}

				// increment the login_failed
				$sql = "UPDATE ".Database :: get_main_table(TABLE_MAIN_USER)." SET login_failed_counter = login_failed_counter+1 WHERE username = '".Database::escape_string($username)."'";
				$result = Database::query($sql, __FILE__, __LINE__);
           
				
				// lock the account if the number of allowed failed logins is reached (and the account is not admin)
				if ($row['login_failed_counter'] >= api_get_setting('login_fail_lock')){
					$sql = "UPDATE ".Database :: get_main_table(TABLE_MAIN_USER)." SET active = 0 WHERE username = '".Database::escape_string($username)."'";
					$result = Database::query($sql, __FILE__, __LINE__);

					// include mail library and configuration
					require_once(api_get_path(LIBRARY_PATH).'mail.lib.inc.php');
					require_once(api_get_path(INCLUDE_PATH).'conf/mail.conf.php');

					// send mail to the user that the account has been blocked
					$emailbody = get_lang('Dear').' '.$row['firstname'].' '.$row['lastname'].",\n\n";
					$emailbody .= get_lang('AccountBlockedBecauseOfSeveralFailedLogins').'('.api_get_setting('login_fail_lock').' '.get_lang('FailedLogins').')';
					@api_mail($row['firstname'].' '.$row['lastname'], $row['email'], get_lang('AccountBlocked'), $emailbody, api_get_setting('administratorName').' '.api_get_setting('administratorSurname'), api_get_setting('emailAdministrator'));

					// send mail to the platform administrator that the account has been blocked
					$emailbody = get_lang('AccountBlocked').': '.$username.",\n\n";
					$emailbody .= get_lang('LastLoginIP').': '.$_SERVER['REMOTE_ADDR'];
					@api_mail(api_get_setting('siteName'), api_get_setting('emailAdministrator'), get_lang('AccountBlocked').': '.$username, $emailbody, api_get_setting('administratorName').' '.api_get_setting('administratorSurname'), api_get_setting('emailAdministrator'));
				}
			}
		}
		header('Location: '.$redirect_url);
		exit;	
}
