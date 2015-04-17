<?php $this->ajax->printJavascript("appcore/library/xajax/"); ?>
<!--<script language="javascript" src="appcore/library/jquery/AjaxUpload.2.0.min.js" type="text/javascript"></script>-->
<script type="text/javascript" src="<?php echo api_get_path(WEB_CODE_PATH).'application/courseInfo/assets/js/infoFunctions.js' ?>"></script>
<script type="text/javascript" src="<?php echo api_get_path(WEB_CODE_PATH).'application/courseInfo/assets/js/infoModel.js' ?>"></script>
<link rel="stylesheet" href="<?php echo api_get_path(WEB_CODE_PATH).'application/courseInfo/assets/css/info.css' ?>">
<a deleteFn="" title="Crop" id="logo-info" href="<?php echo api_get_path(WEB_CODE_PATH)."/index.php?module=courseInfo&cmd=Info&func=uploadShopLogo"; ?>"></a>
<script type="text/javascript">
    $(document).ready(function() {
        $("#btnUpdate").click(function() {
            var summary = CKEDITOR.instances['summary'].getData();
            var values = {};
            values['new_summary'] = summary;
            $.each($("#formEditCourse").serializeArray(), function(i, field) {
                values[field.name] = field.value;
            });
            var description = CKEDITOR.instances['textareaDescription'].getData();
            values['description'] = description;
            xajax_updateCourseShop(values);
            $('html,body').scrollTop(0);
        });

//        new AjaxUpload('#btnImageUpload', {
//            //action: 'application/ecommerce/views/Course/upload.php',
//            action: 'index.php?module=courseInfo&cmd=InfoAjax&func=uploadlogocourseshopEx',
//            onSubmit: function(file, ext) {
//                if (!(ext && /^(jpg|png|jpeg|gif|jpg)$/.test(ext))) {
//                    // extensiones permitidas
//                    alert('Error: Only permitted images.');
//                    // cancela upload
//                    return false;
//                }
//            },
//            onComplete: function(file, response) {
//            //    xajax_uploadImage(file);
//				
//				InfoModel.showActionDialogX($("#logo-info"));
//				
//            }
//        });
    });
</script>
<style>
    .ecommerce_left{
        width: 100px;
        text-align: right;
        padding-right: 20px;
        /*        padding-left: 15px;*/
    }
    .divPreview{
        border: 1px dashed #000;
        width: 100px;
        height: 100px;
    }
    .fontPreview{
        font-size: 20px;
        color: silver;
    }
</style>
<div id="divMessage"></div>
<form name="formEditCourse" id="formEditCourse" action="" method="POST" onsubmit="return false;">
    <table>
        <tr>
            <td colspan="2"><h1><?php echo $this->course['name']; ?></h1></td>
        </tr>
        <tr>
            <td  class="ecommerce_left"><?php echo $this->get_lang('Description'); ?></td>
            <td><?php api_disp_html_area('textareaDescription', $this->item['description'], '', '100%', null, array('ToolbarSet' => 'Survey')) ?></td>
        </tr>
        <tr>
            <td  class="ecommerce_left"><?php echo $this->get_lang('Summary'); ?></td>
            <td>
                <?php api_disp_html_area('summary', $this->item['summary'], '', '100%', null, array('ToolbarSet' => 'Survey')) ?>
            </td>
        </tr>
        <?php
        /*
        <tr>
            <td class="ecommerce_left"><?php echo $this->get_lang('AddPicture'); ?></td>
            <td>
                <div style="float: left; width:400px;">
                    <input type="text" style="width: 60%;" class="txtImageFile" readonly="readonly" name="txtImageFile" id="txtImageFile" value="" >
                    <button class="btnUpload" id="btnImageUpload" name="btnImageUpload"><?php echo get_lang('Upload'); ?></button>
                </div>
            </td>
        </tr>
        <tr>
            <td  class="ecommerce_left"></td>
            <td><?php echo $this->get_lang('PNGorJPGimages'); ?></td>
        </tr>
        <tr>
            <td  class="ecommerce_left"><?php echo $this->get_lang('Preview'); ?></td>
            <td><div id="divImgPreview" class="divPreview">
            <?php
            $img = '';
            if (strlen(trim($this->item['image'])) > 0) {
                $img = '<img src="' . api_get_path(WEB_PATH) . 'main/application/ecommerce/assets/images/' . $this->item['image'] . '" />';
            } else {
                $img = '<p class="fontPreview">100 x 100<p>';
            }
            echo $img;
            ?>
        </div></td>
        </tr>
        <tr>
            <td  class="ecommerce_left"></td>
            <td><input id="qf_d13865" type="checkbox" value="1" name="remove_img">
                <label for="qf_d13865"><?php echo stripslashes($this->get_lang('RomeveImageAndWithoutImage')); ?></label></td>
        </tr>
        
        */
        ?>
                
        <tr>
            <td><input type="hidden" id="id_item_commerce" name="id_item_commerce" value="<?php echo $this->item['id']; ?>" /></td>
            <td><button class="save" style="margin-top: 20px;" id="btnUpdate" type="submit" name="btnUpdate"><?php echo get_lang('Submit'); ?></button></td>
        </tr>        
    </table>
</form>
<input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
