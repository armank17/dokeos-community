// Reworks and improvements by Alberto Flores
var dialog = window.parent ;
var oEditor = dialog.InnerDialogLoaded() ;
var FCK = oEditor.FCK ;
var FCKLang = oEditor.FCKLang ;
var FCKConfig = oEditor.FCKConfig ;
var FCKTools = oEditor.FCKTools ;

// Security RegExp
var REG_SCRIPT = new RegExp( "< *script.*>|< *style.*>|< *link.*>|< *body .*>", "i" ) ;
var REG_PROTOCOL = new RegExp( "javascript:|vbscript:|about:", "i" ) ;
var REG_CALL_SCRIPT = new RegExp( "&\{.*\};", "i" ) ;
var REG_EVENT = new RegExp( "onError|onUnload|onBlur|onFocus|onClick|onMouseOver|onMouseOut|onSubmit|onReset|onChange|onSelect|onAbort", "i" ) ;
// Cookie Basic
var REG_AUTH = new RegExp( "document\.cookie|Microsoft\.XMLHTTP", "i" ) ;
// TEXTAREA
var REG_NEWLINE = new RegExp( "\x0d|\x0a", "i" ) ;

var YoutubeSite = 'http://www.youtube.com/v/' ;
var YoutubeSiteWatch = 'http://www.youtube.com/watch?v=';
var VimeoSite   = 'http://vimeo.com/';
var HighQualityString = '%26hl=en%26fs=1%26rel=0%26ap=%2526fmt=18' ;
var LowQualityString = '%26hl=en%26fs=1%26rel=0' ;

// Set the language direction.
window.document.dir = FCKLang.Dir ;

// We have to avoid javascript errors if some language variables have not been defined.
FCKLang['UploadSelectFileFirst'] = FCKLang['UploadSelectFileFirst'] ? FCKLang['UploadSelectFileFirst'] : 'Please, select a file before pressing the upload button.' ;
FCKLang['FileSuccessfullyUploaded'] = FCKLang['FileSuccessfullyUploaded'] ? FCKLang['FileSuccessfullyUploaded'] : 'Your file has been successfully uploaded.' ;
FCKLang['FileRenamed'] = FCKLang['FileRenamed'] ? FCKLang['FileRenamed'] : 'A file with the same name is already available. The uploaded file has been renamed to ' ;
FCKLang['InvalidFileType'] = FCKLang['InvalidFileType'] ? FCKLang['InvalidFileType'] : 'Invalid file type.' ;
FCKLang['InvalidProviderType'] = FCKLang['InvalidProviderType'] ? FCKLang['InvalidProviderType'] : 'Invalid provider type.' ;
FCKLang['SecurityError'] = FCKLang['SecurityError'] ? FCKLang['SecurityError'] : 'Security error. You probably don\'t have enough permissions to upload. Please check your server.' ;
FCKLang['ConnectorDisabled'] = FCKLang['ConnectorDisabled'] ? FCKLang['ConnectorDisabled'] : 'The upload feature (connector) is disabled.' ;
FCKLang['UploadError'] = FCKLang['UploadError'] ? FCKLang['UploadError'] : 'Error on file upload. Error number: ' ;

// Dialog Tabs

var theTab = 'social';
var theVideoType = 'single';
var selected_url = ''; 
var oFakeImage = FCK.Selection.GetSelectedElement() ;
// Set the dialog tabs.
dialog.AddTab( 'videofile', FCKLang['DlgFLVPlayerURL']) ;
dialog.AddTab( 'flash', 'Flash') ;
dialog.AddTab( 'social', 'Youtube & Vimeo') ;

// This function is called when a dialog tab has been selected.
function OnDialogTabChange(tabCode) {
    ShowE( 'divInfo', (tabCode == 'videofile'));
    //ShowE( 'divPreview', (tabCode == 'preview'));
    ShowE( 'divUpload', (tabCode == 'flash'));
    ShowE( 'divSocial', (tabCode == 'social')) ;
    theTab = tabCode;
}

function OnDialogModeChange( mode )
{
    if ( mode == 'single') {
       // try {
        
        //txtUploadFile.disabled = false;
        //btnUpload.disabled = false;
        GetE('txtUploadFile').disabled = false;
        GetE('btnUpload').disabled = false;
        
        
        btnBrowse.disabled = false ;
        txtURL.disabled = false ;
        txtStreaming.disabled = true ;
        txtStreaming.value = '' ;
        txtURL.style.background = '#ffffff' ;
        txtStreaming.style.background = 'transparent' ;
        theVideoType = 'single';
        //} catch(e){}
    }
    else {  
        //try {
        txtStreaming.disabled = false;
        //txtUploadFile.disabled = true;
        GetE('txtUploadFile').disabled = true;
        GetE('btnUpload').disabled = true;
        //btnUpload.disabled = true;
        btnBrowse.disabled = true ;
        txtURL.disabled = true ;
        txtURL.value = '' ;
        txtURL.style.background = 'transparent' ;
        txtStreaming.style.background = '#ffffff' ;
        theVideoType = 'streaming';
        //} catch(e){}
    }
}

var oMedia = null ;
var oEmbed;
var is_new_videoplayer = true ;
var is_new_youtube = true;

