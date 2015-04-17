<?php

/**
 * 	Display class
 * 	contains several public functions dealing with the display of
 * 	table data, messages, help topics, ...
 *
 * 	@version 1.0.4
 * 	@package dokeos.library
 */
require_once 'sortabletable.class.php';

class Display {

    private function __construct() {
        
    }

    /**
     * Displays the tool introduction of a tool.
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @param string $tool These are the constants that are used for indicating the tools.
     * @param array $editor_config Optional configuration settings for the online editor.
     * @return $tool return a string array list with the "define" in main_api.lib
     * @return html code for adding an introduction
     */
    public static function display_introduction_section($tool, $editor_config = null) {
        $is_allowed_to_edit = api_is_allowed_to_edit();
        $moduleId = $tool;

        if (api_get_setting('enable_course_scenario') == 'true' && $tool == TOOL_COURSE_HOMEPAGE) {
            include (api_get_path(INCLUDE_PATH) . "introductionSection.inc.php");
        } else if (api_get_setting('enable_tool_introduction') == 'true' || $tool == TOOL_COURSE_HOMEPAGE) {
            include (api_get_path(INCLUDE_PATH)."introductionSection_html.inc.php");
            //include (api_get_path(INCLUDE_PATH) . "introductionSection_toolintro.inc.php");
        }
    }

    /**
     * 	Displays a localised html file
     * 	tries to show the file "$full_file_name"."_".$language_interface.".html"
     * 	and if this does not exist, shows the file "$full_file_name".".html"
     * 	warning this public function defines a global
     * 	@param $full_file_name, the (path) name of the file, without .html
     * 	@return return a string with the path
     */
    public static function display_localised_html_file($full_file_name) {
        global $language_interface;
        $localised_file_name = $full_file_name . "_" . $language_interface . ".html";
        $default_file_name = $full_file_name . ".html";
        if (file_exists($localised_file_name)) {
            include ($localised_file_name);
        } else {
            include ($default_file_name); //default
        }
    }

    /**
     * 	Display simple html header of table.
     */
    public static function display_table_header() {
        $bgcolor = "bgcolor='white'";
        echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"4\" width=\"85%\"><tbody>";
        return $bgcolor;
    }

    /**
     * Gets a nice grid in html string
     * @param string grid name (important to create css)
     * @param array header content
     * @param array array with the information to show
     * @param array $paging_options Keys are:
     * 					'per_page_default' = items per page when switching from
     * 										 full-	list to per-page-view
     * 					'per_page' = number of items to show per page
     * 					'page_nr' = The page to display
     * 					'hide_navigation' =  true to hide the navigation
     * @param array $query_vars Additional variables to add in the query-string
     * @param array $form actions Additional variables to add in the query-string
     * @param mixed An array with bool values to know which columns show. i.e: $vibility_options= array(true, false) we will only show the first column
     * 				Can be also only a bool value. TRUE: show all columns, FALSE: show nothing
     * @param bool  true for sorting data or false otherwise
     * @return 	string   html grid
     */
    public static function return_sortable_grid($name, $header, $content, $paging_options = array(), $query_vars = null, $form_actions = array(), $vibility_options = true, $sort_data = true) {
        if (!class_exists('SortableTable')) {
            require_once 'sortabletable.class.php';
        }
        global $origin;
        $column = 0;
        $default_items_per_page = isset($paging_options['per_page']) ? $paging_options['per_page'] : 20;
        $table = new SortableTableFromArray($content, $column, $default_items_per_page, $name);

        if (is_array($query_vars)) {
            $table->set_additional_parameters($query_vars);
        }
        return $table->return_simple_grid($vibility_options, $paging_options['hide_navigation'], $paging_options['per_page'], $sort_data);
    }

    /**
     * 	Display html header of table with several options.
     *
     * 	@param $properties, array with elements, all of which have defaults
     * 	"width" - the table width, e.g. "100%", default is 85%
     * 	"class"	 - the class to use for the table, e.g. "class=\"data_table\""
     *   "cellpadding"  - the extra border in each cell, e.g. "8",default is 4
     *   "cellspacing"  - the extra space between cells, default = 0
     * 	@param column_header, array with the header elements.
     * 	@author Roan Embrechts
     * 	@version 1.01
     * 	@return return type string, bgcolor
     */
    public static function display_complex_table_header($properties, $column_header) {
        $width = $properties["width"];
        if (!isset($width))
            $width = "85%";
        $class = $properties["class"];
        if (!isset($class))
            $class = "class=\"data_table\"";
        $cellpadding = $properties["cellpadding"];
        if (!isset($cellpadding))
            $cellpadding = "4";
        $cellspacing = $properties["cellspacing"];
        if (!isset($cellspacing))
            $cellspacing = "0";
        //... add more properties as you see fit
        //api_display_debug_info("Dokeos light grey is " . DOKEOSLIGHTGREY);
        $bgcolor = "bgcolor='" . DOKEOSLIGHTGREY . "'";
        echo "<table $class border=\"0\" cellspacing=\"$cellspacing\" cellpadding=\"$cellpadding\" width=\"$width\">\n";
        echo "<thead><tr $bgcolor>";
        foreach ($column_header as $table_element) {
            echo "<th>" . $table_element . "</th>";
        }
        echo "</tr></thead>\n";
        echo "<tbody>\n";
        $bgcolor = "bgcolor='" . HTML_WHITE . "'";
        return $bgcolor;
    }

    /**
     * 	Displays a table row.
     *
     * 	@param $bgcolor the background colour for the table
     * 	@param $table_row an array with the row elements
     * 	@param $is_alternating true: the row colours alternate, false otherwise
     */
    public static function display_table_row($bgcolor, $table_row, $is_alternating = true) {
        echo "<tr $bgcolor>";
        foreach ($table_row as $table_element) {
            echo "<td>" . $table_element . "</td>";
        }
        echo "</tr>\n";
        if ($is_alternating) {
            if ($bgcolor == "bgcolor='" . HTML_WHITE . "'") {
                $bgcolor = "bgcolor='" . DOKEOSLIGHTGREY . "'";
            } else {
                if ($bgcolor == "bgcolor='" . DOKEOSLIGHTGREY . "'") {
                    $bgcolor = "bgcolor='" . HTML_WHITE . "'";
                }
            }
        }
        return $bgcolor;
    }

    /**
     * 	Displays a table row.
     * 	This public function has more options and is easier to extend than display_table_row()
     *
     * 	@param $properties, array with properties:
     * 	["bgcolor"] - the background colour for the table
     * 	["is_alternating"] - true: the row colours alternate, false otherwise
     * 	["align_row"] - an array with, per cell, left|center|right
     * 	@todo add valign property
     */
    public static function display_complex_table_row($properties, $table_row) {
        $bgcolor = $properties["bgcolor"];
        $is_alternating = $properties["is_alternating"];
        $align_row = $properties["align_row"];
        echo "<tr $bgcolor>";
        $number_cells = count($table_row);
        for ($i = 0; $i < $number_cells; $i++) {
            $cell_data = $table_row[$i];
            $cell_align = $align_row[$i];
            echo "<td align=\"$cell_align\">" . $cell_data . "</td>";
        }
        echo "</tr>\n";
        if ($is_alternating) {
            if ($bgcolor == "bgcolor='" . HTML_WHITE . "'")
                $bgcolor = "bgcolor='" . DOKEOSLIGHTGREY . "'";
            else
            if ($bgcolor == "bgcolor='" . DOKEOSLIGHTGREY . "'")
                $bgcolor = "bgcolor='" . HTML_WHITE . "'";
        }
        return $bgcolor;
    }

