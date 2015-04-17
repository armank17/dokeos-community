<?php
/**
* @author Cesar Pillihuaman <cesarpillihuaman@gmail.com>
* created on Mar 12, 2012 at 1:34:34 AM
*/
require_once ('HTML/QuickForm/textarea.php');

require_once (api_get_path(LIBRARY_PATH).'ckeditor/ckeditor.php');
/**
 * A html editor field to use with QuickForm
 */
class HTML_QuickForm_html_editor extends HTML_QuickForm_textarea
{
    /**
     * Full page
     */
    var $fullPage;
    var $fck_editor;
    protected $editorName;
    protected $basePathCkEditor;
    protected $config;
    /**
     * Class constructor
     * @param   string  HTML editor name/id
     * @param   string  HTML editor  label
     * @param   string  Attributes for the textarea
     * @param array $editor_config	Optional configuration settings for the online editor.
     */
    public function HTML_QuickForm_html_editor($elementName = null, $elementLabel = null, $attributes = null, $config = null)
    {
        // The global variable $fck_attribute has been deprecated. It stays here for supporting old external code.
        global $fck_attribute, $language_interface;

        $this->config = $config;

        $temp = HTML_QuickForm_element :: HTML_QuickForm_element($elementName, $elementLabel, $attributes);
                       
        $this->_persistantFreeze = true;
        $this->_type = 'html_editor';
        $this->fullPage = true;
        
        $name = $this->getAttribute('name');
        
        
        $this->editorName = $name;
        
        if(!is_null($this->editorName))
        {
            
            $this->basePathCkEditor = (api_get_path(WEB_PATH).'main/inc/lib/ckeditor/');
            $u = $_SERVER['HTTP_USER_AGENT'];
            $browser_info = $this->getBrowser();
            $isIE7  = (bool)preg_match('/msie 7./i', $u );
            if ($isIE7 || ($browser_info['ub'] == 'Firefox' && $browser_info['version'] < 4)) {
            $this->basePathCkEditor = (api_get_path(WEB_PATH).'main/inc/lib/ckeditor-old/');
            }
            $this -> fck_editor = new CKeditor($this->basePathCkEditor);
            
            
            $this->fck_editor->ToolbarSet = $fck_attribute['ToolbarSet'] ;
            $this -> fck_editor->Width = !empty($fck_attribute['Width']) ? $fck_attribute['Width'] : '940';
            $this -> fck_editor->Height = !empty($fck_attribute['Height']) ? $fck_attribute['Height'] : '400';
            //We get the optionnals config parameters in $fck_attribute array
            $this -> fck_editor->Config = !empty($fck_attribute['Config']) ? $fck_attribute['Config'] : array();
            
            // This is an alternative (a better) way to pass configuration data to the editor.
            
            
            $config['name'] = $elementName;
            if (is_array($config)) {
                foreach ($config as $key => $value) {
                    $this->fck_editor->Config[$key] = $config[$key];
                }
                if (isset($config['ToolbarSet'])) {
                    $this->fck_editor->ToolbarSet = $config['ToolbarSet'];
                }
                if (isset($config['Width'])) {
                    $this->fck_editor->Width = $config['Width'];
                }
                if (isset($config['Height'])) {
                    $this->fck_editor->Height = $config['Height'];
                }
                if (isset($config['FullPage'])) {
                    $this->fullPage = is_bool($config['FullPage']) ? $config['FullPage'] : ($config['FullPage'] === 'true');
                }
                if (isset($config['baseHref'])) {
                    $this->fullPage = is_bool($config['FullPage']) ? $config['FullPage'] : ($config['FullPage'] === 'true');
                }            
                $this->language = 'fr';
                $this->defaultLanguage = 'fr';
            }
            // set language to editor
            if (!empty($language_interface)) {
                $lang_info = api_get_language_info($language_interface);
                $this->language = $lang_info['isocode'];
            }
            else {
                $this->language = 'en';
            }
        }
      
    }

