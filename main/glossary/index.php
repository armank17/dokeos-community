<?php //$id: $
/* For licensing terms, see /dokeos_license.txt */
/**
 * @package dokeos.glossary
 * @author Christian Fasanando, initial version
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium, refactoring and tighter integration in Dokeos
 */

// name of the language file that needs to be included
$language_file = array('glossary');
define('DOKEOS_GLOSSARY', true);

// including the global dokeos file
require_once('../inc/global.inc.php');

// redirect to mvc pattern (temporally)
header('Location: '.api_get_path(WEB_VIEW_PATH).'glossary/index.php?'.api_get_cidreq());
exit;