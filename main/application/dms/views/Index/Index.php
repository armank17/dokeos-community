<?php
 echo $this->headDokeos();
?>
<?php
$this->ajax->printJavascript("appcore/library/xajax/");
?>
<!--    <script type="text/javascript" src="application/dms/assets/js/jquery.miniColors.js"></script>
    <link type="text/css" rel="stylesheet" href="application/dms/assets/css/jquery.miniColors.css" />
    <script>
	$(function() {
                $(".color-picker").miniColors({
                        letterCase: 'uppercase'
                        /*change: function(hex, rgb) {
                                logData(hex, rgb);
                        }*/
                });
	});
  </script>-->
<script type="text/javascript" src="application/dms/assets/js/AjaxUpload.2.0.min.js"></script>
  <style>
      .dms_fieldset{
                    border: 1px solid #CCCCCC;
                    margin: 1em 0em 3em;
                    padding: 2.5em 0 2em;
                    position: relative;
      }
      .dms_fieldset_legend{
          margin-top:-20px;
        padding-left: 15px;
        position: absolute;
        text-transform: uppercase;}
      tr{
          height: 45px;
      }
      .dms_fieldset_description{
          color: #666666;
    font-size: 0.923em;
    line-height: 1.231em;
      }
      .dms_loading{
          float: left;
          margin-top: 12px;
          margin-left: 5px;
           
      }
      .dms_Loading{
        width:32px;
        height:32px;
        position:fixed;
        left:50%;
        top:50%;
        margin:-16px 0 0 -16px;
        z-index: 999;
      }
  </style>
  <script>
    function show_loading(){
    xajax.dom.create('divLoading','div', 'cargando');
    xajax.$('cargando').innerHTML='<img src="application/dms/assets/images/loading.gif" alt="cargando..." border="0">';
    }
    function hidden_loading(){
        xajax.$('cargando').innerHTML='';
    }

    xajax.callback.global.onResponseDelay = show_loading;
    xajax.callback.global.onComplete = hidden_loading;
  </script>
        <div id="divLoading" class="dms_Loading"></div>
        <form name="formEditTemplate" id="formEditTemplate" method="post" action="">
        <fieldset class="dms_fieldset">
            <legend class="dms_fieldset_legend">
                <span class="fieldset-legend">Edit Theme &nbsp;&nbsp;&nbsp;&nbsp; <a href="#" onclick="xajax_newTheme()">New Theme</a></span>
            </legend>
            
                <table>
                    <tr>
                        <td colspan="2" style="text-align: right;">
                            <div style="float:left;" id="divTitle">
                                <h3><?php
                                    $template = explode("_",$this->getThemeDokeos());
                                    unset($template[0]);
                                    $template = implode(" ", $template);
                                    echo strtoupper($template); ?> : Stylesheet <span class="dms_undertext">(default.css)</span></h3>
                                    
                            </div>
                            <input type="hidden" name="name_file" id="name_file" value="default.css" />
                            <div style="float:right; margin-top: 10px;" id="divSelectTemplate">
                                Select theme to edit: 
                                <select name="template"  onChange="xajax_getTemplate(document.formEditTemplate.template.options[document.formEditTemplate.template.selectedIndex].value)">
                                <?php 
                                    $arrayObjectTemplate = $this->getListTemplates();
                                    if(count($arrayObjectTemplate)==0)
                                        echo '<option value="0">Not found template</option>';
                                    else
                                    {
                                        foreach ($arrayObjectTemplate as $index=>$objTemplate)
                                            if($objTemplate->active)
                                                echo '<option value="'.$objTemplate->name_file.'" selected>'.$objTemplate->name_publish.' (default)</option>';
                                            else
                                                echo '<option value="'.$objTemplate->name_file.'" >'.$objTemplate->name_publish.'</option>';
                                    }
                                ?>                             
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; caption-side: top;">
                            <div id="divListCSS">
                                <textarea id="dms_textarea" name="dms_textarea" html="true"><?php echo $this->print_css(); ?></textarea>
                           </div>
                            <div id="dms_button_update">
                                <input type="button" name="updateFile" id="updateFile"  value="Update file" onclick="xajax_updateFile(xajax.getFormValues('formEditTemplate'))" />
                            </div>
                        </td>
                        <td style="caption-side: top; vertical-align: text-top;">
                            <div id="divListFileCSS">
                                <?php 
                                    $listFile = $this->listFilesTemplate();
                                    if(count($listFile)>0)
                                    {
                                        $html ='<ul style="list-style-type:none;">';
                                        foreach($listFile as $index=>$value)
                                        {
                                            $html.='<li><a href="#" onclick="xajax_showFileCSS(\''.$this->getThemeDokeos().'\',\''.$value['basename'].'\')"><span class="dms_textLinkFile">'.$value['filename'].'</span></a><br />';
                                                $html.='<span class="dms_undertext">('.$value['basename'].')</span>';
                                            $html.='</li>';
                                        }
                                    }
                                    $html.='</ul>';
                                    echo $html;
                                ?>
                             </div>
                        </td>
                    </tr>                    
                   
                </table>
            

            </fieldset>
        
        <fieldset class="dms_fieldset">
            <legend class="dms_fieldset_legend">
                <span class="fieldset-legend">Logo image settings</span></legend>
                <div style="float:left;" id="divFormUploadLogo">
                    If toggled on, the following logo will be displayed.<br />
                    <input type="checkbox" name="logo" checked="true" onmouseup="xajax_showInputUpload(xajax.getFormValues('formEditTemplate'))" /> Use the default logo. <br />
                    <div id="divInputUpload"></div>
                    <div class="dms_fieldset_description">Check here if you want the theme to use the logo supplied with it.</div>
                </div>
            <div style="float:right; width: 300px;" id="divPreviewLogo">
                <img src="css/<?php echo $this->getThemeDokeos(); ?>/images/logo-dokeos.png" />
            </div>
           
        </fieldset>
            
        <fieldset class="dms_fieldset">
            <legend class="dms_fieldset_legend">
                <span class="fieldset-legend">More Settings</span></legend>
                <input type="checkbox" name="checksitename" checked="true" onmouseup="xajax_showInputChangeSitename(xajax.getFormValues('formEditTemplate'))" /> Use the default siteName. <br />
                <div class="dms_fieldset_description">Check here if you want to change the site name</div>
                
                <div id="divFieldSitename"></div> 
        </fieldset>            
            
        <!--<div id="divButtomPreview" style="float: left;"><input type="button" name="btnGenerate" id="btnGenerate" onclick="xajax_generateTemplateTemp(xajax.getFormValues('formEditTemplate'))" value="Preview" /></div>-->
        <div style="float: left; margin-left: 10px;"> <input type="button" name="btnSave" id="btnSave" value="Save configuration" onclick="xajax_saveConfiguration(xajax.getFormValues('formEditTemplate'))" /></div>
        </form>
        <div id="formSelectTheme"></div>
        <div id="preview_template"></div>