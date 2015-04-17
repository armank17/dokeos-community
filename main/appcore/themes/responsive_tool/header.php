<?php
global $htmlHeadXtra, $htmlFootXtra,$text_dir;
include 'inc/responsive_tool_header.inc.php';
?>
<?php
    if(method_exists($this, 'getAction'))
       echo $this->getAction();
?>