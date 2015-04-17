/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
// The configuration options below are needed when running CKEditor from source files.
//config.plugins = 'dialogui,dialog,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,clipboard,button,panelbutton,panel,floatpanel,colorbutton,colordialog,templates,menu,contextmenu,div,resize,toolbar,elementspath,list,indent,enterkey,entities,popup,filebrowser,find,fakeobjects,flash,floatingspace,listblock,richcombo,font,forms,format,htmlwriter,horizontalrule,iframe,wysiwygarea,image,smiley,justify,link,liststyle,magicline,maximize,newpage,pagebreak,pastetext,pastefromword,preview,print,removeformat,save,selectall,showblocks,showborders,sourcearea,specialchar,menubutton,scayt,stylescombo,tab,table,tabletools,undo,wsc,xml,ajax,backgrounds,codemirror,confighelper,divarea,docprops,fastimage,htmlbuttons,iframedialog,imagebrowser,insertpre,symbol,maxheight,oembed,mediaembed,placeholder,sharedspace,sourcedialog,stylesheetparser,syntaxhighlight,tableresize,uicolor,uploadcare,allmedias,onchange';
config.skin = 'moono';
config.extraPlugins = 'tableresize,mascotmanager,imgmap,asciimath,audio,mindmaps,imagemanager,streaming,videoplayer,codemirror,confighelper';
config.codemirror = {

    // Set this to the theme you wish to use (codemirror themes)
    theme: 'default',

    // Whether or not you want to show line numbers
    lineNumbers: true,

    // Whether or not you want to use line wrapping
    lineWrapping: true,

    // Whether or not you want to highlight matching braces
    matchBrackets: true,

    // Whether or not you want tags to automatically close themselves
    autoCloseTags: true,

    // Whether or not you want Brackets to automatically close themselves
    autoCloseBrackets: true,

    // Whether or not to enable search tools, CTRL+F (Find), CTRL+SHIFT+F (Replace), CTRL+SHIFT+R (Replace All), CTRL+G (Find Next), CTRL+SHIFT+G (Find Previous)
    enableSearchTools: true,

    // Whether or not you wish to enable code folding (requires 'lineNumbers' to be set to 'true')
    enableCodeFolding: true,

    // Whether or not to enable code formatting
    enableCodeFormatting: true,

    // Whether or not to automatically format code should be done every time the source view is opened
    autoFormatOnStart: true,

    // Whether or not to automatically format code which has just been uncommented
    autoFormatOnUncomment: true,

    // Whether or not to highlight the currently active line
    highlightActiveLine: true,

    // Whether or not to highlight all matches of current word/selection
    highlightMatches: true,

    // Whether or not to show the format button on the toolbar
    showFormatButton: true,

    // Whether or not to show the comment button on the toolbar
    showCommentButton: true,

    // Whether or not to show the uncomment button on the toolbar
    showUncommentButton: true
};

config.customConfig='config.js.php';
};