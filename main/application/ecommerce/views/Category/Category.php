<?php
$this->ajax->printJavascript("appcore/library/xajax/");
api_display_tool_title($this->get_lang('Categories'));
?>
<!--<script type="text/javascript" src="appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>
<link rel="stylesheet" media="all" type="text/css" href="appcore/library/jquery/jquery.alerts/jquery.alerts.css" />-->
<link rel="stylesheet" media="all" type="text/css" href="<?php echo $this->css; ?>" />
<div class="ecommerce_bar_menu"><span class="ecommerce_link" onclick="xajax_addCategory();"><?php echo '<img title="'.$this->get_lang('langAdd').'" class="actionplaceholdericon actionaddpage" src="'.  api_get_path(WEB_PATH).'main/img/pixel.gif" />'; echo $this->get_lang('AddCategory'); ?></span></div>
<form name="formListCategory" id="formListCategory" method="POST" action="" onsubmit="return false;">
    <div id="divListCategory">
        <table style="width:100%" class="data_table">
            <tr class="row_odd">
                <th style="width:10px;"><span class="ecommerce_link" onclick="xajax_selectAll();"><?php echo $this->get_lang('Del'); ?></span></th>
                <th style="width:400px;"><div class="ecommerce_link" id="divTitleCategory" onclick="xajax_orderListCategory('chr_category')"><?php echo $this->get_lang('EcommerceCategory'); ?></div></th>
                <th style="width:100px;"><div class="ecommerce_link" id="divTitleCountProduct" onclick="xajax_orderListCategory('amount')"><?php echo $this->get_lang('EcommerceProduct'); ?></div></th>
                <th style="width:100px;"><?php echo $this->get_lang('Language'); ?></th>
                <th style="width:80px;"><?php echo $this->get_lang('langVisible'); ?></th>
                <th style="width:80px;"><?php echo $this->get_lang('EcommerceAction'); ?></th>
            </tr>
        <?php if (count($this->getListCategory()) > 0) {
            $collectionCategory = $this->getListCategory();
            $html='';
            foreach ($collectionCategory as $index => $arrayCategory) {
                $html.='<tr class="' . (($index) % 2 ? 'row_odd' : 'row_even') . '">';
                $html.='<td><input type="checkbox" name="checkdelete[]" id="checkdelete" value="' . $arrayCategory['id_category'] . '"></td>';
                $html.='<td>' . $arrayCategory['chr_category'] . '</td>';
                $html.='<td><center>' . $this->getCountCategory($arrayCategory['id_category']) . '</center></td>';
                $html.='<td><center>' . $arrayCategory['chr_language'] . '</center></td>';
                if ($arrayCategory['bool_active'])
                    $html.='<td><center><div id="div_' . $arrayCategory['id_category'] . '"><span class="ecommerce_link" onclick="xajax_active(0, ' . $arrayCategory['id_category'] . ')"><img title="'.$this->get_lang('langVisible').'" class="actionplaceholdericon actionvisible" src="'.  api_get_path(WEB_PATH).'main/img/pixel.gif" /></span></div></center></td>';
                else
                    $html.='<td><center><div  id="div_' . $arrayCategory['id_category'] . '"><span class="ecommerce_link" onclick="xajax_active(1, ' . $arrayCategory['id_category'] . ')"><img title="'.$this->get_lang('langInvisible').'" class="actionplaceholdericon actioninvisible" src="'.  api_get_path(WEB_PATH).'main/img/pixel.gif" /></span></div></center></td>';
                $html.='<td><center><span class="ecommerce_link" onclick="xajax_editCategory(' . $arrayCategory['id_category'] . ')"><img title="'.$this->get_lang('langModify').'" class="actionplaceholdericon actionedit" src="'.  api_get_path(WEB_PATH).'main/img/pixel.gif" /></span>  '
                        . '<span class="ecommerce_link" onclick="xajax_deleteCategory(' . $arrayCategory['id_category'] . ')"><img title="'.$this->get_lang('langDelete').'" class="actionplaceholdericon actiondelete" src="'.  api_get_path(WEB_PATH).'main/img/pixel.gif" /></span></center></td>';
                $html.='</tr>';
            }
        }
        else {
            $html.='<tr><td style="width:100%;" colspan="6">' . $this->get_lang('TheListIsEmpty') . '</td></tr>';
        }
        echo $html;
        ?>
        </table>
    </div>
    <div class="btn-quiz-submit-bottom"></div>
    <div><button type="button" class="cancel multiaction" name="btnDelete" id="btnDelete" onclick="xajax_deleteAll(xajax.getFormValues('formListCategory'));"><?php echo $this->get_lang('langDelete'); ?></button></div>
</form>
<div id="divFormCategory"></div>