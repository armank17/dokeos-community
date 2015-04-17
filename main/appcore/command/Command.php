<?php
//require 'appcore/base/ApiDokeos.php';
abstract class appcore_command_Command //extends appcore_base_ApiDokeos
{
    public $request;
    
    public $session;
    
    protected $fileView;
    
    protected $headerView;
    
    protected $footerView;
    
    protected $cmd;
    protected $module;
    protected $submodule_path;
    
    protected $fileViewDirName;
    
    private $footerCore = true;
    
    private $headerCore = true;
    
    private $reducedHeader = false;
    
    private $reducedHeaderPath;
    
    private $language_file;
    
    public $languageInterface;
    
    public function __construct() {}
    
    public function setLanguageFile($array)
    {
        $this->language_file = $array;
    }
    
    public function getSession()
    {
        $this->session = new appcore_controller_Session();
        return $this->session;
    }
    
    public function getRequest()
    {
        $this->request = new appcore_controller_Request();
        return $this->request;
    }
    
    public function execute(appcore_controller_Request $objRequest)
    {
        $this->session = new appcore_controller_Session();
        
        $this->request = $objRequest;
        
        $func       = $objRequest->getProperty('func');
        $cmd        = $objRequest->getProperty('cmd');
        $module     = $objRequest->getProperty('module');
        $submodule  = $objRequest->getProperty('submodule');
        
        
        if(! $module) $module = 'index';
        if(! $cmd) $cmd = 'IndexController';

        $func = str_replace(array('.','/','\\'),'',$func);
        $cmd = str_replace(array('.','/','\\'),'',$cmd);
        $module = str_replace(array('.','/','\\'),'',$module);
        $this->cmd = $cmd;
        $this->module = $module;
        /*echo "<pre>";
        var_dump($submodule);
        echo "</pre>";*/
        $submodule = str_replace(array('.','/','\\'),'',$submodule);
        /*echo "<pre>";
        var_dump($submodule);
        echo "</pre>";*/
        if(strlen(trim($submodule))== 0) {
                $submodule_path = '';
                $submodule_class = '';
                $submodule_qstring = '';
        }else{
                $submodule_path = $submodule.'/';
                $submodule_class = $submodule.'_';
                $submodule_qstring = '&submodule='.$submodule;
        }  
        $this->submodule_path = $submodule_path;
        $name_controller = explode("Controller", $cmd);
        $cmd = $name_controller[0]; 
        //$this->queryString = "func=$func&cmd=$cmd&module=$module";
        $this->queryString = "func=$func&cmd=".strtolower($cmd)."&module=$module{$submodule_qstring}";
        //$this->fileView = ''
        //$this->fileViewDefault = "application/$module/views/{$cmd}.php";
        $this->fileViewDefault = "application/$module/{$submodule_path}views/{$cmd}/".$cmd.".php";
        //var_dump($this->fileViewDefault);
        if(!file_exists($this->fileViewDefault)){
            $this->fileViewDefault = "application/$module/{$submodule_path}views/".strtolower($cmd)."/".  strtolower($cmd).".php";
            if(!file_exists($this->fileViewDefault)){
                $this->fileViewDefault = "application/$module/{$submodule_path}views/".  ucwords($cmd)."/".  strtolower($cmd).".php";
                if(!file_exists($this->fileViewDefault)){
                        $this->fileViewDefault = "application/$module/{$submodule_path}views/".  strtolower($cmd)."/".  ucwords($cmd).".php";                
                    if(!file_exists($this->fileViewDefault)){
                            $this->fileViewDefault = "application/$module/{$submodule_path}views/".  ucwords($cmd)."/".  ucwords($cmd).".php";
                        }
                }
            }
        }
        $fileView2 = "application/$module/{$submodule_path}views/{$cmd}/$func.php";

        $this->fileViewDirName = "application/$module/{$submodule_path}views/{$cmd}/";
        
        if($this->headerView == null || $this->footerView == null)
        {
            $this->headerView = 'appcore/themes/default/header.php';
            $this->footerView = 'appcore/themes/default/footer.php';            
        }
        
        $this->reducedHeaderPath = 'appcore/themes/default/reduced_header.php';;

        $this->doExecute($objRequest);

        if (method_exists($this,$func)){
           $this->$func();
        }

        //if (!api_is_xml_http_request()) {
            if (file_exists($fileView2)) {
                if ($this->headerCore) { include($this->headerView); }
                else if ($this->reducedHeader) { include($this->reducedHeaderPath); }
                include($fileView2);
                if($this->footerCore) { include($this->footerView); }
            }
            else {
                if (file_exists($this->fileView)) {
                    if ($this->headerCore) { include($this->headerView); }
                    else if ($this->reducedHeader) { include($this->reducedHeaderPath); }
                    include($this->fileView);
                    if ($this->footerCore) { include($this->footerView); }

                }
                else {
                    if (file_exists($this->fileViewDefault)) {
                        if ($this->headerCore) { include($this->headerView); }
                        else if ($this->reducedHeader) { include($this->reducedHeaderPath); }
                        include($this->fileViewDefault);
                        if ($this->footerCore) { include($this->footerView); }
                    } else {
                        $objRequest->addFeedback('Error to link the view of the controller '.$cmd);
                        include "appcore/views/error.php";
                    }
                }
            }
        //}
                    
    }
    
