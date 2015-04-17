<?php
$id=$_GET['cmsId'];
if($this->showEditor):
    ?>
<div id="message-content"></div>
<form id="cms-form" name="cms_form" accept-charset="utf-8" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=cms&cmd=Cms&func=saveCms&'.api_get_cidreq(); ?>" method="POST">
    <span>
  <button id="cms-submit">
    <?php echo $this->get_lang('Save'); ?>
</button>
    </span>
        <div id="message-box">
            <span id="confirmation-message"></span>
        </div>

<div style="padding-right:1px;text-align:left;margin-left:10px;margin-top:5px;display:inline-block">
    <div class="row">
        <span class="label"style='margin-right: 17px;margin-top: -11px'><?php echo get_lang('Title');?></span>
        <span class="formw">
            <input id="idTitle" class="" type="text" value="<?php echo $this->title ?>" name="title" size="44%"/>
            <input type="hidden" name="cmsId" value="<?php echo $id ?>"/>
        </span>
    </div>
</div>
<?php
    api_disp_html_area('cms_editor', $this->editorValue, '', '', null,$this->editorConfig);
?>
</form>
<?php endif;

?>
