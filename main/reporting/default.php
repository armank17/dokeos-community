<?php

require_once ('../inc/global.inc.php');

Display::display_responsive_reporting_header();   
// Top actions bar
?>
<div class="span12">
    <div class="container bar-action">
     <?php echo Display::return_icon('pixel.gif',"",array('class' => 'toolactionplaceholdericon toolcalendaraddevent'))." &nbsp;Hello"; ?>
     <?php echo Display::return_icon('pixel.gif',"",array('class' => 'toolactionplaceholdericon toolcalendaraddevent'))." &nbsp;Thomas"; ?>
    </div>
</div>
<!-- Content -->
<div class="span12">
    <div class="container" id="dokeos_content">
     New reporting layout
    </div>
</div>
<!-- End content -->
<div class="span12">
    <div class="container bar-action">
     <?php echo Display::return_icon('pixel.gif',"",array('class' => 'toolactionplaceholdericon toolcalendaraddevent'))." &nbsp;Hello"; ?>
     <?php echo Display::return_icon('pixel.gif',"",array('class' => 'toolactionplaceholdericon toolcalendaraddevent'))." &nbsp;Thomas"; ?>
    </div>
</div>
<?php
Display::display_responsive_reporting_footer();