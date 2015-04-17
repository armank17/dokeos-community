<?php
$this->ajax->printJavascript("appcore/library/xajax/");
$sign = api_get_setting('e_commerce_catalog_decimal');
$user_id = api_get_user_id();
$user_info = api_get_user_info($user_id);
$admin = ($user_info['official_code'] == 'ADMIN') ? true : false;
?>
<!--<script type="text/javascript" src="appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>
<link rel="stylesheet" media="all" type="text/css" href="appcore/library/jquery/jquery.alerts/jquery.alerts.css" />-->
<link rel="stylesheet" media="all" type="text/css" href="appcore/library/jquery/jQuery-Timepicker/jquery-ui-timepicker-addon.css" />
<script type="text/javascript" src="appcore/library/jquery/jQuery-Timepicker/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="appcore/library/jquery/jQuery-Timepicker/jquery-ui-sliderAccess.js"></script>
<script type="text/javascript" src="appcore/library/jquery/jquery.numeric.js"></script>
<script type="text/javascript" src="appcore/library/jquery/jquery.validate.js"></script>
<!--<script language="javascript" src="appcore/library/jquery/AjaxUpload.2.0.min.js" type="text/javascript"></script>-->
<script>
    $(document).ready(function() {
        $("#txtCost").keypress(function(e){
            if($(this).val().length>6 && e.keyCode!=8){
                return false;
            }
        })
        $("#txtDuration").keypress(function(e){
            if($(this).val().length>=2 && e.keyCode!=8){
                return false;
            }
        })
<?php if (intval($sign) == 1): ?>
            $(".numeric").numeric({decimal: "."});
<?php else: ?>
            $(".numeric").numeric();
<?php endif; ?>
        $(".integer").numeric(false, function() {
            alert("Integers only");
            this.value = "";
            this.focus();
        });


        $("#formEditCourse").validate({
            debug: false,
            rules: {
                name: {
                    required: true
                }
            },
            messages: {
                name: {
                    required: "text"
                }
            },
            submitHandler: function(form) {
                var summary = CKEDITOR.instances['summary_shop'].getData();
                var values = {};
                values['new_summary'] = summary;
                $.each($("#formEditCourse").serializeArray(), function(i, field) {
                    values[field.name] = field.value;
                });
                var description = CKEDITOR.instances['textareaDescription'].getData();
                values['textareaDescription'] = description;
                xajax_editCourse(values);
                $('html,body').scrollTop(0);
                if ($('#id_catgory').val() != 0) {
                    setTimeout(function() {
                        var mypath = typeof myDokeosWebPath !== 'undefined' ? myDokeosWebPath : '/';
                        window.location.href = mypath + "main/admin/ecommerce_courses.php";
                    }, 1000);
                }
            }
        });

        $('#duration_start').datetimepicker({
            onClose: function(dateText, inst) {
                var endDateTextBox = $('#duration_end');
                if (endDateTextBox.val() != '') {
                    var testStartDate = new Date(dateText);
                    var testEndDate = new Date(endDateTextBox.val());
                    if (testStartDate > testEndDate)
                        endDateTextBox.val(dateText);
                }
                else {
                    endDateTextBox.val(dateText);
                }
            },
            onSelect: function(selectedDateTime) {
                var start = $(this).datetimepicker('getDate');
                $('#duration_end').datetimepicker('option', 'minDate', new Date(start.getTime()));
            }
        });
        $('#duration_end').datetimepicker({
            onClose: function(dateText, inst) {
                var startDateTextBox = $('#duration_start');
                if (startDateTextBox.val() != '') {
                    var testStartDate = new Date(startDateTextBox.val());
                    var testEndDate = new Date(dateText);
                    if (testStartDate > testEndDate)
                        startDateTextBox.val(dateText);
                }
                else {
                    startDateTextBox.val(dateText);
                }
            },
            onSelect: function(selectedDateTime) {
                var end = $(this).datetimepicker('getDate');
                $('#duration_start').datetimepicker('option', 'maxDate', new Date(end.getTime()));
            }
        });

//        new AjaxUpload('#btnImageUpload', {
//            action: 'application/ecommerce/views/Course/upload.php',
//            onSubmit: function(file, ext) {
//                if (!(ext && /^(jpg|png|jpeg|gif|jpg)$/.test(ext))) {
//                    // extensiones permitidas
//                    alert('Error: Only permitted images.');
//                    // cancela upload
//                    return false;
//                }
//            },
//            onComplete: function(file, response) {
//                xajax_uploadImage(file);
//            }
//        });

$("#FreeCourse").click(function(){
    if ($(this).is(":checked")) {
        $("#txtCost").attr('disabled','disabled');
        $("#txtCostTTC").attr('disabled','disabled');
        $("#rdPrixHT").attr('disabled','disabled');
        $("#rdPrixTTC").attr('disabled','disabled');
        $("#txtCost").val("0.00");
        $("#txtCostTTC").val("0.00");
    }else{
        $("#txtCost").removeAttr('disabled');
        $("#txtCostTTC").removeAttr('disabled');
        $("#rdPrixHT").removeAttr('disabled');
        $("#rdPrixTTC").removeAttr('disabled');
    }
});
 
    });
