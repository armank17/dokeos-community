<?php
/**
* @author Cesar Pillihuaman <cesarpillihuaman@gmail.com>
* created on Mar 12, 2012 at 1:31:42 AM
*/

require_once ('HTML/QuickForm/textarea.php');
require_once (api_get_path(LIBRARY_PATH).'fckeditor/fckeditor.php');
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
    /**
     * Class constructor
     * @param   string  HTML editor name/id
     * @param   string  HTML editor  label
     * @param   string  Attributes for the textarea
     * @param array $editor_config	Optional configuration settings for the online editor.
     */
    function HTML_QuickForm_html_editor($elementName = null, $elementLabel = null, $attributes = null, $config = null)
    {
        // The global variable $fck_attribute has been deprecated. It stays here for supporting old external code.
        global $fck_attribute;
        
        HTML_QuickForm_element :: HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_type = 'html_editor';
        $this->fullPage = false;

        $name = $this->getAttribute('name');

        $this -> fck_editor = new FCKeditor($name);

        $this->fck_editor->ToolbarSet = $fck_attribute['ToolbarSet'] ;
        $this->fck_editor->Width = !empty($fck_attribute['Width']) ? $fck_attribute['Width'] : '940';
        $this->fck_editor->Height = !empty($fck_attribute['Height']) ? $fck_attribute['Height'] : '400';
        //We get the optionnals config parameters in $fck_attribute array
        $this->fck_editor->Config = !empty($fck_attribute['Config']) ? $fck_attribute['Config'] : array();

        // This is an alternative (a better) way to pass configuration data to the editor.
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
        return FCKeditor :: IsCompatible();
    }
    /**
     * Return the HTML editor in HTML
     * @return string
     */
    function toHtml()
    {
        $value = $this->getValue();

        if ($this->fullPage)
        {
            if (strlen(trim($value)) == 0)
            {
                // TODO: To be considered whether here to be added DOCTYPE, language and character set declarations.
                $value = '<html><head><title></title><style type="text/css" media="screen, projection">/*<![CDATA[*/body{font-family: arial, verdana, helvetica, sans-serif;font-size: 12px;}/*]]>*/</style></head><body></body></html>';
                $this->setValue($value);
            }
        }
        if ($this->_flagFrozen)
        {
            return $this->getFrozenHtml();
        }
        else
        {
            return $this->build_FCKeditor();
        }
    }
    /**
     * Returns the htmlarea content in HTML
     *@return string
     */
    function getFrozenHtml()
    {
        return $this->getValue();
    }
    /**
     * Build this element using FCKeditor
     */
    function build_FCKeditor()
    {
        $result = '';
        if(! FCKeditor :: IsCompatible())
        {
            return parent::toEditorHtml();
        }        
        
        if (!defined('DOKEOS_EXERCISE') && !defined('DOKEOS_WIKI')) {
            $result .= "<script type=\"text/javascript\">";
            $result .= '
                        function FCKeditor_OnComplete( editorInstance ) {
                            $(".ck-loading textarea").css("visibility", "visible");
                            if ($(\'.ck-icon-loading\').length > 0) {
                                $(\'.ck-icon-loading\').remove();
                            }                
                        }';
            $result .= "</script>\n";     
        }
        $this -> fck_editor->Value = $this->getValue();        
        $result .= $this->fck_editor->CreateHtml(true);
        return $result;
    }
}
?>
