<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.announcements
* 	@author Frederik Vermeire <frederik.vermeire@pandora.be>, UGent University Internship
* 	@author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
==============================================================================
*/

/*
functionality that has been removed and will not be available in Dokeos 2.0
* survey announcement (badly coded)
* change the visibility of the announcement
* move announcement up or down

functionality that has been removed and has to be re-added for Dokeos 2.0
* send by email + configuration setting for the platform admin: never, always, let course admin decide
* configruation of the number of items that have to appear (jcarousel)
*/

// variables that will be converted into platform settings
// Maximum title messages to display
$maximum 	= '12';

// Language files that should be included
$language_file[] = 'announcements';
$language_file[] = 'group';
$language_file[] = 'survey';

// setting the help
$help_content = 'announcements';

// use anonymous mode when accessing this course tool
$use_anonymous = true;

// including the global Dokeos file
include('../inc/global.inc.php');

// redirect to mvc pattern (temporally)
header('Location: '.api_get_path(WEB_VIEW_PATH).'announcement/index.php?'.api_get_cidreq());
exit;