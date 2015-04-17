<?php // $Id: download.php 22201 2009-07-17 19:57:03Z cfasanando $

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	This file is responsible for  passing requested documents to the browser.
*	Html files are parsed to fix a few problems with URLs,
*	but this code will hopefully be replaced soon by an Apache URL
*	rewrite mechanism.
*
*	@package dokeos.document
==============================================================================
*/

session_cache_limiter('none');

require_once '../inc/global.inc.php';
api_not_allowed(true);
exit;