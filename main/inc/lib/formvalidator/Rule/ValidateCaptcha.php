<?php
/* For licensing terms, see /dokeos_license.txt */

require_once 'HTML/QuickForm/Rule.php';

/**
 * QuickForm rule to check if a username is of the correct format
 */
class HTML_QuickForm_Rule_ValidateCaptcha extends HTML_QuickForm_Rule {
	/**
	 * Function to check if a username is of the correct format
	 * @see HTML_QuickForm_Rule
	 * @param  string   captcha text
	 * @return boolean  True if text is correct
	 */
	function validate($value) {
            if (api_get_setting('captcha') == 'true'){
		// captcha check
		if (md5($value) <> $_SESSION['captcha']){
			return false;
		}
            }
            return true;
	}
}

