<?php
global $htmlHeadXtra, $htmlFootXtra, $htmlFootXtra, $lp_theme_css,$text_dir;
include 'inc/header.inc.php';
?>
<?php
    if(method_exists($this, 'getAction'))
       echo $this->getAction();
?>
<div id="content">