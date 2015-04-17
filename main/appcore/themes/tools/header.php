<?php
global $htmlHeadXtra, $tool_name, $htmlFootXtra, $lp_theme_css,$text_dir;

if (isset($this->toolName)) {
    $tool_name = $this->toolName;
}

include 'inc/tool_header.inc.php';
?>
<?php
    if(method_exists($this, 'getAction'))
       echo $this->getAction();
?>

<div id="content">