    public function setTemplate($tplName, $cmd = null) {
        $tplFileName = $tplName.'.tpl.php'; 
        $fileViewDirName = $this->fileViewDirName;
        if (isset($cmd)) {
            $viewPath = "application/{$this->module}/{$this->submodule_path}views/{$cmd}";
            $templateFile = api_get_path(SYS_CODE_PATH).$viewPath.'/'.$tplName.'.tpl.php'; 
        }    
        else {
            $templateFile = $fileViewDirName.$tplFileName;
        }
        if (!empty($fileViewDirName) && file_exists($templateFile)) {
            include $templateFile;
        }        
    }
    
    public function getTemplate($tplName, $cmd = null) {
        ob_start();
        $this->setTemplate($tplName, $cmd);
        return ob_get_clean();  
    }

    public function getXmlHtmlCdata($aContents) {
        ob_start();
        echo "<?xml version='1.0'?>
                <response>";
        if (!empty($aContents)) {
            foreach ($aContents as $key => $html) {
                echo "<$key><![CDATA[ $html ]]></$key>";    
            }
        }        
        echo "</response>";
        return ob_get_clean();
    }
    
    public function doExecute(appcore_controller_Request $objRequest)
    {
        
    }
    
    public function encodingCharset($string) {
        global $charset;
        $detectEncoding = mb_detect_encoding($string);
        //if (!api_equal_encodings($charset, $detectEncoding)) {
        //    $string = api_convert_encoding($string, $charset,$detectEncoding);
        //}
        $string = api_xml_http_response_encode($string);
        return $string;
    }
    
    public function disabledHeaderCore()
    {
        $this->headerCore = false;
        $this->reducedHeader = false;
    }
    
    public function setReducedHeader() {
        $this->headerCore = false;
        $this->reducedHeader = true;
    }
    public function disabledFooterCore()
    {
        $this->footerCore = false;
    }
    
    public function setTheme($theme='', $file='header')
    {
        if(strlen(trim($theme)) > 0)
        {
            $this->headerView = 'appcore/themes/'.$theme.'/'.$file.'.php';
            $this->footerView = 'appcore/themes/'.$theme.'/footer.php';            
        }
        else
        {
            $theme = THEME_DEFAULT;
            $this->headerView = 'appcore/themes/'.$theme.'/header.php';
            $this->footerView = 'appcore/themes/'.$theme.'/footer.php';
        }
    }
    
    public function setStyleSheet($themeCss) {       
        $GLOBALS['lp_theme_css'] = $themeCss;
    }
    
    public function setPageSection($section) {
        $GLOBALS['this_section'] = $section;        
    }
    
    public function getApiDokeos($function)
    {
        $returnValue = NULL;
        $returnValue = parent::$function();
        return $returnValue;
    }
    
    public function getConfiguration($key='') {
        include 'inc/conf/configuration.php';
        if(strlen(trim($key))>0)
        {
            if(key_exists($key, $_configuration))
                    return $_configuration[$key];
            return false;
        }
        else
            return $_configuration;
            
    }
    
