<?php
$this->ajax->printJavascript("appcore/library/xajax/");
?>
<?php

$html = $this->getHtmlUser();
echo $html;


?>
<a href="#" onclick="xajax_showForm()">mostrar formulario</a>
<div id="divForm"></div>
