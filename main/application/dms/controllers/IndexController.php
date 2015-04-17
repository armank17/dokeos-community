<?php
class application_dms_controllers_Index extends appcore_command_Command
{
    public $css = NULL;
    
    public $css_template;
    
    public $template;
    
    public $access_url;
    
    public $language;
    
    public $path_web;
    
    public function __construct() 
    {
        $this->verifySession(); 
        //$this->setTheme('main');
        $this->access_url = 1;
        $this->language = 'french';
        $this->path_web = $this->full_url();

        //$this->getThemeDokeos();
        $this->template = $template = $this->getThemeDokeos();//$this->getSettings('stylesheets');
        $this->css_template = 'css/'.$template.'/default.css';
        $this->jquery = 'application/dms/assets/js/jquery-1.7.2.min.js';
        $this->css = 'application/dms/assets/css/dms.css';
        $this->loadAjax();
        //$this->createFilePreview();
        //$this->disabledHeaderCore();
        //$this->disabledFooterCore();  
    }
    
    public function verifySession()
    {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
    }
    
    public function getThemeDokeos()
    {
        if($this->getConfiguration('multiple_access_urls'))
        {
            $objUser = $this->getSession()->getProperty('_user');
            $id_user = $objUser->user_id;//$this->getRequest()->getProperty('id_user');
            $array_id_url = $this->getIdUrlByIdUser($id_user);
            $collectionObjURL = $this->getObjUrl($array_id_url);
            foreach( $collectionObjURL as $index=>$objUrl)
            {
                if($this->full_url() == $objUrl->url)
                {
                    $template = $this->getSettings('stylesheets', $objUrl->id);
                    $this->access_url = $objUrl->id;
                }
            }
            return $template;
        }
        else
        {
            $template = $this->getSettings('stylesheets');
            return $template;
        }
    }
    
    public function full_url()
    {
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
        //return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
        return $protocol . "://" . $_SERVER['SERVER_NAME'].'/';
    }    
    
    public function getIdUrlByIdUser($id_user)
    {
        $objModel = new application_dms_models_modeldms();
        $result = $objModel->getIdUrlByIdUser($id_user);
        return $result;        
    }
    
    public function getObjUrl($array_id_url)
    {
        $objModel = new application_dms_models_modeldms();
        $result = $objModel->getObjUrl($array_id_url);
        return $result;         
    }
    
    private function loadAjax()
    {
        $this->ajax = new xajax();
        $this->ajax->setFlag("debug", false);
        $this->ajax->register(XAJAX_FUNCTION, array('getTemplate', $this, 'getTemplate'));
        $this->ajax->register(XAJAX_FUNCTION, array('openEditorCSS', $this, 'openEditorCSS'));
        $this->ajax->register(XAJAX_FUNCTION, array('saveStyle', $this, 'saveStyle'));
        $this->ajax->register(XAJAX_FUNCTION, array('generateTemplateTemp', $this, 'generateTemplateTemp'));
        $this->ajax->register(XAJAX_FUNCTION, array('showInputUpload', $this, 'showInputUpload'));
        $this->ajax->register(XAJAX_FUNCTION, array('uploadLogo', $this, 'uploadLogo'));
        
        $this->ajax->register(XAJAX_FUNCTION, array('showFileCSS', $this, 'showFileCSS'));
        $this->ajax->register(XAJAX_FUNCTION, array('newTheme', $this, 'newTheme'));
        $this->ajax->register(XAJAX_FUNCTION, array('createTheme', $this, 'createTheme'));
        $this->ajax->register(XAJAX_FUNCTION, array('updateFile', $this, 'updateFile'));
        $this->ajax->register(XAJAX_FUNCTION, array('showInputChangeSitename', $this, 'showInputChangeSitename'));
        $this->ajax->register(XAJAX_FUNCTION, array('saveConfiguration', $this, 'saveConfiguration'));
        $this->ajax->processRequest();
    }
    
