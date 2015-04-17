<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.admin
*/

// Language files that should be included
$language_file='admin';

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationcoursecategory';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();

// Setting the breadcrumbs
$interbreadcrumb[]=array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));
//$interbreadcrumb[]=array('url' => 'configure_homepage.php',"name" => get_lang('ConfigureHomePage'));
//alerts
//$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.css" />';
//$htmlHeadXtra[] = '<script  type="text/javascript" src="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).on("ready",Jload);
    function Jload(){


//            window.parent.$.confirm("confirmMsn", "asdasd", function() {
////                $.get(url, function(data){
////                    //$("#images").html(data);
////                });
//            }, false);

        }
</script>';
// Database table definitions
$tbl_course  	= Database::get_main_table(TABLE_MAIN_COURSE);
$tbl_category 	= Database::get_main_table(TABLE_MAIN_CATEGORY);

// variable initialisation
$category=$_GET['category'];
$action=$_GET['action'];
$errorMsg='';

// action handling
if(!empty($action))
{
	if($action == 'delete')
	{
		deleteNode($_GET['id']);

		header('Location: '.api_get_self().'?category='.Security::remove_XSS($category).( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ) );
		exit();
	}
	elseif(($action == 'add' || $action == 'edit') && $_POST['formSent'])
	{
		$_POST['categoryCode']=trim($_POST['categoryCode']);
		$_POST['categoryName']=trim($_POST['categoryName']);

		if(!empty($_POST['categoryCode']) && !empty($_POST['categoryName']))
		{
			if($action == 'add')
			{
				$ret=addNode($_POST['categoryCode'],$_POST['categoryName'],$_POST['canHaveCourses'],$category);
			}
			else
			{
				$ret=editNode($_POST['categoryCode'],$_POST['categoryName'],$_POST['canHaveCourses'],$_GET['id']);
			}

			if($ret)
			{
				$action='';
			}
			else
			{
				$errorMsg=get_lang('CatCodeAlreadyUsed');
			}
		}
		else
		{
			$errorMsg=get_lang('PleaseEnterCategoryInfo');
		}
	}
	elseif($action == 'edit')
	{
		$categoryCode=Database::escape_string($_GET['id']);

		$result=Database::query("SELECT name,auth_course_child FROM $tbl_category WHERE code='$categoryCode'",__FILE__,__LINE__);

		list($categoryName,$canHaveCourses)=Database::fetch_row($result);

		$canHaveCourses=($canHaveCourses == 'FALSE')?0:1;
	}
	elseif($action == 'moveUp')
	{
		moveNodeUp($_GET['id'],$_GET['tree_pos'],$category);

		header('Location: '.api_get_self().'?category='.Security::remove_XSS($category). ( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ));
		exit();
	}
}

// Display the header
Display::display_header(get_lang('AdminCategories'));

// Display the tool title
//api_display_tool_title(get_lang('AdminCategories'));

if(!empty($category))
{
	$myquery = "SELECT * FROM $tbl_category WHERE code ='$category'";
	$result	= Database::query($myquery,__FILE__,__LINE__);
	if(Database::num_rows($result)==0)
	{
		$category = '';
	}
}

if(empty($action))
{
	$myquery="SELECT t1.name,t1.code,t1.parent_id,t1.tree_pos,t1.children_count,COUNT(DISTINCT t3.code) AS nbr_courses FROM $tbl_category t1 LEFT JOIN $tbl_category t2 ON t1.code=t2.parent_id LEFT JOIN $tbl_course t3 ON t3.category_code=t1.code WHERE t1.parent_id ".(empty($category)?"IS NULL":"='$category'")." GROUP BY t1.name,t1.code,t1.parent_id,t1.tree_pos,t1.children_count ORDER BY t1.tree_pos";
	$result=Database::query($myquery,__FILE__,__LINE__);

	$Categories=Database::store_result($result);
}




