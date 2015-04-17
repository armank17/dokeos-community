<script type="text/javascript" src="<?php echo api_get_path(WEB_PATH);?>main/appcore/library/jquery/jquery.validate.js"></script>
<link rel="stylesheet" href="/main/application/glossary/assets/css/css-export.css" />

<div class="fila">
    <div id="divMessage"></div>
    <div id="wrapper_glossary_content" style="float: left">
        <?php
            echo $this->form->display();
        ?>
    </div>
    <div class="glossary-instructor">
    </div>
</div>