    /**
     * 	display html footer of table
     */
    public static function display_table_footer() {
        echo "</tbody></table>";
    }

    /**
     * Display a table
     * @param array $header Titles for the table header
     * 						each item in this array can contain 3 values
     * 						- 1st element: the column title
     * 						- 2nd element: true or false (column sortable?)
     * 						- 3th element: additional attributes for
     *  						th-tag (eg for column-width)
     * 						- 4the element: additional attributes for the td-tags
     * @param array $content 2D-array with the tables content
     * @param array $sorting_options Keys are:
     * 					'column' = The column to use as sort-key
     * 					'direction' = SORT_ASC or SORT_DESC
     * @param array $paging_options Keys are:
     * 					'per_page_default' = items per page when switching from
     * 										 full-	list to per-page-view
     * 					'per_page' = number of items to show per page
     * 					'page_nr' = The page to display
     * @param array $query_vars Additional variables to add in the query-string
     * @param string The style that the table will show. You can set 'table' or 'grid'
     * @author bart.mollet@hogent.be
     */
    public static function display_sortable_table($header, $content, $sorting_options = array(), $paging_options = array(), $query_vars = null, $form_actions = array(), $style = 'table') {
        global $origin;
        $column = isset($sorting_options['column']) ? $sorting_options['column'] : 0;
        $default_items_per_page = isset($paging_options['per_page']) ? $paging_options['per_page'] : 20;

        $table = new SortableTableFromArray($content, $column, $default_items_per_page);

        if (is_array($query_vars)) {
            $table->set_additional_parameters($query_vars);
        }
        if ($style == 'table') {
            if (is_array($header) && count($header) > 0) {
                foreach ($header as $index => $header_item) {
                    $table->set_header($index, $header_item[0], $header_item[1], $header_item[2], $header_item[3]);
                }
            }
            $table->set_form_actions($form_actions);
            $table->display();
        } else if ($style == 'ajax_grid') {
            $table->display_ajax_grid();
        } else {
            $table->display_grid();
        }
    }

    /**
     * Shows a nice grid
     * @param string grid name (important to create css)
     * @param array header content
     * @param array array with the information to show
     * @param array $paging_options Keys are:
     * 					'per_page_default' = items per page when switching from
     * 										 full-	list to per-page-view
     * 					'per_page' = number of items to show per page
     * 					'page_nr' = The page to display
     * 					'hide_navigation' =  true to hide the navigation
     * @param array $query_vars Additional variables to add in the query-string
     * @param array $form actions Additional variables to add in the query-string
     * @param mixed An array with bool values to know which columns show. i.e: $vibility_options= array(true, false) we will only show the first column
     * 				Can be also only a bool value. TRUE: show all columns, FALSE: show nothing
     */
    public static function display_sortable_grid($name, $header, $content, $paging_options = array(), $query_vars = null, $form_actions = array(), $vibility_options = true) {
        global $origin;
        $column = 0;
        $default_items_per_page = isset($paging_options['per_page']) ? $paging_options['per_page'] : 20;
        $table = new SortableTableFromArray($content, $column, $default_items_per_page, $name);

        if (is_array($query_vars)) {
            $table->set_additional_parameters($query_vars);
        }
        $table->display_simple_grid($vibility_options, $paging_options['hide_navigation']);
    }

    /**
     * Display a table with a special configuration
     * @param array $header Titles for the table header
     * 						each item in this array can contain 3 values
     * 						- 1st element: the column title
     * 						- 2nd element: true or false (column sortable?)
     * 						- 3th element: additional attributes for
     *  						th-tag (eg for column-width)
     * 						- 4the element: additional attributes for the td-tags
     * @param array $content 2D-array with the tables content
     * @param array $sorting_options Keys are:
     * 					'column' = The column to use as sort-key
     * 					'direction' = SORT_ASC or SORT_DESC
     * @param array $paging_options Keys are:
     * 					'per_page_default' = items per page when switching from
     * 										 full-	list to per-page-view
     * 					'per_page' = number of items to show per page
     * 					'page_nr' = The page to display
     * @param array $query_vars Additional variables to add in the query-string
     * @param array $column_show Array of binaries 1= show columns 0. hide a column
     * @param array $column_order An array of integers that let us decide how the columns are going to be sort.
     * 						      i.e:  $column_order=array('1''4','3','4'); The 2nd column will be order like the 4th column
     * @param array $form_actions Set optional forms actions
     *
     * @author Julio Montoya
     */
    public static function display_sortable_config_table($header, $content, $sorting_options = array(), $paging_options = array(), $query_vars = null, $column_show = array(), $column_order = array(), $form_actions = array()) {
        global $origin;
        $column = isset($sorting_options['column']) ? $sorting_options['column'] : 0;
        $default_items_per_page = isset($paging_options['per_page']) ? $paging_options['per_page'] : 20;

        $table = new SortableTableFromArrayConfig($content, $column, $default_items_per_page, 'tablename', $column_show, $column_order);

        if (is_array($query_vars)) {
            $table->set_additional_parameters($query_vars);
        }
        // show or hide the columns header
        if (is_array($column_show)) {
            for ($i = 0; $i < count($column_show); $i++) {
                if (!empty($column_show[$i])) {
                    isset($header[$i][0]) ? $val0 = $header[$i][0] : $val0 = null;
                    isset($header[$i][1]) ? $val1 = $header[$i][1] : $val1 = null;
                    isset($header[$i][2]) ? $val2 = $header[$i][2] : $val2 = null;
                    isset($header[$i][3]) ? $val3 = $header[$i][3] : $val3 = null;
                    $table->set_header($i, $val0, $val1, $val2, $val3);
                }
            }
        }
        $table->set_form_actions($form_actions);
        $table->display();
    }