if($action == 'add' || $action == 'edit')
{
	// Actions
        echo '<div class="actions">';
        if(isset($_GET['origin'])){
            echo '<a href="'.api_get_self().'?category='.Security::remove_XSS($category) . ( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ) .'">'.Display::return_icon('pixel.gif',get_lang('Back'),array('class'=>'toolactionplaceholdericon toolactionback')).get_lang("Back"); 
	if(!empty($category)){
		echo ' ('.Security::remove_XSS($category).')';
	}
	echo '</a>';
        }else{
            CourseManager::show_menu_course_admin('category');
        }
	
	echo '</div>';

	// start the content div
	echo '<div id="content">';
    if(!isset($_GET['origin'])){
        if($action == 'add' || $action == 'edit'){
            echo '<div class="noorigin" style="width:100%;margin-bottom: 20px;">';
                 echo '<a href="'.api_get_self().'?category='.Security::remove_XSS($category) . ( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ) .'">'. get_lang("Back"); 
                if(!empty($category)){
                        echo ' ('.Security::remove_XSS($category).')';
                }
                echo '</a>';
                echo '</div>';
            }
    }
    
            
    if(!empty($errorMsg))
	{
		echo '<div class="normal-message new-message2">'.$errorMsg.'</div>';
	}

	$form_title = ($action == 'add')?get_lang('AddACategory'):get_lang('EditNode');
	if(!empty($category))
	{
		$form_title .= ' '.get_lang('Into').' '.Security::remove_XSS($category);
	}

	$form = new FormValidator('course_category');
	$form->addElement('header', '', $form_title);
	$form->display();


	echo '<form method="post" action="'.api_get_self().'?action='.Security::remove_XSS($action).'&amp;category='.Security::remove_XSS($category).'&amp;id='.Security::remove_XSS($_GET['id']). ( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ) .'">';
	echo '<input type="hidden" name="formSent" value="1" />';
	echo '<table border="0" cellpadding="5" cellspacing="0">';

	
	echo '
		<tr>
		  <td nowrap="nowrap">'.get_lang("CategoryCode").':</td>
		  <td><input type="text" name="categoryCode" size="20" class="focus" maxlength="20" value="'.api_htmlentities(stripslashes($categoryCode),ENT_QUOTES,$charset).'" /></td>
		</tr>';

	echo '
		<tr>
		  <td nowrap="nowrap">'.get_lang("CategoryName").':</td>
		  <td><input type="text" name="categoryName" size="20" maxlength="100" value="'.api_htmlentities(stripslashes($categoryName),ENT_QUOTES,$charset).'" /></td>
		</tr>
		<tr>
		  <td nowrap="nowrap">'.get_lang("AllowCoursesInCategory").'</td>
		  <td>
			<input class="checkbox" type="radio" name="canHaveCourses" value="0"';
			if(($action == 'edit' && !$canHaveCourses) || ($action == 'add' && $formSent && !$canHaveCourses)){
				echo 'checked="checked"';
			}
	echo '/>'.get_lang("No");
	echo '	<input class="checkbox" type="radio" name="canHaveCourses" value="1"';
			if (($action == 'edit' && $canHaveCourses) || ($action == 'add' && !$formSent || $canHaveCourses)){ 
				echo 'checked="checked"';
			}
	echo '/>'.get_lang("Yes");
	echo '</td>
		</tr>';
	echo '
		<tr>
		  <td>&nbsp;</td>';
  	if(isset($_GET['id']) && !empty($_GET['id'])) {
			$class="save";
			$text=get_lang('CategoryMod');
		} else {
			$class="add";
			$text=get_lang('AddCategory');
		}
	echo '	<td><button type="submit" class="'.$class.'" value="'.$text.'" >'.$text.'</button></td>
		</tr>';
	echo '
		</table>
	</form>';
}
else
{
	// actions
	echo '<div class="actions">';
        if(isset($_GET['origin'])){
            if(!empty($category) && empty($action)){
		$myquery = "SELECT parent_id FROM $tbl_category WHERE code='$category'";
		$result=Database::query($myquery,__FILE__,__LINE__);
		$parent_id = 0;
		if(Database::num_rows($result)>0){
			$parent_id=Database::fetch_array($result);
		}

		$parent_id['parent_id']?$link=' ('.$parent_id['parent_id'].')':$link='';
                    echo '<a href="'.api_get_self().'?category='.$parent_id['parent_id']. ( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ) .'">'.Display::return_icon('pixel.gif',get_lang('Back'),array('class'=>'toolactionplaceholdericon toolactionback')).get_lang("Back");
		if(!empty($parent_id)) echo $link;
		echo '</a>';
	}

        echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/configure_homepage.php">'.Display::return_icon('pixel.gif',get_lang('Back'),array('class' => 'toolactionplaceholdericon toolactionhomepage')).get_lang('HomePage').'</a>';
            echo '<a href="'.api_get_self().'?category='.Security::remove_XSS($category). ( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ) .'&action=add">'.Display::return_icon('pixel.gif',get_lang('AddACategory'), array('class' => 'toolactionplaceholdericon toolactioncreatefolder')).get_lang("AddACategory");
	if(!empty($category)) echo ' '.get_lang('Into').' '.Security::remove_XSS($category);
	echo '</a>';
            
            
            
        }else{
            CourseManager::show_menu_course_admin('category');
        }
        
	
	echo '</div>';

	// start the content div
	echo '<div id="content">';
//        echo '<a class="alertttt" href="'.api_get_path(WEB_CODE_PATH).'admin/configure_homepage.php">Eddd</a>';
        if(!isset($_GET['origin'])){
            
            echo '<div class="noorigin" style="width:100%;margin-bottom: 20px;">';
            if(!empty($category) && empty($action)){
                    $myquery = "SELECT parent_id FROM $tbl_category WHERE code='$category'";
                    $result=Database::query($myquery,__FILE__,__LINE__);
                    $parent_id = 0;
                    if(Database::num_rows($result)>0){
                            $parent_id=Database::fetch_array($result);
                    }

                    $paddinl = 'style="margin-left: 15px;"';
                    $parent_id['parent_id']?$link=' ('.$parent_id['parent_id'].')':$link='';
                    echo '<a href="'.api_get_self().'?category='.$parent_id['parent_id']. ( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ) .'">'. get_lang("Back");
                    if(!empty($parent_id)) echo $link;
                    echo '</a>';
                    
            }


            
            echo '<a '.$paddinl.'  href="'.api_get_self().'?category='.Security::remove_XSS($category). ( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ) .'&action=add">'.get_lang("AddACategory");
            if(!empty($category)) echo ' '.get_lang('Into').' '.Security::remove_XSS($category);
            echo '</a>';
            echo '</div>';
        }
	if(count($Categories)>0)
	{
		foreach($Categories as $enreg)
		{
		?>
		  <div>
			<a href="<?php echo api_get_self(); ?>?category=<?php echo Security::remove_XSS($enreg['code']).( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ); ?>"><?php Display::display_icon('pixel.gif', get_lang('OpenNode'),array('class'=>'actionplaceholdericon actionnewfolder22')); ?></a>
			<a href="<?php echo api_get_self(); ?>?category=<?php echo Security::remove_XSS($category); ?>&amp;action=edit&amp;id=<?php echo Security::remove_XSS($enreg['code']) .( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ); ?>"><?php echo Display::return_icon('pixel.gif', get_lang('EditNode'), array('class' => 'actionplaceholdericon actionedit')); ?></a>
			<!-- <a href="<:?php echo api_get_self(); ?>?category=<:?php echo Security::remove_XSS($category); ?>&amp;action=delete&amp;id=<:?php echo Security::remove_XSS($enreg['code']) . ( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ); ?>" onclick="javascript:if(!confirm('<?php echo addslashes(api_htmlentities(get_lang('ConfirmYourChoice'),ENT_QUOTES,$charset)); ?>')) return false;"><?php echo Display::return_icon('pixel.gif', get_lang('DeleteNode'), array('class' => 'actionplaceholdericon actiondelete'));?></a> -->
                        <?php
                        $link = api_get_self(). '?category='. Security::remove_XSS($category).'&amp;action=delete&amp;id='.Security::remove_XSS($enreg['code']) . ( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') );

                        ?>
                        <a href="javascript:void(0);" <?php echo "onclick='Alert_Confim_Delete(\"".$link."\",\"".get_lang('ConfirmationDialog')."\",\"".get_lang('ConfirmYourChoice')."\");'" ?>><?php echo Display::return_icon('pixel.gif', get_lang('DeleteNode'), array('class' => 'actionplaceholdericon actiondelete'));?></a>
                        <?php
                        //echo "<a class='action' href='javascript:void(0);' onclick='Jalert(\"".$link."\",\"".$lang."\",\"".$title."\");' >".Display::return_icon('pixel.gif',get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete'))."</a>";
                        ?>
			<a href="<?php echo api_get_self(); ?>?category=<?php echo Security::remove_XSS($category); ?>&amp;action=moveUp&amp;id=<?php echo Security::remove_XSS($enreg['code']). ( (isset($_GET['origin'])) ? ('&origin='. $_GET['origin']) : ('') ); ?>&amp;tree_pos=<?php echo $enreg['tree_pos']; ?>"><?php echo Display::return_icon('pixel.gif', get_lang('UpInSameLevel'), array('class' => 'actionplaceholdericon actionup'));?></a>
			<?php echo $enreg['name']; ?>
			(<?php echo $enreg['children_count']. ' '.get_lang('langCategories'); ?> - <?php echo $enreg['nbr_courses']; ?> <?php echo get_lang("Courses"); ?>)
		  </div>
		<?php
		}
		unset($Categories);
	}
	else
	{
		echo get_lang('NoCategories');
	}
}

// Display the footer
Display::display_footer();


function deleteNode($node)
{
	global $tbl_category, $tbl_course;
	$node = Database::escape_string($node);

	$result=Database::query("SELECT parent_id,tree_pos FROM $tbl_category WHERE code='$node'",__FILE__,__LINE__);

	if($row=Database::fetch_array($result))
	{
		if(!empty($row['parent_id']))
		{
			Database::query("UPDATE $tbl_course SET category_code='".$row['parent_id']."' WHERE category_code='$node'",__FILE__,__LINE__);
			Database::query("UPDATE $tbl_category SET parent_id='".$row['parent_id']."' WHERE parent_id='$node'",__FILE__,__LINE__);
		}
		else
		{
			Database::query("UPDATE $tbl_course SET category_code='' WHERE category_code='$node'",__FILE__,__LINE__);
			Database::query("UPDATE $tbl_category SET parent_id=NULL WHERE parent_id='$node'",__FILE__,__LINE__);
		}

		Database::query("UPDATE $tbl_category SET tree_pos=tree_pos-1 WHERE tree_pos > '".$row['tree_pos']."'",__FILE__,__LINE__);
		Database::query("DELETE FROM $tbl_category WHERE code='$node'",__FILE__,__LINE__);

		if(!empty($row['parent_id']))
		{
			updateFils($row['parent_id']);
		}
	}
}

function addNode($code,$name,$canHaveCourses,$parent_id)
{
	global $tbl_category;

	$canHaveCourses=$canHaveCourses?'TRUE':'FALSE';
	$code 			= Database::escape_string($code);
	$name 			= Database::escape_string($name);
	$parent_id		= Database::escape_string($parent_id);

	$result=Database::query("SELECT 1 FROM $tbl_category WHERE code='$code'",__FILE__,__LINE__);

	if(Database::num_rows($result))
	{
		return false;
	}

	$result=Database::query("SELECT MAX(tree_pos) AS maxTreePos FROM $tbl_category",__FILE__,__LINE__);

	$row=Database::fetch_array($result);

	$tree_pos=$row['maxTreePos']+1;

	Database::query("INSERT INTO $tbl_category(name,code,parent_id,tree_pos,children_count,auth_course_child) VALUES('$name','$code',".(empty($parent_id)?"NULL":"'$parent_id'").",'$tree_pos','0','$canHaveCourses')",__FILE__,__LINE__);

	updateFils($parent_id);

	return true;
}

function editNode($code,$name,$canHaveCourses,$old_code)
{
	global $tbl_category;

	$canHaveCourses=$canHaveCourses?'TRUE':'FALSE';
	$code 			= Database::escape_string($code);
	$name 			= Database::escape_string($name);
	$old_code 		= Database::escape_string($old_code);

	if($code != $old_code)
	{
		$result=Database::query("SELECT 1 FROM $tbl_category WHERE code='$code'",__FILE__,__LINE__);

		if(Database::num_rows($result))
		{
			return false;
		}
	}

	Database::query("UPDATE $tbl_category SET name='$name',code='$code',auth_course_child='$canHaveCourses' WHERE code='$old_code'",__FILE__,__LINE__);

	$sql = "UPDATE $tbl_category SET parent_id = '$code' WHERE parent_id = '$old_code'";
	Database::query($sql,__FILE__,__LINE__);

	return true;
}

function moveNodeUp($code,$tree_pos,$parent_id)
{
	global $tbl_category;
	$code 		= Database::escape_string($code);
	$tree_pos 	= Database::escape_string($tree_pos);
	$parent_id	= Database::escape_string($parent_id);

	$result=Database::query("SELECT code,tree_pos FROM $tbl_category WHERE parent_id ".(empty($parent_id)?"IS NULL":"='$parent_id'")." AND tree_pos<'$tree_pos' ORDER BY tree_pos DESC LIMIT 0,1",__FILE__,__LINE__);

	if(!$row=Database::fetch_array($result))
	{
		$result=Database::query("SELECT code,tree_pos FROM $tbl_category WHERE parent_id ".(empty($parent_id)?"IS NULL":"='$parent_id'")." AND tree_pos>'$tree_pos' ORDER BY tree_pos DESC LIMIT 0,1",__FILE__,__LINE__);

		if(!$row=Database::fetch_array($result))
		{
			return false;
		}
	}

	Database::query("UPDATE $tbl_category SET tree_pos='".$row['tree_pos']."' WHERE code='$code'",__FILE__,__LINE__);
	Database::query("UPDATE $tbl_category SET tree_pos='$tree_pos' WHERE code='$row[code]'",__FILE__,__LINE__);
}

function updateFils($category)
{
	global $tbl_category;
	$category = Database::escape_string($category);
	$result=Database::query("SELECT parent_id FROM $tbl_category WHERE code='$category'",__FILE__,__LINE__);

	if($row=Database::fetch_array($result))
	{
		updateFils($row['parent_id']);
	}

	$children_count=compterFils($category,0)-1;

	Database::query("UPDATE $tbl_category SET children_count='$children_count' WHERE code='$category'",__FILE__,__LINE__);
}

function compterFils($pere,$cpt)
{
	global $tbl_category;
	$pere = Database::escape_string($pere);
	$result=Database::query("SELECT code FROM $tbl_category WHERE parent_id='$pere'",__FILE__,__LINE__);

	while($row=Database::fetch_array($result))
	{
		$cpt=compterFils($row['code'],$cpt);
	}

	return ($cpt+1);
}
?>