    public function saveConfiguration($form)
    {
        $objResponse = new xajaxResponse();
        
        if(!isset($form['logo']) && !isset($form['checksitename']))
        {
            if(strlen(trim($form['logo_dokeos']))>0)
            {
                if(file_exists($this->getConfiguration('root_sys').'main/css/'.$form['template'].'/images/logo-dokeos.png'.'.old'))
                {
                    $path_logo = $this->getConfiguration('root_sys').'main/css/'.$form['template'].'/images/logo-dokeos.png';
                    rename ($path_logo, $this->getConfiguration('root_sys').'main/css/'.$form['template'].'/images/logo-dokeos.png'.'.old');
                }
                
                $path_logo2 = $this->getConfiguration('root_sys').'main/application/dms/assets/files/dms/'.$form['logo_dokeos'];
                $new_name = $this->getConfiguration('root_sys').'main/application/dms/assets/files/dms/logo-dokeos.png';
                rename ($path_logo2,$new_name);                
                
                $path_base = $this->getConfiguration('root_sys').'main/application/dms/assets/files/dms/';
                $path_destine = $this->getConfiguration('root_sys').'main/css/'.$form['template'].'/images/';
                $move="cd $path_base ; mv -f logo-dokeos.png ".$path_destine;
                shell_exec($move); 
                

            }
            else
                $objResponse->alert('The image logo field is required');
            
            if(strlen(trim($form['institutionname']))>0)
            {
                if(strlen(trim($form['sitename']))>0)
                {
                    $arraySettings['Institution'] = $form['institutionname'];
                    $arraySettings['siteName'] = $form['sitename'];
                    $this->saveSetting($arraySettings, $this->access_url);
                    $objResponse->alert('changes saved successfully');
                }
                else
                    $objResponse->alert('The Site name field is required');
            }
            else
                $objResponse->alert('The Institution field is required');
            
        }
        
        if(!isset($form['logo']) && isset($form['checksitename']))
        {
            if(strlen(trim($form['logo_dokeos']))>0)
            {
                if(file_exists($this->getConfiguration('root_sys').'main/css/'.$form['template'].'/images/logo-dokeos.png'.'.old'))
                {
                    $path_logo = $this->getConfiguration('root_sys').'main/css/'.$form['template'].'/images/logo-dokeos.png';
                    rename ($path_logo, $this->getConfiguration('root_sys').'main/css/'.$form['template'].'/images/logo-dokeos.png'.'.old');
                }
                
                $path_logo2 = $this->getConfiguration('root_sys').'main/application/dms/assets/files/dms/'.$form['logo_dokeos'];
                $new_name = $this->getConfiguration('root_sys').'main/application/dms/assets/files/dms/logo-dokeos.png';
                rename ($path_logo2,$new_name);                 
                
                $path_base = $this->getConfiguration('root_sys').'main/application/dms/assets/files/dms/';
                $path_destine = $this->getConfiguration('root_sys').'main/css/'.$form['template'].'/images/';
                $move="cd $path_base ; mv -f logo-dokeos.png ".$path_destine;
                shell_exec($move); 
                $objResponse->alert('changes saved successfully');

            }
        }
        
        if(!isset($form['checksitename']) && isset($form['logo']))
        {
            if(strlen(trim($form['institutionname']))>0)
            {
                if(strlen(trim($form['sitename']))>0)
                {
                    $arraySettings['Institution'] = $form['institutionname'];
                    $arraySettings['siteName'] = $form['sitename'];
                    $this->saveSetting($arraySettings, $this->access_url);
                    $objResponse->alert('changes saved successfully');
                }
                else
                    $objResponse->alert('The Site name field is required');
            }
            else
                $objResponse->alert('The Institution field is required');
        }        
        return $objResponse;
    }
    
    public function saveSetting($arraySettings, $access_url)
    {
        $objModel = new application_dms_models_modeldms();
        $result = $objModel->saveSetting($arraySettings, $access_url);
        return $result;
    }
    
    public function showInputChangeSitename($form)
    {
        $objResponse = new xajaxResponse();
        if(isset($form['checksitename']))
        {
            $html='<table>
                            <tr>
                                <td>Institution:</td><td><input type="text" name="institutionname" id="institutionname" value ="'.$this->getSettings('Institution', $this->access_url).'" /></td>
                            </tr>                        
                            <tr>
                                <td>Site name:</td><td><input type="text" name="sitename" id="sitename" value ="'.$this->getSettings('Sitename', $this->access_url).'" /></td>
                            </tr>                        
                        </table>';
            $objResponse->assign("divFieldSitename", "innerHTML", $html);
        }
        else
            $objResponse->assign("divFieldSitename", "innerHTML", "");
        return $objResponse;
    }
    
    public function updateFile($form)
    {
        $objResponse = new xajaxResponse();
        /*$script="function muestra_cargando(){
        xajax.dom.create('divLoading','div', 'cargando');
        xajax.$('cargando').innerHTML='<img src=\"application/dms/assets/images/loading.gif\" alt=\"cargando...\" border=\"0\">';
        }
        function oculta_cargando(){
            xajax.$('cargando').innerHTML='';
        }

        xajax.callback.global.onResponseDelay = muestra_cargando;
        xajax.callback.global.onComplete = oculta_cargando;  ";*/
       // $objResponse->script($script);        
        if(strlen(trim($form['dms_textarea']))>0)
        {
            $this->createFileCSS($form);
        }
        else
            $objResponse->alert('The style sheet can not be empty.');
        
        return $objResponse;
    }
    
