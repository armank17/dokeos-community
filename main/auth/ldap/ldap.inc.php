<?php
/**
 * Licence: GPL
 * Please contact CBlue regarding any licences issues.
 * Author: noel@cblue.be
 *  Copyright: CBlue SPRL, 20XX
 * Improved by Sources Coders S.A.C
 *
 * This files is included by newUser.ldap.php and login.ldap.php
 * It implements the functions nedded by both files
 **/

//Includes the configuration file
require_once(dirname(__FILE__).'/../../inc/global.inc.php');
require_once(dirname(__FILE__).'/ldap.conf.php');

/**
 * Returns a transcoded and trimmed string
 *
 * @param string 
 * @return string 
 * @author ndiechburg <noel@cblue.be>
 **/
function ldap_purify_string($string)
{
  global $ldap_config;
  if(isset($ldap_config['encoding'])) {
    return trim(api_to_system_encoding($string, $ldap_config['encoding']));
  }
  else {
    return trim($string);
  }
}

/**
 * Establishes a connection to the LDAP server and sets the protocol version
 *
 * @return resource ldap link identifier or false
 * @author ndiechburg <noel@cblue.be>
 **/
function ldap_get_connection()
{
  global $ldap_config;

  if (!is_array($ldap_config['host']))
    $ldap_config['host'] = array($ldap_config['host']);

  foreach($ldap_config['host'] as $host) {
    //Trying to connect
    if (isset($ldap_config['port'])) {
      $ds = ldap_connect($host,$ldap_config['port']);
    } else {
      $ds = ldap_connect($host);
    }
    if (!$ds) {
      $port = isset($ldap_config['port']) ? $ldap_config['port'] : 389;
      error_log('LDAP ERROR : cannot connect to '.$ldap_config['host'].':'. $port);
    } else
      break;
  }
  if (!$ds) { 
    error_log('LDAP ERROR : no valid server found');
    return false;
  }
  //Setting protocol version
  if (isset($ldap_config['protocol_version'])) {
    if ( ! ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $ldap_config['protocol_version'])) {
      ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 2);
    }
  }

  //Setting protocol version
  if (isset($ldap_config['referrals'])) {
    if ( ! ldap_set_option($ds, LDAP_OPT_REFERRALS, $ldap_config['referrals'])) {
      ldap_set_option($ds, LDAP_OPT_REFERRALS, $ldap_config['referrals']);
    }
  }
  
  return $ds;
}

/**
 * Authenticate user on external ldap server and return user ldap entry if that succeeds
 *
 * @return mixed false if user cannot authenticate on ldap, user ldap entry if tha succeeds
 * @author ndiechburg <noel@cblue.be>
 **/
function ldap_authenticate($username, $password)
{
  global $ldap_config;

  if (empty($username) or empty($password)){
    return false;
  }

  $ds = ldap_get_connection();
  if (!$ds) {
    return false;
  }

  //Connection as admin to search dn of user
  $ldapbind = @ldap_bind($ds, $ldap_config['admin_dn'], $ldap_config['admin_password']);
  if ($ldapbind === false){
    error_log('LDAP ERROR : cannot connect with admin login/password');
    return false;
  }

  // fill the search filter if it is set
  $custom_search_filter  = "";
  if (!empty($ldap_config['user_search']))
    $custom_search_filter = $ldap_config['user_search'];

  if (api_get_setting('ldap_server_type') == '1') {
    // We are using Microsoft Active directory where username is stored as sAMAccountName
    $search_filter = "(&(sAMAccountName=$username) $custom_search_filter)";
  } else {
    // We are using OpenLDAP where username is stored as uid
    $search_filter = "(&(uid=$username))";// $custom_search_filter )";
  }

  $sr = ldap_search($ds, $ldap_config['base_dn'], $search_filter);


  if ( !$sr ){
    error_log('LDAP ERROR : ldap_search('.$ds.', '.$ldap_config['base_dn'].', $search_filter ) failed');
    return false;
  }
  $entries_count = ldap_count_entries($ds,$sr);

  if ($entries_count > 1) {
    error_log('LDAP ERROR : more than one entry for that user ( ldap_search(ds, '.$ldap_config['base_dn'].", $search_filter) )");
    return false;
  }
  if ($entries_count < 1) {
    error_log('LDAP ERROR :  No entry for that user ( ldap_search(ds, '.$ldap_config['base_dn'].", $search_filter) )");
    return false;
  }
  $users = ldap_get_entries($ds,$sr);
  $user = $users[0];

  //now we try to autenthicate the user in the ldap
  $ubind = @ldap_bind($ds, $user['dn'], $password);
  if($ubind !== false){
    return $user;
  }
  else {
    error_log('LDAP : Wrong password for '.$user['dn']);
    return false;
  }
}

/**
 * Return an array with userinfo compatible with dokeos using $ldap_user_correspondance
 * configuration array declared in ldap.conf.php file
 *
 * @param array ldap user
 * @param array correspondance array (if not set use ldap_user_correspondance declared 
 * in ldap.conf.php
 * @return array userinfo array
 * @author ndiechburg <noel@cblue.be>
 **/
function ldap_get_dokeos_user($ldap_user, $cor = null)
{
  global $ldap_user_correspondance;
  if ( is_null($cor) ) {
    $cor = $ldap_user_correspondance;
  }

  $dokeos_user =array();
  foreach ($cor as $dokeos_field => $ldap_field) {
    if (is_array($ldap_field)){
      $dokeos_user[$dokeos_field] = ldap_get_dokeos_user($ldap_user, $ldap_field);
      continue;
    }

    switch ($ldap_field) {
    case 'func':
      $func = "ldap_get_$dokeos_field";
      if (function_exists($func)) {
        $dokeos_user[$dokeos_field] = ldap_purify_string($func($ldap_user));
      } else {
        error_log("LDAP WARNING : You forgot to declare $func");
      }
      break;
    default:
      //if string begins with "!", then this is a constant
      if($ldap_field[0] === '!' ){
        $dokeos_user[$dokeos_field] = trim($ldap_field, "!\t\n\r\0");
        break;
      }
      if ( isset($ldap_user[$ldap_field][0]) ) {
        $dokeos_user[$dokeos_field] = ldap_purify_string($ldap_user[$ldap_field][0]);
      } else {
        error_log('LDAP WARNING : '.$ldap_field. '[0] field is not set in ldap array');

      }
      break;
    }
  }
  return $dokeos_user;
}
?>
