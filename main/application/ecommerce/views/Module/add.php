<?php
$this->ajax->printJavascript("appcore/library/xajax/");
$sign = api_get_setting('e_commerce_catalog_decimal');
?>
<script type="text/javascript" src="appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>
<link rel="stylesheet" media="all" type="text/css" href="appcore/library/jquery/jquery.alerts/jquery.alerts.css" />
<link rel="stylesheet" media="all" type="text/css" href="appcore/library/jquery/jQuery-Timepicker/jquery-ui-timepicker-addon.css" />
<link rel="stylesheet" media="all" type="text/css" href="<?php echo $this->css; ?>" />
<script type="text/javascript" src="appcore/library/jquery/jQuery-Timepicker/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="appcore/library/jquery/jQuery-Timepicker/jquery-ui-sliderAccess.js"></script>
<script type="text/javascript" src="appcore/library/jquery/jquery.numeric.js"></script>
<script type="text/javascript" src="appcore/library/jquery/jquery.validate.js"></script>
<script language="javascript" src="appcore/library/jquery/AjaxUpload.2.0.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        $('#tabs-left').tabs();
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
                var description = CKEDITOR.instances['textareaDescription'].getData();
                xajax_sentForm(xajax.getFormValues('formEditCourse'), description);
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

        new AjaxUpload('#btnImageUpload', {
            action: 'application/ecommerce/views/Module/upload.php',
            onSubmit: function(file, ext) {
                if (!(ext && /^(jpg|png|jpeg|gif|jpg)$/.test(ext))) {
                    // extensiones permitidas
                    alert('Error: Only permitted images.');
                    // cancela upload
                    return false;
                }
            },
            onComplete: function(file, response) {
                xajax_uploadImage(file);
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
$objItem = $this->getItemEcommerce();
?>
<form name="formEditCourse" id="formEditCourse" action="" method="POST" onsubmit="return false;">
    <table width="100%">
        <tr>
            <td colspan="2"><div id="divMessage"></div></td>
        </tr>
        <tr>
            <td colspan="2">
                <table>
                    <tr>
                        <td>
                            <table>
                                <tr>
                                    <td  class="ecommerce_left">FORMATION: </td>
                                    <td><input type="text" name="txtName" id="txtName" value="<?php echo $objItem->code; ?>" /></td>
                                </tr>
                                <tr>
                                    <td  class="ecommerce_left"><?php echo $this->get_lang('EcommerceCost'); ?></td>
                                    <td><?php
                                        $ht = 'checked="checked"';
                                        $ttc = '';
                                        $prixHT = $objItem->cost;
                                        if ($objItem->chr_type_cost == 'TTC') {
                                            $ht = '';
                                            $ttc = 'checked="checked"';
                                            $prixHT = $objItem->cost_ttc;
                                        }
                                        ?>
                                        <input name="txtCostHT" id="txtCostHT" type="hidden" size="10" value="<?php echo $objItem->cost; ?>" class="numeric" />
                                        <input name="txtCost" id="txtCost" type="text" size="10" onkeyup="xajax_calculateCostTTC(xajax.getFormValues('formEditCourse'))" value="<?php echo api_number_format($prixHT); ?>" class="numeric" />
                                        <input name="priceType" id="rdPrixHT" type="radio" value="HT" <?php echo $ht; ?> onclick="xajax_calculateCostTTC(xajax.getFormValues('formEditCourse'))"/><label for="rdPrixHT">HT</label>
                                        <input name="priceType" id="rdPrixTTC" type="radio" value="TTC" <?php echo $ttc; ?> onclick="xajax_calculateCostTTC(xajax.getFormValues('formEditCourse'))"/><label for="rdPrixTTC">TTC</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="ecommerce_left"><?php echo get_lang('langTotal'); ?>:</td>
                                    <td><input name="txtCostTTC" id="txtCostTTC" type="text" onkeyup="xajax_calculateCostHT(xajax.getFormValues('formEditCourse'))" size="10" value="<?php echo api_number_format($objItem->cost_ttc); ?>" class="numeric" readonly="readonly" />
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="ecommerce_left"><?php echo $this->get_lang('EcommerceDuration'); ?></td>
                                    <td><input name="txtDuration" id="txtDuration" value="<?php echo $objItem->duration; ?>" class="integer" />
                                        <?php
                                        $array_duration = array('day', 'week', 'month');
                                        ?>
                                        <select name="duration_type">
                                            <?php
                                            foreach ($array_duration as $index => $duration)
                                                if ($duration == $objItem->duration_type)
                                                    echo '<option value="' . $duration . '" selected="selected">' . $this->get_lang(ucfirst($duration)) . '</option>';
                                                else
                                                    echo '<option value="' . $duration . '">' . $this->get_lang(ucfirst($duration)) . '</option>';
                                            ?>
                                        </select></td>
                                </tr>
                                <?php
                                if ($objItem->status) {
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
                                    <td  class="ecommerce_left"><?php echo $this->get_lang('EcommerceCategory'); ?>
                                        <input type="hidden" name="id_session" id="id_session" value="<?php echo $objItem->id_session; ?>" /></td></td>
                                    <td>
                                        <select name="id_category" id="id_catgory">
                                            <option value="0"><?php echo get_lang("SelectACategory") ?></option>
                                            <?php
                                            $objCollection = $this->getListCategory();
                                            foreach ($objCollection as $arrayCategory) {
                                                if ($objItem->id_category == $arrayCategory['id_category'])
                                                    echo '<option value="' . $arrayCategory['id_category'] . '" selected="selected">' . $arrayCategory['chr_category'] . '</option>';
                                                else
                                                    echo '<option value="' . $arrayCategory['id_category'] . '">' . $arrayCategory['chr_category'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>                                
                            </table>
                        </td>
                        <td style="width:35%">
                            <!--Imagen preview of the Catalog-->
                            <div id="thumnailCatalogPreview" class="catalogPreview dialog-content" onclick="xajax_loadPreviewCatalog()">
                                <img src="application/ecommerce/assets/images/thumb_prod_small.jpg" />
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="ecommerce_left"><?php echo $this->get_lang('AddPicture'); ?></td>
            <td>
                <input type="text" readonly="readonly" name="txtImageFile" id="txtImageFile" value="<?php echo $objItem->image; ?>" >
                <button id="btnImageUpload" name="btnImageUpload"><?php echo get_lang('Examinar'); ?></button>
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
                    //if (strlen(trim($objItem->image)) > 0)
                    if (file_exists("../main/application/ecommerce/assets/images/" . $objItem->image . ""))
                        $img = '<img src="../main/application/ecommerce/assets/images/' . $objItem->image . '" />';
                    else
                        $img = '<img src="../home/default_platform_document/ecommerce_thumb/thumb_dokeos-orig.jpg" />';
                    echo $img;
                    ?>
                </div></td>
        </tr>
        <tr>
            <td></td>
            <td><input id="qf_d13865" type="checkbox" value="1" name="remove_img">
                <label for="qf_d13865"><?php echo stripslashes($this->get_lang('EcommerceRomeveImageAndWithoutImage')); ?></label></td>
        </tr>
        <tr>
            <td  class="ecommerce_left"><?php echo $this->get_lang('EcommerceDescription'); ?></td>
            <td><?php echo $this->getTextarea('textareaDescription', $objItem->description); ?></td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>        
        <tr>
            <td></td>
            <td>
                <div id="tabs-left">
                    <ul>
                        <?php
                        foreach ($this->getListCourse() as $index => $arrayCourse)
                            if ($this->hasModule($arrayCourse['code']))
                                echo '<li><a href="#tabs-' . ($index + 1) . '" title="' . $arrayCourse['title'] . '"><span style="font-size:10px;">' . cut($arrayCourse['title'], 25) . '</span></a></li>';
                        ?>
                    </ul>
                    <?php
                    foreach ($this->getListCourse() as $index => $arrayCourse) {
                        if ($this->hasModule($arrayCourse['code'])) {
                            echo '<div id="tabs-' . ($index + 1) . '">';
                            echo $this->getHtmlListModuleByCourse($arrayCourse);
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><input type="hidden" id="id_item_commerce" name="id_item_commerce" value="<?php echo $objItem->id; ?>" /></td>
            <td><button class="save" type="submit" style="margin-top: 20px;" name="submit"><?php echo get_lang('Submit'); ?></button></td>
        </tr>
    </table>
</form><div id="divPreviewCatalog"></div>