<?php
/*
 * Added by Isaac flores on October 28 of 2010
 * This is an example of how the webservice consumer(client) should be implemented
 */
require_once '../inc/global.inc.php';
$libpath = api_get_path(LIBRARY_PATH);
require_once $libpath.'nusoap/nusoap.php';

// Create the client instance
$wsdl = $_configuration['root_web']."main/webservices/registration.soap.php?wsdl";
$client = new nusoap_client($wsdl, true);

/*$Ip_address = $_SERVER['SERVER_ADDR'];
$secret_key = sha1($Ip_address.'5d0960e9d95a0f9433d113b6c861e8ac');*/

$Ip_address = $_SERVER['SERVER_ADDR']; // This should be the IP address of the client
$secret_key = sha1($Ip_address.$_configuration['security_key']);// Hash of the combination of IP Address + dokeos security key

// Testing DokeosWSUserExists

$params = array('login_name'   => 'admin',
                         'secret_key' => $secret_key);
$result = $client->call('DokeosWSUserExists', $params);


// Testing DokeosWSGetUserInfo
/*
$params = array('login_name'   => 'admin',
                         'secret_key' => $secret_key);
$result = $client->call('DokeosWSGetUserInfo', $params);
*/

// Testing DokeosWSGetUsersList
/*$params = array('all_info'   => true,
                         'secret_key' => $secret_key);
$result = $client->call('DokeosWSGetUsersList', $params);
*/

// Testing DokeosWSSessionExists
/*$params = array('session_id'   => '1',
                         'secret_key' => $secret_key);
$result = $client->call('DokeosWSSessionExists', $params);
*/

// Testing DokeosWSGetSessionList
/*$params = array('secret_key' => $secret_key);
$result = $client->call('DokeosWSGetSessionList', $params);
*/

//  Testing DokeosWSGetSessionInfo
/*$params = array('session_id' => '1','secret_key' => $secret_key);
$result = $client->call('DokeosWSGetSessionInfo', $params);
*/

//  Testing DokeosWSGetSubscribedUsersToSession
/*$params = array('session_id' => '1','secret_key' => $secret_key);
$result = $client->call('DokeosWSGetSubscribedUsersToSession', $params);
*/

//  Testing DokeosWSGetUrlToPermitSSO
/*$params = array('login_name' => 'admin','secret_key' => $secret_key);
$result = $client->call('DokeosWSGetUrlToPermitSSO', $params);*/

//  Testing DokeosWSCreateUser
/*
$usersParams = array('firstname' => 'Isaac',
    'lastname' => 'Flores',
    'status' => '5', // STUDENT
    'email' => 'florespaz_isaac@hotmail.com',
    'loginname' => 'iflores',
    'password' => '#ffG567a',
    'language' => 'english',
    'phone' => '00000000',
    'expiration_date' => '0000-00-00',
    'original_user_id_name' => 'uid',
    'original_user_id_value' => '50', // The user ID of an user into of the webservice consumer(client)
    'secret_key' => $secret_key);
$result = $client->call('DokeosWSCreateUser', array('createUser' => $usersParams));
*/

//  Testing DokeosWSDeleteUser

/*$usersParams = array(
    'original_user_id_name' => 'uid',
    'original_user_id_value' => '50', // The user ID of an user into of the webservice consumer(client)
    'secret_key' => $secret_key);

$result = $client->call('DokeosWSDeleteUser', array('deleteUser' => $usersParams));*/

//  Testing DokeosWSCreateSession
/*$addSessionParams = array(
    'name' => 'My first session',
    'year_start' => '2010',
    'month_start' => '11',
    'day_start' => '4',
    'year_end' => '2010',
    'month_end' => '12',
    'day_end' => '25',
    'nb_days_access_before' => '0',
    'nb_days_access_after' => '0',
    'nolimit' => '',
    'user_id' => '',
    'original_session_id_name' => 'session_id',
    'original_session_id_value' => '1' // The numeric value that will be used for the client(consumer) for identify to a session
    );

$createSessionParamList = array('createSessionParamList' => $addSessionParams);
$result = $client->call('DokeosWSCreateSession', array('createSession' => array('sessions' => $createSessionParamList,
    'secret_key' => $secret_key)));*/

//  Testing DokeosWSCreateSession
/*
$originalUsersList = array(50);// The user ID into the customer application
$subscribeUsersToSessionParams = array(
    'original_user_id_values' => $originalUsersList,
    'original_user_id_name' => 'uid',
    'original_session_id_value' => '1',
    'original_session_id_name' => 'session_id'
    );
$subscribeUsersToSessionParamsList = array('subscribeUsersToSessionParamsList' => $subscribeUsersToSessionParams);
$result = $client->call('DokeosWSSuscribeUsersToSession', array('subscribeUsersToSession' => array('userssessions' => $subscribeUsersToSessionParamsList,
    'secret_key' => $secret_key)));
*/
// Check for an error
$err = $client->getError();
if ($err) {
    // Display the error
    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}

if ($client->fault) {
    echo '<h2>Fault</h2><pre>';
    print_r($result);
    echo '</pre>';
} else {
    // Check for errors
    $err = $client->getError();
    if ($err) {
        // Display the error
        echo '<h2>Error</h2><pre>' . $err . '</pre>';
    } else {
        // Display the result
        echo '<h2>Result</h2><pre/>';
        var_dump($result);

    }
}