    /**
     * Displays a normal message. It is recommended to use this public function
     * to display any normal information messages.
     *
     * @author Roan Embrechts
     * @param string $message - include any additional html
     *                          tags if you need them
     * @param bool	Filter (true) or not (false)
     * @return void
     */
    public static function display_normal_message($message, $filter = true, $force = false, $display_none = false, $message_id = "id_normal_message", $title = "") {
        global $charset;
        global $notification;
        if (empty($title)) {
            $title = get_lang('Message');
        }
        // no feedback messages
        if (api_get_setting('display_feedback_messages') == 'false' && $force === false) {
            return false;
        }
        if (!is_array($message)) {
            $notification[] = array('type' => 'normal', 'message' => $message);
            if ($filter) {
                //filter message
                $message = api_htmlentities($message, ENT_QUOTES, api_is_xml_http_request() ? 'UTF-8' : $charset);
            }
        }
        $display_css = "";
        if ($display_none) {
            $display_css = "style=\"display:none;\"";
        }
        $microtime =  microtime(true);
        $microtime = str_replace('.', '', $microtime);
        $message_id = $message_id . '_' .$microtime;
//        echo "<script> 
//        var time$microtime = 1;
//        function delay$microtime(id){
//            if(time$microtime == 5){
//                time$microtime = 1;
//                $('#'+id).fadeOut(2000);        
//            }
//            time$microtime = time$microtime + 1;
//        }
//        $(document).ready(function(){
//            $('#".$message_id."')
//                .mouseover(function() {
//                    clearInterval(_delay$microtime);
//                    $('#".$message_id."').stop(true, true).fadeIn();        
//                })
//                .mouseout(function() {
//                    timedelay = 5;
//                    clearInterval(_delay$microtime);
//                    _delay$microtime = setInterval( function() { delay$microtime('".$message_id."') }, 500)
//                });
//            // page loads starts delay timer
//            _delay$microtime = setInterval( function() { delay$microtime('".$message_id."') }, 500)
//        });
//        </script>";
//		echo '<div id="'.$message_id.'" class="normal-message " '.$display_css.'>';
        echo '<div id="' . $message_id . '" class="normal-message-lib" ' . $display_css . '>';
        //Display :: display_icon('message_error.png', get_lang('ErrorMessage'), array ('style' => 'float:right   ; margin-right:10px;'));
        echo '<div class="back_title_message_blue" style="height: 30px;line-height: 30px;padding:0px;">';

        echo '<div style="float: right;">';
        echo '<a style="color:#ffffff;padding-right:10px;display: block;" href="#" class="close_message_box"> <span class="close_box2" style="background-position:right;padding-right:15px;">' . get_lang('Close') . ' </span></a>';
        echo '</div>';

        echo "<div style=\"color:#ffffff;padding-left:10px;\" style=\"\" class=\"title_message\">$title</div>";
        echo "</div>";

        echo '<div class="normal_message_content" style="">';
        if (is_array($message)) {
            if (count($message)) {
                echo '<ul>';
                foreach ($message as $pointer => $msg) {
                    echo '<li>' . $msg . '</li>';
                }
                echo '</ul>';
            }
        } else {
            echo $message;
        }
        echo '</div>';

        echo '<div class="image_right">';
        echo Display :: display_icon('avatar_normal_message.png');
        echo '</div>';
        /*
          get_lang('ErrorMessage', array ('style' => 'float:left; margin-right:10px;'));
         */
        echo '<div class="clear"></div>';
        echo '</div>';
    }

    /**
     * Displays an warning message. Use this if you want to draw attention to something
     * This can also be used for instance with the hint in the exercises
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @param string $message
     * @param bool	Filter (true) or not (false)
     * @return void
     */
    public static function display_warning_message($message, $filter = true, $force = false, $display_none = false, $message_id = "id_warning_message", $title = "") {
        global $charset;
        global $notification;
        if (empty($title)) {
            $title = get_lang('WarningMessage');
        }
        // no feedback messages
        if (api_get_setting('display_feedback_messages') == 'false' && $force === false) {
            return false;
        }

        $notification[] = array('type' => 'warning', 'message' => $message);
        if ($filter) {
            //filter message
            $message = api_htmlentities($message, ENT_QUOTES, api_is_xml_http_request() ? 'UTF-8' : $charset);
        }
        
        if (!headers_sent()) {
            echo '<style type="text/css" media="screen, projection">
            /*<![CDATA[*/
            @import "' . api_get_path(WEB_CODE_PATH) . 'css/' . api_get_setting('stylesheets') . '/default.css";
            /*]]>*/
            </style>';
        }
        $display_css = "";
        if ($display_none) {
            $display_css = "style=\"display:none;\"";
        }
        $microtime =  microtime(true);
        $microtime = str_replace('.', '', $microtime);
        $message_id = $message_id . '_' .$microtime;
        echo '<div id="' . $message_id . '" class="warning-message-lib" ' . $display_css . ' >';        
//		echo '<div id="'.$message_id.'" class="warning-message" '.$display_css.' >';
        echo '<div class="back_title_message_yellow" style="height: 30px;line-height: 30px;padding:0px;" >';
//        echo '<div style="float:right">';
//        echo '<a style="color:#ffffff" href="#" class="close_message_box">' . strtoupper(get_lang('Close')) . ' ' . Display::return_icon('pixel.gif', get_lang('Close'), array('class' => 'close_box')) . '</a>';
//        echo '</div>';
        echo '<div style="float: right;">';
        echo '<a style="color:#ffffff;padding-right:10px;display: block;" href="#" class="close_message_box"> <span class="close_box2" style="background-position:right;padding-right:15px;">' . get_lang('Close') . ' </span></a>';
        echo '</div>';
        
        echo "<div class=\"title_message\" style=\"color:#ffffff;padding-left:10px;\" >$title</div>";
        echo '</div>';
        echo '<div class="warning_message_content">';
        echo $message;
        echo '</div>';
        echo '<div class="image_right">';
        echo Display :: display_icon('avatar_warning_message.png');
        echo '</div>';
        echo '<div class="clear"></div>';
        echo '</div>';
    }

    /**
     * Displays an confirmation message. Use this if something has been done successfully
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @param string $message
     * @param bool	Filter (true) or not (false)
     * @return void
     */
    public static function display_confirmation_message($message, $filter = true, $force = false, $display_none = false, $message_id = "id_confirmation_message", $title = "", $headers_sent = true) {
        global $charset;
        global $notification;
        if (empty($title)) {
            $title = get_lang('ConfirmationMessage');
        }
        // no feedback messages
        // Always is necessary feedback, we are adding a lof code with divs(confirmation-message) and this is ugly when we have available a display function for make this, I added the force parameter that allow to use this function and avoid add more divs with class="confirmation-message"
        if (api_get_setting('display_feedback_messages') == 'false' && $force === false) {
            return false;
        }

        $notification[] = array('type' => 'confirmation', 'message' => $message);
        if (!is_array($message)) {
            $notification[] = array('type' => 'error', 'message' => $message);
            if ($filter) {
                //filter message
                $message = api_htmlentities($message, ENT_QUOTES, api_is_xml_http_request() ? 'UTF-8' : $charset);
            }
        }
        if (!headers_sent() AND $headers_sent) {
            echo '
						<style type="text/css" media="screen, projection">
						/*<![CDATA[*/
						@import "' . api_get_path(WEB_CODE_PATH) . 'css/' . api_get_setting('stylesheets') . '/default.css";
						/*]]>*/
						</style>';
        }
        $display_css = "style=\"padding:0px;\"";
        if ($display_none) {
            $display_css = "style=\"display:none;padding:0px;\"";
        }
        $microtime =  microtime(true);
        $microtime = str_replace('.', '', $microtime);
        $message_id = $message_id . '_' .$microtime;
        echo "<script> 
        var time$microtime = 1;
        function delay$microtime(id){
            if(time$microtime == 5){
                time$microtime = 1;
                $('#'+id).fadeOut(2000);        
            }
            time$microtime = time$microtime + 1;
        }
        $(document).ready(function(){
            $('#".$message_id."')
                .mouseover(function() {
                    clearInterval(_delay$microtime);
                    $('#".$message_id."').stop(true, true).fadeIn();        
                })
                .mouseout(function() {
                    timedelay = 5;
                    clearInterval(_delay$microtime);
                    _delay$microtime = setInterval( function() { delay$microtime('".$message_id."') }, 500)
                });
            // page loads starts delay timer
            _delay$microtime = setInterval( function() { delay$microtime('".$message_id."') }, 500)
        });
        </script>";
//	echo '<div id="'.$message_id.'" class="confirmation-message" '.$display_css.'>';
        echo '<div id="' . $message_id . '" class="confirmation-message-lib" ' . $display_css . '>';
        //Display :: display_icon('message_error.png', get_lang('ErrorMessage'), array ('style' => 'float:right   ; margin-right:10px;'));
        echo '<div class="back_title_message" style="height: 30px;line-height: 30px;padding:0px;">';

        echo '<div style="float: right;">';
        echo '<a style="color:#ffffff;padding-right:10px;display: block;" href="#" class="close_message_box"> <span class="close_box2" style="background-position:right;padding-right:15px;">' . get_lang('Close') . ' </span></a>';
        echo '</div>';

        echo "<div class=\"title_message\" style=\"padding-left:10px;\">$title</div>";
        echo '</div>';

        echo '<div class="confirmation_message_content" >';
        if (is_array($message)) {
            if (count($message)) {
                echo '<ul>';
                foreach ($message as $pointer => $msg) {
                    echo '<li>' . $msg . '</li>';
                }
                echo '</ul>';
            }
        } else {
            echo $message;
        }
        echo '</div>';

        echo '<div class="image_right" >';
        echo Display :: display_icon('avatar_confirmation_message.png');
        echo '</div>';
        /*
          get_lang('ErrorMessage', array ('style' => 'float:left; margin-right:10px;'));
         */
        echo '<div class="clear"></div>';
        echo '</div>';
    }

