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
$language_file=array('admin','tracking');

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
		$("#tabss").tabs();
        $("ul.categories" ).sortable({
            placeholder: "placeholder",
            opacity: 0.6,
            update: function(event, ui){
                var info = $(this).sortable("serialize");
                $.ajax({
                    url: "product_ajax.php?"+info,
                    data: {action: "update_order", type: "category"}
                });
            }
        });
        
        $("ul.items").sortable({
            connectWith: "ul.items",
            placeholder: "placeholder",
            opacity: 0.6,
            update: function(event, ui){
                var info = $(this).sortable("serialize");
                var category = $(this).parent().attr("id").replace("category_","");
                $.ajax({
                    url: "product_ajax.php?"+info,
                    data: {action: "update_order", type: "tool", key: category}
                });
            }
        });
        
        $("#product_edit").toggle(function() {
            $("#tabss").show("blind", 1000);
        }, function() {
            $("#tabss").hide("blind", 1000);
        });
        
        $(".product_check").change(function(){
            var product_id = $(this).val();
            var status = 0;
            if($(this).is(":checked"))
            {
                status = 1;
            }
            $.ajax({
                url: "product_ajax.php",
                data: {action: "update_product_status", id: product_id, status: status}
            });
        });
        
        $("#add_product").live("click", function(e){
            e.preventDefault();
            $("#dialog-item-form").dialog({modal: true, title: "'.get_lang("ProductForm").'", width: "700px"});
        });
        
        $(".save_tool").click(function(e){
            e.preventDefault();
            $("#category_id").val($(this).attr("rel"));
            $("#dialog-tool-form").dialog({modal: true, title: "'.get_lang("ToolForm").'", width: "600px"});
        });
        
    });
</script>';
$htmlHeadXtra[] = '<style type="text/css">
		ul.categories ul li {
            margin-left: 30px;
        }
        ul.items {
            min-height: 10px;
            list-style: none;
            margin: 0 0 10px;
            padding: 0;
        }
        ul.categories {
            list-style: none outside none;
            margin: 0;
            padding: 0;
        }
        .handler {
            border: 1px solid #d4d4d4;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
            border-color: #D4D4D4 #D4D4D4 #BCBCBC;
            padding: 0 5px;
            margin: 0;
            cursor: move;
            background: #f6f6f6;
            background: -moz-linear-gradient(top,  #ffffff 0%, #f6f6f6 47%, #ededed 100%);
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(47%,#f6f6f6), color-stop(100%,#ededed));
            background: -webkit-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
            background: -o-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
            background: -ms-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
            background: linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
            filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#ffffff", endColorstr="#ededed",GradientType=0 );
        }
        .placeholder {
            border: 1px dashed #4183C4;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
            min-height:30px;
        }
        #product_list_check p {
            margin: 0;
        }
        div.row {
            margin: 0 auto;
            padding: 8px 0 0 !important;
            width: 95%;
        }
	</style>';
$nameTools = get_lang('PlatformAdmin');

// setting breadcrumbs
//$interbreadcrumb[] = array('url' => 'index.php', 'name' => $nameTools);

// setting the name of the tool
$tool_name=get_lang('PlatformAdmin');

// Displaying the header
Display::display_header($nameTools);

echo '<div class="actions">';
echo '<a href="'.api_get_self().'?action=add'.'">' . Display::return_icon('pixel.gif',get_lang('AddProduct'), array('class' => 'toolactionplaceholdericon toolactionaddprogramme')) . get_lang('AddProduct') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/tool_list.php">' . Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('ListTool') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/product_user_list.php">' . Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('ProductListUser') . '</a>';
echo '</div>';

$products = get_product_categories();
echo '<div id="content">
    <div class="row"><div class="form_header">'.get_lang('ProductAdministrationTool').'</div></div>
    <div class="row">
        <div class="form_header">'.get_lang('ProductList').' <a id="add_product" href="#"><img src="/" alt="('.get_lang('AddProduct').')"/></a></div>
        <div id="product_list_check">';
foreach ($products as $product)
{
    $checked = ($product['status']==1)?'checked="checked"':'';
    echo '<p>
            <input type="checkbox" class="product_check" id="product_'.$product['id'].'" name="product_'.$product['id'].'" value="'.$product['id'].'" '.$checked.'/>
            <label for="product_'.$product['id'].'">'.get_lang($product['name']).'</label>
          </p>';
}
echo '  </div>
    </div>
    <div class="row">
        <div id="product_edit" class="form_header">'.get_lang('ProductEdit').'</div>
    </div>
    <div id="tabss" style="display:;">
      <ul>';
foreach ($products as $product) {
    echo '<li><a href="#tabs-'.$product['id'].'">'.$product['name'].'</a></li>';
}
echo '</ul>';

display_item_form();
display_tool_form();
foreach ($products as $product) {
$categories = get_product_categories($product['id']);
?>
<div id="tabs-<?php echo $product['id']?>" class="row">
    <ul class="categories">
    <?php
    foreach ($categories as $category)
    {
    ?>
        <li id="category_<?php echo $category['id']?>">
            <div class="handler"><table width="100%"><tr>
                <td><?php echo $category['name']?></td>
                <td width="190" align="right">
                    <?php
                    echo '<a href="#" class="save_tool" rel="'.$category['id'].'"><span>'.get_lang('Addtool').'</span></a>';
                    ?>
                    <!--
                    <a class="" href="course_edit.php?course_code=POSIBLE"><img class="actionplaceholdericon actionedit" title="Edit" alt="Edit" src="http://main.dokeos-30.net/main/img/pixel.gif"></a>
                    <a onclick="javascript:if(!confirm('Please confirm your choice')) return false;" href="course_list.php?delete_course=POSIBLE"><img class="actionplaceholdericon actiondelete" title="Delete" alt="Delete" src="/main/img/pixel.gif"></a>
                    -->
                </td>
            </tr></table></div>
            <ul class="items">
                <?php
                $tools = get_category_tools($category['id']);
                $tools_html = '';
                foreach ($tools as $tool)
                {
                    $tools_html .= '<li id="tool_'.$tool['id'].'"><div class="handler">
                        <table width="100%">
                            <tr>
                                <td>'.$tool['name'].'</td>
                                <td width="90" align="right">
                                </td>
                            </tr>
                        </table>
                    </div></li>';
//                        <a class="" href="product_list.php?tool_id='.$tool['id'].'"><img class="actionplaceholdericon actionedit" title="Edit" alt="Edit" src="'.api_get_path(WEB_PATH).'main/img/pixel.gif"></a>
//                                    <a onclick="javascript:if(!confirm(\''.get_lang('PleaseConfirmYourChoice').'\')) return false;" href="product_list.php?delete_tool='.$tool['id'].'"><img class="actionplaceholdericon actiondelete" title="Delete" alt="Delete" src="'.api_get_path(WEB_PATH).'main/img/pixel.gif"></a>
                                
                }
                echo $tools_html;
                ?>
            </ul>
        </li>
    <?php
    }
    ?>
    </ul>
</div>
<?php
}
echo '</div>';

//echo $libpath.'fileManage.lib.php'.'<hr>';

// close the content div
echo '<div class="clear"> </div>';
echo '</div>';
// display the footer
Display :: display_footer();
?>
