<?php
$this->ajax->printJavascript("appcore/library/xajax/");
?>
<script type="text/javascript" src="appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>
<link rel="stylesheet" media="all" type="text/css" href="appcore/library/jquery/jquery.alerts/jquery.alerts.css" />
<link rel="stylesheet" media="all" type="text/css" href="<?php echo $this->css; ?>" />
<script type="text/javascript">
$(document).ready(function() {

var id_orden = 1;

$(".data_table tbody tr:not(:first)").each(function(){
    
    $(this).attr("id", "recordsArray_c"+id_orden);
    id_orden++;
});

var id_orden = 1;

$(".data_table tbody tr:not(:first)").each(function(){
    
    course_code = $("#recordsArray_c"+id_orden).find("input").attr('name','id[]').val();
    $(this).attr("id", "recordsArray_"+course_code);
    id_orden++;
});

    // Make sortable the table by tr element
                $(".data_table tbody").sortable({ 
                    opacity: 0.6, 
                    placeholder:"effectDragDrop",  
                    items:"tr:not(:first)", // Not make sortable the first tr which is the header
                    cursor: "crosshair", 
                    cancel: ".nodrag", 
                    update: function() {
                        
                        var order = $(this).sortable("serialize");
                        var record = order.split("&");
                        var recordlen = record.length;
                        var disparr = new Array();
                        for (var i=0;i<(recordlen);i++) {
                            var recordval = record[i].split("=");
                            disparr[i] = recordval[1];			 
                        }

                        $.ajax({
                        type: "GET",
                        url: "<?php echo api_get_path(WEB_AJAX_PATH)?>ecommerce_courses.ajax.php?action=updatePositionModules&disporder="+disparr,
                        success: function(msg){}
                        })			
                    }   
		}).sortable("serialize");

});
</script>
<div class="ecommerce_bar_menu"><a href="index.php?module=ecommerce&cmd=Module&func=add"><?php echo '<img title="'.$this->get_lang('langAdd').'" class="actionplaceholdericon actionaddpage" src="'.  api_get_path(WEB_PATH).'main/img/pixel.gif" />'; ?><?php echo $this->get_lang('AddProducts')?></a></div>
<form name="formListSession" id="formListSession" method="POST" action="" onsubmit="return false;">
    <div id="divListCategory">
        <table style="100%" class="data_table">
            <tr class="row_odd">
                <th style="width:10px;"><input onclick="xajax_selectAll();" type="checkbox" name="checkdelete[]" id="checkdelete"></th>
                <th style="width:10px;"></th>
                <th style="width:20px;">ID</th>
                <th style="width:300px;"><?php echo $this->get_lang('Title'); ?></th>
                <th style="width:150px;"><?php echo $this->get_lang('Category'); ?></th>
                <th style="width:100px;"><?php echo $this->get_lang('Price'); ?></th>
                <th style="width:100px;"><?php echo $this->get_lang('Duration'); ?></th>
                <th style="width:100px;"><?php echo $this->get_lang('Catalog'); ?></th>
                <th style="width:100px;"><?php echo $this->get_lang('EcommerceAction'); ?></th>
            </tr>
            <?php
            if (count($this->getListModule()) > 0) {
                $collectionModule = $this->getListModule();
                $html = '';
                foreach ($collectionModule as $index => $arrayModule) {
                    $html.='<tr class="' . (($index) % 2 ? 'row_odd' : 'row_even') . '">';
                    $html.='<td><input type="checkbox" name="checkdelete[]" id="checkdelete" value="' . $arrayModule['id'] . '"></td>';
                    $html.='<td>' . Display::return_icon('pixel.gif', get_lang('Move'),array("class"=>"actionplaceholdericon actionsdraganddrop")) . '</td>';
                    $html.='<td>' . $arrayModule['id'] . '</td>';
                    $html.='<td>' . $arrayModule['code'] . '</td>';
                    if ($arrayModule['id_category'] == null)
                        $html.='<td><center>' . $arrayModule['id_category'] . '</center></td>';
                    else
                        $html.='<td><center>' . $this->getCategory($arrayModule['id_category'])->chr_category . '</center></td>';
                    $html.='<td><center>' . $arrayModule['cost'] . '</center></td>';
                    $html.='<td><center>' . $arrayModule['duration'] . ' ' . $this->get_lang(ucfirst($arrayModule['duration_type'])) . '</center></td>';
                    if ($arrayModule['status'])
                        $html.='<td><center><div id="div_' . $arrayModule['id'] . '"><span class="ecommerce_link" onclick="xajax_active(0, ' . $arrayModule['id'] . ')"><img title="'.$this->get_lang('langVisible').'" class="actionplaceholdericon actionvisible" src="'.  api_get_path(WEB_PATH).'main/img/pixel.gif" /></span></div><center></td>';
                    else
                        $html.='<td><center><div  id="div_' . $arrayModule['id'] . '"><span class="ecommerce_link" onclick="xajax_active(1, ' . $arrayModule['id'] . ')"><img title="'.$this->get_lang('langInvisible').'" class="actionplaceholdericon actioninvisible" src="'.  api_get_path(WEB_PATH).'main/img/pixel.gif" /></span></div><center></td>';
                    $html.='<td><center><a href="index.php?module=ecommerce&cmd=Module&func=add&id_item=' . $arrayModule['id'] . '" ><img title="'.$this->get_lang('langModify').'" class="actionplaceholdericon actionedit" src="'.  api_get_path(WEB_PATH).'main/img/pixel.gif" /></a> <span class="ecommerce_link" onclick="xajax_deleteItem(' . $arrayModule['id'] . ')"><img title="'.$this->get_lang('langDelete').'" class="actionplaceholdericon actiondelete" src="'.  api_get_path(WEB_PATH).'main/img/pixel.gif" /></span></center></td>';
                    $html.='</tr>';
                }
            }
            else {
                $html.='<tr><td style="width:100%;" colspan="8">' . $this->get_lang('TheListIsEmpty') . '</td></tr>';
            }
            echo $html;
            ?>
        </table>
    </div>
    <div><button name="btnDelete" id="btnDelete" onclick="xajax_deleteAll(xajax.getFormValues('formListSession'));"><?php echo $this->get_lang('langDelete')?></button></div>
</form>
<div id="divFormCategory"></div>