    /**
     * Displays an error message. It is recommended to use this public function if an error occurs
     *
     * @author Hugues Peeters
     * @author Roan Embrechts
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @param string $message - include any additional html
     *                          tags if you need them
     * @param bool	Filter (true) or not (false)
     * @return void
     */
    public static function display_error_message($message, $filter = true, $force = false, $display_none = false, $message_id = "id_error_message", $title = "") {
        global $charset;
        global $notification;

        if (empty($title)) {
            $title = get_lang('ErrorMessage');
        }
        // no feedback messages
        if (api_get_setting('display_feedback_messages') == 'false' && $force === false) {
            return false;
        }
        if (!is_array($message)) {
            $notification[] = array('type' => 'error', 'message' => $message);
            if ($filter) {
                //filter message
                $message = api_htmlentities($message, ENT_QUOTES, api_is_xml_http_request() ? 'UTF-8' : $charset);
            }
        }

        if (!headers_sent()) {
            echo '
						<style type="text/css" media="screen, projection">
						/*<![CDATA[*/
						@import "' . api_get_path(WEB_CODE_PATH) . 'css/' . api_get_setting('stylesheets') . '/default.css";
						/*]]>*/
						</style>';
        }
        $display_css = "";
        if ($display_none) {
            $display_css = "style=\"display:none;\"";
        }
        $microtime =  microtime(true);
        $microtime = str_replace('.', '', $microtime);
        $message_id = $message_id . '_' .$microtime;
        echo "<script> 
        var time$microtime = 1;
        function delay$microtime(id){
            if(time$microtime == 5){
                time$microtime = 1;
                $('#'+id).fadeOut(2000);        
            }
            time$microtime = time$microtime + 1;
        }
        $(document).ready(function(){
            $('#".$message_id."')
                .mouseover(function() {
                    clearInterval(_delay$microtime);
                    $('#".$message_id."').stop(true, true).fadeIn();        
                })
                .mouseout(function() {
                    timedelay = 5;
                    clearInterval(_delay$microtime);
                    _delay$microtime = setInterval( function() { delay$microtime('".$message_id."') }, 500)
                });
            // page loads starts delay timer
            _delay$microtime = setInterval( function() { delay$microtime('".$message_id."') }, 500)
        });
        </script>";
        echo '<div id="' . $message_id . '" class="error-message-lib" ' . $display_css . '>';
        //Display :: display_icon('message_error.png', get_lang('ErrorMessage'), array ('style' => 'float:right   ; margin-right:10px;'));                
        echo '<div class="back_title_message_red" style="height: 30px;line-height: 30px;padding:0px;">';

        echo '<div style="float: right;">';
        echo '<a style="color:#ffffff;padding-right:10px;display: block;" href="#" class="close_message_box"> <span class="close_box2" style="background-position:right;padding-right:15px;">' . get_lang('Close') . ' </span></a>';
        echo '</div>';

        echo "<div class=\"title_message\" style=\"padding-left:10px;\" style='color:#ffffff;'>$title</div>";
        echo '</div>';

        echo '<div class="error_message_content" >';
        if (is_array($message)) {
            if (count($message)) {
                echo '<ul>';
                foreach ($message as $pointer => $msg) {
                    echo '<li>' . $msg . '</li>';
                }
                echo '</ul>';
            }
        } else {
            echo $message;
        }
        echo '</div>';

        echo '<div class="image_right">';
        echo Display :: display_icon('avatar_error_message.png');
        echo '</div>';
        /*
          get_lang('ErrorMessage', array ('style' => 'float:left; margin-right:10px;'));
         */
        echo '<div class="clear"></div>';
        echo '</div>';
    }

    /**
     * Return an encrypted mailto hyperlink
     *
     * @param - $email (string) - e-mail
     * @param - $text (string) - clickable text
     * @param - $style_class (string) - optional, class from stylesheet
     * @return - encrypted mailto hyperlink
     */
    public static function encrypted_mailto_link($email, $clickable_text = null, $style_class = '') {
        global $charset;
        if (is_null($clickable_text)) {
            $clickable_text = $email;
        }
        //mailto already present?
        if (substr($email, 0, 7) != 'mailto:') {
            $email = 'mailto:' . $email;
        }
        //class (stylesheet) defined?
        if ($style_class != '') {
            $style_class = ' class="' . $style_class . ' clickable_email_link"';
        }
        //encrypt email
        $hmail = '';
        for ($i = 0; $i < strlen($email); $i++) {
            $hmail .= '&#' . ord($email {
                            $i }) . ';';
        }
        //encrypt clickable text if @ is present
        if (strpos($clickable_text, '@')) {

            for ($i = 0; $i < strlen($clickable_text); $i++) {
                $hclickable_text .= '&#' . ord($clickable_text {
                                $i }) . ';';
            }
        } else {
            $hclickable_text = htmlspecialchars($clickable_text, ENT_QUOTES, $charset);
        }

        //return encrypted mailto hyperlink
        return '<a href="' . $hmail . '"' . $style_class . '>' . $hclickable_text . '</a>';
    }

    /**
     * 	Create a hyperlink to the platform homepage.
     * 	@param string $name, the visible name of the hyperlink, default is sitename
     * 	@return string with html code for hyperlink
     */
    public static function get_platform_home_link_html($name = '') {
        if ($name == '') {
            $name = api_get_setting('siteName');
        }
        return "<a href=\"" . api_get_path(WEB_PATH) . "index.php\">$name</a>";
    }

