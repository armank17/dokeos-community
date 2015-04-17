<?php

// including the global Dokeos file
require_once '../inc/global.inc.php';

Display::display_responsive_tool_header();
// Top actions bar
echo '<div class="container bar-action">
         <div class="content-padding-small">
              <div class="row-fluid">';
// icons section

// end icons section
echo          '</div>
         </div>
      </div>';
// Content
echo '<div class="container">
                <div class="content-padding">
                    <div class="row-fluid">';

echo "Hello World";

echo '               </div>
                </div>
            </div>';
// End content

// Bottom actions bar
echo '<div class="container bar-action">
         <div class="content-padding-small">
              <div class="row-fluid">';
// icons section

// end icons section
echo '        </div>
         </div>
      </div>'; 
Display::display_responsive_tool_footer();