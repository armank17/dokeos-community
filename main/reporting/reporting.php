<?php

require_once ('../inc/global.inc.php');

Display::display_responsive_reporting_header();   
// Top actions bar
?>
<!-- Content -->
<div class="span12">
    <div class="container" id="dokeos_content">
    <?php
    if (isset($_GET['action'])) {
        $module = get_lang(ucfirst($_GET['action']));
    } else {
        $module = get_lang("Course");
    }
    echo api_display_tool_title($module); ?>
    </div>
</div>
<!-- End content -->
<?php
Display::display_responsive_reporting_footer();