    /**
     * Display the page header
     * @param string The name of the page (will be showed in the page title)
     * @param string Optional help file name
     */
    public static function display_header($tool_name = '', $help = NULL) {
        $nameTools = $tool_name;
        global $_plugins, $lp_theme_css, $mycoursetheme, $user_theme, $platform_theme;
        global $httpHeadXtra, $htmlHeadXtra, $htmlIncHeadXtra, $_course, $_user, $clarolineRepositoryWeb, $text_dir, $plugins, $_user, $rootAdminWeb, $_cid, $interbreadcrumb, $charset, $language_file, $noPHP_SELF;
        global $menu_navigation;
        global $help_content;
        include (api_get_path(INCLUDE_PATH) . "header.inc.php");
    }

    /**
     * Display the page header with responsive design
     * @param string The name of the page (will be showed in the page title)
     * @param string Optional help file name
     */
    public static function display_header_responsive($tool_name = '', $help = NULL) {
        $nameTools = $tool_name;
        global $_plugins, $lp_theme_css, $mycoursetheme, $user_theme, $platform_theme;
        global $httpHeadXtra, $htmlHeadXtra, $htmlIncHeadXtra, $_course, $_user, $clarolineRepositoryWeb, $text_dir, $plugins, $_user, $rootAdminWeb, $_cid, $interbreadcrumb, $charset, $language_file, $noPHP_SELF;
        global $menu_navigation;
        global $help_content;
        include (api_get_path(INCLUDE_PATH) . "header_responsive.inc.php");
    }

    /**
     * Display the page header for mobile
     * @param string The name of the page (will be showed in the page title)
     * @param string Optional help file name
     */
    public static function display_header_mobile() {
        include (api_get_path(INCLUDE_PATH) . "headerMobile.inc.php");
    }

    /**
     * Display the reduced page header (without banner)
     */
    public static function display_reduced_header($tool_name = '', $help = NULL) {
        global $_plugins, $lp_theme_css, $mycoursetheme, $user_theme, $platform_theme;
        global $httpHeadXtra, $htmlHeadXtra, $htmlIncHeadXtra, $_course, $_user, $clarolineRepositoryWeb, $text_dir, $plugins, $_user, $rootAdminWeb, $_cid, $interbreadcrumb, $charset, $language_file, $noPHP_SELF, $language_interface;
        global $menu_navigation;
        include (api_get_path(INCLUDE_PATH) . "reduced_header.inc.php");
    }

    /**
     * Display the page footer
     */
    public static function display_footer() {
        global $dokeos_version; //necessary to have the value accessible in the footer
        global $_plugins, $origin;
        if ($origin == 'learnpath') {
            include (api_get_path(INCLUDE_PATH) . "tool_footer.inc.php");
        } else {
            include (api_get_path(INCLUDE_PATH) . "footer.inc.php");
        }
    }

    /**
     * Display the page footer
     */
    public static function display_footer_responsive() {
        global $dokeos_version; //necessary to have the value accessible in the footer
        global $_plugins, $origin;
        if ($origin == 'learnpath') {
            include (api_get_path(INCLUDE_PATH) . "tool_footer.inc.php");
        } else {
            include (api_get_path(INCLUDE_PATH) . "footer_responsive.inc.php");
        }
    }

    /*     * *
     * Display the page footer for mobile
     */

    public static function display_footer_mobile() {
        include (api_get_path(INCLUDE_PATH) . "footerMobile.inc.php");
    }

    /**
     * Print an <option>-list with all letters (A-Z).
     * @param char $selected_letter The letter that should be selected
     */
    public static function get_alphabet_options($selected_letter = '') {
        $result = '';
        for ($i = 65; $i <= 90; $i++) {
            $letter = chr($i);
            $result .= '<option value="' . $letter . '"';
            if ($selected_letter == $letter) {
                $result .= ' selected="selected"';
            }
            $result .= '>' . $letter . '</option>';
        }
        return $result;
    }

    public static function get_numeric_options($min, $max, $selected_num = 0) {
        $result = '';
        for ($i = $min; $i <= $max; $i++) {
            $result .= '<option value="' . $i . '"';
            if (is_int($selected_num))
                if ($selected_num == $i) {
                    $result .= ' selected="selected"';
                }
            $result .= '>' . $i . '</option>';
        }
        return $result;
    }

    /**
     * Show the so-called "left" menu for navigating
     */
    public static function show_course_navigation_menu($isHidden = false) {
        global $output_string_menu;
        global $_setting;

        // check if the $_SERVER['REQUEST_URI'] contains already url parameters (thus a questionmark)
        if (!strstr($_SERVER['REQUEST_URI'], "?")) {
            $sourceurl = api_get_self() . "?";
        } else {
            $sourceurl = $_SERVER['REQUEST_URI'];
        }
        $output_string_menu = "";
        if ($isHidden == "true" and $_SESSION["hideMenu"]) {

            $_SESSION["hideMenu"] = "hidden";

            $sourceurl = str_replace("&isHidden=true", "", $sourceurl);
            $sourceurl = str_replace("&isHidden=false", "", $sourceurl);

            $output_string_menu .= " <a href='" . $sourceurl . "&isHidden=false'>" . "<img src=../../main/img/expand.gif alt='Show menu1' padding:'2px'/>" . "</a>";
        } elseif ($isHidden == "false" and $_SESSION["hideMenu"]) {
            $sourceurl = str_replace("&isHidden=true", "", $sourceurl);
            $sourceurl = str_replace("&isHidden=false", "", $sourceurl);

            $_SESSION["hideMenu"] = "shown";
            $output_string_menu .= "<div id='leftimg'><a href='" . $sourceurl . "&isHidden=true'>" . "<img src=../../main/img/collapse.gif alt='Hide menu2' padding:'2px'/>" . "</a></div>";
        } elseif ($_SESSION["hideMenu"]) {
            if ($_SESSION["hideMenu"] == "shown") {
                $output_string_menu .= "<div id='leftimg'><a href='" . $sourceurl . "&isHidden=true'>" . "<img src='../../main/img/collapse.gif' alt='Hide menu3' padding:'2px'/>" . "</a></div>";
            }
            if ($_SESSION["hideMenu"] == "hidden") {
                $sourceurl = str_replace("&isHidden=true", "", $sourceurl);
                $output_string_menu .= "<a href='" . $sourceurl . "&isHidden=false'>" . "<img src='../../main/img/expand.gif' alt='Hide menu4' padding:'2px'/>" . "</a>";
            }
        } elseif (!$_SESSION["hideMenu"]) {
            $_SESSION["hideMenu"] = "shown";
            if (isset($_cid)) {
                $output_string_menu .= "<div id='leftimg'><a href='" . $sourceurl . "&isHidden=true'>" . "<img src='main/img/collapse.gif' alt='Hide menu5' padding:'2px'/>" . "</a></div>";
            }
        }
    }

