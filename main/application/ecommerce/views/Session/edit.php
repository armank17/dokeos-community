<?php
$this->ajax->printJavascript("appcore/library/xajax/");
$sign = api_get_setting('e_commerce_catalog_decimal');

$user_id = api_get_user_id();
$user_info = api_get_user_info($user_id);
$admin = ($user_info['official_code'] == 'ADMIN') ? true : false;
?>
<script type="text/javascript" src="appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>
<link rel="stylesheet" media="all" type="text/css" href="appcore/library/jquery/jquery.alerts/jquery.alerts.css" />
<link rel="stylesheet" media="all" type="text/css" href="appcore/library/jquery/jQuery-Timepicker/jquery-ui-timepicker-addon.css" />
<script type="text/javascript" src="appcore/library/jquery/jQuery-Timepicker/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="appcore/library/jquery/jQuery-Timepicker/jquery-ui-sliderAccess.js"></script>
<script type="text/javascript" src="appcore/library/jquery/jquery.numeric.js"></script>
<script type="text/javascript" src="appcore/library/jquery/jquery.validate.js"></script>
<!--<script language="javascript" src="appcore/library/jquery/AjaxUpload.2.0.min.js" type="text/javascript"></script>-->
<script>
    $(document).ready(function() {
        $("#txtCost").keypress(function(e) {
            if ($(this).val().length > 6 && e.keyCode != 8) {
                return false;
            }
        })
        $("#txtDuration").keypress(function(e) {
            if ($(this).val().length >= 2 && e.keyCode != 8) {
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
                var values = {};
                $.each($("#formEditCourse").serializeArray(), function(i, field) {
                    values[field.name] = field.value;
                });
                var description = CKEDITOR.instances['textareaDescription'].getData();
                values['textareaDescription'] = description;
                xajax_editSession(values);
                if ($('#id_catgory').val() != 0) {
                    var mypath = typeof myDokeosWebPath !== 'undefined' ? myDokeosWebPath : '/';
                    setTimeout(function() { window.location.href = mypath + 'main/index.php?module=ecommerce&cmd=Session'; },400);
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
//            action: 'application/ecommerce/views/Session/upload.php',
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
        $("#FreeCourse").click(function() {
            if ($(this).is(":checked")) {
                $("#txtCost").attr('disabled', 'disabled');
                $("#txtCostTTC").attr('disabled', 'disabled');
                $("#rdPrixHT").attr('disabled', 'disabled');
                $("#rdPrixTTC").attr('disabled', 'disabled');
                $("#txtCost").val("0.00");
                $("#txtCostTTC").val("0.00");
            } else {
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
        width: 170px;
        text-align: right;
        padding-right: 20px;
    }
    .ecommerce_date    {
        color:#777;

    }
    .catalogPreview{
        box-shadow: 2px 2px 5px #999;
        width: 300px;
        height: 132px;
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
</style>
<?php
$objSession = $this->getSessionEcommerce();
$objSession->image = ( $objSession->image == 'thumb_dokeos.jpg') ? ('default-sessions.png') : ($objSession->image);
$objS = $this->getSessionById();
?>
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
                        <!--
                        <td  class="ecommerce_left"></td>
                        -->
                        <td colspan="2"><h1><?php echo $this->getTitleSession($objSession->id_session); ?></h1></td>
                    </tr>
                    <tr>
                        <td>
                            <table>
                                <?php
                                if ($admin) {
                                    ?>
                                    <tr>
                                        <td  class="ecommerce_left"><?php echo $this->get_lang('Price'); ?></td>
                                        <td><?php
                                            $ht = 'checked="checked"';
                                            $ttc = '';
                                            $prixHT = $objSession->cost;
                                            if ($objSession->chr_type_cost == 'TTC') {
                                                $ht = '';
                                                $ttc = 'checked="checked"';
                                                $prixHT = $objSession->cost_ttc;
                                            }
                                            ?>
                                            <input name="txtCostHT" id="txtCostHT" type="hidden" size="10" value="<?php echo $objSession->cost; ?>" class="numeric" />
                                            <input name="txtCost" id="txtCost" type="text" size="10" onkeyup="xajax_calculateCostTTC(xajax.getFormValues('formEditCourse'))" value="<?php echo api_number_format($prixHT, false); ?>" class="numeric" />
                                            <input name="priceType" id="rdPrixHT" type="radio" value="HT" <?php echo $ht; ?> onclick="xajax_calculateCostTTC(xajax.getFormValues('formEditCourse'))"/><label for="rdPrixHT">HT</label>
                                            <input name="priceType" id="rdPrixTTC" type="radio" value="TTC" <?php echo $ttc; ?> onclick="xajax_calculateCostTTC(xajax.getFormValues('formEditCourse'))"/><label for="rdPrixTTC">TTC</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td  class="ecommerce_left"><?php echo get_lang('langTotal'); ?>:</td>
                                        <td><input name="txtCostTTC" id="txtCostTTC" type="text" onkeyup="xajax_calculateCostHT(xajax.getFormValues('formEditCourse'))" size="10" value="<?php echo api_number_format($objSession->cost_ttc, false); ?>" class="numeric" readonly />
                                        </td>
                                    </tr>
                                    <?php
                                    if (api_get_setting('display_shop_free_course') === 'true') {

                                        if ($objSession->chr_type_cost == "0") {
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
                                                if ($objSession->chr_type_cost == "0") {
                                                    echo "checked='checked'";
                                                }
                                                ?>/>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <tr>
                                        <td  class="ecommerce_left"><?php echo $this->get_lang('Access'); ?></td>
                                        <td><input name="txtDuration" id="txtDuration" value="<?php echo $objSession->duration; ?>" class="integer" />
                                            <?php
                                            $array_duration = array('day', 'week', 'month');
                                            ?>
                                            <select name="duration_type">
                                                <?php
                                                foreach ($array_duration as $index => $duration)
                                                    if ($duration == $objSession->duration_type)
                                                        echo '<option value="' . $duration . '" selected="selected">' . $this->get_lang(ucfirst($duration)) . '</option>';
                                                    else
                                                        echo '<option value="' . $duration . '">' . $this->get_lang(ucfirst($duration)) . '</option>';
                                                ?>
                                            </select></td>
                                    </tr>
                                    <?php
                                    if ($objSession->status) {
                                        ?>
                                        <tr>
                                            <td  class="ecommerce_left"><?php echo $this->get_lang('EcommerceProfile'); ?></td>
                                            <td><input id="rdstatus" type="radio" checked="checked" value="1" name="status"> <?php echo $this->get_lang('EcommerceCurseActive'); ?></td>
                                        </tr>
                                        <tr>
                                            <td  class="ecommerce_left"></td>
                                            <td><input id="rdstatus" type="radio" value="0" name="status"> <?php echo $this->get_lang('EcommerceCurseInactive'); ?></td>
                                        </tr>
                                        <?php
                                    } else {
                                        ?>
                                        <tr>
                                            <td  class="ecommerce_left"><?php echo $this->get_lang('EcommerceProfile'); ?></td>
                                            <td><input id="rdstatus" type="radio" value="1" name="status"> <?php echo $this->get_lang('EcommerceCurseActive'); ?></td>
                                        </tr>
                                        <tr>
                                            <td  class="ecommerce_left"></td>
                                            <td><input id="rdstatus" type="radio"  checked="checked" value="0" name="status"> <?php echo $this->get_lang('EcommerceCurseInactive'); ?></td>
                                        </tr>        
                                        <?php
                                    }
                                    ?>
                                    <tr>
                                        <td  class="ecommerce_left"><?php echo $this->get_lang('Category'); ?>
                                            <input type="hidden" name="id_session" id="id_session" value="<?php echo $objSession->id_session; ?>" /></td></td>
                                        <td>
                                            <select name="id_category" id="id_catgory">
                                                <option value="0"><?php echo get_lang("SelectACategory") ?></option>
                                                <?php
                                                $objCollection = $this->getListCategory();
                                                foreach ($objCollection as $arrayCategory) {
                                                    if ($objSession->id_category == $arrayCategory['id_category'])
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
                                $sys_image = api_get_path(SYS_PATH) . "home/default_platform_document/ecommerce_thumb/" . $objS->image;
                                $web_image = api_get_path(WEB_PATH) . "home/default_platform_document/ecommerce_thumb/" . $objS->image;
                                ?>
                                <div id="thumnailCatalogPreview" class="catalogPreview dialog-content" style="width:185px; height:140px; float:right; margin-right: 25px;" onclick="xajax_loadPreviewCatalog(<?php echo "'" . $sys_image . "','" . $web_image . "'" ?>)">
                                <?php ?>
                                    <img src="<?php echo (is_file($sys_image) && $objS->image != 'default-sessions.png' ? $web_image : '/main/application/ecommerce/assets/images/course_logo_default.jpg' ) ?>" />
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
          <td><input type="text" readonly="readonly" name="txtImageFile" id="txtImageFile" value="<?php echo $objSession->image; ?>" >
          <button id="btnImageUpload" name="btnImageUpload"><?php echo get_lang('Examinar'); ?></button></td>
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
          //if (strlen(trim($objSession->image)) > 0)
          $url_image = "home/default_platform_document/ecommerce_thumb/" . $objSession->image ;
          if (file_exists($url_image))
          $img = '<img src="home/default_platform_document/ecommerce_thumb/' . $objSession->image . '" />';
          else
          $img = '<img style="width:100px;height:100px;" src="'.api_get_path(WEB_PATH).'home/default_platform_document/ecommerce_thumb/'.$objSession->image.'" />';
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
            <td><?php echo $this->getTextarea('textareaDescription', $objSession->description); ?></td>
        </tr>                
        <tr>
            <td><input type="hidden" id="id_item_commerce" name="id_item_commerce" value="<?php echo $objSession->id; ?>" /></td>
            <td><button class="save" type="submit" style=" margin-top: 20px;" name="submit"><?php echo get_lang('Submit'); ?></button></td>
        </tr>
    </table>
</form><div id="divPreviewCatalog"></div>
