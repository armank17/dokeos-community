<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Admin products
 *
 * Copyright (C) 2006 - 2012 Admin Products
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   Admin
 * @package    dokeos.admin
 * @copyright  Copyright (c) 2006 - 2012 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.7, 2012-05-19
 */

// Language files that should be included
//$language_file=array('admin','tracking');

// resetting the course id
$cidReset=true;

// setting the help
$help_content = 'platformadministration';

// we are not inside a course, so we reset the course id
// $cidReset = true;

// including the global file that gets the general configuration, the databases, the languages, ...
include ('../inc/global.inc.php');

require_once ('../course_home/course_home_functions.php');
require ('product_functions.php');

api_protect_admin_script();

$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function(){        
    });
</script>';
$htmlHeadXtra[] = '<style type="text/css">
	</style>';

// setting the name of the tool
$nameTools = get_lang('PlatformAdmin');

// setting breadcrumbs
//$interbreadcrumb[] = array('url' => 'index.php', 'name' => $nameTools);

// Displaying the header
Display::display_header($nameTools);

echo '<div class="actions">';
echo '<a href="'.api_get_self().'?action=add'.'">' . Display::return_icon('pixel.gif',get_lang('AddProduct'), array('class' => 'toolactionplaceholdericon toolactionaddprogramme')) . get_lang('AddProduct') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/tool_list.php">' . Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('ListTool') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/product_user_list.php">' . Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('ProductListUser') . '</a>';
echo '</div>';


// open the content div
echo '<div id="content">';
//$table->display();

// close the content div
echo '<div class="clear"> </div>';
echo '</div>';

// close the content div
echo '<div class="clear"> </div>';
echo '</div>';
// display the footer
Display :: display_footer();
?>
