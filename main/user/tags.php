<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.user
==============================================================================
*/

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';

echo UserManager::get_tags($_GET['tag'], intval($_GET['field_id']),'json','10');