    /**
     * Check if the browser supports FCKeditor
     *
     * @access public
     * @return boolean
     */
    function browserSupported()
    {
        return true;
    }
    /**
     * Return the HTML editor in HTML
     * @return string
     */
    function toHtml()
    {
       return $this->buildCkEditor();
    }
    /**
     * Returns the htmlarea content in HTML
     *@return string
     */
    function getFrozenHtml()
    {
        return $this->getValue();
    }
    
    function getBrowser() {
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
   
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif(preg_match('/Firefox/i',$u_agent))
    {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    elseif(preg_match('/Chrome/i',$u_agent))
    {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }
    elseif(preg_match('/Safari/i',$u_agent))
    {
        $bname = 'Apple Safari';
        $ub = "Safari";
    }
    elseif(preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Opera';
        $ub = "Opera";
    }
    elseif(preg_match('/Netscape/i',$u_agent))
    {
        $bname = 'Netscape';
        $ub = "Netscape";
    }
   
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
   
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
   
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}
   
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'ub'        => $ub,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
} 
    /**
     * Build this element using FCKeditor
     */
    function buildCkEditor()
    {
        //$startupFocus = defined('DOKEOS_EXERCISE') && DOKEOS_EXERCISE?'true':'false';
        if (!isset($this->config['Placeholder'])) {                                         
            $this->config['Placeholder'] = get_lang("TypeHere");
        }
        $result  = '';        
        $result .= parent::toEditorHtml();
        $result .= '<script type="text/javascript" src="'.$this->basePathCkEditor .'imagealign.js"></script>';
        $result .= '<script type="text/javascript" src="'.$this->basePathCkEditor .'ckeditor.js"></script>';               
        $result .= "<script type=\"text/javascript\">";
        $result .= "//<![CDATA[\n";
        $result .= 'var ToolbarSet = "'.$this->config['ToolbarSet'].'";'."\n";
        $result .= 'CKEDITOR.replace("'. $this->editorName.'", {
			
				
			
				on: {
					 instanceReady: function(ev) { 
                                                    configureHtmlOutput(ev);
                                                    if ($(\'.ck-icon-loading\').length > 0) {
                                                        $(\'.ck-icon-loading\').remove();
                                                    }
                                                    
                                                    body = ev.editor.document.getBody();
                                                    
                                                    if(ToolbarSet=="Authoring")
                                                    {
																													
                                                        body.setStyle("font-size","28px");
                                                        $("#cke_9").hide();
                                                        $("#cke_29").hide();
                                                        $("#cke_31").hide();
                                                        $( "#cke_34" ).bind( "click", function() {
                                                                $("#cke_9").toggle();
                                                                $("#cke_29").toggle();
                                                                $("#cke_31").toggle();
                                                        });
                                                        $.data(window.document.body,"content",body.getOuterHtml());
                                                    }
                                                    
                                                    
                                                    if(ToolbarSet=="TestProposedFeedback")
                                                    {
														body.setStyle("font-size","16px");
														
													}
                                                    
                                                    // Fix problem with cursor not appearing in Chrome when clicking below the body
                                                    this.document.on("mousedown", function (evt) {
                                                        if (CKEDITOR.env.webkit) {
                                                            if (evt.data.getTarget().is("html")) {
                                                              ev.editor.editable().focus();
                                                            }
                                                        }
                                                    });
                                            }
					 },
                 toolbar: "'.$this->config['ToolbarSet'].'",
                 width : "'.$this->config['Width'].'",
                 height: "'.$this->config['Height'].'",
                 fullPage: "'.$this->config['FullPage'].'",
                 baseHref: "'.$this->config['BaseHref'].'",
                 language: "'.$this->language.'",
                 placeholder: "'.$this->config['Placeholder'].'",
                 
                 allowedContent: true
                 
            });
            
            
            
			
			
            
            
            ';
        $result .= "\n//]]>";
        //   "'.$this->config['FullPage'].'"
        $result .= "</script>\n";
        return $result;
    }
}