    /**
     * This public function displays an icon
     * @param string $image the filename of the file (in the main/img/ folder
     * @param string $alt_text the alt text (probably a language variable)
     * @param array additional attributes (for instance height, width, onclick, ...)
     */
    public static function display_icon($image, $alt_text = '', $additional_attributes = array()) {
        echo Display::return_icon($image, $alt_text, $additional_attributes);
    }

    /**
     * This public function returns the htmlcode for an icon
     *
     * @param string $image the filename of the file (in the main/img/ folder
     * @param string $alt_text the alt text (probably a language variable)
     * @param array additional attributes (for instance height, width, onclick, ...)
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version October 2006
     */
    public static function return_icon($image, $alt_text = '', $additional_attributes = array()) {
        $attribute_list = '';
        // alt text = the image if there is none provided (for XHTML compliance)
//        if ($alt_text == '') {
//            $alt_text = $image;
//        }

        // managing the additional attributes
        if (!empty($additional_attributes) and is_array($additional_attributes)) {
            $attribute_list = '';
            foreach ($additional_attributes as $key => $value) {
                $attribute_list.=$key . '="' . $value . '" ';
            }
        }
        return '<img src="' . api_get_path(WEB_IMG_PATH) . $image . '" alt="' . $alt_text . '"  title="' . $alt_text . '" ' . $attribute_list . '  />';
    }

    /**
     * Ivan, 05-SEP-2009: Deprecated, see api_get_person_name().
     *
     * Display name and lastname in a specific order
     * @param string Firstname
     * @param string Lastname
     * @param string Title in the destination language (Dr, Mr, Miss, Sr, Sra, etc)
     * @param string Optional format string (e.g. '%t. %l, %f')
     * @author Carlos Vargas <carlos.vargas@dokeos.com>
     */
    public static function user_name($fname, $lname, $title = '', $format = null) {
        if (empty($format)) {
            if (empty($fname) or empty($lname)) {
                $user_name = $fname . $lname;
            } else {
                $user_name = $fname . ' ' . $lname;
            }
        } else {
            $find = array('%t', '%f', '%l');
            $repl = array($title, $fname, $lname);
            $user_name = str_replace($find, $repl, $format);
        }
        return $user_name;
    }

    /**
     * This function adds a css file to $htmlHeadXtra, which is in /main/inc/header.inc.php then used and added to the output
     *
     * @param string $cssfile: the full web path to the css file
     *
     * @since Dokeos 2.0
     * @since June 2010
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
     */
    public static function css($cssfile) {
        global $htmlHeadXtra;

        $htmlHeadXtra[] = '<link type="text/css" href="' . $cssfile . '" rel="stylesheet" />';
    }

    /**
     * This function adds a javascript file to $htmlHeadXtra, which is in /main/inc/header.inc.php then used and added to the output
     *
     * @param string $javascriptfile: the full web path to the javascript file
     *
     * @since Dokeos 2.0
     * @since June 2010
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
     */
    public static function javascript($javascriptfile) {
        global $htmlHeadXtra;

        $htmlHeadXtra[] = '<script type="text/javascript" src="' . $javascriptfile . '" language="javascript"></script>';
    }

    /**
     * Display a reduced header in tools
     */
    public static function display_tool_header() {
        global $_plugins, $lp_theme_css, $mycoursetheme, $user_theme, $platform_theme;
        global $httpHeadXtra, $htmlHeadXtra, $htmlIncHeadXtra, $_course, $_user, $clarolineRepositoryWeb, $text_dir, $plugins, $_user, $rootAdminWeb, $_cid, $interbreadcrumb, $charset, $language_file, $noPHP_SELF, $language_interface;
        global $menu_navigation;
        include (api_get_path(INCLUDE_PATH) . "tool_header.inc.php");
    }

    public static function display_tool_footer() {
        global $dokeos_version; //necessary to have the value accessible in the footer
        global $_plugins;
        include (api_get_path(INCLUDE_PATH) . "tool_footer.inc.php");
    }

    /**
     * Display a reduced header in tools
     */
    public static function display_responsive_tool_header() {
        global $_plugins, $lp_theme_css, $mycoursetheme, $user_theme, $platform_theme;
        global $httpHeadXtra, $htmlHeadXtra, $htmlIncHeadXtra, $_course, $_user, $clarolineRepositoryWeb, $text_dir, $plugins, $_user, $rootAdminWeb, $_cid, $interbreadcrumb, $charset, $language_file, $noPHP_SELF, $language_interface;
        global $menu_navigation;
        include (api_get_path(INCLUDE_PATH) . "responsive_tool_header.inc.php");
    }

    public static function display_responsive_tool_footer() {
        global $dokeos_version; //necessary to have the value accessible in the footer
        global $_plugins;
        include (api_get_path(INCLUDE_PATH) . "responsive_tool_footer.inc.php");
    }

    public static function display_responsive_reporting_header() {
        global $_plugins, $lp_theme_css, $mycoursetheme, $user_theme, $platform_theme;
        global $httpHeadXtra, $htmlHeadXtra, $htmlIncHeadXtra, $_course, $_user, $clarolineRepositoryWeb, $text_dir, $plugins, $_user, $rootAdminWeb, $_cid, $interbreadcrumb, $charset, $language_file, $noPHP_SELF, $language_interface;
        global $menu_navigation;
        include (api_get_path(INCLUDE_PATH) . "responsive_reporting_header.inc.php");
    }

    public static function display_responsive_reporting_footer() {
        global $dokeos_version; //necessary to have the value accessible in the footer
        global $_plugins;
        include (api_get_path(INCLUDE_PATH) . "responsive_reporting_footer.inc.php");
    }

    /**
     * Prints a linkable icon
     * 
     * @author Ricardo Garcia Rodriguez <master.ojitos@gmail.com>
     * 
     * @param string $icon_class Class of icon to be printed
     * @param string $text Text to be printed on right of the icon
     * @param string $href Link to be followed by the clicked icon
     * @param string $title Title of the link
     * @param string $class Class of link to be printed
     * @param array $datas Array of data attributes to the link
     */
    function display_return_icon($icon_class, $text = '', $href = '', $title = '', $class = '', $datas = array()) {
        $icon = '<i class="' . $icon_class . '"></i>' . $text;
        if ($href != '' || $title != '') {
            if ($href != '')
                $href = ' href="' . $href . '"';
            if ($title != '')
                $title = ' title="' . $title . '"';
            if ($class != '')
                $class = ' class="' . $class . '"';
            if (count($datas)) {
                $data_text = "";
                foreach ($datas as $key => $value) {
                    $data_text.= ' data-' . $key . '="' . $value . '"';
                }
            }
            $icon = '<a' . $href . $title . $class . $data_text . '>' . $icon . '</a>';
        }
        return $icon;
    }