</script>
<style>
    .ecommerce_left{
        width: 100px;
        text-align: right;
        padding-right: 20px;
        padding-left: 15px;
    }
    .ecommerce_date    {
        color:#777;        
    }
    .catalogPreview{
        box-shadow: 2px 2px 5px #999;
        -webkit-box-shadow:  2px 2px 5px #999;
        -moz-box-shadow:  2px 2px 5px #999;
        width: 300px;
        height: 132px;
        padding:10px;
        border:1px solid #cccccc;
        background:#ffffff;
    }
    #content {
        position:relative;        
    }
    #dialog-content {
        position: absolute; 
        top: 50px;
        right: 50px;
        width: 850px; 
    }
    #thumnailCatalogPreview {
        cursor: pointer;
    }
    #divImgPreview{
        border: 1px dashed #000;
        width: 100px;
        height: 100px;
    }
    .fontPreview{
        font-size: 20px;
        color: silver;
    }    
</style>
<?php
$objCourseEcommerce = $this->getCourseEcommerce();
$objCourse = $this->getCourse($objCourseEcommerce->code);

?>
<div id="divMessage"></div>
<form name="formEditCourse" id="formEditCourse" action="" method="POST" onsubmit="return false;">
    <input name="admin" id="admin" type="hidden" value="<?php echo $admin ?>"  />
    <table width="100%">
        <tr>
            <td colspan="2"><div id="divMessage"></div></td>
        </tr>
        <tr>
            <td colspan="2">
                <table style="width: 100%;">
                     <tr>
						<td colspan="2"><h1><?php echo $objCourse->title; ?></h1></td>
                     </tr>                         
                    <tr>
                        <td>
                            <table style="width:500px;">
                                      
                                <?php
                                if ($admin) {
                                    ?>
                                    <tr>
                                        <td  class="ecommerce_left"><?php echo $this->get_lang('Price'); ?></td>
                                        <td><?php
                                            $ht = 'checked="checked"';
                                            $ttc = '';
                                            $prixHT = $objCourseEcommerce->cost;
                                            if ($objCourseEcommerce->chr_type_cost == 'TTC') {
                                                $ht = '';
                                                $ttc = 'checked="checked"';
                                                $prixHT = $objCourseEcommerce->cost_ttc;
                                            }
                                            ?>
                                            <input name="txtCostHT" id="txtCostHT" type="hidden" size="10" value="<?php echo $objCourseEcommerce->cost; ?>" class="numeric" />
                                            <input <?php echo $this->read_only ?> name="txtCost" id="txtCost" type="text" size="10" onkeyup="xajax_calculateCostTTC(xajax.getFormValues('formEditCourse'))" value="<?php echo api_number_format($prixHT); ?>" class="numeric" />
                                            <input <?php echo $this->disabled ?> name="priceType" id="rdPrixHT" type="radio" value="HT" <?php echo $ht; ?> onclick="xajax_calculateCostTTC(xajax.getFormValues('formEditCourse'))"/><label for="rdPrixHT"><?php echo $this->get_lang('Ht'); ?></label>
                                            <input <?php echo $this->disabled ?> name="priceType" id="rdPrixTTC" type="radio" value="TTC" <?php echo $ttc; ?> onclick="xajax_calculateCostTTC(xajax.getFormValues('formEditCourse'))"/><label for="rdPrixTTC"><?php echo $this->get_lang('Ttc'); ?></label>
                                        </td>
                                    </tr>                                
                                    <tr>
                                        <td  class="ecommerce_left"><?php echo get_lang('langTotal'); ?>:</td>
                                        <td><input name="txtCostTTC" id="txtCostTTC" type="text" onkeyup="xajax_calculateCostHT(xajax.getFormValues('formEditCourse'))" size="10" value="<?php echo api_number_format($objCourseEcommerce->cost_ttc); ?>" class="numeric" readonly />
                                        </td>
                                    </tr>
                                    <?php
                                    if(api_get_setting('display_shop_free_course') === 'true'){
                                        
                                        if($objCourseEcommerce->chr_type_cost == "0"){
                                            echo '<script>
                                            $("#txtCost").attr("disabled","disabled");
                                            $("#txtCostTTC").attr("disabled","disabled");
                                            $("#rdPrixHT").attr("disabled","disabled");
                                            $("#rdPrixTTC").attr("disabled","disabled");
                                            </script>';
                                        }
                                        ?>
                                    <tr>
                                        <td class="ecommerce_left"><?php echo get_lang('Free'); ?>:
                                            
                                        </td>
                                        <td>
                                            <input type="checkbox" id="FreeCourse" 
                                                <?php  
                                                if ( $objCourseEcommerce->chr_type_cost == "0" ){echo "checked='checked'" ;}?>/>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                    <tr>
                                        <td  class="ecommerce_left"><?php echo $this->get_lang('EcommerceDuration'); ?></td>
                                        <td><input <?php echo $this->read_only ?> name="txtDuration" id="txtDuration" value="<?php echo $objCourseEcommerce->duration; ?>" class="integer" />
                                            <?php
                                            $array_duration = array('day', 'week', 'month');
                                            ?>
                                            <select name="duration_type" <?php echo $this->disabled ?> >
                                                <?php
                                                foreach ($array_duration as $index => $duration)
                                                    if ($duration == $objCourseEcommerce->duration_type)
                                                        echo '<option value="' . $duration . '" selected="selected">' . $this->get_lang(ucfirst($duration)) . '</option>';
                                                    else
                                                        echo '<option value="' . $duration . '">' . $this->get_lang(ucfirst($duration)) . '</option>';
                                                ?>
                                            </select></td>
                                    </tr>
                                    <?php
                                    if ($objCourseEcommerce->status) {
                                        ?>
                                        <tr>
                                            <td  class="ecommerce_left"><?php echo $this->get_lang('EcommerceProfile'); ?></td>
                                            <td>
                                                <input <?php echo $this->disabled ?>  id="rdstatus" type="radio" checked="checked" value="1" name="status"> <?php echo $this->get_lang('Yes'); ?>
                                                <input <?php echo $this->disabled ?>  id="rdstatus" type="radio"  value="0" name="status"> <?php echo $this->get_lang('No'); ?>
                                            </td>
                                        </tr>                                    
                                        <?php
                                    } else {
                                        ?>
                                        <tr>
                                            <td  class="ecommerce_left"><?php echo $this->get_lang('EcommerceProfile'); ?></td>
                                            <td>
                                                <input <?php echo $this->disabled ?>  id="rdstatus" type="radio" value="1" name="status"> <?php echo $this->get_lang('Yes'); ?>
                                                <input <?php echo $this->disabled ?>  id="rdstatus" type="radio"  checked="checked" value="0" name="status"> <?php echo $this->get_lang('No'); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td  class="ecommerce_left"></td>
                                            <td></td>
                                        </tr>        
                                        <?php
                                    }
                                    ?>                                
                                    <tr>
                                        <td  class="ecommerce_left"><?php echo $this->get_lang('Category'); ?></td>
                                        <td>
                                            <select name="id_category" id="id_catgory" <?php echo $this->disabled ?> >
                                                <option value="0"><?php echo get_lang("SelectACategory") ?></option>
                                                <?php
                                                $objCollection = $this->getListCategory();
                                                foreach ($objCollection as $arrayCategory) {
                                                    if ($objCourseEcommerce->id_category == $arrayCategory['id_category'])
                                                        echo '<option value="' . $arrayCategory['id_category'] . '" selected="selected">' . $arrayCategory['chr_category'] . '</option>';
                                                    else
                                                        echo '<option value="' . $arrayCategory['id_category'] . '">' . $arrayCategory['chr_category'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </td>
                        <td style="width:35%">
                            <?php
                            if ($admin) {
                                $logo_sys_path = api_get_path(SYS_COURSE_PATH).$objCourse->directory.'/';
                                $logo_web_path = api_get_path(WEB_COURSE_PATH).$objCourse->directory.'/';
                                foreach (glob($logo_sys_path . 'course_logo.*') as $path_file) {
                                        $new_file_path = pathinfo($path_file);
                                        $exts = array("jpg","jpeg","gif","png");
                                        if(in_array($new_file_path['extension'],$exts))
                                                $image = $logo_web_path . $new_file_path['basename'];
                                }
                                ?>
                                <div id="thumnailCatalogPreview" style="width:185px; height:140px; float:right; margin-right: 25px;" class="catalogPreview dialog-content" onclick="xajax_loadPreviewCatalog(<?php echo "'".$logo_sys_path."','".$logo_web_path."'" ?>)">
                                    <img src="<?php echo ($image ? $image :"application/ecommerce/assets/images/thumb_shop_small.png"); ?>"/>
                                </div>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php /*
        <tr>
            <td class="ecommerce_left"><?php echo $this->get_lang('AddPicture'); ?></td>
            <td>
                <div style="float: left;">
                    <input type="text" readonly="readonly" name="txtImageFile" id="txtImageFile" value="<?php echo $objCourseEcommerce->image; ?>" >
                    <button class="btnUpload" id="btnImageUpload" name="btnImageUpload"><?php echo get_lang('Search'); ?></button>
                </div>
            </td>
        </tr>
        
        <tr>
            <td  class="ecommerce_left"></td>
            <td><?php echo $this->get_lang('EcommercePNGorJPGimages'); ?></td>
        </tr> 
        
        <tr>
            <td  class="ecommerce_left"><?php echo $this->get_lang('EcommercePreview'); ?></td>
            <td><div id="divImgPreview">
                    <?php
                    $img = '';
                    if (strlen(trim($objCourseEcommerce->image)) > 0)
                        $img = '<img src="' . api_get_path(WEB_PATH) . 'main/application/ecommerce/assets/images/' . $objCourseEcommerce->image . '" />';
                    else
                        $img = '<p class="fontPreview">100 x 100<p>';
                    echo $img;
                    ?>
                </div></td>
        </tr>
      
        <tr>
            <td></td>
            <td><input id="qf_d13865" type="checkbox" value="1" name="remove_img">
                <label for="qf_d13865"><?php echo stripslashes($this->get_lang('EcommerceRomeveImageAndWithoutImage')); ?></label></td>
        </tr>
        */ ?> 
        <tr>
            <td  class="ecommerce_left"><?php echo 'Pop-up ' . $this->get_lang('Description'); ?></td>
            <td>
                <?php api_disp_html_area('textareaDescription', $objCourseEcommerce->description, '', '100%', null, array('ToolbarSet' => 'Survey')) ?>
            </td>
        </tr>                
        <tr>
            <td  class="ecommerce_left"><?php echo end(explode(" ", get_lang('TooShort'))) . ' ' . $this->get_lang('Description'); ?></td>
            <td><?php api_disp_html_area('summary_shop', $objCourseEcommerce->summary, '', '100%', null, array('ToolbarSet' => 'Survey')) ?></td>
        </tr>                
        <tr>
            <td><input type="hidden" id="id_item_commerce" name="id_item_commerce" value="<?php echo $objCourseEcommerce->id; ?>" /></td>
            <td><button class="save" style="float: right ! important; margin-top: 20px;" type="submit" name="submit"><?php echo get_lang('Submit'); ?></button></td>
        </tr>
    </table>
</form><div id="divPreviewCatalog"></div>
