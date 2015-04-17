<?php
/**
 * Licence: GPL
 * Please contact CBlue regarding any licences issues.
 * Author: noel@cblue.be
 *  Copyright: CBlue SPRL, 20XX
 *
 * External login module : LDAP
 *
/**
 * Configuration file 
 * Please edit this file to match with your LDAP settings
 **/

require_once dirname(__FILE__).'/ldap.inc.php';

/** 
 * Array of connection parameters
 **/
$ldap_config = array(
  //base dommain string
  'base_dn' => api_get_setting('ldap_domain'), // should include complete branch to search
  //admin distinguished name
  'admin_dn' => api_get_setting('ldap_authentication_login'), // proper, complete LDAP Admin login name, e.g. CN=admin,dc=dokeos,dc=net
  //admin password
  'admin_password' => api_get_setting('ldap_authentication_password'),
  //ldap host
  'host' => array(api_get_setting('ldap_main_server_address'), api_get_setting('ldap_replicate_server_address')),
  // filter
  // 'filter' => '', // no () arround the string
  'port' => api_get_setting('ldap_main_server_port'),
  //protocl version (2 or 3)
  'protocol_version' => api_get_setting('ldap_version'),
  // set this to 0 to connect to AD server
  'referrals' => 0,
  //String used to search the user in ldap. %username will ber replaced by the username.
  //See ldap_get_user_search_string() function below
  'user_search' => html_entity_decode(api_get_setting('ldap_search_term')), // a proper LDAP search filter, e.g. (|(o=Teacher)(o=Student))
  //encoding used in ldap (most common are UTF-8 and ISO-8859-1
  'encoding' => 'UTF-8',
  //Set to true if user info have to be updated at each login
  'update_userinfo' => true
);

/**
 * return the string used to search a user in ldap
 *
 * @param string username
 * @return string the serach string
 * @author ndiechburg <noel@cblue.be>
 **/
function ldap_get_user_search_string($username)
{
  global $ldap_config;

  // init
  $filter = '('.$ldap_config['user_search'].')';
  // replacing %username% by the actual username
  $filter = str_replace('%username%',$username,$filter);
  // append a global filter if needed
  if (isset($ldap_config['filter']) && $ldap_config['filter'] != "")
    $filter = '(&'.$filter.'('.$ldap_config['filter'].'))';

  return $filter;
}

/**
 * Correspondance array between dokeos user info and ldap user info
 * This array is of this form : 
 *  '<dokeos_field> => <ldap_field>
 *
 * If <ldap_field> is "func", then the value of <dokeos_field> will be the return value of the function
 * ldap_get_<dokeos_field>($ldap_array)
 * In this cas you will have to declare the ldap_get_<dokeos_field> function
 *
 * If <ldap_field> is a string beginning with "!", then the value will be this string without "!"
 * 
 * If <ldap_field> is any other string then the value of <dokeos_field> will be 
 * $ldap_array[<ldap_field>][0]
 *
 * If <ldap_field> is an array then its value will be an array of values with the same rules as above
 * 
 **/
$ldap_user_correspondance = array(
  'firstname' => 'givenname',
  'lastname' => 'sn',
  'status' => 'func',
  'admin' => 'func',
  'email' => 'mail',
  'auth_source' => '!ldap',
  //'username' => ,
  'language' => 'func',
  'password' => '!PLACEHOLDER',
  //'extra' => array()
  );
/**
 * Please declare here all the function you use in ldap_user_correspondance
 * All these functions must have an $ldap_user parameter. This parameter is the 
 * array returned by the ldap for the user
 **/
/**
 * example function for email
 **/
/*
function ldap_get_email($ldap_user){
  return $ldap_user['cn'].$ldap['sn'].'@gmail.com';
}
 */
function ldap_get_status($ldap_user){
  return STUDENT;
}
function ldap_get_admin($ldap_user){
  return false;
}
//Return default plaform language
function ldap_get_language($ldap_user){
  return api_get_setting('platformLanguage');
}

?>
