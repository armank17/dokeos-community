<?php

/* For licensing terms, see /license.txt */

/**
 * Work Model
 */
class LinkModel {

    // definition tables
    protected $tableLink;
    protected $tableLinkCategory;
    protected $attributes = Array();

    /**
     * Magic method
     */
    public function __get($key) {
        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : null;
    }

    /**
     * Magic method
     */
    public function __set($key, $value) {
        $this->attributes[$key] = $value;
    }

    /**
     * Constructor
     */
    public function __construct($courseDb = '') {
        $this->tableLink = Database :: get_course_table(TABLE_LINK, $courseDb);
        $this->tableLinkCategory = Database :: get_course_table(TABLE_LINK_CATEGORY, $courseDb);
    }

    public function getNumberOfLinks() {
        $sqlLinks = "SELECT * FROM {$this->tableLink} WHERE category_id=0 or category_id IS NULL";
        $result = Database::query($sqlLinks);
        $numberofzerocategory = Database::num_rows($result);
        return $numberofzerocategory;
    }

    public function getZeroCategoryLinks() {
        $session_id = api_get_session_id();
        $condition_session = api_get_session_condition($session_id, false, true);

        $TABLE_ITEM_PROPERTY = Database :: get_course_table(TABLE_ITEM_PROPERTY);

        $sqlLinks = "SELECT * FROM {$this->tableLink} link, " . $TABLE_ITEM_PROPERTY . " itemproperties WHERE itemproperties.tool='" . TOOL_LINK . "' AND link.id=itemproperties.ref AND link.category_id=0 AND (itemproperties.visibility='0' OR itemproperties.visibility='1') $condition_session ORDER BY link.display_order DESC";
        $result = Database::query($sqlLinks);
        if (Database::num_rows($result)) {
            while ($row = Database::fetch_object($result)) {
                $zerocategorylinks[$row->id] = $row;
            }
        }
        return $zerocategorylinks;
    }

