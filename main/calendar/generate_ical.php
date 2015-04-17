<?php
// including the global file that gets the general configuration, the databases, the languages, ...
include ('../inc/global.inc.php');

// additional libraries
require (api_get_path ( LIBRARY_PATH ) . 'groupmanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');
require ('functions.php');

$lib_path = api_get_path(LIBRARY_PATH);
require_once ($lib_path . '/display.lib.php');

// google calendar import
if (api_get_setting('calendar_google_import')=='true'){
	require_once ('google.inc.php');
}

$event_info = get_course_agenda_item($_GET['id']);
if ($_GET['type'] == '' || $_GET['type'] == 'ics') {
    $file['ext'] = '.ics';
    $file['type'] = 'text/Calendar';
    $file['version'] = '2.0';
} else if($_GET['type'] == 'vcs') {
    $file['ext'] = '.vcs';
    $file['type'] = 'text/x-vCalendar';
    $file['version'] = '1.0';
}
$userInfo = api_get_user_info(api_get_user_id());
$Filename = "Event_" .date('Ymd')."-".intval($_GET['id']) . $file['ext'];
$DescDump = str_replace("\r", "=0D=0A=", $event_info['content']);
$iCalStart = date("Ymd\THi00", $event_info['start_full']);
$iCalEnd = date("Ymd\THi00", $event_info['end_full']);
$old_iCalStart = date("Ymd\THi00", "2011/01/01 00:00:00");
$sender_name = api_get_person_name($userInfo['firstname'], $userInfo['lastname'], null, PERSON_NAME_EMAIL_ADDRESS);
$email_admin = $userInfo['mail'];
    