    public function get_lang($word)
    {
        global $language_interface, $language_files;       
        $language_files = $this->language_file;
        $resultTranslateDokeos = get_lang($word);               
        if (api_get_setting('hide_dltt_markup') === 'false') {
            $resultTranslateDokeos = str_replace(array("[=", "=]"), "", $resultTranslateDokeos);
        }         
        if ($resultTranslateDokeos == $word)
        {
            if(is_array($this->language_file))
            {
                foreach ($this->language_file as $index_i=>$itemLanguage)
                {
                    $path_lang_msf = api_get_path(SYS_CODE_PATH).'lang/'.$language_interface.'/'.$itemLanguage.'.inc.php';                    
                    include $path_lang_msf;
                }
            }

            if (isset($$word)) {
                $resultTranslateDokeos = $$word;
            }
            else if (isset(${"lang$word"})) {
                $resultTranslateDokeos = ${"lang$word"};
            }
            else {
                // We search the variable in english folder
                if(is_array($this->language_file))
                {
                    foreach ($this->language_file as $index_i=>$itemLanguage)
                    {
                        $path_lang_msf = api_get_path(SYS_CODE_PATH).'lang/english/'.$itemLanguage.'.inc.php';                    
                        include $path_lang_msf;
                    }
                }
                $resultTranslateDokeos = isset($$word) ? $$word : (isset(${"lang$word"}) ? ${"lang$word"}:$word);
            }            
        }        
        return $resultTranslateDokeos;
    } 
    
    /**
     * set html content inside head block (global variable htmlHeadXtra)
     * @param   array | string  resource list, file or inline code
     * @return  void
     */
    public function setHtmlHeadXtra($resource, $type = 'js', $head = true) {        
        if (!empty($resource)) {
            $js = $css = $inline = $media = '';
            switch ($type) {
                case 'js':
                    if (is_array($resource)) {
                        foreach ($resource as $res) {
                            $js .= '<script language="javascript" src="'.$res.'" type="text/javascript"></script>'.PHP_EOL;
                        }
                    }
                    else {
                        $js .= '<script language="javascript" src="'.$resource.'" type="text/javascript"></script>'.PHP_EOL;
                    }
                    break;
                case 'css':
                    if (is_array($resource)) {
                        foreach ($resource as $res) {
                            $css .= '<link rel="stylesheet" href="'.$res.'" />'.PHP_EOL;
                        }
                    }
                    else {
                        $css .= '<link rel="stylesheet" href="'.$resource.'" />'.PHP_EOL;
                    }
                    break;
                case 'print':
                    if (is_array($resource)) {
                        foreach ($resource as $res) {
                            $css .= '<link rel="stylesheet" type="text/css" href="'.$res.'" media="print" />'.PHP_EOL;
                        }
                    }
                    else {
                        $css .= '<link rel="stylesheet" type="text/css" href="'.$res.'" media="print" />'.PHP_EOL;
                    }
                    break;
                case 'inline':
                    $inline .= $resource;
                    break;
            }
            if ($head) {
                $GLOBALS['htmlHeadXtra'][] = $js.$css.$inline;
            }
            else {
                $GLOBALS['htmlFootXtra'][] = $js.$css.$inline;
            }
        
           
        }
    }
    
     /**
     * set html content inside head block (global variable htmlHeadXtra)
     * @param   array | string  resource list, file or inline code
     * @return  void
     */
    public function setHtmlFootXtra($resource, $type = 'js') { 
        $this->setHtmlHeadXtra($resource, $type, false);
    }
    
    public function redirect($url) {
    	 header("Location: " . $url );
         exit;
    }

    /**
     * Get current language of user or interface
     * @global type $language_interface
     * @param type $language name or id of language
     */
    public function setLanguageInterface($language = NULL) {
        global $language_interface;
        
        if(isset($language)){
            
            // if is numeric, search by id
            if (is_numeric($language)) {                
                $languages = api_get_languages();                
                $languages_id = array();
                foreach ($languages['folder'] as $language_name) {
                    $languages_id[$language_name] = api_get_language_id($language_name);
                }
                
                $language = array_search($language, $languages_id);
                if($language !== FALSE)
                    $language_interface = $language;
                
            // if is string search by name
            } else if(is_string($language)){
                $languages = api_get_languages();
                if(in_array($language, $languages['folder']))
                    $language_interface = $language;
            }
        }
        $this->languageInterface = $language_interface;        
    }
    
    /**
     * print variable preformatted 
     */
    public function vd($var)
    {
        echo"<pre>";
        print_r($var);
        echo"</pre>";
    }
    
}