    public function getDisplayActionIcons($linkId) {

        $urlview = (!empty($_GET['urlview']) ? $_GET['urlview'] : '');
        $action_icons = '<li><a href="' . api_get_path(WEB_CODE_PATH) . 'document/document.php?' . api_get_cidReq() . '">' . Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactiondocument')) . ' ' . get_lang('Documents') . '</a></li>';
        if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'addcategory' || $_REQUEST['action'] == 'editcategory' || $_REQUEST['action'] == 'addlink' || $_REQUEST['action'] == 'editlink') && $_REQUEST['done'] != 'Y') {

            $action_icons .= '<a href="index.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&urlview=' . Security::remove_XSS($_GET['urlview']) . '">' . Display::return_icon('pixel.gif', get_lang('BackToLinksOverview'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('BackToLinksOverview') . '</a>';

            if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
                $action_icons .= '<a href="../../../newscorm/lp_controller.php?' . api_get_cidreq() . '&action=add_item&type=step&lp_id=' . $_GET['lp_id'] . '">' . Display::return_icon('pixel.gif', get_lang('Content'), array('class' => 'toolactionplaceholdericon toolactionauthorcontent')) . get_lang("Content") . '</a>';
                $action_icons .= '<a href="../../../newscorm/lp_controller.php?' . api_get_cidreq() . '&action=admin_view&lp_id=' . $_GET['lp_id'] . '">' . Display::return_icon('pixel.gif', get_lang('Scenario'), array('class' => 'toolactionplaceholdericon toolactionauthorscenario')) . get_lang("Scenario") . '</a>';
            }
        } else {
            if (api_is_allowed_to_edit(null, true)) {

                $action_icons .= "<a href=\"" . api_get_self() . "?" . api_get_cidreq() . "&action=addlink&category=" . (!empty($category) ? $category : '') . "&urlview=$urlview\">" . Display::return_icon('pixel.gif', get_lang('LinkAdd'), array('class' => 'toolactionplaceholdericon toolactionslink')) . '&nbsp;&nbsp;' . get_lang("LinkAdd") . "</a>\n";
                $action_icons .= "<a href=\"" . api_get_self() . "?" . api_get_cidreq() . "&action=addcategory&urlview=" . $urlview . "\">" . Display::return_icon('pixel.gif', get_lang("Folder"), array('class' => 'toolactionplaceholdericon toolactioncreatefolder')) . '&nbsp;&nbsp;' . get_lang("Folder") . "</a>";

                if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
                    $action_icons .= '<a href="../../../newscorm/lp_controller.php?' . api_get_cidreq() . '&action=add_item&type=step&lp_id=' . Security::remove_XSS($_GET['lp_id']) . '">' . Display::return_icon('pixel.gif', get_lang('Content'), array('class' => 'toolactionplaceholdericon toolactionauthorcontent')) . get_lang("Content") . '</a>';
                    $action_icons .= '<a href="../../../newscorm/lp_controller.php?' . api_get_cidreq() . '&action=admin_view&lp_id=' . Security::remove_XSS($_GET['lp_id']) . '">' . Display::return_icon('pixel.gif', get_lang('Scenario'), array('class' => 'toolactionplaceholdericon toolactionauthorscenario')) . get_lang("Scenario") . '</a>';
                }
            }
            $no_of_categories = $this->getTotalCategories();

            if ($no_of_categories > 0) {
                if (isset($_GET['toogle']) && $_GET['toogle'] == 'hide') {
                    $action_icons .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&fold=Y&toogle=show">' . Display::return_icon('pixel.gif', get_lang('Unfold'), array('class' => 'toolactionplaceholdericon toolactionunfold'), $shownone) . '&nbsp;&nbsp;' . get_lang('Unfold') . '</a>';
                } else {
                    $action_icons .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&unfold=Y&toogle=hide">' . Display::return_icon('pixel.gif', get_lang('Fold'), array('class' => 'toolactionplaceholdericon toolactionfold'), $showall) . '&nbsp;&nbsp;' . get_lang('Fold') . '</a>';
                }
            }
        }

        return $action_icons;
    }

    public function getTotalCategories() {

        $session_id = api_get_session_id();
        $condition_session = api_get_session_condition($session_id, false, true);

        $sqlcategories = "SELECT * FROM {$this->tableLinkCategory} $condition_session ORDER BY display_order DESC";
        $resultcategories = Database::query($sqlcategories);
        $totalcategories = Database::num_rows($resultcategories);
        return $totalcategories;
    }

    public function getLinkCategories() {
        $session_id = api_get_session_id();
        $condition_session = api_get_session_condition($session_id, false, true);

        $sqlcategories = "SELECT * FROM {$this->tableLinkCategory} $condition_session ORDER BY display_order DESC";
        $resultcategories = Database::query($sqlcategories);
        if (Database::num_rows($resultcategories)) {
            while ($row = Database::fetch_object($resultcategories)) {

                $linkCategories[$row->id] = $row;
            }
        }
        return $linkCategories;
    }

    public function getLinksofCategory($catid = 0, $display = "block") {
        $session_id = api_get_session_id();
        $condition_session = api_get_session_condition($session_id, true, true);
        $urlview = $_GET['urlview'];

        global $charset, $_user, $linkCounter;
        $TABLE_ITEM_PROPERTY = Database :: get_course_table(TABLE_ITEM_PROPERTY);

        $sqlLinks = "SELECT * FROM {$this->tableLink} link, " . $TABLE_ITEM_PROPERTY . " itemproperties WHERE itemproperties.tool='" . TOOL_LINK . "' AND link.id=itemproperties.ref AND link.category_id=" . $catid . " AND (itemproperties.visibility='0' OR itemproperties.visibility='1') $condition_session ORDER BY link.display_order DESC";
        
        $result = Database::query($sqlLinks);
        $message = '';
        if (Database::num_rows($result) == 0) {
            $message = get_lang('NoLinkAvailable');
            $output .= '<div style="clear: both; height: 10px; line-height: 3;"></div>';
            $output .= '<div id="emptyPage">'.$message.'</div>';
        }
        if (Database::num_rows($result) > 0) {
            $output .= '<div id="contentWrap"><div id="contentLeft"><ul style="min-height: 10px;display:' . $display . '" class="dragdrop nobullets category_' . $catid . '">';
            $i = 1;
            while ($myrow = Database::fetch_array($result)) {
                $session_img = api_get_session_image($myrow['session_id'], $_user['status']);

                if ($i % 2 == 0)
                    $css_class = 'row_odd';
                else
                    $css_class = 'row_even';

                $description = text_filter($myrow[3]);

                $paddingLeftSubLinks = "";
                if ($catid != "0") {
                    $paddingLeftSubLinks = "padding-left: 0px;";
                }
                $drag = 'move1';
                if ($myrow['visibility'] == '1') {
                    $draggable_class = api_is_allowed_to_edit() ? 'draggable' : '';
                    $output .= "<li id='recordsArray_" . $myrow[0] . "' class='$drag'>
							<table class='data_table' width='100%'>
								<tr class='" . $css_class . "'>";
                    if (api_is_allowed_to_edit()) {
                        $output .= "<td align='left' valign='middle' width='6%' class='$draggable_class'>" . Display::return_icon('pixel.gif', null, array('class' => 'actionplaceholdericon actionsdraganddrop')) . "</td>";
                    } else {
                        $output .= "<td align='left' valign='middle' width='6%' class='$draggable_class'>" . Display::return_icon('pixel.gif', null, array('class' => 'placeholdericon toolshortcut_links')) . "</td>";
                    }
                    if (!api_is_allowed_to_edit(null, true)) {
                        $output .= "<td class='nodrag' width='94%' align='left' valign='top' style='text-align:left !important; padding-left:5px; padding-right:5px;' >";
                    } else {
                        if ($catid == 0) {
                            $output .= "<td class='nodrag' width='54%' align='left' style='text-align:left !important; padding-left:5px; padding-right:5px;'" . $paddingLeftSubLinks . "' valign='middle'>";
                        } else {
                            $output .= "<td class='nodrag' width='52%' align='left' style='text-align:left !important; padding-left:5px; padding-right:5px;'" . $paddingLeftSubLinks . "' valign='middle'>";
                        }
                    }
                    $output .= "<a href='../../../link/link_goto.php?" . api_get_cidreq() . "&link_id=" . $myrow[0] . "&link_url=" . urlencode($myrow[1]) . "' target='_blank'>" . api_htmlentities($myrow[2], ENT_QUOTES, $charset) . "</a>" . $session_img . "<br/>" . $myrow[3] . "
					</td>";
                } else {
                    if (api_is_allowed_to_edit(null, true)) {
                        $output .= "<li id='recordsArray_" . $myrow[0] . "' class='draggable $drag'>
								<table class='data_table' width='100%'>
									<tr class='" . $css_class . "'>
										<td align='left' valign='middle' width='6%'>
											" . Display::return_icon('pixel.gif', null, array('class' => 'actionplaceholdericon actionsdraganddrop')) . "<span style='display: inline-block; margin-top: 5px;margin-left: 2px;'></span>.
										</td>
										<td class='nodrag' width='54%' align='left' style='" . $paddingLeftSubLinks . "' valign='middle'>
											<a href='../../../link/link_goto.php?" . api_get_cidreq() . "&link_id=" . $myrow[0] . "&link_url=" . urlencode($myrow[1]) . "' target='_blank'  class='invisible'>" . api_htmlentities($myrow[2], ENT_QUOTES, $charset) . "</a>\n" . $session_img . "<br />" . $myrow[3] . "
										</td>";
                    }
                }
                if (api_is_allowed_to_edit(null, true)) {
                    $output .= '<td class="nodrag" style="text-align:center; width: 10%;">';
                    $output .= "<a href='" . api_get_self() . "?" . api_get_cidreq() . "&action=editlink&category=" . (!empty($category) ? $category : '') . "&id=" . $myrow[0] . " &urlview=$urlview'   title='" . get_lang('Modify') . "'  >" . Display::return_icon('pixel.gif', get_lang('Modify'), array('class' => 'actionplaceholdericon actionedit')) . "</a>";
                    $output .= '</td>';
                    $output .= '<td class="nodrag" style="text-align:center; width: 10%;">';
                    if(api_get_session_id() == $myrow['id_session'])
                    {
						//$output .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&action=deletelink&id=' . $myrow[0] . '&urlview=' . $urlview . '" onclick="javascript:if(!confirm(' . "'" . get_lang('LinkDelconfirm') . "'" . ')) return false;"  title="' . get_lang('Delete') . '" >' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
                                                $link =  api_get_self() . '?' . api_get_cidreq() . '&action=deletelink&id=' . $myrow[0] . '&urlview=' . $urlview;
                                                $title = get_lang("ConfirmationDialog");
                                                $text = get_lang("ConfirmYourChoice");
                                                $output .= '<a href="javascript:void(0);" onclick="Alert_Confim_Delete(\''.$link.'\',\''.$title.'\',\''.$text.'\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
					}
					else
					{
						$output .= '<a title="' . get_lang('Delete') . '" >' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete invisible')) . '</a>';
					}
					$output .= '</td>';
                    $output .= '<td class="nodrag" style="text-align:center; width: 10%;">';
                    if ($myrow['visibility'] == "1") {
                        if(api_get_session_id() == $myrow['id_session'])
						{
							$output .= '<a href="index.php?' . api_get_cidreq() . '&action=invisible&id=' . $myrow['id'] . '&scope=link&urlview=' . $urlview . '" title="' . get_lang('Hide') . '">' . Display::return_icon('pixel.gif', get_lang('Hide'), array('class' => 'actionplaceholdericon actionvisible')) . '</a>';
						}
						else
						{
							$output .= Display::return_icon('pixel.gif', get_lang('Hide'), array('class' => 'actionplaceholdericon actionvisible'));
						}
                    }
                    if ($myrow['visibility'] == "0") {
						if(api_get_session_id() == $myrow['id_session'])
						{
							$output .= '<a href="index.php?' . api_get_cidreq() . '&action=visible&id=' . $myrow['id'] . '&scope=link&urlview=' . $urlview . '" title="' . get_lang('Show') . '">' . Display::return_icon('pixel.gif', get_lang('Show'), array('class' => 'actionplaceholdericon actioninvisible')) . '</a>';
						}
						else
						{
							$output .= Display::return_icon('pixel.gif', get_lang('Show'), array('class' => 'actionplaceholdericon actioninvisible'));
						}
                    }
                    $output .= '</td>';
                    /*// deprecated since 3.0
                    $output .= '<td class="nodrag" style="text-align:center; width: 10%;">';
                    $output .= '<a class="display_link_form" href="javascript:void(0);" id="id=' . $myrow[0] . '&urlview=' . $urlview . '&' . api_get_cidreq() . '&action=integrateincourse" title="' . get_lang('IntegrateLinkInCourse') . '">' . Display::return_icon('pixel.gif', get_lang('IntegrateLinkInCourse'), array('class' => 'actionplaceholdericon actioncourseadd')) . '</a>';
                    $output .= '</td>';
                    *///
                }
                $output .= '</td>';
                $output .= '</tr></table></li>';
                $i++;
                $linkCounter++;
            }
            $output .= '</ul></div></div>';
        } else { //End for the loop if number of link > 0
            $output .= '<div id="contentWrap"><div id="contentLeft"><ul style="min-height: 10px;display:' . $display . '" class="dragdrop nobullets category_' . $catid . '">';
            //$output .= get_lang('ThereAreNoLinksInThisFolder');//There are no links in this folder
            $output .= '<br /></ul></div></div>';
        }

        return $output;
    }

    /**
     * creates a correct $view for in the URL
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     */
    function makedefaultviewcode($locatie) {
        global $view;
        $aantalcategories = $this->getTotalCategories();

        for ($j = 0; $j <= $aantalcategories - 1; $j++) {
            $view[$j] = 0;
        }
        $view[intval($locatie)] = "1";
    }

    // END of function makedefaultviewcode

    public function getCategoryLinks() {
        global $catcounter;

        $i = 0;
        $catcounter = 1;
        $view = "0";
        $linkCategories = $this->getLinkCategories();
        $output = '';
        $urlview = $_REQUEST['urlview'];

        if (!empty($linkCategories)) {

            $output .= '<div id="contentWrap"><div id="contentLeft"><ul class="dragdrop nobullets" id="categories">';
            //Create empty list to allow move link outside category
            $draggable_class = api_is_allowed_to_edit() ? 'draggable' : '';
            $output .= '<ul class="dragdrop ui-sortable"><li id="recordsArray_0" class="' . $draggable_class . '">&nbsp;</li></ul>';

            foreach ($linkCategories as $categoryId => $categories) {

                //validacion when belongs to a session
                $session_img = api_get_session_image($categories->session_id, $_user['status']);

                if ($urlview == '') {

                    // No $view set in the url, thus for each category link it should be all zeros except it's own
                    $this->makedefaultviewcode($i);
                } else {

                    $view = $urlview;
                    $view[$i] = "1";
                }
                // if the $urlview has a 1 for this categorie, this means it is expanded and should be desplayed as a
                // - instead of a +, the category is no longer clickable and all the links of this category are displayed
                $category_desc = text_filter($categories->description);

                $width_link_column = "94";
                $parent_draggable = "parent_no_draggable";
                if (api_is_allowed_to_edit(null, true)) {
                    $parent_draggable = "parent_draggable";
                    $width_link_column = "60";
                }

                if (isset($_GET['toogle']) && $_GET['toogle'] == "hide") {

                    // Collapsed view
                    $output .= '<li id="recordsArray_' . $categories->id . '" class="category">';
                    $output .= '<div class="' . $parent_draggable . ' rounded move">';
                    $output .= '<table width="100%" style="margin-left: -10px;margin-top:-2px;">';
                    $output .= '<tr>';
                    $output .= '<th width="' . $width_link_column . '%" style="font-weight: bold; text-align:left;padding-left: 5px;vertical-align: top; height: 40px;">';

                    $output .= '<a href="javascript:void(0)" class="toogle-category" id="toogle-icon-category-' . $categories->id . '"><img src="' . api_get_path(WEB_IMG_PATH) . 'action-slide-down.png" align="top" style="vertical-align:middle;" /></a>&nbsp;&nbsp;';
                    $output .= '<a href="javascript:void(0)" class="toogle-category" id="toogle-title-category-' . $categories->id . '">' . api_htmlentities($categories->category_title, ENT_QUOTES, $charset) . $session_img . '</a>';

                    $output .= '</th>';
                    if (api_is_allowed_to_edit(null, true)) {
                        $output .= $this->showcategoryadmintools($categories->id);
                        $output .= '<th style="">&nbsp;</th>';
                    }
                    $output .= '</tr>';
                    $output .= '</table></div>';
                    $output .= $this->getLinksofCategory($categories->id, 'none');
                    $output .= '</li>';
                } else {
                    $output .= '<li id="recordsArray_' . $categories->id . '" class="category">';
                    $output .= '<div class="' . $parent_draggable . ' rounded move">';
                    $output .= '<table width="100%" style="margin-left: -10px;margin-top:-2px;" >';
                    $output .= '<tr>';
                    $output .= '<th width="' . $width_link_column . '%"  style="font-weight: bold; text-align:left;padding-left: 5px; vertical-align: top; height: 40px;">';

                    $output .= '<a href="javascript:void(0)" class="toogle-category" id="toogle-icon-category-' . $categories->id . '"><img src="' . api_get_path(WEB_IMG_PATH) . 'action-slide-up.png" align="top" style="vertical-align:middle;" /></a>&nbsp;&nbsp;';
                    $output .= '<a href="javascript:void(0)" class="toogle-category" id="toogle-title-category-' . $categories->id . '">' . api_htmlentities($categories->category_title, ENT_QUOTES, $charset) . '</a>';

                    $output .= '</th>';
                    if (api_is_allowed_to_edit(null, true)) {
                        $output .= $this->showcategoryadmintools($categories->id);
                        $output .= '<th style="">&nbsp;</th>';
                    }
                    $output .= '</tr>';
                    $output .= '</table>';
                    $output .= '</div>';
                    $output .= $this->getLinksofCategory($categories->id);
                    $output .= '</li>';
                }
                // displaying the link of the category
                $i++;
            }
            $output .= '</ul></div></div>';
        }
        return $output;
    }

    /**
     * displays the edit, delete and move icons
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     */
    public function showcategoryadmintools($categoryid) {
        global $urlview;
        global $catcounter;

        $admin_icons .= '<th style="text-align:center; width: 10%;padding-right: 0px; vertical-align: top;"><a href="' . api_get_self() . '?' . api_get_cidreq() . '&amp;action=editcategory&amp;category_id=' . $categoryid . '&amp;urlview=' . $urlview . '"  title="' . get_lang('Modify') . ' ">' . Display::return_icon('pixel.gif', get_lang('Modify'), array('class' => 'actionplaceholdericon actionedit', 'border' => '0', 'align' => 'top')) . '</a></th>';

        //$admin_icons .= '<th style="text-align:center; width: 10%;padding-right: 0px; vertical-align: top;"><a href="' . api_get_self() . '?' . api_get_cidreq() . '&amp;action=deletecategory&amp;category_id=' . $categoryid . '&amp;urlview=' . $urlview . '" onclick="javascript:if(!confirm(\'' . get_lang('CategoryDelconfirm') . '\')) return false;">&nbsp;&nbsp;' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete', 'border' => '0', 'alt' => '', 'align' => 'top')) . '</a></th>';
        $link =  api_get_self() . '?' . api_get_cidreq() . '&amp;action=deletecategory&amp;category_id=' . $categoryid . '&amp;urlview=' . $urlview;
        $title = get_lang("ConfirmationDialog");
        $text = get_lang("ConfirmYourChoice");
        $admin_icons .= '<th style="text-align:center; width: 10%;padding-right: 0px; vertical-align: top;"><a href="javascript:void(0);" onclick="Alert_Confim_Delete(\''.$link.'\',\''.$title.'\',\''.$text.'\');">&nbsp;&nbsp;' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete', 'border' => '0', 'alt' => '', 'align' => 'top')) . '</a></th>';
        $catcounter++;

        return $admin_icons;
    }

    public function getAddCategoryForm($categoryId = null) {

        $form = new FormValidator('add_category', 'post', api_get_self() . '?' . api_get_cidreq() . '&done=Y' . ($categoryId ? '&action=editcategory&category_id=' . intval($categoryId) : '&action=addcategory'));

        if ($this->category_id) {
            $form->addElement('hidden', 'category_id', $categoryId);
        }

        $form->addElement('text', 'category_title', get_lang('FolderName'), array('size' => '60', 'class' => 'focus', 'id' => 'category_title'));
        $form->addRule('category_title', get_lang('ThisFieldIsRequired'), 'required');
        $form->addElement('style_submit_button', 'SubmitAnnouncement', get_lang('Validate'), 'class="save"');

        if (!empty($categoryId)) {
            $defaults['category_title'] = $this->category_title;
        }
        $form->setDefaults($defaults);

        return $form;
    }

    public function savecategory($categoryId = null) {

        if (empty($categoryId)) {

            $result = Database::query("SELECT MAX(display_order) FROM  {$this->tableLinkCategory}");
            list ($orderMax) = Database::fetch_row($result);
            $order = $orderMax + 1;

            $session_id = api_get_session_id();

            $sql = "INSERT INTO {$this->tableLinkCategory} (category_title, display_order, session_id) VALUES ('" . Database::escape_string(Security::remove_XSS($this->category_title)) . "', '$order', '$session_id')";

            Database::query($sql, __FILE__, __LINE__);
        } else {

            $sql = "UPDATE {$this->tableLinkCategory} set category_title='" . Database::escape_string(Security::remove_XSS($this->category_title)) . "' WHERE id='" . Database::escape_string(Security::remove_XSS($categoryId)) . "'";

            Database::query($sql, __FILE__, __LINE__);
        }
    }

    public function getCategoryInfo($categoryId) {
        $sql = "SELECT * FROM {$this->tableLinkCategory} WHERE id = " . $categoryId;
        $result = Database::query($sql, __FILE__, __LINE__);
        $info = Database::fetch_array($result);

        return $info;
    }

    public function getLinkInfo($linkId) {
        $sql = "SELECT * FROM {$this->tableLink} WHERE id = " . $linkId;
        $result = Database::query($sql, __FILE__, __LINE__);
        $info = Database::fetch_array($result);

        return $info;
    }

    public function deletecategory() {
        // first we delete the category itself and afterwards all the links of this category.
        $sql = "DELETE FROM {$this->tableLinkCategory} WHERE id='" . Database::escape_string(Security::remove_XSS($this->category_id)) . "'";
        Database::query($sql, __FILE__, __LINE__);
        $sql = "DELETE FROM {$this->tableLink} WHERE category_id='" . Database::escape_string(Security::remove_XSS($this->category_id)) . "'";
        Database::query($sql, __FILE__, __LINE__);
    }

    /* public function getAddLinkForm() {
      $form = new FormValidator('add_link', 'post', api_get_self().'?'.api_get_cidreq().($linkId?'&action=editlink&link_id='.intval($linkId):'&action=addlink'));

      echo 'formaaldateee';
      $form->addElement ( 'text', 'urllink', get_lang ( 'Url' ), array('size'=>'60','class'=>'focus','id'=>'urllink') );
      $form->addElement ( 'text', 'title', get_lang ( 'Text' ), array('size'=>'60','class'=>'focus','id'=>'title') );
      $form->addElement ( 'textarea', 'description', get_lang ( 'Objective' ), array('size'=>'60','class'=>'focus','id'=>'title') );

      $categories = $this->getLinkCategories();
      $linkCategories = array();
      if (!empty($categories)) {
      $linkCategories[0] = get_lang('--');
      foreach ($categories as $category_id => $category) {
      $linkCategories[$category_id] = $category->category_title;
      }
      }
      if(!empty($categories)){
      $form->addElement('select', 'selectcategory', get_lang('Category'), $linkCategories);
      }
      $form->addElement ( 'checkbox', 'id_check_target', get_lang ( 'OnHomepage' ), null, 'id="id_check_target"' );
      // Status
      $homepagetargets = array();
      $homepagetargets['_self'] = '_self';
      $homepagetargets['_blank'] = '_blank';
      $homepagetargets['_parent'] = '_parent';
      $homepagetargets['_top'] = '_top';

      $form->addElement('html','<div id="div_target">');
      $form->addElement('select', 'target_link', get_lang('AddTargetOfLinkOnHomepage'), $homepagetargets,'id="target_link"');
      $form->addElement('html','</div>');

      if (api_get_setting('search_enabled') == 'true') {
      require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');
      $specific_fields = get_specific_field_list();

      foreach ($specific_fields as $specific_field) {
      $default_values = '';
      if ($_GET['action'] == "editlink") {
      $filter = array('course_code' => "'" . api_get_course_id() . "'", 'field_id' => $specific_field['id'], 'ref_id' => Security::remove_XSS($_GET['link_id']), 'tool_id' => '\'' . TOOL_LINK . '\'');
      $values = get_specific_field_values_list($filter, array('value'));
      if (!empty($values)) {
      $arr_str_values = array();
      foreach ($values as $value) {
      $arr_str_values[] = $value['value'];
      }
      $default_values = implode(', ', $arr_str_values);
      }
      }

      $sf_textbox = '
      <div class="row">
      <div class="label">%s</div>
      <div class="formw">
      <input name="%s" type="text" value="%s"/>
      </div>
      </div>';

      echo sprintf($sf_textbox, $specific_field['name'], $specific_field['code'], $default_values);
      }
      }

      $form->addElement('style_submit_button', 'SubmitAnnouncement', get_lang('Validate'), 'class="save"');

      return $form;
      } */

    public function getAddLinkForm($add_params_for_lp, $linkId = null) {

        $form .= "<form name='add_link' method='post' action='" . api_get_self() . "?action=" . Security::remove_XSS($_GET['action']) . "&id=" . $linkId . "&urlview=" . Security::remove_XSS($_GET['urlview']) . $add_params_for_lp . "&done=Y'>";
        if ($_GET['action'] == "editlink") {
            $form .= "<input type='hidden' name='id' value='" . Security::remove_XSS($_GET['id']) . "' />";
            $clean_link_id = trim(Security::remove_XSS($_GET['id']));
        }

        $form .= '	<div class="row">
							<div class="label">
								<span class="form_required">*</span> ' . get_lang('Url') . '
							</div>
							<div class="formw">
								<input type="text" class="focus" name="urllink" size="50" value="' . $this->urllink . '" />
							</div>
						</div>';

        $form .= '	<div class="row">
							<div class="label">
								' . get_lang('Text') . '
							</div>
							<div class="formw">
								<input type="text" name="title" size="50" value="' . api_htmlentities($this->title, ENT_QUOTES, $charset) . '" />
							</div>
						</div>';
        $form .= '	<div class="row">
							<div class="label">
								' . get_lang('Objective') . '
							</div>
							<div class="formw">
								<textarea style="width: 352px; height: 66px;" rows="3" cols="50" name="description">' . api_htmlentities($this->description, ENT_QUOTES, $charset) . '</textarea>
							</div>
						</div>';

        $sqlcategories = "SELECT * FROM {$this->tableLinkCategory} $condition_session ORDER BY display_order DESC";
        $resultcategories = Database::query($sqlcategories, __FILE__, __LINE__);

        if (Database::num_rows($resultcategories)) {
            $form .= '	<div class="row">
								<div class="label">
									' . get_lang('Category') . '
								</div>
								<div class="formw">';
            $form .= '			<select name="selectcategory">';
            $form .= '			<option value="0">--</option>';
            while ($myrow = Database::fetch_array($resultcategories)) {
                $form .= "		<option value=\"" . $myrow["id"] . "\"";
                if ($myrow["id"] == $this->category) {
                    $form .= " selected";
                }
                $form .= ">" . $myrow["category_title"] . "</option>";
            }
            $form .= '			</select>';
            $form .= '		</div>
							</div>';
        }

        $form .= '	<div class="row">
							<div class="label">
								' . get_lang('OnHomepage') . '?
							</div>
							<div class="formw" style="margin-top:8px;">
								<input id="id_check_target" class="checkbox" type="checkbox" name="onhomepage" id="onhomepage" value="1"' . $this->onhomepage . '><label for="onhomepage"> ' . get_lang('Yes') . '</label>
							</div>
						</div>';

        function show_select_target($target_link) {
            //$targets = array('_self' => get_lang("_self"), '_blank' => get_lang("_blank"), '_parent' => get_lang("_parent"), '_top' => get_lang("_top"));
            $targets = array('_self' => get_lang("SelfNavigation"), '_blank' => get_lang("BlankNavigation"), '_parent' => get_lang("ParentNavigation"), '_top' => get_lang("TopNavigation"));
            $html_select = '<select  name="target_link" id="target_link">';
            foreach ($targets as $id => $value) {
                $selected = ($value == $target_link ) ? 'selected="selected"' : '';
                $html_select .= '<option value="' . $id . '" ' . $selected . ' >' . $value . '</option>';
            }
            $html_select .= '</select>';
            return $html_select;
        }

        $form .= '	<div class="row" id="div_target">
							<div class="label">
								' . get_lang('AddTargetOfLinkOnHomepage') . '
							</div>
							<div class="formw"style="margin-top:7px;" >
								' . show_select_target($this->target_link) . '
							</div>
						</div>';

        if (api_get_setting('search_enabled') == 'true') {
            require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');
            $specific_fields = get_specific_field_list();

            foreach ($specific_fields as $specific_field) {
                $default_values = '';
                if ($_GET['action'] == "editlink") {
                    $filter = array('course_code' => "'" . api_get_course_id() . "'", 'field_id' => $specific_field['id'], 'ref_id' => Security::remove_XSS($_GET['id']), 'tool_id' => '\'' . TOOL_LINK . '\'');
                    $values = get_specific_field_values_list($filter, array('value'));
                    if (!empty($values)) {
                        $arr_str_values = array();
                        foreach ($values as $value) {
                            $arr_str_values[] = $value['value'];
                        }
                        $default_values = implode(', ', $arr_str_values);
                    }
                }

                $sf_textbox = '
									<div class="row">
										<div class="label">%s</div>
										<div class="formw">
											<input name="%s" type="text" value="%s"/>
										</div>
									</div>';

                $form .= sprintf($sf_textbox, $specific_field['name'], $specific_field['code'], $default_values);
            }
        }

        $form .= '	<div class="row">
								<div class="label">
								</div>
								<div class="formw">
									<button class="save" type="submit" name="submitLink" value="OK">' . get_lang('SaveLink') . '</button>
								</div>
							</div>';

        $form .= '</form>';

        return $form;
    }

    public function savelink() {

        global $_course, $_user;

        if ($this->onhomepage == '') {
            $this->onhomepage = 0;
            $this->target_link = '_self'; //default target
        }

        // we check weither the $url starts with http://, if not we add this
        if (!strstr($this->urllink, '://')) {
            $this->urllink = "http://" . $this->urllink;
        }

        // looking for the largest order number for this category
        $result = Database::query("SELECT MAX(display_order) FROM  {$this->tableLink} WHERE category_id='" . Database::escape_string($this->selectcategory) . "'");

        list ($orderMax) = Database::fetch_row($result);

        $order = $orderMax + 1;

        $session_id = api_get_session_id();

        $sql = "INSERT INTO {$this->tableLink} (url, title, description, category_id, display_order, on_homepage, target, session_id)
			VALUES ('" . Database::escape_string($this->urllink) . "','" . Database::escape_string($this->title) . "',
		   '" . Database::escape_string($this->description) . "','" . Database::escape_string($this->selectcategory) . "',
		   '" . Database::escape_string($order) . "', '" . Database::escape_string($this->onhomepage) . "',
		   '" . Database::escape_string($this->target_link) . "','$session_id')";

        Database::query($sql, __FILE__, __LINE__);
        $link_id = Database::insert_id();

        if ($link_id > 0) {
            api_item_property_update($_course, TOOL_LINK, $link_id, "LinkAdded", $_user['user_id']);
        }

        if (isset($_SESSION['oLP']) && $link_id > 0) {
            // Get the previous item ID
            $parent = 0;
            $previous = $_SESSION['oLP']->select_previous_item_id();
            // Add quiz as Lp Item
            $_SESSION['oLP']->add_item($parent, $previous, TOOL_LINK, $link_id, $this->title, '');
        }

        if ((api_get_setting('search_enabled') == 'true') && $link_id && extension_loaded('xapian')) {
            require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
            require_once(api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php');
            require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');

            $courseid = api_get_course_id();
            $specific_fields = get_specific_field_list();
            $ic_slide = new IndexableChunk();

            // build the chunk to index
            $ic_slide->addValue("title", $this->title);
            $ic_slide->addCourseId($courseid);
            $ic_slide->addToolId(TOOL_LINK);
            $xapian_data = array(
                SE_COURSE_ID => $courseid,
                SE_TOOL_ID => TOOL_LINK,
                SE_DATA => array('link_id' => (int) $link_id),
                SE_USER => (int) api_get_user_id(),
            );
            $ic_slide->xapian_data = serialize($xapian_data);
            $description = $this->description;
            $ic_slide->addValue("content", $description);

            // add category name if set
            if (isset($this->selectcategory) && $this->selectcategory > 0) {
                $table_link_category = Database::get_course_table(TABLE_LINK_CATEGORY);
                $sql_cat = 'SELECT * FROM %s WHERE id=%d LIMIT 1';
                $sql_cat = sprintf($sql_cat, $table_link_category, (int) $this->selectcategory);
                $result = Database::query($sql_cat, __FILE__, __LINE__);
                if (Database::num_rows($result) == 1) {
                    $row = Database::fetch_array($result);
                    $ic_slide->addValue("category", $row['category_title']);
                }
            }

            $di = new DokeosIndexer();
            isset($_POST['language']) ? $lang = Database::escape_string($_POST['language']) : $lang = 'english';
            $di->connectDb(NULL, NULL, $lang);
            $di->addChunk($ic_slide);

            //index and return search engine document id
            $did = $di->index();
            if ($did) {
                // save it to db
                $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
                $sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did)
						VALUES (NULL , \'%s\', \'%s\', %s, %s)';
                $sql = sprintf($sql, $tbl_se_ref, $courseid, TOOL_LINK, $link_id, $did);
                Database::query($sql, __FILE__, __LINE__);
            }
        }
    }

    public function updatelink() {
        global $_course, $_user;

        if ($this->onhomepage == '') {
            $this->onhomepage = 0;
            $mytarget = '';
        } else {
            $this->onhomepage = Security::remove_XSS($this->onhomepage);
            $target = Security::remove_XSS($this->target_link);
            $mytarget = ",target='" . $target . "'";
        }

        // finding the old category_id
        $sql = "SELECT * FROM {$this->tableLink} WHERE id='" . Database::escape_string(Security::remove_XSS($this->link_id)) . "'";
        $result = Database::query($sql, __FILE__, __LINE__);
        $row = Database::fetch_array($result);
        $category_id = $row['category_id'];

        if ($category_id <> $this->selectcategory) {
            $sql = "SELECT MAX(display_order) FROM {$this->tableLink} WHERE category_id='" . $this->selectcategory . "'";
            $result = Database::query($sql);
            list ($max_display_order) = Database::fetch_row($result);
            $max_display_order++;
        } else {
            $max_display_order = $row['display_order'];
        }

        $sql = "UPDATE {$this->tableLink} set url='" . Database::escape_string(Security::remove_XSS($this->urllink)) . "', title='" . Database::escape_string(Security::remove_XSS($this->title)) . "', description='" . Database::escape_string(Security::remove_XSS($this->description)) . "', category_id='" . Database::escape_string(Security::remove_XSS($this->selectcategory)) . "', display_order='" . $max_display_order . "', on_homepage='" . Database::escape_string(Security::remove_XSS($this->onhomepage)) . " ' $mytarget WHERE id='" . Database::escape_string(Security::remove_XSS($this->link_id)) . "'";

        Database::query($sql, __FILE__, __LINE__);

        // "WHAT'S NEW" notification: update table last_toolEdit
        api_item_property_update($_course, TOOL_LINK, $this->link_id, "LinkUpdated", $_user['user_id']);

        // update search enchine and its values table if enabled
        if (api_get_setting('search_enabled') == 'true') {
            $course_id = api_get_course_id();

            // actually, it consists on delete terms from db, insert new ones, create a new search engine document, and remove the old one
            // get search_did
            $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
            $sql = 'SELECT * FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s LIMIT 1';
            $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_LINK, $this->link_id);
            $res = Database::query($sql, __FILE__, __LINE__);

            if (Database::num_rows($res) > 0) {
                require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
                require_once(api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php');
                require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');

                $se_ref = Database::fetch_array($res);
                $specific_fields = get_specific_field_list();
                $ic_slide = new IndexableChunk();

                $all_specific_terms = '';
                foreach ($specific_fields as $specific_field) {
                    delete_all_specific_field_value($course_id, $specific_field['id'], TOOL_LINK, $this->link_id);
                    if (isset($_REQUEST[$specific_field['code']])) {
                        $sterms = trim($_REQUEST[$specific_field['code']]);
                        if (!empty($sterms)) {
                            $all_specific_terms .= ' ' . $sterms;
                            $sterms = explode(',', $sterms);
                            foreach ($sterms as $sterm) {
                                $ic_slide->addTerm(trim($sterm), $specific_field['code']);
                                add_specific_field_value($specific_field['id'], $course_id, TOOL_LINK, $this->link_id, $sterm);
                            }
                        }
                    }
                }

                // build the chunk to index
                $ic_slide->addValue("title", $this->title);
                $ic_slide->addCourseId($course_id);
                $ic_slide->addToolId(TOOL_LINK);
                $xapian_data = array(
                    SE_COURSE_ID => $course_id,
                    SE_TOOL_ID => TOOL_LINK,
                    SE_DATA => array('link_id' => (int) $this->link_id),
                    SE_USER => (int) api_get_user_id(),
                );
                $ic_slide->xapian_data = serialize($xapian_data);
                $link_description = $all_specific_terms . ' ' . $this->description;
                $ic_slide->addValue("content", $link_description);

                // add category name if set
                if (isset($this->selectcategory) && $this->selectcategory > 0) {
                    $table_link_category = Database::get_course_table(TABLE_LINK_CATEGORY);
                    $sql_cat = 'SELECT * FROM %s WHERE id=%d LIMIT 1';
                    $sql_cat = sprintf($sql_cat, $table_link_category, (int) $this->selectcategory);
                    $result = Database::query($sql_cat, __FILE__, __LINE__);
                    if (Database::num_rows($result) == 1) {
                        $row = Database::fetch_array($result);
                        $ic_slide->addValue("category", $row['category_title']);
                    }
                }

                $di = new DokeosIndexer();
                isset($_POST['language']) ? $lang = Database::escape_string($_POST['language']) : $lang = 'english';
                $di->connectDb(NULL, NULL, $lang);
                $di->remove_document((int) $se_ref['search_did']);
                $di->addChunk($ic_slide);

                //index and return search engine document id
                $did = $di->index();
                if ($did) {
                    // save it to db
                    $sql = 'DELETE FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=\'%s\'';
                    $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_LINK, $this->link_id);
                    Database::query($sql, __FILE__, __LINE__);
                    //var_dump($sql);
                    $sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did)
						VALUES (NULL , \'%s\', \'%s\', %s, %s)';
                    $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_LINK, $this->link_id, $did);
                    Database::query($sql, __FILE__, __LINE__);
                }
            }
        }
    }

    public function deletelink() {
        global $_course, $_user;

        // -> items are no longer fysically deleted, but the visibility is set to 2 (in item_property). This will
        // make a restore function possible for the platform administrator
        //if (isset($this->link_id) && $this->link_id==strval(intval($this->link_id))) {                
        if (!is_null($this->link_id)) {
            $sql = "UPDATE {$this->tableLink} SET on_homepage='0' WHERE id='" . Database::escape_string($this->link_id) . "'";
            Database::query($sql, __FILE__, __LINE__);
        }

        api_item_property_update($_course, TOOL_LINK, $this->link_id, "delete", $_user['user_id']);
        $this->delete_link_from_search_engine(api_get_course_id(), $this->link_id);
    }

    public function delete_link_from_search_engine($course_id, $link_id) {

        // remove from search engine if enabled
        if (api_get_setting('search_enabled') == 'true') {
            $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
            $sql = 'SELECT * FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s LIMIT 1';
            $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_LINK, $link_id);
            $res = Database::query($sql, __FILE__, __LINE__);
            if (Database::num_rows($res) > 0) {
                $row = Database::fetch_array($res);
                require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
                $di = new DokeosIndexer();
                $di->remove_document((int) $row['search_did']);
            }
            $sql = 'DELETE FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s LIMIT 1';
            $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_LINK, $link_id);
            Database::query($sql, __FILE__, __LINE__);

            // remove terms from db
            require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');
            delete_all_values_for_item($course_id, TOOL_DOCUMENT, $link_id);
        }
    }

    public function changeVisibility() {
        global $_course;
        global $_user;
        $TABLE_ITEM_PROPERTY = Database :: get_course_table(TABLE_ITEM_PROPERTY);

        $sqlselect = "SELECT * FROM $TABLE_ITEM_PROPERTY WHERE tool='" . TOOL_LINK . "' and ref='" . Database::escape_string($this->link_id) . "'";
        $result = Database::query($sqlselect);
        $row = Database::fetch_array($result);
        api_item_property_update($_course, TOOL_LINK, $this->link_id, $_GET['action'], $_user['user_id']);
    }

    public function integrateLinkInCourse() {
        $tbl_lp = Database :: get_course_table(TABLE_LP_MAIN);

        $sql_course = "SELECT * FROM " . $tbl_lp;
        $result = Database::query($sql_course, __FILE__, __LINE__);
        $course_numrows = Database::num_rows($result);
        if ($course_numrows <> 0) {
            //create form to link to course
            $form .= '<form name="add_link_to_course" action="index.php?action=integrateincourse&id=' . $this->link_id . $add_params_for_lp . '&done=Y" method="post">';
            $form .= '<table cellpadding="3" cellspacing="3">';
            $form .= '<tr><td>' . get_lang('Title') . ' :&nbsp;</td><td><input type="text" name="link_title" value="' . $this->title . '" size="35"></td></tr>';
            $form .= '<tr><td>' . get_lang('Url') . ' :&nbsp;</td><td><input type="text" name="link_url" value="' . $this->url . '" size="35"></td></tr>';
            $form .= '<tr><td>' . get_lang('Course') . ' :&nbsp;</td><td><select name="course" size="1">';

            while ($obj = Database::fetch_object($result)) {
                $lp_name = $obj->name;
                $lp_id = $obj->id;
                $form .= '<option value="' . $lp_name . '@' . $lp_id . '">' . $lp_name . '</option>';
            }
            $form .= '</select></td></tr>';
            $form .= '<tr><td>&nbsp;</td><td><button type="submit" class="add" name="link_add">' . get_lang('Validate') . '</button></td></tr>';
            $form .= '</table>';
            $form .= '</form>';
        } else {
            $form .= get_lang('NoCourseCreatedPleaseCreateOne');
        }

        return $form;
    }

    public function addLinkToCourse() {

        list($lp_name, $lp_id) = explode("@", $this->course);
        $tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);
        $query = "SELECT max(id) AS maxid FROM " . $tbl_lp_item;
        $result = Database::query($query, __FILE__, __LINE__);
        while ($obj = Database::fetch_object($result)) {
            $maxid = $obj->maxid;
        }
        if (is_null($maxid))
            $maxid = 0;

        $query = "SELECT max(display_order) AS disporder FROM " . $tbl_lp_item . " WHERE lp_id=" . Database::escape_string($lp_id);
        $result = Database::query($query, __FILE__, __LINE__);
        while ($obj = Database::fetch_object($result)) {
            $display_order = $obj->disporder;
        }

        $sql = "INSERT INTO " . $tbl_lp_item . " (lp_id,
							item_type,
							ref,
							title,
							description,
							path,
							previous_item_id,
							next_item_id,
							display_order) VALUES(" . Database::escape_string($lp_id) . ",
							'link',
							'" . ($maxid + 1) . "',
							'" . Database::escape_string($this->title) . "',
							'',
							'" . Database::escape_string($this->link_id) . "',
							" . $maxid . ",
							" . ($maxid + 2) . ",
							" . ($display_order + 1) . ")";

        $result = api_sql_query($sql, __FILE__, __LINE__);
    }

    public function updateRecordsListings() {

        $type = $this->type;
        $disparr = explode(",", $this->disporder);

        if (!is_array($disparr)) {
            $disparr = array($this->disporder);
        }

        if (isset($type) && $type == 'categories') {
            $table_name = $this->tableLinkCategory;
        } else {
            $this->changeLinkCategory($this->itemId, $this->category_id);
            $table_name = $this->tableLink;
        }

        $len = sizeof($disparr);
        $listingCounter = $len;
        for ($i = 0; $i < sizeof($disparr); $i++) {
            $sql = "UPDATE $table_name SET display_order=" . Database::escape_string($listingCounter) . " WHERE id = " . Database::escape_string($disparr[$i]);
            $res = Database::query($sql, __FILE__, __LINE__);
            $listingCounter = $listingCounter - 1;
        }
    }

    public function changeLinkCategory($itemId, $categoryId) {

        $sql = "UPDATE $this->tableLink SET category_id=" . Database::escape_string($categoryId) . " WHERE id = " . Database::escape_string($itemId);
        $res = Database::query($sql, __FILE__, __LINE__);
    }

}

// end class