// Get the selected video (if available).   
window.onload = function() {

    // Translate the dialog box texts.
    oEditor.FCKLanguageManager.TranslatePage(document) ;

    // Load the selected element information (if any).
    LoadSelection() ;
    if (/\.youtube\.com/i.test(selected_url) || (/vimeo\.com/i.test(selected_url))) {
        theTab = 'social';
        ClearFlash();
        ClearVideo();
        LoadSelection() ;
        dialog.SetSelectedTab('social');         
    } else {
        theTab = 'videofile';
        LoadSelection() ;        
        if (/\.(flv|mpg|mpeg|mp4|avi|wmv|mov|asf)/i.test(selected_url) || /(rtmp:\/\/)/i.test(selected_url) ) {
            theTab = 'videofile';
            ClearFlash();
            ClearSocial();
            LoadSelection() ;
            dialog.SetSelectedTab('videofile'); 
        } else {
            if (/\.swf/i.test(selected_url)) {
                theTab = 'flash';
                ClearSocial();
                ClearVideo();
                LoadSelection() ;
                dialog.SetSelectedTab('flash'); 
            }
        }                         
    }

    // Set the actual uploader URL.
    if (FCKConfig.MediaUpload) {         
        GetE('frmUpload').action = FCKConfig.VideoFilesUploadURL; 
        
    }    
    if (FCKConfig.FlashUpload) {
        GetE('frmFlashUpload').action = FCKConfig.FlashUploadURL; 
    }

    dialog.SetAutoSize(true);

    // Activate the "OK" button.
    dialog.SetOkButton(true) ;
}

function getSelectedSocial() {            
    if (oFakeImage) {
        if ( oFakeImage.tagName == 'IMG' && (oFakeImage.getAttribute( '_fckvideo' ) || oFakeImage.getAttribute( '_fckflash' )) ) {
            oEmbed = FCK.GetRealElement( oFakeImage ) ;            
        }
        else {
            oFakeImage = null ;
        }
    }    
}

function getSelectedMovie()
{
    var oFakeImage = FCK.Selection.GetSelectedElement() ;
    var oSel = null ;
    oMedia = new Media() ;
    if (oFakeImage )
    {
        if ( oFakeImage.tagName == 'IMG' && oFakeImage.getAttribute( '_fckvideo' ) )
        {
            oSel = FCK.GetRealElement( oFakeImage ) ;
            if ( oSel && oSel.id && oSel.id.match( /^player[0-9]*-parent$/ ) )
            {                
                for ( var i = 0 ; i < oSel.childNodes.length ; i++ )
                {
                    if ( oSel.childNodes.item(i).nodeName == "DIV" )
                    {
                        for ( var k = 0 ; k < oSel.childNodes.item(i).childNodes.length ; k++ )
                        {
                            if ( oSel.childNodes.item(i).childNodes.item(k).nodeName == "DIV" &&
                                oSel.childNodes.item(i).childNodes.item(k).id &&
                                oSel.childNodes.item(i).childNodes.item(k).id.match( /^player[0-9]*-config$/ ) )
                            {
                                var oC = oSel.childNodes.item(i).childNodes.item(k).innerHTML.split(' ') ;
                                for ( var o = 0 ; o < oC.length ; o++ )
                                {
                                    var tmp = oC[o].split( '=' ) ;
                                    oMedia.setAttribute( tmp[0], tmp[1] ) ;
                                }
                                is_new_videoplayer = false ;
                                break ;
                            }
                        }
                    }
                }
            }
        }
    }
    return oMedia ;
}

function LoadSelection()
{       
    if (theTab == 'videofile') {            
        oMedia = new Media() ;
        oMedia = getSelectedMovie();                
        if (oMedia.fileType == 'streaming') {
            GetE( 'txtStreaming' ).value = oMedia.purl ;
            selected_url = oMedia.purl;        
            OnDialogModeChange('streaming');
            GetE('rbFileTypeStreaming').checked = true;
        } else {            
            GetE('txtURL').value = oMedia.url ;
            selected_url = oMedia.url;
            OnDialogModeChange('single');
            GetE('rbFileTypeSingle').checked = true;
        }        
        GetE('txtWidth').value = oMedia.width.toString().length > 0 ? oMedia.width : 720 ;        
        GetE('txtHeight').value = oMedia.height.toString().length > 0 ? oMedia.height : 450 ;
        GetE('selAlign').value = oMedia.align;
        GetE('selBuffer').value = oMedia.buffer;
        GetE('chkLoop').checked = oMedia.loop ;
        GetE('chkAutoplay').checked = oMedia.play ;
        GetE('chkDownload').checked = oMedia.downloadable ;
        GetE('chkFullscreen').checked = oMedia.fullscreen ;
        dialog.SetSelectedTab('videofile');   
    } 
    else if (theTab == 'flash') {
        document.getElementById('divInfo').display = 'none';
        oMedia = new Media() ;
        oMedia = getSelectedMovie() ;        
        GetE( 'txtFlashURL' ).value = oMedia.url ;        
        selected_url = oMedia.url;        
        GetE( 'txtFlashWidth' ).value = oMedia.width.toString().length > 0 ? oMedia.width : 720 ;
        GetE( 'txtFlashHeight' ).value = oMedia.height.toString().length > 0 ? oMedia.height : 450 ;
        GetE('selFlashAlign').value = oMedia.align;
        GetE( 'chkFlashLoop' ).checked = oMedia.loop ;
        GetE( 'chkFlashAutoplay' ).checked = oMedia.play ;
    } 
    else if (theTab == 'social') {          
        document.getElementById('divInfo').display = 'none';
        oMedia = new Media() ;
        oMedia = getSelectedMovie() ;        
        GetE( 'txtSocialURL' ).value = oMedia.url.replace("/v/", "/watch?v=") ;        
        selected_url = oMedia.url;        
        GetE( 'txtSocialWidth' ).value = oMedia.width.toString().length > 0 ? oMedia.width : 425 ;
        GetE( 'txtSocialHeight' ).value = oMedia.height.toString().length > 0 ? oMedia.height : 344 ;
        GetE('selSocialAlign').value = oMedia.align;
        GetE( 'radioLow' ).checked = oMedia.low ;
        GetE( 'radioHigh' ).checked = oMedia.heigh ;        
    }   
}

