<?php
// $Id: html_editor.php 22096 2009-07-15 03:43:25Z ivantcholakov $
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
$currentEditor = api_get_setting('use_default_editor');
switch (strtolower($currentEditor) )
{
    case 'fckeditor':
        require 'elementlibs/FckEditorFactory.php';
    break;
    case 'ckeditor':
         require 'elementlibs/CkEditorFactory.php';
        break;
}