    public function createFileCSS($form)
    {
        //sleep(5);
        if(!file_exists($this->getConfiguration('root_sys').'main/css/'.$form['template'].'/'.$form['name_file'].'.old'))
        {
            //change default.css to default.css.old
            $path_css = $this->getConfiguration('root_sys').'main/css/'.$form['template'].'/'.$form['name_file'];
            rename ($path_css, $this->getConfiguration('root_sys').'main/css/'.$form['template'].'/'.$form['name_file'].'.old');
        }
        $path = $this->getConfiguration('root_sys').'main/css/'.$form['template'].'/'.$form['name_file'];
        umask(0);
        $ourFileHandle = fopen($path, 'w'); //or die("can't open file");
//        chmod($path, '0777');
        if(!fwrite($ourFileHandle, $form['dms_textarea']))
            return false;
        fclose($ourFileHandle);        
        return true;
    }
    
    public function createTheme($form)
    {
        $objResponse = new xajaxResponse();
        if(isset($form['name_theme']))
        {
            if(strlen(trim($form['name_theme'])))
            {
                $form['name_theme'] = DMS_PREFIX_TEMPLATE.$form['name_theme'];
                if($this->existTheme($form['name_theme']))
                {
                    $objResponse->alert('The name of the theme already exists.');
                }
                else
                {
                    $this->generateTheme($form);
                    //divSelectTemplate
                    $html = $this->getHtmlSelectTheme($form);
                    $objResponse->assign("divSelectTemplate", "innerHTML", $html);
                    //divListFileCSS
                    $jquery ='$(document).ready(function(){
                                $("#formSelectTheme").dialog("close");
                                });';
                    $objResponse->script($jquery);                    
                    $objResponse->call('xajax_getTemplate',$form['name_theme']);
                }
            }
            else
            {
                $objResponse->alert('The name field is required.');
            }
        }
        return $objResponse;
    }
    
    public function getHtmlSelectTheme($form)
    {
        $html='Select theme to edit: ';
        $html.='<select name="template"  onChange="xajax_getTemplate(document.formEditTemplate.template.options[document.formEditTemplate.template.selectedIndex].value)">';
            $arrayObjectTemplate = $this->getListTemplates();
            if(count($arrayObjectTemplate)==0)
                $html.= '<option value="0">Not found template</option>';
            else
            {
                foreach ($arrayObjectTemplate as $index=>$objTemplate)
                    if($objTemplate->name_file == $form['name_theme'])
                        $html.= '<option value="'.$objTemplate->name_file.'" selected>'.$objTemplate->name_publish.'</option>';
                    else
                        $html.= '<option value="'.$objTemplate->name_file.'" >'.$objTemplate->name_publish.'</option>';
            }
        $html.='</select>';
        return $html;
    }
    
    public function generateTheme($form)
    {
        $path_template_zip = $this->generateZipTemplate($form['theme']);
        $this->createFolderTemp($form['name_theme']);
        $this->moveZipTemplate($form, $path_template_zip);
        $this->unpackingTemplate($form);
        $this->deleteFileZip($form);
    }
    
    public function deleteFileZip($form)
    {
        $path = 'css/'.$form['name_theme'].'/template.zip';
        unlink($path);
    }


    public function existTheme($template)
    {
        $path_template = 'css/'.$template.'/';
        if(is_dir($path_template))
            return true;
        else
            return false;
       
    }
    
    public function newTheme()
    {
        $objResponse = new xajaxResponse();
        $formHTML ='<form id="formNewTheme" name="formNewTheme">';
        $formHTML.='<table>';
            $formHTML.='<tr>';
                $formHTML.='<td>Name new theme</td>';
                $formHTML.='<td><input type="text" name="name_theme" id="name_theme" /></td>';
            $formHTML.='</tr>';
            $list = $this->getListTemplates();
            $formHTML.='<tr>';
                $formHTML.='<td>Select theme</td>';
                $formHTML.='<td>';
                    if(count($list)>0)
                    {
                        foreach($list as $index=>$value)
                        {
                            if(strlen($value->name_publish)>0)
                            {
                                if($value->name_publish == 'black_tablet')
                                    $checked = 'checked="checked"';
                                else
                                    $checked = '';
                                $formHTML.='<input type="radio" name="theme" value="'.$value->name_publish.'" '.$checked.' />'.$value->name_publish.'<br>';
                            }
                        }
                    }
                $formHTML.='</td>';
            $formHTML.='</tr>';
        $formHTML.='</table></form>';
        $objResponse->assign("formSelectTheme", "innerHTML", $formHTML);
        $jquery ='$(function(){
                                $("#formSelectTheme").dialog("open");
                                $("#formSelectTheme").dialog({
                                autoOpen: true,
                                title:"New theme",
                                resizable: false,
                                width: 400,
                                height: 330,
                                modal:true,
                                buttons: {
                                        "Create theme": function() {
                                                xajax_createTheme(xajax.getFormValues(\'formNewTheme\'));
                                        }
                                }
                        });
                    });';
        $objResponse->script($jquery);         
        return $objResponse;
    }
    
    public function print_css($template ='', $file_css ='')
    {
        if(strlen($file_css)>0)
            $css = $file_css;
        else
            $css = 'default.css';
        
        if(strlen($template)>0)
            $style = 'css/'.$template.'/'.$css;
        else
            $style = 'css/'.$this->getThemeDokeos().'/'.$css;
        $path_css = $this->getConfiguration('root_sys').'main/'.$style;
        $f=fopen($path_css,'rb');
        $data='';
        while(!feof($f))
            $data.=fread($f,  filesize ($path_css));
        fclose($f);
        return $data;
    }
    
    public function listFilesTemplate($file_template='')
    {
        if(strlen($file_template)>0)
            $path_template = 'css/'.$file_template.'/';
        else
            $path_template = 'css/'.$this->getThemeDokeos().'/';
        $array_css = array();
        foreach(glob($path_template . '*') as $file) 
        {
            $path_info = pathinfo($file);
            if(isset($path_info['extension']))
            {
                if($path_info['extension'] == 'css')
                {
                    array_push($array_css, $path_info);
                }
            }
        }
        return $array_css;
    }
    
    public function showFileCSS($folder, $file_css)
    {
        $objResponse = new xajaxResponse();
        $html='<textarea id="dms_textarea" name="dms_textarea" html="true">'.$this->print_css($folder, $file_css).'</textarea>';
        $objResponse->assign("divListCSS", "innerHTML", $html);
        
        $template = explode("_",$folder);
        unset($template[0]);
        $template = implode(" ", $template);
        $title ='<h3> '.strtoupper($template).' : Stylesheet <span class="dms_undertext">('.$file_css.')</span></h3>';
        $objResponse->assign("divTitle", "innerHTML", $title);
        $objResponse->assign("name_file", "value", $file_css);
        return $objResponse;
    }
    
    public function showInputUpload($form)
    {
        $objResponse = new xajaxResponse();
        $path_repository_logo = $this->getConfiguration('root_sys').'main/application/dms/assets/files/dms/';
        $this->clearDirectory($path_repository_logo);
        
        if(isset($form['template']))
            $template = $form['template'];
        else
            $template  = $this->getThemeDokeos();
        if(!isset($form['logo']))
        {
            $html='';
            $img='<img src="css/'.$template.'/images/logo-dokeos.png" />';
            $objResponse->assign("divPreviewLogo", "innerHTML", $img);
            $objResponse->assign("divInputUpload", "innerHTML", $html);
        }
        else
        {
            $html='<input type="text" id="logo_dokeos" name="logo_dokeos" readonly="readonly" />
                   <input type="button" name="upload_button" id="upload_button" value="Examinar..." />';
            $objResponse->assign("divInputUpload", "innerHTML", $html);
            $jquery="$(document).ready(function(){
                    var button = $('#upload_button'), interval;
                    new AjaxUpload('#upload_button', {
                        action: 'application/dms/views/Index/upload.php',
                        onSubmit : function(file , ext){
                        if (! (ext && /^(jpg|png|jpeg|gif|jpg)$/.test(ext))){
                            // extensiones permitidas
                            alert('Error: Only permitted images.');
                            // cancela upload
                            return false;
                        }
                        },
                        onComplete: function(file, response){
                            xajax_uploadLogo(file, '".$template."');
                        }   
                    });
                });";
            $objResponse->script($jquery);
        }
        
        return $objResponse;
    }
    
    public function uploadLogo($file, $template)
    {
        $objResponse = new xajaxResponse();
        /*$path_logo = $this->getConfiguration('root_sys').'main/application/dms/assets/files/dms/'.$file;
        $new_name = $this->getConfiguration('root_sys').'main/application/dms/assets/files/dms/logo-dokeos.png';
        rename ($path_logo,$new_name);*/
        $objResponse->assign("logo_dokeos", "value", $file);
        $img ='<img src="application/dms/assets/files/dms/'.$file.'" height="50px" />';
        $objResponse->assign("divPreviewLogo", "innerHTML", $img);
        return $objResponse;
    }

    public function generateTemplateTemp($form)
    {
        $objResponse = new xajaxResponse();
        
        $path_template_zip = $this->generateZipTemplate($form['template']);
        $path_folder = $this->createFolderTemp();
        
        $this->moveZipTemplate($form['template']);
        $this->unpackingTemplate();
        
        
        
        //change default.css to default.css.old
        $path_css = $this->getConfiguration('root_sys').'main/css/temp/default.css';
        chmod($path_css, 0777);
        rename ($path_css, $this->getConfiguration('root_sys').'main/css/temp/default.css.old');
        
        $this->generateFileDefaultCSS($this->generateDefaultCSS());
        
        $this->createFilePreview('temp');
        
        
        $iframe = '<iframe src="view.php" width="600px" height="500px" scrolling="no" frameborder="0" ></iframe>';
        //$iframe = '<b>Hello</b>';
        $objResponse->assign("preview_template", "innerHTML", $iframe);
        $jquery ='$(function(){
                                $("#preview_template").dialog("open");
                                $("#preview_template").dialog({
                                autoOpen: true,
                                title:"Template preview",
                                resizable: false,
                                width: 520,
                                height: 580,
                                modal:true,
                                buttons: {
                                        "Ok": function() {
                                                $(this).dialog("close");
                                        }
                                }
                        });
                    });';
        $objResponse->script($jquery);        
        return $objResponse;
    }
    
    public function generateDefaultCSS()
    {
        $css = '';
        $array_css = $this->getSession()->getProperty('template');
        if(count($array_css)>0)
        {
            foreach($array_css as $index=>$objStyle)
            {
                $css.= $objStyle->class.'{'.PHP_EOL;
                
                $vars_clase =get_object_vars($objStyle);
                foreach ($vars_clase as $nombre => $valor) 
                 {
                    if($nombre != 'class')
                        $css.=substr($nombre,2)." $valor".PHP_EOL;
                }
                $css.='}'.PHP_EOL;
            }
        }
        return $css;
    }
    
    public function generateFileDefaultCSS($css)
    {
        $path = $this->getConfiguration('root_sys').'main/css/temp/default.css';
        umask(0);
        $ourFileHandle = fopen($path, 'w'); //or die("can't open file");
        if(!fwrite($ourFileHandle, $css))
            return false;
        fclose($ourFileHandle);        
    }
    
    public function unpackingTemplate($form)
    {
		$pathbase = $this->getConfiguration('root_sys').'main/css/'.$form['name_theme'].'/';
        $path = $pathbase.'template.zip';
        umask(0);
        $objZip = new ZipArchive();
        if ($objZip->open($path)) {
            //$this->deleteDirectory('application/offline/files/synchronize');
            $objZip->extractTo($pathbase);
            $objZip->close();
        }
    }
    
    public function moveZipTemplate($form)
    {
        $path_base = $this->getConfiguration('root_sys').'main/css/'.DMS_PREFIX_TEMPLATE.$form['theme'].'/';
        $move="cd $path_base ; mv -f template.zip ../".$form['name_theme']."/";
        shell_exec($move);
    }
    
    public function createFolderTemp($folder_template ='')
    {
        if(strlen($folder_template) > 0)
            $directory = $this->getConfiguration('root_sys').'main/css/'.$folder_template;
        else
            $directory = $this->getConfiguration('root_sys').'main/css/temp';
        if(!is_dir($directory))
        {
            umask(0);
            mkdir($directory, 0777);
        }
        else
        {
            //clear directory
            $this->clearDirectory($directory);
        }
    }
    
    public function clearDirectory($directory)
    {
        foreach(glob($directory . '/*') as $file) {
            if(is_dir($file))
                $this->clearDirectory($file);
            else
                unlink($file);
        }
        //rmdir($directory);
        return true;
    }    
    
    public function generateZipTemplate($template)
    {
        $template = DMS_PREFIX_TEMPLATE.$template;
        $path_template = $this->getConfiguration('root_sys')."main/css/".$template;
        $name_zip = 'template'.'.zip';
        if(file_exists($path_template.'/'.$name_zip))
            unlink ($path_template.'/'.$name_zip);
        $compressAll= "cd $path_template ; zip -r $name_zip *";
        umask(0);
        shell_exec($compressAll); 
        return $path_template.'/'.$name_zip;
    }
    
    public function saveStyle($style, $index)
    {
        $objResponse = new xajaxResponse();
        $array_style = $this->getSession()->getProperty('template');
        $objTemp = $array_style[$index];
        $className = $objTemp->class;
        $lineStyle = explode(PHP_EOL, $style);
        //var_dump($lineStyle);die();
        if(count($lineStyle)>0)
        {
            $object = new stdClass();
            $object->class = $className;
            $num=10;
            foreach ($lineStyle as $indice=>$value)
            {
                if(strlen($value) > 0)
                {
                    $value = str_replace(PHP_EOL, '', $value);
                    //$patron = "/*[^/]*/"; 
                    //$patron = "*"; 
                    //if(!ereg($patron, $value))
                    //{
                        //$value = eregi_replace($patron, "", $value); 
                        $value = explode(':', $value);
                        $object->$num.$value[0] = trim(':'.trim($value[1]));
                    //}
                }
                $num++;
            }
            //die();
            $array_style[$index] = $object;
            //$this->vd($object, 0);die();
            $this->getSession()->setProperty('template', $array_style);
            
        }
            $jquery ='$(document).ready(function(){
                        $("#divTextArea").dialog("close");
                        });';
            $objResponse->script($jquery);         
        return $objResponse;
    }
    
    public function getHtmlButtomPreview()
    {
        $html='';
        $html.='<input type="button" name="btnGenerate" id="btnGenerate" onclick="xajax_generateTemplateTemp(xajax.getFormValues(\'formEditTemplate\'))" value="Preview" />';
        return $html;
    }
    
    public function openEditorCSS($index)
    {
        $objResponse = new xajaxResponse();
        $html = $this->sourceCSS($index);
        //$htmlButtom = $this->getHtmlButtomPreview();
        //$objResponse->assign("preview_template", "innerHTML", "");
        //$objResponse->assign("divButtomPreview", "innerHTML", $htmlButtom);
        $objResponse->assign("divTextArea", "innerHTML", $html);
        $jquery ='$(function(){
                                $("#divTextArea").dialog("open");
                                $("#divTextArea").dialog({
                                autoOpen: true,
                                title:"Edit CSS",
                                resizable: false,
                                width: 550,
                                height: 300,
                                modal:true,
                                buttons: {
                                        "Save changes": function() {
                                                xajax_saveStyle(document.getElementById(\'style_template\').value, '.$index.');
                                        },
                                        "Cancel": function() {
                                                $(this).dialog("close");
                                        }
                                }
                        });
                    });';
        $objResponse->script($jquery);        
        return $objResponse;        
    }
    
    public function sourceCSS($index)
    {
       $array_template = $this->getSession()->getProperty('template');
       if(count($array_template)>0)
       {
           $content='';
          foreach($array_template as $indice=>$objStyle)
          {
              
              if($index == $indice)
              {
                $vars_clase =get_object_vars($objStyle);
                foreach ($vars_clase as $nombre => $valor) {
                    if($nombre != 'class')
                        $content.=substr($nombre,2)." $valor".PHP_EOL;
                }
              }
          }
       }
       $html='<textarea name="style_template" id="style_template" style="width:500px; height:180px;">'.$content.'</textarea>';
       return $html;
    }
    
    public function createFilePreview($template='')
    {
        $path_config = 'view.php';
        $html = $this->getHtmlContent($template);
        umask(0);
        $ourFileHandle = fopen($path_config, 'w');
        if(!fwrite($ourFileHandle, $html))
           return false;
        fclose($ourFileHandle);         
    }
    
    public function getTemplate($template)
    {
        $objResponse = new xajaxResponse();
        
        $file_template = $template;
        $img='<img src="css/'.$file_template.'/images/logo-dokeos.png" />';
        $objResponse->assign("divPreviewLogo", "innerHTML", $img);        
        $html='<textarea id="dms_textarea" name="dms_textarea" html="true">'.$this->print_css($template, 'default.css').'</textarea>';
        $objResponse->assign("divListCSS", "innerHTML", $html);
        $formulario = 'If toggled on, the following logo will be displayed.<br />
                    <input type="checkbox" name="logo" checked="true" onmouseup="xajax_showInputUpload(xajax.getFormValues(\'formEditTemplate\'))" /> Use the default logo. <br />
                    <div id="divInputUpload"></div>
                    <div class="dms_fieldset_description">Check here if you want the theme to use the logo supplied with it.</div>';
        //divFormUploadLogo
        $objResponse->assign("divFormUploadLogo", "innerHTML", $formulario);
        $template = explode("_",$template);
        unset($template[0]);
        $template = implode(" ", $template);
        $title ='<h3> '.strtoupper($template).' : Stylesheet <span class="dms_undertext">(default.css)</span></h3>';
        $objResponse->assign("divTitle", "innerHTML", $title); 
        
        //divListFileCSS
        $listFile = $this->listFilesTemplate($file_template);
        if(count($listFile)>0)
        {
            $list ='<ul style="list-style-type:none;">';
            foreach($listFile as $index=>$value)
            {
                $list.='<li><a href="#" onclick="xajax_showFileCSS(\''.$file_template.'\',\''.$value['basename'].'\')"><span class="dms_textLinkFile">'.$value['filename'].'</span></a><br />';
                    $list.='<span class="dms_undertext">('.$value['basename'].')</span>';
                $list.='</li>';
            }
            $list.='</ul>';
        }
        //divPreviewLogo
        //var_dump($file_template);

        $objResponse->assign("name_file", "value", "default.css");
        $objResponse->assign("divListFileCSS", "innerHTML", $list);
        return $objResponse;
    }
    
    public function getStyle($template)
    {
        $style = 'css/'.$template.'/default.css';
        $lines = file($style);
        $array_style = array();
        $num = 10;
        foreach ($lines as $line_num => $line)
        {
            $flag = true;
            $line = trim($line);
            if(strlen($line) > 0)
            {
                if(preg_match("/{/i", $line))
                {
                    if(preg_match("/}/i", $line))
                    {
                        $name_object = explode('{',trim($line)); 
                        $objStyle = new stdClass();
                        $objStyle->class = $name_object[0];
                        
                        $properties = explode(';', $name_object[1]);
                        if(count($properties) >1)
                        {
                           foreach($properties as $i=>$v)
                           {
                               if(!preg_match("/}/i", $v))
                               {
                                    $stile = explode(':', $v);
                                    $atributo = $num.$stile[0];
                                    $objStyle->$atributo = ':'.$stile[1];                                   
                               }
                           }
                           $num=10;
                           array_push($array_style, $objStyle);
                        }
                        else
                        {
                            $proper = explode('}', $properties[0]);
                            $stile = explode(':', $proper[0]);
                            $atributo = $num.$stile[0];
                            $objStyle->$atributo = ':'.$stile[1];
                            $num=10;
                            array_push($array_style, $objStyle);
                        }
                        
                        $flag = false;                        
                    }
                    else
                    {
                        $name_object = explode('{',trim($line)); 
                        $objStyle = new stdClass();
                        $objStyle->class = $name_object[0];
                        $flag = false;
                    }
                }
                if(preg_match("/}/i", $line) && $flag)
                {
                    if(preg_match("/:/i", $line))
                    {
                        $attribute = explode(':',trim($line));
                        $attribute_end = explode('}',$attribute[1]);
                        $atributo = $num.$attribute[0];
                        $objStyle->$atributo = ':'.$attribute_end[0];                        
                    }
                    $num=10;
                    array_push($array_style, $objStyle);
                    $flag = false;
                }                
                if(preg_match("/:/i", $line)  && $flag)
                {
                    $attribute = explode(':',trim($line));
                    $attrib = $attribute[0];
                    unset($attribute[0]);
                    $attribute = implode(":",$attribute);
                    $atributo = $num.$attrib;
                    $objStyle->$atributo = ':'.$attribute; 
                    $flag = false;
                }
                          
            }
            $num++;
        }
        $this->getSession()->setProperty('template',$array_style);
        return $array_style;
        //$this->vd($array_style,0);die();        
    }
    
    public function headDokeos()
    {
        $html ='';
        $html.='<link type="text/css" href="application/dms/assets/css/ui-lightness/jquery-ui-1.8.20.custom.css" rel="stylesheet" />';
        $html.='<script type="text/javascript" src="application/dms/assets/js/jquery-ui-1.8.20.custom.min.js"></script>';        
        if(file_exists($this->css))
            $html.='<link rel="stylesheet" type="text/css" href="'.$this->css.'" />';
        /*if(file_exists($this->css_template))
            $html.='<link rel="stylesheet" type="text/css" href="'.$this->css_template.'" />';*/
        return $html;
    }    
    
    public function getSettings($key, $access_url='')
    {
        $objModel = new application_dms_models_modeldms();
        if(strlen(trim($access_url))>0)
            $result = $objModel->getSettings($key, $access_url);
        else
            $result = $objModel->getSettings($key);
        return $result;        
    }
    
    public function getListTemplates()
    {
        $path_template = 'css/';
        $array_template = array();
        foreach(glob($path_template . '*') as $file) 
        {
            $path_info = pathinfo($file);
            if(is_dir($path_template.$path_info['filename']))
            {
                //unset name list template
                $name_file = explode("_", $path_info['filename']);
                unset($name_file[0]);
                $name_file = implode("_", $name_file);
                
                $objTemplate            = new stdClass();
                $objTemplate->name_publish      = $name_file;
                $objTemplate->name_file         = $path_info['filename'];
 
                if($this->getThemeDokeos() == $path_info['filename'])
                    $objTemplate->active    = 1;
                else
                    $objTemplate->active    = 0;
                array_push($array_template, $objTemplate);
            }
        }
        return $array_template;
    }
    
    public function getHtmlContent($template ='')
    {
        $html='';
        if(strlen($template) > 0)
        { 
            $html.='<link href="css/'.$template.'/default.css" type="text/css" rel="stylesheet" />'.PHP_EOL;
        }
        else
        {
            $html.='<link href="css/'.$this->getThemeDokeos().'/default.css" type="text/css" rel="stylesheet">';
        }
           // 
        $html.='<div id="wrapper">
                <div id="header">
                    <div id="header1" style="display: block; min-height: 50px; z-index: 1000;">		
                        <div class="headerinner">
                                <div id="top_corner">
                                    <a style="display:block;height:50px;width:200px;"  href="http://main.dokeos22w.net/index.php"></a></div>
                                <div id="institution">
                                    <a href="http://main.dokeos22w.net/index.php" target="_top">Academy</a>	-&nbsp;<a href="http://www.dokeos.com" target="_top">Organisation</a>						</div>
                        </div>

                    </div>
                    <div id="header2">
                        <div class="headerinner1">
                            <div id="dokeostabs">                                                                                      				                               
                                <a href="#prev" id="prev"></a>                 
                                <div id="tabs_container">              
                                    <ul id="tabs" ><li  id="current" class="tab_mycampus_current"><a href="#" target="_top">Home</a></li><li  class="tab_mycourses"><a href="#" target="_top">Courses</a></li>
                                    </ul>
                                </div> <!-- /#tabs_container -->
                                <a href="#next" id="next"></a>                                                                                             
                            </div> <!-- /#dokeostabs -->
                            <div style="clear: both;" class="clear"> </div>                
                        </div>
                        <div id="clear_div"> </div>
                    </div>

                    </div>
            </div>';
        $html.='<div class="clear">&nbsp;</div>';
        $html.='<div id="main">';
            $html.='<div id="content2" style="width:570px">';
            $html.='<div>
                        <table>
                            <tr>
                                <td>
                                    <div class="section">
                                            <h3 class="tablet_title">Account</h3>
                                            <a href="main/create_course/add_course.php">
                                            <img src="'.$this->getConfiguration('root_web').'main/img/pixel.gif" alt="Create a course" title="Create a course" class="homepage_button homepage_create_course" align="absmiddle">Create a course</a><br>
                                    <a href="main/auth/courses.php">
                                    <img src="'.$this->getConfiguration('root_web').'main/img/pixel.gif" alt="Sort courses" title="Sort courses" class="homepage_button homepage_catalogue" align="absmiddle">Sort courses</a>
                                    </div>
                                </td>
                                <td>
                                    
                                </td>
                            </tr>
                        </table>
                    </div>';
                $html.='<div id="margin_template">
                        <table class=" " style="width: 570px; padding-left: 20px;">
                                    <tbody>
                                        <tr>
                                            <td height="161">
                                            <table cellspacing="15" cellpadding="0" border="0">
                                                <tbody>
                                                    <tr>
                                                        <th width="25%" scope="row">
                                                        <div class="section_white">
                                                        <table cellspacing="0" cellpadding="0" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <th scope="row">
                                                                    <table cellspacing="0" cellpadding="0" border="0">
                                                                        <tbody>
                                                                            <tr>
                                                                                <th width="241" align="left" scope="row" style="padding: 10px;">
                                                                                <h2>Demo</h2>
                                                                                <p>si erilit ad magna ad dolorercing ea consequis dolorpe raessequat. Si erilit ad magna ad dolorercing</p>
                                                                                </th>
                                                                                <td width="120"><span style="padding: 10px;"><!--<img vspace="0" hspace="0" border="0" align="right" src="'.$this->getConfiguration('root_web').'main/default_course_document/images/icons/office/tablet_new.png" alt=" " />--></span></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    </th>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        </div>
                                                        </th>
                                                        <td width="25%">
                                                        <div class="section_white">
                                                        <table cellspacing="0" cellpadding="0" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <th scope="row">
                                                                    <table cellspacing="0" cellpadding="0" border="0">
                                                                        <tbody>
                                                                            <tr>
                                                                                <th width="241" align="left" scope="row" style="padding: 10px;">
                                                                                <h2>Overview</h2>
                                                                                <p>Deliquisim vero ex enibh ectem il in ullummodolor at.<br />
                                                                                Ratem ipis at alit irit in veliscipit.</p>
                                                                                </th>
                                                                                <td width="120"><span style="padding: 10px;"><!--<img vspace="0" hspace="0" border="0" align="right" src="'.$this->getConfiguration('root_web').'main/default_course_document/images/icons/office/mouse_new.png" alt=" " />--></span></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    </th>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                        </di>';
            $html.='</div>';
        $html.='</div>';
        return $html;
    }    
    
    public function vd($var,$band)
    {
        echo"<pre>";
        if($band)
            var_dump($var);
        else
            print_r($var);
        echo"</pre>";
    }
    
    public function view()
    {
        
    }

}