// The OK button was hit.
function Ok()
{        
    switch (theTab) {        
        case 'videofile':            
            var rbFileTypeVal = "single";
            if (location.protocol === 'https:') {
                GetE('txtURL').value = GetE('txtURL').value.replace("http://", "https://");
            }
            if (!GetE('rbFileTypeSingle').checked) {
                rbFileTypeVal = "list" ;
            }
            
            if (theVideoType == "single") {
                if (GetE('txtURL').value.length == 0) {
                    GetE('txtURL').focus() ;
                    alert( oEditor.FCKLang.DlgFLVPlayerAlertUrl ) ;
                    return false ;
                }
                if (!(/\.(flv|mpg|mpeg|mp4|avi|wmv|mov|asf)/i.test(GetE('txtURL').value))) {
                    GetE('txtURL').focus() ;
                    alert(FCKLang['InvalidFileType']) ;
                    return false ;
                }
            }
            
            if (theVideoType == "streaming") {
                if (GetE( 'txtStreaming' ).value.length == 0) {
                    GetE( 'txtStreaming' ).focus() ;
                    alert( oEditor.FCKLang.DlgFLVPlayerAlertUrl ) ;
                    return false ;
                }                
                if (!(/rtmp:\/\//i.test(GetE('txtStreaming').value))) {
                    GetE('txtStreaming').focus() ;
                    alert(FCKLang['InvalidFileType']) ;
                    return false ;
                }                
            }            
            
            if (GetE('txtWidth').value.length == 0) {
                GetE('txtWidth').focus() ;
                alert( oEditor.FCKLang.DlgFLVPlayerAlertWidth ) ;
                return false ;
            }
            if (GetE('txtHeight').value.length == 0 ) {
                GetE('txtHeight').focus() ;
                alert( oEditor.FCKLang.DlgFLVPlayerAlertHeight ) ;
                return false;
            }            

            var e = (oMedia || new Media()) ;
            UpdateMovie(e);
            
            // Replace or insert?
            if (!is_new_videoplayer) {
                var oFakeImage = FCK.Selection.GetSelectedElement();
                var oSel = null;
                oMedia = new Media();
                if (oFakeImage) {
                    if (oFakeImage.tagName == 'IMG' && oFakeImage.getAttribute('_fckvideo')) {
                        oSel = FCK.GetRealElement(oFakeImage);
                        if (oSel) {
                            oSel = null ;
                            FCK.InsertHtml(e.getVideoInnerHTML());
                        }
                    }
                }
            } else {
                FCK.InsertHtml(e.getVideoInnerHTML());
            }            
            break;
        case 'flash':
            if (location.protocol === 'https:') {
                GetE('txtFlashURL').value = GetE('txtFlshURL').value.replace("http://", "https://");
            }
            if (GetE('txtFlashURL').value.length == 0) {
                GetE('txtFlashURL').focus() ;
                alert( oEditor.FCKLang.DlgFLVPlayerAlertUrl ) ;
                return false ;
            }
            if (GetE('txtFlashWidth').value.length == 0) {
                GetE('txtFlashWidth').focus() ;
                alert( oEditor.FCKLang.DlgFLVPlayerAlertWidth ) ;
                return false ;
            }
            if (GetE('txtFlashHeight').value.length == 0 ) {
                GetE('txtFlashHeight').focus() ;
                alert( oEditor.FCKLang.DlgFLVPlayerAlertHeight ) ;
                return false ;
            } 
            
            if (!(/\.swf/i.test(GetE('txtFlashURL').value))) {
                GetE('txtFlashURL').focus() ;
                alert(FCKLang['InvalidFileType']) ;
                return false ;
            }
            
            var e = (oMedia || new Media()) ;
            UpdateMovie(e);
            FCK.InsertHtml(e.getFlashInnerHTML());            
            break;
       case 'social':
           oFakeImage = null ;
           if (GetE('txtSocialURL').value.length == 0) {
                GetE('txtSocialURL').focus() ;
                alert( oEditor.FCKLang.DlgFLVPlayerAlertUrl ) ;
                return false ;
           }
           // Check security
           if (checkCode(GetE('txtSocialURL' ).value) == false) {
                alert(oEditor.FCKLang.DlgYouTubeSecurity) ;
                return false ;
           }   
           
           if (!(/\.youtube\.com/i.test(GetE('txtSocialURL').value)) && !(/vimeo\.com/i.test(GetE('txtSocialURL').value))) {        
               GetE('txtSocialURL').focus() ;
               alert(FCKLang['InvalidProviderType']) ;
               return false ;
           }
           
           var e = (oMedia || new Media()) ;
           UpdateMovie(e);
           FCK.InsertHtml(e.getSocialInnerHTML()); 
           break;
    }
    return true ;
}

function UpdateMovie(e) {
    if (theTab == 'videofile') {        
        if (GetE( 'txtStreaming' ).value != "") {
            txtStreaming.disabled = false;
            btnBrowse.disabled = true ;
            txtURL.disabled = true ;
            txtURL.value = '' ;
            txtURL.style.background = 'transparent' ;
            txtStreaming.style.background = '#ffffff' ;
        }   
        e.fileType = theVideoType ;
        e.url = GetE( 'txtURL' ).value ;
        e.purl = GetE( 'txtStreaming' ).value ;
        e.width = ( isNaN( GetE( 'txtWidth' ).value ) ) ? 0 : parseInt( GetE( 'txtWidth' ).value ) ;
        e.height = ( isNaN( GetE( 'txtHeight' ).value ) ) ? 0 : parseInt( GetE( 'txtHeight' ).value ) ;
        e.align = GetE('selAlign').value;
        e.buffer = GetE('selBuffer').value;
        e.loop = ( GetE( 'chkLoop' ).checked ) ? 'true' : 'false' ;
        e.play = ( GetE( 'chkAutoplay' ).checked ) ? 'true' : 'false' ;
        e.downloadable = ( GetE( 'chkDownload' ).checked ) ? 'true' : 'false' ;
        e.fullscreen = ( GetE( 'chkFullscreen' ).checked ) ? 'true' : 'false' ;
    } 
    else if (theTab == 'flash') {
        e.url = GetE('txtFlashURL').value ;
        e.width = (isNaN(GetE('txtFlashWidth').value))?0 : parseInt(GetE('txtFlashWidth').value);
        e.height = (isNaN(GetE( 'txtFlashHeight').value))?0:parseInt(GetE('txtFlashHeight').value);
        e.align = GetE('selFlashAlign').value;
        e.loop = (GetE('chkFlashLoop' ).checked ) ? 'true' : 'false' ;
        e.play = ( GetE('chkFlashAutoplay' ).checked ) ? 'true' : 'false' ;
    } else if (theTab == 'social') {
        e.url = GetE('txtSocialURL').value ;
        e.width = (isNaN(GetE('txtSocialWidth').value))?0 : parseInt(GetE('txtSocialWidth').value);
        e.height = (isNaN(GetE( 'txtSocialHeight').value))?0:parseInt(GetE('txtSocialHeight').value);
        e.align = GetE('selSocialAlign').value;
        e.low = (GetE('radioLow' ).checked ) ? 'true' : 'false' ;
        e.heih = ( GetE('radioHigh' ).checked ) ? 'true' : 'false' ;
    }    
}

function BrowseServer() {    
    switch (theTab) {        
        case 'videofile':
            OpenServerBrowser( 'videos', FCKConfig.VideoFilesBrowserURL, FCKConfig.VideoBrowserWindowWidth, FCKConfig.VideoBrowserWindowHeight ) ;
            break;
        case 'flash':
            OpenServerBrowser( 'flash', FCKConfig.FlashBrowserURL, FCKConfig.VideoBrowserWindowWidth, FCKConfig.VideoBrowserWindowHeight ) ;
            break;        
    }        
}

function LnkBrowseServer()
{
    OpenServerBrowser( 'link', FCKConfig.LinkBrowserURL, FCKConfig.LinkBrowserWindowWidth, FCKConfig.LinkBrowserWindowHeight ) ;
}

function OpenServerBrowser( type, url, width, height) {
    sActualBrowser = type ;
    OpenFileBrowser( url, width, height ) ;
}

var sActualBrowser;
function SetUrl(url) {
    url = FCK.GetUrl(url, FCK.SEMI_ABSOLUTE_URL);    
    if ( sActualBrowser == 'videos' ) {                
        GetE('txtURL').value = url;
        GetE('txtWidth').value = 720;
        GetE('txtHeight').value = 450;        
    } 
    else if ( sActualBrowser == 'link' ) {
        GetE('txtStreaming').value = url ;
    }
    else if (sActualBrowser == 'flash') {
        GetE('txtFlashURL').value = url;
        GetE('txtFlashWidth').value = 720;
        GetE('txtFlashHeight').value = 450;
    }       
}

var Media = function (o) {
    this.fileType = '' ;
    this.url = '' ;
    this.purl = '' ;
    this.width = '' ;
    this.height = '' ;
    this.loop = true ;
    this.play = true ;
    this.downloadable = false ;
    this.fullscreen = true ;
    this.low = true;
    this.heigh = false;    
    this.align = 'left';
    this.buffer = '1';
    if (o) {
        this.setObjectElement(o) ;
    }
} ;

Media.prototype.setObjectElement = function(e) {
    if ( !e ) return ;
    this.width = GetAttribute( e, 'width', this.width);
    this.height = GetAttribute( e, 'height', this.height);
} ;

Media.prototype.setAttribute = function( attr, val )
{
    if ( val == 'true' )
    {
        this[attr] = true ;
    }
    else if (val == 'false' )
    {
        this[attr] = false ;
    }
    else
    {
        this[attr] = val ;
    }
} ;

Media.prototype.getVideoInnerHTML = function ( objectId )
{
    var url = this.url;
    var sType, pluginspace, codebase, classid ;    
    var randomnumber = Math.floor( Math.random() * 1000001 ) ;
    var thisWidth = GetE('txtWidth').value ;
    var thisHeight = GetE('txtHeight').value ;  
    var streaming_url = GetE('txtStreaming').value;        

    var s = '' ;    
    s += '<br />\n' ;
    s += '<div id="player' + randomnumber + '-parent" align="'+this.align+'">\n';
    s += '<div id="test" style="border-style: none; height: ' + thisHeight + 'px; width: ' + thisWidth + 'px; overflow: hidden; background-color: rgb(220, 220, 220);">';

    // A hidden div containing setting, added width, height, overflow for MSIE7
    s += '<div id="player' + randomnumber + '-config" style="display: none; visibility: hidden; width: 0px; height: 0px; overflow: hidden;">' ;    
    if (theVideoType == 'streaming') {
        s += 'purl='+streaming_url+' fileType='+theVideoType+' ';
    }
    s += 'url='+url+' width='+thisWidth+' height='+thisHeight+' align='+this.align+' buffer='+this.buffer+' loop='+(GetE('chkLoop').checked? '1' : '0')+' play='+(GetE('chkAutoplay').checked? 'true':'false')+' downloadable='+( GetE( 'chkDownload' ).checked ? 'true' : 'false')+' fullscreen='+(GetE( 'chkFullscreen' ).checked ? 'true' : 'false');    
    s += '</div>' ;   
    s += '<div id="player' + randomnumber + '" class="thePlayer">' ;   

    if (txtStreaming.disabled == false) {
        
        var exploded = streaming_url.split('/');       
        var theFile  = exploded[exploded.length-1];
        var thePath  = streaming_url.replace("/"+theFile, "");
        s += '<script src="' + FCKConfig.ScriptJwPlayer + '" type="text/javascript"></script>';
        s += '<div id="player' + randomnumber + '-parent2">Loading the player ...</div>';        
        s += '<script type="text/javascript">';
        s += '  jwplayer("player' + randomnumber + '-parent2").setup({';        
        s += '      height: '+thisHeight+',';
        s += '      width: '+thisWidth+',';
        s += '      modes: [';
        s += '              { ';
        s += '                  type: "flash",';
        s += '                  src: "'+ FCKConfig.SWFJwPlayer +'",';
        s += '                  autostart: "'+ ( GetE('chkAutoplay').checked? 'true' : 'false') +'",';
        s += '                  repeat: "'+ ( GetE('chkLoop').checked? 'always' : '') +'",';
        s += '                  bufferlength: "'+this.buffer+'",';        
        s += '                  config: { ';
        s += '                              file: "'+theFile+'",';
        s += '                              streamer: "'+thePath+'",';
        s += '                              provider: "rtmp"';
        s += '                          }';
        s += '              }';
        s += '             ],';           
        s += '      skin: "'+ FCKConfig.SkinPathJwPlayer +'facebook.zip"';
        s += '  });';
        s += '</script>';  
        
    } else {
        var sExt = url.match( /\.(flv|mpg|mpeg|mp4|avi|wmv|mov|asf)$/i);
        if ( sExt.length && sExt.length > 0 ) {
            sExt = sExt[0];
        } else {
            sExt = '';
        }

        if (sExt == '.flv' || sExt == '.mp4' || sExt == '.mov') {
            
            if ((/MSIE[\/\s](\d+\.\d+)/.test(navigator.userAgent))) {
                s += '<script src="' + FCKConfig.ScriptJwPlayer + '" type="text/javascript"></script>';
                s += '<object id="player' + randomnumber + '-parent2" name="player' + randomnumber + '-parent2" width="'+thisWidth+'" height="'+thisHeight+'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
                s += '<param name="movie" value="'+ FCKConfig.SWFJwPlayer +'" />';
                s += '<param name="allowFullScreen" value="' + ( GetE( 'chkFullscreen' ).checked ? 'true' : 'false') + '" />';
                s += '<param name="allowscriptaccess" value="always" />';
                s += '<param name="seamlesstabbing" value="true" />';
                s += '<param name="wmode" value="transparent" />'
                s += '<param name="flashvars" value="id=player' + randomnumber + '-parent2&autostart='+ ( GetE('chkAutoplay').checked? 'true' : 'false') +'&repeat='+ ( GetE('chkLoop').checked? 'always' : '') +'&file='+ url +'&skin='+ FCKConfig.SkinPathJwPlayer +'facebook.zip&bufferlength='+this.buffer+'&controlbar.position=over"  />'                
                s += '</object>';                
            } else {
                s += '<script src="' + FCKConfig.ScriptJwPlayer + '" type="text/javascript"></script>';
                s += '<div id="player' + randomnumber + '-parent2">Loading the player ...</div>';    
                s += '<script type="text/javascript">';
                s += 'jwplayer("player' + randomnumber + '-parent2").setup({';
                s += 'flashplayer: "'+ FCKConfig.SWFJwPlayer +'",';
                s += 'autostart: "'+ ( GetE('chkAutoplay').checked? 'true' : 'false') +'",';
                s += 'repeat: "'+ ( GetE('chkLoop').checked? 'always' : '') +'",';
                s += 'file: "'+ url +'",';
                s += 'height: '+thisHeight+',';
                s += 'width: '+thisWidth+',';
                s += 'bufferlength: '+this.buffer+',';
                s += 'skin: "'+ FCKConfig.SkinPathJwPlayer +'facebook.zip"';
                s += '});';        
                if (!GetE('chkFullscreen').checked) {        
                    s += 'jwplayer("player' + randomnumber + '-parent2").setFullscreen(false);';
                }
                s += '</script>';
            }       
         } 
         else {
            pluginspace = 'http://www.microsoft.com/Windows/MediaPlayer/' ;
            codebase = 'http://www.microsoft.com/Windows/MediaPlayer/' ;
            classid = 'classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95"' ;
            sType = ( sExt == '.mpg' || sExt == '.mpeg' ) ? 'video/mpeg' :
                                    ( sExt == '.avi' || sExt == '.wmv' || sExt == '.asf' ) ? 'video/x-msvideo' :
                                    ( sExt == '.mov' ) ? 'video/quicktime' :
                                    ( sExt == '.mp4' ) ? 'video/mpeg4-generic' :
                                    'video/x-msvideo' ;
           s += '<embed type="' + sType + '" src="' + url + '" ' +
               'autosize="false" ' +
               'autostart="' + ( GetE('chkAutoplay').checked? 'true' : 'false')  + '" ' +
               'loop="' + ( GetE( 'chkLoop' ).checked ? 'true' : 'false' ) + '" ' +
               'fullscreen="' + ( GetE( 'chkFullscreen' ).checked ? 'true' : 'false') + '" ' +
               'showcontrols="true"' + //( GetE( 'chkShowNavigation' ).checked ? 'true' : 'false' ) + '" ' +
               'showpositioncontrols="false" ' +
               'showtracker="true"' + //( GetE( 'chkShowDigits' ).checked ? 'true' : 'false' ) + '" ' +
               'showaudiocontrols="true" ' +
               'showgotobar="true" ' +
               'showstatusbar="true" ' +
               'pluginspace="' + pluginspace + '" ' +
               'codebase="' + codebase + '"' ;
           s += 'width="' + GetE( 'txtWidth' ).value + '" height="' + GetE( 'txtHeight' ).value + '"' ;
           s += '></embed>' ;

         }        
    }
    
    s += '</div>';    
    s += '</div>';
    s += '</div>'; 
    return s ; 
} ;

Media.prototype.getSocialInnerHTML = function (objectId) {
    
    var url = '';
    var urlsel = '';
    var src = GetE('txtSocialURL').value;
    var video = parseVideoURL(src);   
    
    if (video.provider == 'vimeo') {        
        url = 'http://vimeo.com/moogaloop.swf?clip_id='+video.id;
    }
    else {        
        var YoutubeId = GetYoutubeId(src);        
        if ( GetE( 'radioHigh' ).checked ) {
            url = YoutubeSite + YoutubeId + HighQualityString;
        }
        else {
            url = YoutubeSite + YoutubeId + LowQualityString;
        }
    }    

    var thisWidth = GetE('txtSocialWidth').value ;
    var thisHeight = GetE('txtSocialHeight').value ;
    var randomnumber = Math.floor( Math.random() * 1000001 ) ;

    var s = '' ;    
    s += '\n' ;
    s += '<div id="player' + randomnumber + '-parent" align="'+this.align+'">\n';
    s += '<div id="test" style="border-style: none; height: ' + thisHeight + 'px; width: ' + thisWidth + 'px; overflow: hidden; background-color: rgb(220, 220, 220);">';

    // A hidden div containing setting, added width, height, overflow for MSIE7
    s += '<div id="player' + randomnumber + '-config" style="display: none; visibility: hidden; width: 0px; height: 0px; overflow: hidden;">' ;    
    s += 'url='+(video.provider == 'vimeo'?src:url)+' width='+thisWidth+' height='+thisHeight+' align='+this.align+' heigh='+(GetE('radioHigh').checked? '1' : '0')+' low='+(GetE('radioLow').checked? 'true':'false');    
    s += '</div>' ;   
    s += '<div id="player' + randomnumber + '" class="thePlayer">' ;   
    
    if ((/MSIE[\/\s](\d+\.\d+)/.test(navigator.userAgent))) {
        s += '<embed src="'+url+'"';
        s += 'type="application/x-shockwave-flash"';
        s += 'allowfullscreen="true"';
        s += 'width="'+thisWidth+'" height="'+thisHeight+'">';
        s += '</embed>';
    } else {
        s += '<object width="'+thisWidth+'" height="'+thisHeight+'">';
        s += '<param name="movie" value="'+url+'"></param>';
        s += '<param name="allowFullScreen" value="true"></param>';
        s += '<embed src="'+url+'"';
        s += 'type="application/x-shockwave-flash"';
        s += 'allowfullscreen="true"';
        s += 'width="'+thisWidth+'" height="'+thisHeight+'">';
        s += '</embed>';
        s += '</object>';
    }

    s += '</div>';
    s += '</div>';
    s += '</div>';
    
    return s;
    
}

/* Flash functions */
/**
 * Devuelve el codigo HTML interno del elemento
 * 	Returns the HTML code inside the element
 */
Media.prototype.getFlashInnerHTML = function (objectId) {        
	var s = '' ;        
        var url = this.url;
        var randomnumber = Math.floor( Math.random() * 1000001 ) ;
        var thisWidth = GetE('txtFlashWidth').value ;
        var thisHeight = GetE('txtFlashHeight').value ; 
        
        s += '<div id="player' + randomnumber + '-parent" align="'+this.align+'">\n';
        s += '<div id="test" style="border-style: none; height: ' + thisHeight + 'px; width: ' + thisWidth + 'px; overflow: hidden; background-color: rgb(220, 220, 220);">';

        // A hidden div containing setting, added width, height, overflow for MSIE7
        s += '<div id="player' + randomnumber + '-config" style="display: none; visibility: hidden; width: 0px; height: 0px; overflow: hidden;">' ;    
        s += 'url='+url+' width='+thisWidth+' height='+thisHeight+' align='+this.align+' loop='+(GetE('chkLoop').checked? '1' : '0')+' play='+(GetE('chkAutoplay').checked? 'true':'false');    
        s += '</div>';
        
	s+= '<embed ' ;
	s += this.createAttribute( 'controller', 'true') ;
	s += this.createAttribute( 'pluginspage', 'http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash' ) ;
	s += this.createAttribute( 'type', 'application/x-shockwave-flash');
	s += this.createAttribute( 'src', GetE('txtFlashURL').value) ;
	s += this.createAttribute( 'quality', 'high' ) ;
	s += this.createAttribute( 'scale', 'showall' ) ;
	s += this.createAttribute( 'bgcolor', '') ;
	s += this.createAttribute( 'loop', (GetE('chkFlashLoop').checked?'true':'false'));
	s += this.createAttribute( 'play', (GetE('chkFlashAutoplay').checked?'true':'false'));

	if ( objectId ) {
		s += this.createAttribute( 'id', objectId ) ;
	}
	if ( objectId ) {
		s += this.createAttribute( 'id', objectId ) ;
	}		
        s += this.createAttribute('width', GetE('txtFlashWidth').value );		
        s += this.createAttribute('height', GetE('txtFlashHeight').value);	
        s += this.createAttribute('align', '');	
        s += this.createAttribute('vspace', '');	
        s += this.createAttribute('hspace', '');

	s += '></embed>' ;
        
        s += '</div>';
        s += '</div>';
	return s ;
} ;



var ePreview ;
function IsValidMedia(oMedia) {
    if (!oMedia) {
        return false ;
    }

    var url = oMedia.url ;
    var purl = oMedia.purl ;
    var width = oMedia.width ;
    var height = oMedia.height ;

    if ( url.length == 0)
    {
        return false ;
    }

    if ( isNaN( width ) )
    {
        return false ;
    }

    if ( parseInt( width, 10 ) <= 0 )
    {
        return false ;
    }

    if ( isNaN( height ) )
    {
        return false ;
    }

    if ( parseInt( height, 10 ) <= 0 )
    {
        return false ;
    }

    return true ;
}

function SetPreviewElement(previewEl) {
    ePreview = previewEl ;
    if (IsValidMedia(oMedia)) {
        UpdatePreview(theTab) ;
    }
}

function UpdatePreview(theTab) {
    if ( !ePreview )
    {
        return ;
    }

    while ( ePreview.firstChild )
    {
        ePreview.removeChild( ePreview.firstChild ) ;
    }

    if ( !oMedia )
    {
        var oMedia = new Media() ;
        UpdateMovie( oMedia ) ;
    }

    if ( !IsValidMedia( oMedia ) )
    {
        ePreview.innerHTML = '&nbsp;' ;
    }
    else
    {
        var max_width = 710 ;
        var max_height = 400 ;
        var new_size = FCK.ResizeToFit( oMedia.width, oMedia.height, max_width, max_height ) ;
        oMedia.width = new_size[0] ;
        oMedia.height = new_size[1] ;
        oMedia.play = false ;
       
        ePreview.innerHTML = oMedia.getVideoInnerHTML() ;        

        var margin_left = parseInt( ( max_width - oMedia.width ) / 2, 10 ) ;
        var margin_top = parseInt( ( max_height - oMedia.height ) / 2, 10 ) ;

        if ( ePreview.currentStyle )
        {
            // IE
            ePreview.style.marginLeft = margin_left ;
            ePreview.style.marginTop = margin_top ;
        }
        else
        {
            // Other browsers
            SetAttribute( ePreview, 'style', 'margin-left: ' + margin_left + 'px; margin-top: ' + margin_top + 'px;' ) ;
        }
    }
}

function ClearPreview() {
    if (!ePreview) {
        return ;
    }
    while (ePreview.firstChild) {
        ePreview.removeChild( ePreview.firstChild ) ;
    }
    ePreview.innerHTML = '&nbsp;' ;
}

function ClearVideo() {
    GetE( 'txtURL' ).value = '';
    GetE( 'txtWidth' ).value = 720;
    GetE( 'txtHeight' ).value = 450;
    GetE( 'chkLoop' ).checked = true;
    GetE( 'chkAutoplay' ).checked = true;
    GetE( 'chkDownload' ).checked = false;
    GetE( 'chkFullscreen' ).checked = true;
}

function ClearFlash() {
    GetE( 'txtFlashURL' ).value = '';
    GetE( 'txtFlashWidth' ).value = 720;
    GetE( 'txtFlashHeight' ).value = 450;
    GetE( 'chkFlashLoop' ).checked = true;
    GetE( 'chkFlashAutoplay' ).checked = true;
}

function ClearSocial() {
    GetE( 'txtSocialURL' ).value = '';
    GetE( 'txtSocialWidth' ).value = 425;
    GetE( 'txtSocialHeight' ).value = 344;
    GetE( 'radioLow' ).checked = true;
    GetE( 'radioHigh' ).checked = false;
}

function OnUploadCompleted(errorNumber, fileUrl, fileName, customMsg) {
    // Remove animation
    window.parent.Throbber.Hide() ;
    //GetE( 'divUpload' ).style.display  = '' ;
    switch (errorNumber) {
        case 0 :	// No errors
            alert( FCKLang['FileSuccessfullyUploaded'] ) ;
            break ;
        case 1 :	// Custom error
            alert( customMsg ) ;
            return ;
        case 101 :	// Custom warning
            alert( customMsg ) ;
            break ;
        case 201 :
            alert( FCKLang['FileRenamed'] + ' "' + fileName + '".' ) ;
            break ;
        case 202 :
            alert( FCKLang['InvalidFileType'] ) ;
            return ;
        case 203 :
            alert( FCKLang['SecurityError'] ) ;
            return ;
        case 500 :
            alert( FCKLang['ConnectorDisabled'] ) ;
            break ;
        default :
            alert( FCKLang['UploadError'] + errorNumber ) ;
            return ;
    }

    if (theTab == 'videofile') {
        OnDialogModeChange( 'single' ) ;
        sActualBrowser = 'videos' ;
        SetUrl(fileUrl) ;
        GetE('frmUpload').reset() ;
    } else if (theTab == 'flash') {
        sActualBrowser = 'flash' ;
        SetUrl( fileUrl ) ;
        GetE('frmFlashUpload').reset() ;
    }
}

var oUploadAllowedExtRegex = new RegExp( FCKConfig.VideoFilesUploadAllowedExtensions, 'i' ) ;
var oUploadDeniedExtRegex = new RegExp( FCKConfig.VideoFilesUploadDeniedExtensions, 'i' ) ;
var oFlashUploadAllowedExtRegex	= new RegExp( FCKConfig.FlashUploadAllowedExtensions, 'i' ) ;
var oFlashUploadDeniedExtRegex	= new RegExp( FCKConfig.FlashUploadDeniedExtensions, 'i' ) ;

function CheckUpload() {
    
    var sFile = theTab == 'videofile'?GetE('txtUploadFile').value:GetE('txtFlashUploadFile').value;    
    if ( sFile.length == 0 ) {
        alert( FCKLang['UploadSelectFileFirst'] ) ;
        return false ;
    }
    if (theTab == 'videofile') {
        if ((FCKConfig.VideoFilesUploadAllowedExtensions.length > 0 && !oUploadAllowedExtRegex.test(sFile)) ||
            (FCKConfig.VideoFilesUploadDeniedExtensions.length > 0 && oUploadDeniedExtRegex.test(sFile))) {
            OnUploadCompleted(202);
            return false ;
        } 
    } else if (theTab == 'flash') {
        if ((FCKConfig.FlashUploadAllowedExtensions.length > 0 && !oFlashUploadAllowedExtRegex.test(sFile)) ||
            (FCKConfig.FlashUploadDeniedExtensions.length > 0 && oFlashUploadDeniedExtRegex.test(sFile))) {
            OnUploadCompleted(202);
            return false ;
        } 
    }    
    // Show animation.
    window.parent.Throbber.Show(100) ;    
    return true ;
}

Media.prototype.createParam = function(n, v) {
	return '<param name="' + n + '" value="' + v + '">' ;
}

Media.prototype.createAttribute = function( n, v) {
	return ' ' + n + '="' + v + '" ' ;
}

Media.prototype.isFlash = function () {
	return ( this.url.match( new RegExp( '.*\.swf$' ) ) != null ) ;
}

/* Social functions */
function UpdateEmbed(e) {
    var src = GetE('txtSocialURL').value;
    var video = parseVideoURL(src);   
    SetAttribute( e, 'type', 'application/x-shockwave-flash' ) ;
    SetAttribute( e, 'pluginspage', 'http://www.macromedia.com/go/getflashplayer' ) ;
    SetAttribute( e, 'allowfullscreen', 'true' ) ;
    if (video.provider == 'vimeo') {        
        SetAttribute( e, 'src', 'http://vimeo.com/moogaloop.swf?clip_id='+video.id);
    }
    else {        
        var YoutubeId = GetYoutubeId(src);
        if ( GetE( 'radioHigh' ).checked ) {
            SetAttribute( e, 'src', YoutubeSite + YoutubeId + HighQualityString ) ;
        }
        else {
                SetAttribute( e, 'src', YoutubeSite + YoutubeId + LowQualityString ) ;
        }
    }
    SetAttribute(e, 'width' , GetE('txtSocialWidth').value == '' ? 425 : GetE('txtSocialWidth').value);
    SetAttribute(e, 'height', GetE('txtSocialHeight').value == '' ? 344 : GetE('txtSocialHeight').value);    
}


function checkCode(code) {
    if (code.search( REG_SCRIPT ) != -1) {
        return false ;
    }

    if (code.search( REG_PROTOCOL ) != -1) {
        return false ;
    }

    if (code.search( REG_CALL_SCRIPT ) != -1) {
        return false ;
    }

    if (code.search( REG_EVENT ) != -1) {
        return false ;
    }

    if (code.search( REG_AUTH ) != -1) {
        return false ;
    }

    if (code.search( REG_NEWLINE ) != -1) {
        return false ;
    }
}

function GetOriginalYoutubeUrl (url) {
    var end = url.indexOf( '%' ) ;

    if (end > 0) {
            url = url.substring( 0, end ) ;
    }
    url = url.replace( '/v/', '/watch?v=' ) ;
    return url ;
}

function GetYoutubeId(url) {
    var YoutubeId = url.toString().slice( url.search( /\?v=/i ) + 3 ) ;
    var end = YoutubeId.indexOf( '%' ) ;
    if (end > 0) {
            YoutubeId = YoutubeId.substring( 0, end ) ;
    }
    return YoutubeId ;
}

function GetQuality (url) {
    var quality = 'low' ;
    var QualityString = url.toString().substr( url.search( '%' ) ) ;
    if (QualityString.length > LowQualityString.length) {
        quality = 'high' ;
    }
    return quality ;
}


function IsSocialValidMedia( e )
{
	if (!e) return false ;

	var src = GetE('txtSocialURL').value;
	var width = GetE('txtSocialWidth').value;
	var height = GetE('txtSocialHeight').value;

	if (src.length == 0) return false ;

	if (src.toString().toLowerCase().indexOf('youtube.com/v/%' ) != -1) return false ;

	if ( isNaN(width)) return false ;

	if (parseInt(width, 10) <= 0) return false ;

	if (isNaN( height)) return false ;

	if (parseInt(height, 10) <= 0) return false ;

	return true ;
}

function parseVideoURL(url) {
    url.match(/^http:\/\/(?:.*?)\.?(youtube|vimeo)\.com\/(watch\?[^#]*v=(\w+)|(\d+)).*$/);
    return {
        provider : RegExp.$1,
        id : RegExp.$1 == 'vimeo' ? RegExp.$2 : RegExp.$3
    }
}