    /**
     * Render HTML for top actions in author tool  (icon + text) in:
     *
     * 		"author" 			=> main index author tool
     * 		"content"
     * 		"more_criteria"		=> show more criteria to forms
     * 		"page"
     * 		"questions"
     * 		"quiz"
     * 		"scenario"
     * 		"templates"
     * @deprecated this code is not used anymore
     * @param action - string - action to display
     * @since October 2010
     * @return string, html code
     */
    public static function render_author_action($action = null) {
        if (empty($action))
            return "";

        $text = "";
        $icon = "";

        switch ($action) {
            case "author": $icon = Display::return_icon('go_previous_32.png', get_lang("Author"));
                $text = get_lang("Author");
                break;

            case "content": break;

            case "more_criteria": $icon = Display::return_icon('navigation/plus.png', get_lang("SeeMoreOptions"));
                $text = get_lang("SeeMoreOptions");
                break;

            case "page": break;
            case "questions": break;
            case "quiz": break;
            case "scenario": break;
            case "templates": break;

            default: break;
        }

        return $icon . $text;
    }
    public static function display_confirmation_message2($message, $filter = true, $force = false, $display_none = false, $message_id = "id_confirmation_message", $title = "", $headers_sent = true) {
        global $charset;
        global $notification;
        if (empty($title)) {
            $title = get_lang('ConfirmationMessage');
        }
        // no feedback messages
        // Always is necessary feedback, we are adding a lof code with divs(confirmation-message) and this is ugly when we have available a display function for make this, I added the force parameter that allow to use this function and avoid add more divs with class="confirmation-message"
        if (api_get_setting('display_feedback_messages') == 'false' && $force === false) {
            return false;
        }

        $notification[] = array('type' => 'confirmation', 'message' => $message);
        if (!is_array($message)) {
            $notification[] = array('type' => 'error', 'message' => $message);
            if ($filter) {
                //filter message
                $message = api_htmlentities($message, ENT_QUOTES, api_is_xml_http_request() ? 'UTF-8' : $charset);
            }
        }
      
        $display_css = "style=\"padding:0px;\"";
        if ($display_none) {
            $display_css = "style=\"display:none;padding:0px;\"";
        }
        $microtime =  microtime(true);
        $microtime = str_replace('.', '', $microtime);
        $message_id = $message_id . '_' .$microtime;
        echo "<script> 
        var time$microtime = 1;
        function delay$microtime(id){
            if(time$microtime == 5){
                time$microtime = 1;
                $('#'+id).fadeOut(2000);        
            }
            time$microtime = time$microtime + 1;
        }
        $(document).ready(function(){
        $('#".$message_id."').click(function() {
                    $(this).remove();
            });
        $('#".$message_id."').animate({'bottom': '+=30px'}, 500);
            $('#".$message_id."')
                .mouseover(function() {
                    clearInterval(_delay$microtime);
                    $('#".$message_id."').stop(true, true).fadeIn();        
                })
                .mouseout(function() {
                    timedelay = 5;
                    clearInterval(_delay$microtime);
                    _delay$microtime = setInterval( function() { delay$microtime('".$message_id."') }, 500)
                });
            // page loads starts delay timer
            _delay$microtime = setInterval( function() { delay$microtime('".$message_id."') }, 500)
        });
        </script>";
        echo '<div id="' . $message_id . '" class="confirmation-message2" ' . $display_css . '>';
        //Display :: display_icon('message_error.png', get_lang('ErrorMessage'), array ('style' => 'float:right   ; margin-right:10px;'));
        echo '<div class="back_title_message" style="height: 30px;line-height: 30px;padding:0px;">';

        echo '<div style="float: right;">';
        echo '<a style="color:#ffffff;padding-right:10px;display: block;" href="#" class="close_message_box"> <span class="close_box2" style="background-position:right;padding-right:15px;">' . get_lang('Close') . ' </span></a>';
        echo '</div>';

        echo "<div class=\"title_message\" style=\"padding-left:10px;\">$title</div>";
        echo '</div>';

        echo '<div class="confirmation_message_content2" >';
        if (is_array($message)) {
            if (count($message)) {
                echo '<ul>';
                foreach ($message as $pointer => $msg) {
                    echo '<li>' . $msg . '</li>';
                }
                echo '</ul>';
            }
        } else {
            echo $message;
        }
        echo '</div>';


        echo '<div class="clear"></div>';
        echo '</div>';
    }
    public static function display_error_message2($message, $filter = true, $force = false, $display_none = false, $message_id = "id_error_message", $title = "") {
        global $charset;
        global $notification;
        if (empty($title)) {
            $title = get_lang('ErrorMessage');
        }
        // no feedback messages
        // Always is necessary feedback, we are adding a lof code with divs(confirmation-message) and this is ugly when we have available a display function for make this, I added the force parameter that allow to use this function and avoid add more divs with class="confirmation-message"
        if (api_get_setting('display_feedback_messages') == 'false' && $force === false) {
            return false;
        }

        
        if (!is_array($message)) {
            $notification[] = array('type' => 'error', 'message' => $message);
            if ($filter) {
                //filter message
                $message = api_htmlentities($message, ENT_QUOTES, api_is_xml_http_request() ? 'UTF-8' : $charset);
            }
        }
      
        $display_css = "style=\"padding:0px;\"";
        if ($display_none) {
            $display_css = "style=\"display:none;padding:0px;\"";
        }
        $microtime =  microtime(true);
        $microtime = str_replace('.', '', $microtime);
        $message_id = $message_id . '_' .$microtime;
        echo "<script> 
        var time$microtime = 1;
        function delay$microtime(id){
            if(time$microtime == 5){
                time$microtime = 1;
                $('#'+id).fadeOut(2000);        
            }
            time$microtime = time$microtime + 1;
        }
        $(document).ready(function(){
        $('#".$message_id."').click(function() {
                    $(this).remove();
            });
        $('#".$message_id."').animate({'bottom': '+=30px'}, 500);
            $('#".$message_id."')
                .mouseover(function() {
                    clearInterval(_delay$microtime);
                    $('#".$message_id."').stop(true, true).fadeIn();        
                })
                .mouseout(function() {
                    timedelay = 5;
                    clearInterval(_delay$microtime);
                    _delay$microtime = setInterval( function() { delay$microtime('".$message_id."') }, 500)
                });
            // page loads starts delay timer
            _delay$microtime = setInterval( function() { delay$microtime('".$message_id."') }, 500)
        });
        </script>";
        echo '<div id="' . $message_id . '" class="error-message2" ' . $display_css . '>';
        //Display :: display_icon('message_error.png', get_lang('ErrorMessage'), array ('style' => 'float:right   ; margin-right:10px;'));
        echo '<div class="back_title_message_red" style="height: 30px;line-height: 30px;padding:0px;">';

        echo '<div style="float: right;">';
        echo '<a style="color:#ffffff;padding-right:10px;display: block;" href="#" class="close_message_box"> <span class="close_box2" style="background-position:right;padding-right:15px;">' . get_lang('Close') . ' </span></a>';
        echo '</div>';

        echo "<div class=\"title_message\" style=\"padding-left:10px;\">$title</div>";
        echo '</div>';

        echo '<div class="error_message_content2" >';
        if (is_array($message)) {
            if (count($message)) {
                echo '<ul>';
                foreach ($message as $pointer => $msg) {
                    echo '<li>' . $msg . '</li>';
                }
                echo '</ul>';
            }
        } else {
            echo $message;
        }
        echo '</div>';


        echo '<div class="clear"></div>';
        echo '</div>';
    }
    
