<?php
// $Id: EmailAvailable.php 10190 2012-11-15 16:44:20Z pcool $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2005 Dokeos S.A.
	Copyright (c) Bart Mollet, Hogeschool Gent

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/
require_once ('HTML/QuickForm/Rule.php');
/**
 * QuickForm rule to check if a email is available
 */
class HTML_QuickForm_Rule_EmailAvailable extends HTML_QuickForm_Rule
{
	/**
	 * Function to check if a email is available
	 * @see HTML_QuickForm_Rule
	 * @param string $email Wanted email
	 * @param string $email
	 * @return boolean True if email is available
	 */
	function validate($email,$current_email = null)
	{
		$user_table = Database::get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT * FROM $user_table WHERE email = '$email'";
		if(!is_null($current_email))
		{
			$sql .= " AND email != '$current_email'";
		}
		$res = Database::query($sql,__FILE__,__LINE__);
		$number = Database::num_rows($res);
		return $number == 0;
	}
}
?>