if ($_GET['type'] == 'ics') {
    header("Content-Type: " . $file['type'] . "; charset=UTF-8");
    header("Content-Disposition: inline; filename=$Filename");
    
    $ical_file = "";
    $ical_file .= "BEGIN:VCALENDAR\n";
    $ical_file .= "PRODID:-//Microsoft Corporation//Outlook 10.0 MIMEDIR//EN\n";
    $ical_file .= "VERSION:". $file['version'] . "\n";
    $ical_file .= "METHOD:REQUEST". "\n";
   // Begin VTIMEZONE
    $ical_file .= "BEGIN:VTIMEZONE\n";
    $ical_file .= "TZID:".date_default_timezone_get().""."\n";
    // Begin standar
    $ical_file .= "BEGIN:STANDARD\n";
    $ical_file .= "DTSTART:".$old_iCalStart."\n";
    $ical_file .= "RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10\n";
    $ical_file .= "TZOFFSETFROM:+0200\n";
    $ical_file .= "TZOFFSETTO:+0100\n";
    $ical_file .= "TZNAME:Standard Time\n";
    $ical_file .= "END:STANDARD\n";
    // End standar
    // Begin daililight
    $ical_file .= "BEGIN:DAYLIGHT\n";
    $ical_file .= "DTSTART:".$old_iCalStart."\n";
    $ical_file .= "RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3\n";
    $ical_file .= "TZOFFSETFROM:+0100\n";
    $ical_file .= "TZOFFSETTO:+0200\n";
    $ical_file .= "TZNAME:Daylight Savings Time\n";
    $ical_file .= "END:DAYLIGHT\n";
    // End daily light
    $ical_file .= "END:VTIMEZONE\n";
    // end VTIMEZONE
    
    // Start BEGIN:VEVENT
    $ical_file .= "BEGIN:VEVENT\n";
    $ical_file .= "ATTENDEE;CN='{StudentFullName}';ROLE=REQ-PARTICIPANT;RSVP=TRUE:mailto:{StudentEmail}\n";
    $ical_file .= "ORGANIZER;CN='" . $sender_name . "':mailto:" . $email_admin . "\n";
    $ical_file .= "DTSTART;TZID='".date_default_timezone_get()."':" . $iCalStart . "\n";
    $ical_file .= "DTEND;TZID= '".date_default_timezone_get()."':" . $iCalEnd . "\n";
    $ical_file .= "LOCATION:".  api_get_path(WEB_PATH)."\n";
    $ical_file .= "TRANSP:OPAQUE\n";
    $ical_file .= "SEQUENCE:1\n";
    $ical_file .= "UID:".md5(uniqid(mt_rand(), true))."-dokeos.com \n";
    $ical_file .= "DTSTAMP:".$iCalEnd."\n";
    $ical_file .= "DESCRIPTION:" . $DescDump . "\n";
    $ical_file .= "SUMMARY:" . $event_info['title'] . "\n";
    $ical_file .= "PRIORITY:5\n";
    $ical_file .= "CLASS:PUBLIC\n";
    $ical_file .= "BEGIN:VALARM\n";
    $ical_file .= "TRIGGER:-PT5M\n";
    $ical_file .= "ACTION:DISPLAY\n";
    $ical_file .= "DESCRIPTION:Reminder\n";
    $ical_file .= "END:VALARM\n";
    $ical_file .= "END:VEVENT\n";
    $ical_file .= "END:VCALENDAR\n";
    echo $ical_file;
} else {
    $ical_file = "";
    $ical_file .= "BEGIN:VCALENDAR\n";
    $ical_file .= "PRODID:-//Microsoft Corporation//Outlook 10.0 MIMEDIR//EN\n";
    $ical_file .= "VERSION:". $file['version'] . "\n";
    $ical_file .= "METHOD:REQUEST". "\n";
   // Begin VTIMEZONE
    $ical_file .= "BEGIN:VTIMEZONE\n";
    $ical_file .= "TZID:".date_default_timezone_get().""."\n";
    // Begin standar
    $ical_file .= "BEGIN:STANDARD\n";
    $ical_file .= "DTSTART:".$old_iCalStart."\n";
    $ical_file .= "RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10\n";
    $ical_file .= "TZOFFSETFROM:+0200\n";
    $ical_file .= "TZOFFSETTO:+0100\n";
    $ical_file .= "TZNAME:Standard Time\n";
    $ical_file .= "END:STANDARD\n";
    // End standar
    // Begin daililight
    $ical_file .= "BEGIN:DAYLIGHT\n";
    $ical_file .= "DTSTART:".$old_iCalStart."\n";
    $ical_file .= "RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3\n";
    $ical_file .= "TZOFFSETFROM:+0100\n";
    $ical_file .= "TZOFFSETTO:+0200\n";
    $ical_file .= "TZNAME:Daylight Savings Time\n";
    $ical_file .= "END:DAYLIGHT\n";
    // End daily light
    $ical_file .= "END:VTIMEZONE\n";
    // end VTIMEZONE
    
    // Start BEGIN:VEVENT
    $ical_file .= "BEGIN:VEVENT\n";
    $ical_file .= "ATTENDEE;CN='{StudentFullName}';ROLE=REQ-PARTICIPANT;RSVP=TRUE:mailto:{StudentEmail}\n";
    $ical_file .= "ORGANIZER;CN='" . $sender_name . "':mailto:" . $email_admin . "\n";
    $ical_file .= "DTSTART;TZID='".date_default_timezone_get()."':" . $iCalStart . "\n";
    $ical_file .= "DTEND;TZID= '".date_default_timezone_get()."':" . $iCalEnd . "\n";
    $ical_file .= "LOCATION:".  api_get_path(WEB_PATH)."\n";
    $ical_file .= "TRANSP:OPAQUE\n";
    $ical_file .= "SEQUENCE:1\n";
    $ical_file .= "UID:".md5(uniqid(mt_rand(), true))."-dokeos.com \n";
    $ical_file .= "DTSTAMP:".$iCalEnd."\n";
    $ical_file .= "DESCRIPTION:" . $DescDump . "\n";
    $ical_file .= "SUMMARY:" . $event_info['title'] . "\n";
    $ical_file .= "PRIORITY:5\n";
    $ical_file .= "CLASS:PUBLIC\n";
    $ical_file .= "BEGIN:VALARM\n";
    $ical_file .= "TRIGGER:-PT5M\n";
    $ical_file .= "ACTION:DISPLAY\n";
    $ical_file .= "DESCRIPTION:Reminder\n";
    $ical_file .= "END:VALARM\n";
    $ical_file .= "END:VEVENT\n";
    $ical_file .= "END:VCALENDAR\n";
    return $ical_file;
}