    /**
     * Prints a linkable menu
     * 
     * @author Edgar Huamani <ragdexd.rgd@gmail.com>
     * @deprecated This code is used in administration/portal
     * @param $page - integer - display exclude
     * @param $lang - String - use in Configure Inscription when $page = 9
     * @param $nodeId - String - use in Configure Inscription when $page = 9
     * @since January 2014
     * @return string, html code
     */
    public static function display_header_admin_of_portal($page=0,$lang="",$nodeId=""){
            $condition = "true";
            echo "\n<div class=\"actions\">";
            //$page_on = $page;
            $display[1] = "\n\t<a href=\"".api_get_path(WEB_PATH)."main/admin/settings.php?category=Platform\">".Display::return_icon('pixel.gif',get_lang('DokeosConfigSettings'), array('class' => 'toolactionplaceholdericon toolactionauthorsettings')).api_ucfirst(get_lang('DokeosConfigSettings'))."</a>";
            $display[2] = "\n\t<a href=\"".api_get_path(WEB_PATH)."main/admin/special_exports.php\">".Display::return_icon('pixel.gif',get_lang('SpecialExports'), array('class' => 'toolactionplaceholdericon toolactioncoursebackup')).api_ucfirst(get_lang('SpecialExports'))."</a>";
            $display[3] = "\n\t<a href=\"".api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=Index'."\">".Display::return_icon('pixel.gif',get_lang('SystemAnnouncements'), array('class' => 'toolactionplaceholdericon toolactionnews')).api_ucfirst(get_lang('SystemAnnouncements'))."</a>";
            $display[4] = "\n\t<a href=\"".api_get_path(WEB_PATH)."main/index.php?module=node&cmd=MenuLink&func=index\">".Display::return_icon('pixel.gif',get_lang('MenuLinks'), array('class' => 'toolactionplaceholdericon toolactionnewlink')).api_ucfirst(get_lang('MenuLinks'))."</a>";
            $display[5] = "\n\t<a href=\"".api_get_path(WEB_PATH)."main/admin/languages.php\">".Display::return_icon('pixel.gif',get_lang('Languages'), array('class' => 'toolactionplaceholdericon toolactionlanguage')).api_ucfirst(get_lang('Languages'))."</a>";
            $display[6] = "\n\t<a href=\"".api_get_path(WEB_PATH)."main/admin/configure_homepage.php\">".Display::return_icon('pixel.gif',get_lang('ConfigureHomePage'), array('class' => 'toolactionplaceholdericon toolactioneditportal')).api_ucfirst(get_lang('ConfigureHomePage'))."</a>";
            $display[7] = "\n\t<a href=\"".api_get_path(WEB_PATH)."main/admin/configure_inscription.php\">".Display::return_icon('pixel.gif',get_lang('ConfigureInscription'), array('class' => 'toolactionplaceholdericon toolactionregistrationpage')).api_ucfirst(get_lang('ConfigureInscription'))."</a>";
            $display[8] = "\n\t<a href=\"".api_get_path(WEB_PATH)."main/admin/statistics/index.php\">".Display::return_icon('pixel.gif',get_lang('ToolName'), array('class' => 'toolactionplaceholdericon toolactiontrafreporting')).api_ucfirst(get_lang('ToolName'))."</a>";
            $display[9] = "\n\t<a href=\"".api_get_path(WEB_PATH)."main/admin/agenda.php\">".Display::return_icon('pixel.gif',get_lang('GlobalAgenda'), array('class' => 'toolactionplaceholdericon toolactionother')).api_ucfirst(get_lang('GlobalAgenda'))."</a>";

            if (api_get_setting('show_emailtemplates') == 'true') {
                $display[10] = "\n\t<a href=\"".api_get_path(WEB_PATH)."main/admin/emailtemplates.php\">".Display::return_icon('pixel.gif',get_lang('Automaticemails'), array('class' => 'toolactionplaceholdericon toolactionautomaticemail')).api_ucfirst(get_lang('Automaticemails'))."</a>";
}
            if (!empty($phpMyAdminPath)) { 
                $display[11] = "\n\t<a href=\"$phpMyAdminPath\">".Display::return_icon('pixel.gif',get_lang('AdminDatabases'), array('class' => 'actionplaceholdericon actionmini_link')).api_ucfirst(get_lang('DBManagementOnlyForServerAdmin'))."</a>";
            } 
            if (!empty($_configuration['multiple_access_urls'])) {
                $display[12] = "\n\t<a href=\"".api_get_path(WEB_PATH)."main/admin/access_urls.php\">".Display::return_icon('pixel.gif',get_lang('ConfigureMultipleAccessURLs'), array('class' => 'actionplaceholdericon actionmini_link')).api_ucfirst(get_lang('ConfigureMultipleAccessURLs'))."</a>";                    
            }
            if (api_get_setting('allow_terms_conditions') == 'true') {
                $display[13] = "\n\t<a href=\"".api_get_path(WEB_PATH)."main/admin/legal_add.php\">".Display::return_icon('pixel.gif',get_lang('TermsAndConditions'), array('class' => 'actionplaceholdericon actionmini_link')).api_ucfirst(get_lang('TermsAndConditions'))."</a>";
            }
            /*
             * if SystemAnnouncements add menu
             */
            if($page == 3){
                echo '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=Index">' . Display::return_icon('pixel.gif', get_lang('Back'), array('class' => 'toolactionplaceholdericon toolactionlist')) . get_lang("list") . '</a>';
                echo '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=getForm">' . Display::return_icon('pixel.gif', get_lang('HomePage'), array('class' => 'toolactionplaceholdericon toolactionannounce_add')) . get_lang("AddNews") . '</a>';
            }
            /*
             * END if SystemAnnouncements add menu
             */
            /*
             * if ConfigureInscription add menu
             */
            if($page == 7){
                echo '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=RegistrationPage&func=getForm&language='.$lang.'&nodeId='.$nodeId.'">'.Display::return_icon('pixel.gif', get_lang("EditMessage"), array('class' => 'toolactionplaceholdericon tooledithome')).get_lang('EditMessage').'</a>';
                echo '<div class="float_r">';
                //Form of language
                 api_display_language_form(true,'right');
                echo '</div>';
            }
            /*
             * END if ConfigureInscription add menu
             */
            /*
             * if GlobalAgenda add menu
             */
            if($page == 9){
                if ($_SERVER["REQUEST_URI"]=='/main/admin/agenda.php?action=platformadd&view='){
                    $return .= '<a href="agenda.php">'.Display::return_icon('pixel.gif',get_lang('Back'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Back').'</a>';
                    $condition = "false";
                }
                $return .= '<a href="agenda.php?action=platformadd&view='.Security::remove_XSS($_GET['view']).'">'.Display::return_icon('pixel.gif', get_lang('AgendaAddPlatform'), array('class' => 'toolactionplaceholdericon toolcalendaraddevent')).get_lang('AgendaAddPlatform').'</a>';
                echo $return;
            }
            /*
             * END if GlobalAgenda dad menu
             */
            if($condition == "true"){
                foreach ($display as $key => $row) {
                    if($page != $key){
                        echo $row;
                    }
                }
            }
            echo "\n</div>";
    }
}

//end class Display
?>
