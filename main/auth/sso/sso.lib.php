<?php
/**
 * Gets the SSO info give the security token
 * @param string The security token
 */
function get_sso_info_by_security_token($token) {
  $tbl_sso = Database::get_main_table(TABLE_MAIN_SINGLE_SIGN_ON_ASSOCIATION);
  $sql = "SELECT id as token_id,token,date_end,user_id,login_status FROM $tbl_sso WHERE token='".Database::escape_string($token)."'";
  $rs = Database::query($sql, __FILE__, __LINE__);
  $row = Database::fetch_array($rs, 'ASSOC');

  $user_info = array();
  $uid = $row['user_id'];
  $user_info = api_get_user_info($uid);
  $row['username'] = $user_info['username'];
  return $row;
}

/**
 * Update the login status for the SSO authentification
 */
function update_sso_login_status_by_sso_id ($sso_id) {
 $tbl_sso = Database::get_main_table(TABLE_MAIN_SINGLE_SIGN_ON_ASSOCIATION);
 $sql = "UPDATE $tbl_sso SET login_status = 1, token = '".session_id()."' WHERE id = '".Database::escape_string($sso_id)."'";
 $rs = Database::query($sql, __FILE__, __LINE__);
}
