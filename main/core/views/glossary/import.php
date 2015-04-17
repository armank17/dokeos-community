<div id="wrapper_glossary_content">
<?php
if (!empty($formMessage)) {
    echo $formMessage;
}
echo '<a  href="'.  api_get_path(WEB_CODE_PATH).'upload/tool_templates/glossary/GlossaryTemplate.xls"><h3 class="backup-link custom-download-upload">' . get_lang('DownloadGlossaryTemplate') . '</h3></a><br><br><br><br>';
echo $glossaryImportForm->display();
?>
<div id="glossary_image">
    <img src="../../../img/instructor_analysis.png" class="abs">
</div>
</div>