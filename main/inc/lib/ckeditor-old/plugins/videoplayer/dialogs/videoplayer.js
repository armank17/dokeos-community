/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

(function()
{
   
    /*
     * It is possible to set things in three different places.
     * 1. As attributes in the object tag.
     * 2. As param tags under the object tag.
     * 3. As attributes in the embed tag.
     * It is possible for a single attribute to be present in more than one place.
     * So let's define a mapping between a sementic attribute and its syntactic
     * equivalents.
     * Then we'll set and retrieve attribute values according to the mapping,
     * instead of having to check and set each syntactic attribute every time.
     *
     * Reference: http://kb.adobe.com/selfservice/viewContent.do?externalId=tn_12701
     */
    var ATTRTYPE_OBJECT = 1,
        ATTRTYPE_PARAM = 2,
        ATTRTYPE_EMBED = 4;
        var YoutubeSite = 'http://www.youtube.com/v/' ;
        var YoutubeSiteWatch = 'http://www.youtube.com/watch?v=';
        var VimeoSite   = 'http://vimeo.com/';
        var HighQualityString = '%26hl=en%26fs=1%26rel=0%26ap=%2526fmt=18' ;
        var LowQualityString = '%26hl=en%26fs=1%26rel=0' ;
        
    var attributesMap =
    {
               
        id : [ { type : ATTRTYPE_OBJECT, name :  'id' } ],
        classid : [ { type : ATTRTYPE_OBJECT, name : 'classid' } ],
        codebase : [ { type : ATTRTYPE_OBJECT, name : 'codebase'} ],
        pluginspage : [ { type : ATTRTYPE_EMBED, name : 'pluginspage' } ],
        src : [ { type : ATTRTYPE_PARAM, name : 'movie' }, { type : ATTRTYPE_EMBED, name : 'src' }, { type : ATTRTYPE_OBJECT, name :  'data' } ],
        name : [ { type : ATTRTYPE_EMBED, name : 'name' } ],
        align : [ { type : ATTRTYPE_OBJECT, name : 'align' } ],
        title : [ { type : ATTRTYPE_OBJECT, name : 'title' }, { type : ATTRTYPE_EMBED, name : 'title' } ],
        'class' : [ { type : ATTRTYPE_OBJECT, name : 'class' }, { type : ATTRTYPE_EMBED, name : 'class'} ],
        width : [ { type : ATTRTYPE_OBJECT, name : 'width' }, { type : ATTRTYPE_EMBED, name : 'width' } ],
        height : [ { type : ATTRTYPE_OBJECT, name : 'height' }, { type : ATTRTYPE_EMBED, name : 'height' } ],
        hSpace : [ { type : ATTRTYPE_OBJECT, name : 'hSpace' }, { type : ATTRTYPE_EMBED, name : 'hSpace' } ],
        vSpace : [ { type : ATTRTYPE_OBJECT, name : 'vSpace' }, { type : ATTRTYPE_EMBED, name : 'vSpace' } ],
        style : [ { type : ATTRTYPE_OBJECT, name : 'style' }, { type : ATTRTYPE_EMBED, name : 'style' } ],
        type : [ { type : ATTRTYPE_EMBED, name : 'type' } ]
    };

    var names = [ 'play', 'loop', 'menu', 'quality', 'align', 'scale', 'salign', 'wmode', 'bgcolor', 'base', 'flashvars', 'allowScriptAccess',
        'allowFullScreen' ];
    for ( var i = 0 ; i < names.length ; i++ )
        attributesMap[ names[i] ] = [ { type : ATTRTYPE_EMBED, name : names[i] }, { type : ATTRTYPE_PARAM, name : names[i] } ];
    names = [ 'allowFullScreen', 'play', 'loop', 'menu', 'width', 'height', 'align' ];
    for ( i = 0 ; i < names.length ; i++ )
        attributesMap[ names[i] ][0]['default'] = attributesMap[ names[i] ][1]['default'] = true;

    var defaultToPixel = CKEDITOR.tools.cssLength;
        
        
        function parseSocialURL(url) {
            url.match(/^http:\/\/(?:.*?)\.?(youtube|vimeo)\.com\/(watch\?[^#]*v=(\w+)|(\d+)).*$/);
            return {
                provider : RegExp.$1,
                id : RegExp.$1 == 'vimeo' ? RegExp.$2 : RegExp.$3
            }
        }
        
        function GetYoutubeId(url) {
            var YoutubeId = url.toString().slice( url.search( /\?v=/i ) + 3 ) ;
            var end = YoutubeId.indexOf( '%' ) ;
            if (end > 0) {
                    YoutubeId = YoutubeId.substring( 0, end ) ;
            }
            return YoutubeId ;
        }
        
        
    function loadValue( objectNode, embedNode, paramMap )
    {
            
            var CurrObj = CKEDITOR.dialog.getCurrent();
            var tab = CurrObj.definition.dialog._.currentTabId;
    
            switch (tab) {
                   
                   case 'Upload':
                      
                        var attributes = attributesMap[ this.id ];
                        if ( !attributes )
                                return;

                        var isCheckbox = ( this instanceof CKEDITOR.ui.dialog.checkbox );                       
                        for ( var i = 0 ; i < attributes.length ; i++ )
                        {
                                var attrDef = attributes[ i ];
                                switch ( attrDef.type )
                                {
                                        case ATTRTYPE_OBJECT:
                                                if ( !objectNode )
                                                        continue;
                                                if (objectNode.getAttribute(attrDef.name) !== null)
                                                {
                                                        var value = objectNode.getAttribute(attrDef.name);                                                        
                                                        if (attrDef.name == 'width') {
                                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'width').getValue();
                                                        }
                                                        else if (attrDef.name == 'height') {
                                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'height').getValue();
                                                        }
                                                        
                                                        if (isCheckbox) {
                                                                this.setValue( value.toLowerCase() == 'true' );
                                                        } 
                                                        else {
                                                                this.setValue( value );
                                                        }
                                                        return;
                                                }
                                                else if ( isCheckbox )
                                                        this.setValue( !!attrDef[ 'default' ] );
                                                break;
                                        case ATTRTYPE_PARAM:
                                                if ( !objectNode )
                                                        continue;
                                                if ( attrDef.name in paramMap )
                                                {
                                                        value = paramMap[ attrDef.name ];
                                                        if (attrDef.name == 'width') {
                                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'width').getValue();
                                                        }
                                                        else if (attrDef.name == 'height') {
                                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'height').getValue();
                                                        }
                                                        if ( isCheckbox ) {
                                                                this.setValue( value.toLowerCase() == 'true' );
                                                        } 
                                                        else {                                                            
                                                                this.setValue( value );                                                            
                                                        }
                                                        return;
                                                }
                                                else if ( isCheckbox )
                                                        this.setValue( !!attrDef[ 'default' ] );
                                                break;
                                        case ATTRTYPE_EMBED:
                                                if ( !embedNode )
                                                        continue;
                                                if ( embedNode.getAttribute( attrDef.name ) )
                                                {
                                                        value = embedNode.getAttribute( attrDef.name );                                             
                                                        if (attrDef.name == 'width') {
                                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'width').getValue();
                                                        }
                                                        else if (attrDef.name == 'height') {
                                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'height').getValue();
                                                        }
                                                        if ( isCheckbox ) {
                                                                this.setValue( value.toLowerCase() == 'true' );
                                                        } 
                                                        else {                                                            
                                                                this.setValue( value );
                                                        }
                                                                
                                                        return;
                                                }
                                                else if ( isCheckbox )
                                                        this.setValue( !!attrDef[ 'default' ] );
                                }
                        }
                       break;
                   
                   case 'info': break;
            }
    }
        
                
    function commitValue( objectNode, embedNode, paramMap )
    {
                              
               var CurrObj = CKEDITOR.dialog.getCurrent();
               var tab = CurrObj.definition.dialog._.currentTabId;
               
               switch (tab) {
                   
                   case 'Upload':
                        var attributes = attributesMap[this.id];
                        if (!attributes) return;

                        var isRemove = ( this.getValue() === '' ),
                        isCheckbox = ( this instanceof CKEDITOR.ui.dialog.checkbox );

                        for ( var i = 0 ; i < attributes.length ; i++ )
                        {
                            var attrDef = attributes[i];
                            switch ( attrDef.type )
                            {
                                case ATTRTYPE_OBJECT:
                                        // Avoid applying the data attribute when not needed (#7733)
                                        if ( !objectNode || ( attrDef.name == 'data' && embedNode && !objectNode.hasAttribute( 'data' ) ) )
                                                continue;                                            
                                        var value = this.getValue();
                                        if (attrDef.name == 'width') {                                                                
                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'width').getValue();
                                        }
                                        else if (attrDef.name == 'height') {
                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'height').getValue();
                                        }
                                        
                                        if ( isRemove || isCheckbox && value === attrDef[ 'default' ] )
                                                objectNode.removeAttribute( attrDef.name );
                                        else
                                                objectNode.setAttribute( attrDef.name, value );
                                        break;
                                case ATTRTYPE_PARAM:
                                        if ( !objectNode )
                                                continue;
                                        value = this.getValue();
                                        if (attrDef.name == 'width') {                                                                
                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'width').getValue();
                                        }
                                        else if (attrDef.name == 'height') {
                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'height').getValue();
                                        }
                                        if ( isRemove || isCheckbox && value === attrDef[ 'default' ] )
                                        {
                                                if ( attrDef.name in paramMap )
                                                        paramMap[ attrDef.name ].remove();
                                        }
                                        else
                                        {
                                                if ( attrDef.name in paramMap )
                                                        paramMap[ attrDef.name ].setAttribute( 'value', value );
                                                else
                                                {
                                                        var param = CKEDITOR.dom.element.createFromHtml( '<cke:param></cke:param>', objectNode.getDocument() );
                                                        param.setAttributes( { name : attrDef.name, value : value } );
                                                        if ( objectNode.getChildCount() < 1 )
                                                                param.appendTo( objectNode );
                                                        else
                                                                param.insertBefore( objectNode.getFirst() );
                                                }
                                        }
                                        break;
                                case ATTRTYPE_EMBED:
                                        if ( !embedNode )
                                                continue;
                                        value = this.getValue();
                                        if (attrDef.name == 'width') {                                                                
                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'width').getValue();
                                        }
                                        else if (attrDef.name == 'height') {
                                            value = CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'height').getValue();
                                        }
                                        if ( isRemove || isCheckbox && value === attrDef[ 'default' ])
                                                embedNode.removeAttribute( attrDef.name );
                                        else
                                                embedNode.setAttribute( attrDef.name, value );
                            }
                        }
                       break;
                  case 'info':   // video plugin tab
                        var value=this.getValue();
                        if (!value && this.id=='id') value = generateId();
                        //videoNode.setAttribute( this.id, value);
                        //if (!value) return;
                        switch( this.id )
                        {
                            case 'poster':
                                    embedNode.backgroundImage = 'url(' + value + ')';
                                    break;
                            case 'width':
                                    embedNode.width = value + 'px';
                                    break;
                            case 'height':
                                    embedNode.height = value + 'px';
                                    break;
                            case 'autoplay':
                            case 'loop':
                            case 'fullscreen':
                                    value = value=='true'?true:false;
                                    break;                    
                        }
                      break;
                   
               }
               
        
    }

    CKEDITOR.dialog.add( 'videoplayer', function( editor )
    {        
                var me = this;
                //var Video
                var src_video;
                var lang = editor.lang.video.videoplayer;
                var JWplayer = CKEDITOR.plugins.get('videoplayer').path + 'jwplayer/player.swf';
                var JWplayerJs = CKEDITOR.plugins.get('videoplayer').path + 'jwplayer/jwplayer.js';
                var JWplayerSkin = CKEDITOR.plugins.get('videoplayer').path + "jwplayer/skin/facebook.zip";
                var JWswfobject = CKEDITOR.plugins.get('videoplayer').path + 'jwplayer/swfobject.js';
                var oMedia = [] ;
                var is_new_videoplayer = true; 
                // get social description
                function getSocialDescription() 
                {
                    var html = '<table style="height:125px;width:600px;margin-top:7px;margin-left:12px;">'+
                                    '<tr>'+
                                        '<td>'+
                                            '<ul>'+
                                                '<li><span fcklang="DlgSocialURLTipContent1">'+editor.lang.video.goTo+' <a target="_blank" href="http://www.youtube.com/" style="background: none repeat scroll 0% 0% rgba(0, 0, 255, 0.2);">http://www.youtube.com/</a> '+editor.lang.video.or+ ' &nbsp;<a target="_blank" href="http://vimeo.com/">http://vimeo.com/</a>. '+editor.lang.video.navigateWithinTheSiteToFindYourVideo+'.</span></li>'+
                                                '<li><span fcklang="DlgSocialURLTipContent2">'+editor.lang.video.copyUrlvideoFromBrowser+'.</span></li>'+
                                                '<li><span fcklang="DlgSocialURLTipContent3"><span>'+editor.lang.video.theCopiedUrlShouldLookLike+' <a class="smarterwiki-linkify" href="http://www.youtube.com/watch?v=XXXX"> '+editor.lang.video.or+ ' <br /><a class="smarterwiki-linkify" href="http://vimeo.com/YYYY">http://vimeo.com/YYYY</a> (YYYY = vimeo id)</span></span></li>'+
                                            '</ul>'+
                                        '</td>'+
                                    '</tr>'+
                                '</table>';
                    return html;
                }
                
                // get icons block
                function getIconsBlock(page) {
                    var html = '<table width="100%" style="height:110px;">'+
                                '<tr>'+
                                    '<td width="58%" align="center">'+
                                        '<fieldset style="'+(page=='video'?'background-color:#bbb':'')+'">'+
                                            '<legend fcklang="DlgPlayerAllowedFomarts">'+editor.lang.video.videoFileFormatsAccepted+'</legend>'+
                                            '<img border="0" src="'+CKEDITOR.plugins.get('videoplayer').path+'dialogs/icons/mov64.png">&nbsp;'+
                                            '<img border="0" src="'+CKEDITOR.plugins.get('videoplayer').path+'dialogs/icons/mp4_64.png">&nbsp;'+
                                            '<img border="0" src="'+CKEDITOR.plugins.get('videoplayer').path+'dialogs/icons/flv64.png">&nbsp;'+
                                            '<img border="0" src="'+CKEDITOR.plugins.get('videoplayer').path+'dialogs/icons/h264.png">&nbsp;'+
                                            '<img border="0" src="'+CKEDITOR.plugins.get('videoplayer').path+'dialogs/icons/fullhd64.png">&nbsp;'+  
                                        '</fieldset>'+
                                    '</td>'+
                                    '<td width="20%" align="center">'+
                                        '<fieldset style="'+(page=='flash'?'background-color:#bbb':'')+'">'+
                                            '<legend>Flash</legend>'+
                                             '<img border="0" src="'+CKEDITOR.plugins.get('videoplayer').path+'dialogs/icons/swf64.png">&nbsp;'+
                                        '</fieldset>'+
                                    '</td>'+
                                    '<td align="center">'+
                                        '<fieldset style="'+(page=='social'?'background-color:#bbb':'')+'">'+
                                            '<legend>'+editor.lang.video.youtubeVimeo+'</legend>'+
                                            '<img border="0" src="'+CKEDITOR.plugins.get('videoplayer').path+'dialogs/icons/youtube64.png">&nbsp;'+
                                            '<img border="0" src="'+CKEDITOR.plugins.get('videoplayer').path+'dialogs/icons/vimeo64.png">&nbsp;'+
                                        '</fieldset>'+
                                    '</td>'+
                                '</tr>'+
                            '</table>';                
                    return html;                    
                }
                
                //function video
                function commitSrc( videoNode, extraStyles, videos )
                {               
                    var match = this.id.match(/(\w+)(\d)/),
                    id = match[1],
                    number = parseInt(match[2], 10);
                    var video = videos[number] || (videos[number]={});
                    video[id] = this.getValue();
                }
                function loadSrc( videoNode, videos )
                {
                    var match = this.id.match(/(\w+)(\d)/),
                    id = match[1],
                    number = parseInt(match[2], 10);
                    var video = videos[number];
                    if (!video) return;
                    this.setValue( video[ id ] );
                }                
                function loadVideoValue(videoNode)
                {
                    if ( videoNode ) {
                        this.setValue( videoNode.getAttribute( this.id ) );
                    }
                    else {
                        if ( this.id == 'id') this.setValue(generateId());
                    }
                }
                function generateId()
                {
                    var now = new Date();
                    return 'video' + now.getFullYear() + now.getMonth() + now.getDate() + now.getHours() + now.getMinutes() + now.getSeconds();
                }
                function UpdateMovie() {
                    oMedia['fileUrl']    = CKEDITOR.dialog.getCurrent().getContentElement('info', 'src0').getValue();
                    oMedia['fileUrlHtml']    = CKEDITOR.dialog.getCurrent().getContentElement('info', 'src0').getValue();
                    oMedia['previewUrl'] = CKEDITOR.dialog.getCurrent().getContentElement('info', 'poster').getValue();
                    oMedia['width']      = CKEDITOR.dialog.getCurrent().getContentElement('info', 'width').getValue();
                    oMedia['height']     = CKEDITOR.dialog.getCurrent().getContentElement('info', 'height').getValue();
                    oMedia['cmbAlign']   = CKEDITOR.dialog.getCurrent().getContentElement('info', 'cmbAlign').getValue();
                    oMedia['cmbBuffer']   = CKEDITOR.dialog.getCurrent().getContentElement('info', 'cmbBuffer').getValue();
                    oMedia['autoplay']   = CKEDITOR.dialog.getCurrent().getContentElement('info', 'autoplay').getValue();
                    oMedia['loop']   = CKEDITOR.dialog.getCurrent().getContentElement('info', 'loop').getValue();
                    oMedia['fullscreen']   = CKEDITOR.dialog.getCurrent().getContentElement('info', 'fullscreen').getValue();
                }
                function LoadSelection(e) {

                    //switch (currTab) {
                        //case 'info':
                            CKEDITOR.dialog.getCurrent().getContentElement('info', 'src0').setValue(e['fileUrl']);
                            CKEDITOR.dialog.getCurrent().getContentElement('info', 'src0').setValue(e['fileUrlHtml']);
                            CKEDITOR.dialog.getCurrent().getContentElement('info', 'poster').setValue(e['previewUrl']);
                            CKEDITOR.dialog.getCurrent().getContentElement('info', 'width').setValue(e['width']);
                            CKEDITOR.dialog.getCurrent().getContentElement('info', 'height').setValue(e['height']);
                            e['autoplay'] = e['autoplay'] == 'true'?true:false;
                            e['loop'] = e['loop'] == 'true'?true:false;
                            e['fullscreen'] = e['fullscreen'] == 'true'?true:false;
                            CKEDITOR.dialog.getCurrent().getContentElement('info', 'cmbAlign').setValue(e['cmbAlign']);
                            CKEDITOR.dialog.getCurrent().getContentElement('info', 'cmbBuffer').setValue(e['cmbBuffer']);
                            CKEDITOR.dialog.getCurrent().getContentElement('info', 'autoplay').setValue(e['autoplay']);
                            CKEDITOR.dialog.getCurrent().getContentElement('info', 'loop').setValue(e['loop']);
                            CKEDITOR.dialog.getCurrent().getContentElement('info', 'fullscreen').setValue(e['fullscreen']);
                      //      break;
                    //}
                }
                
                function getVideoInnerHTML(playerid) {                    
                        var s = '' ;            
                        var streaming = false;

                        var sExt = oMedia['fileUrl'].match( /\.(flv|mpg|mpeg|mp4|avi|wmv|mov|asf)$/i);
                        if ( sExt.length && sExt.length > 0 ) {
                            sExt = sExt[0];
                        } else {
                            sExt = '';
                        }

                        // A hidden div containing setting, added width, height, overflow for MSIE7
                        s += '<div id="player' + playerid + '-config" style="display: none; visibility: hidden; width: 0px; height: 0px; overflow: hidden;">' ;                            
                        s += 'fileUrl='+oMedia['fileUrl']+' fileUrlHtml='+oMedia['fileUrlHtml']+' previewUrl='+oMedia['previewUrl']+' width='+oMedia['width']+' height='+oMedia['height']+' cmbAlign='+oMedia['cmbAlign']+' cmbBuffer='+oMedia['cmbBuffer']+' autoplay='+oMedia['autoplay']+' loop='+oMedia['loop']+' fullscreen='+oMedia['fullscreen'];
                        s += '</div>' ;
                        s += '<div id="test-'+playerid+'">';
                        s += '<div id="player' + playerid + '" class="thePlayer">';  

                        if (sExt == '.flv' || sExt == '.mp4' || sExt == '.mov') {
                            //s += '<script src="' + JWplayerJs + '" type="text/javascript"></script>';
                            s += '<div id="player' + playerid + '-parent2">Loading the player ...</div>';    
                            s += '<script type="text/javascript">';

                            s += 'jwplayer("player' + playerid + '-parent2").setup({';
                            s +=     'file: "'+oMedia['fileUrl']+'",';
                            if (oMedia['previewUrl'] != "") { 
                            s +=     'image: "'+oMedia['previewUrl']+'",' 
                            }
                            s +=     'height: '+oMedia['height']+',';
                            s +=     'autostart: '+ (oMedia['autoplay'] == true? 'true' : 'false') +',';
                            s +=     'repeat: '+ (oMedia['loop'] == true? 'true' : 'false') +',';
                            s +=     'bufferlength: '+oMedia['cmbBuffer']+',';
                            s +=     'width: '+oMedia['width']+',';
                            s +=     'primary: "flash"';
                            s += '});';                    
                            var controls = oMedia['fullscreen'];
                            s += 'jwplayer("player' + playerid + '-parent2").setControls('+controls+');';                                   
                            s += '</script>';
                        } 
                        else {
                        // only embed for other video types
                            pluginspace = 'http://www.microsoft.com/Windows/MediaPlayer/' ;
                            codebase = 'http://www.microsoft.com/Windows/MediaPlayer/' ;
                            classid = 'classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95"' ;
                            sType = ( sExt == '.mpg' || sExt == '.mpeg' ) ? 'video/mpeg' :
                                                    (sExt == '.avi' || sExt == '.wmv' || sExt == '.asf' ) ? 'video/x-msvideo' :
                                                    (sExt == '.mov') ? 'video/quicktime' :
                                                    (sExt == '.mp4') ? 'video/mpeg4-generic' :
                                                    'video/x-msvideo' ;
                        s += '<embed type="' + sType + '" src="' + oMedia['fileUrl'] + '" ' +
                            'autosize="false" ' +
                            'fullscreen="true" ' +
                            'autostart="' + (oMedia['autoplay'] == true? 'true' : 'false')  + '" ' +
                            'loop="' + (oMedia['loop'] == true? 'true' : 'false') + '" ' +
                            'showcontrols="'+ (oMedia['fullscreen'] == true? 'true' : 'false') +'"' +
                            'showpositioncontrols="'+ (oMedia['fullscreen'] == true? 'true' : 'false') +'" ' +
                            'showtracker="true"' +
                            'showaudiocontrols="'+ (oMedia['fullscreen'] == true? 'true' : 'false') +'" ' +
                            'showgotobar="true" ' +
                            'showstatusbar="true" ' +
                            'pluginspace="' + pluginspace + '" ' +
                            'codebase="' + codebase + '"' ;
                        s += 'width="' + oMedia['width'] + '" height="' + oMedia['height'] + '"' ;
                        s += '></embed>' ;                        
                        }
                        s += '</div>';    
                        s += '</div>';                                              
                        return s;
                    }
                    // set align to selected element
                    function setCustomAlign(editor, alignment) {                                    
                        var selection = editor.getSelection(), enterMode = editor.config.enterMode;
                        if (!selection) return;
                        var bookmarks = selection.createBookmarks(), ranges = selection.getRanges(true);
                        var iterator, block;
                        var useComputedState = editor.config.useComputedState;
                        useComputedState = useComputedState === undefined || useComputedState;

                        for (var i = ranges.length - 1 ; i >= 0 ; i--) {
                                iterator = ranges[i].createIterator();
                                iterator.enlargeBr = enterMode != CKEDITOR.ENTER_BR;
                                while ((block = iterator.getNextParagraph(enterMode == CKEDITOR.ENTER_P ? 'p' : 'div'))) {
                                    block.removeAttribute( 'align' );
                                    block.removeStyle( 'text-align' );
                                    block.setAttribute('align', alignment);
                                }
                        }
                        editor.focus();
                        editor.forceNextSelectionCheck();
                        selection.selectBookmarks( bookmarks );
                    }
                 // To automatically get the dimensions of the poster image
                    var onImgLoadEvent = function()
                    {
                            // Image is ready.
                            var preview = this.previewImage;
                            preview.removeListener( 'load', onImgLoadEvent );
                            preview.removeListener( 'error', onImgLoadErrorEvent );
                            preview.removeListener( 'abort', onImgLoadErrorEvent );

                            this.setValueOf( 'info', 'width', preview.$.width );
                            this.setValueOf( 'info', 'height', preview.$.height );
                    };

                    var onImgLoadErrorEvent = function()
                    {
                            // Error. Image is not loaded.
                            var preview = this.previewImage;
                            preview.removeListener( 'load', onImgLoadEvent );
                            preview.removeListener( 'error', onImgLoadErrorEvent );
                            preview.removeListener( 'abort', onImgLoadErrorEvent );
                    };
                //end function video
               
               //var flash
                var src_flash;
        var makeObjectTag = !editor.config.flashEmbedTagOnly,
            makeEmbedTag = editor.config.flashAddEmbedTag || editor.config.flashEmbedTagOnly;
        var previewPreloader,
            previewAreaHtml = '<div>' + CKEDITOR.tools.htmlEncode( editor.lang.common.preview ) +'<br>' +
            '<div id="cke_FlashPreviewLoader' + CKEDITOR.tools.getNextNumber() + '" style="display:none"><div class="loading">&nbsp;</div></div>' +
            '<div id="cke_FlashPreviewBox' + CKEDITOR.tools.getNextNumber() + '" class="FlashPreviewBox" style="height:167px;width:305px;"></div></div>';

        return {
                                            
            title : editor.lang.video.videoplayer,
            minWidth : 900,
            minHeight : 345,                       
            onShow : function()
            {
                               
                             var fakeImage = this.getSelectedElement();
                             
                             if (fakeImage) {
                                 if (fakeImage.data('cke-real-element-type') == 'flash') {
                                    this.selectPage('Upload');
                                 }
                                 else if (fakeImage.data( 'cke-real-element-type' ) == 'video') {
                                     this.selectPage('info');
                                 }
                                 else {
                                     this.selectPage('iframe');
                                 }
                             }
                             else {
                                 this.selectPage('info');
                             }

                             var CurrObj = CKEDITOR.dialog.getCurrent();
                             var tab = CurrObj.definition.dialog._.currentTabId;

                              switch(tab) {
                              case 'Upload':                                          
                                            //is tab is Flash
                                            // Clear previously saved elements.
                                            this.objectNode = this.embedNode = null;
                                            previewPreloader = new CKEDITOR.dom.element( 'embed', editor.document );

                                            // Try to detect any embed or object tag that has Flash parameters.                                            
                                            if ( fakeImage && fakeImage.data( 'cke-real-element-type' ) && fakeImage.data( 'cke-real-element-type' ) == 'flash' )
                                            {
                                                    this.fakeImage = fakeImage;
                                                    var realElement = editor.restoreRealElement(fakeImage),
                                                    objectNode = null, embedNode = null, paramMap = {};
                                                    if ( realElement.getName() == 'cke:object' )
                                                    {
                                                            objectNode = realElement;
                                                            var embedList = objectNode.getElementsByTag( 'embed', 'cke' );
                                                            if ( embedList.count() > 0 )
                                                                    embedNode = embedList.getItem( 0 );
                                                            var paramList = objectNode.getElementsByTag( 'param', 'cke' );
                                                            for ( var i = 0, length = paramList.count() ; i < length ; i++ )
                                                            {
                                                                    var item = paramList.getItem( i ),
                                                                            name = item.getAttribute( 'name' ),
                                                                            value = item.getAttribute( 'value' );
                                                                            paramMap[ name ] = value;                                                                            
                                                            }                                                            
                                                            CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'width').setValue(objectNode.getAttribute("width"));
                                                            CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'height').setValue(objectNode.getAttribute("height"));
                                                            CKEDITOR.dialog.getCurrent().getContentElement('Upload', 'align').setValue(objectNode.getAttribute("align"));
                                                    }
                                                    else if ( realElement.getName() == 'cke:embed' )
                                                            embedNode = realElement;

                                                    this.objectNode = objectNode;
                                                    this.embedNode = embedNode;                                                                                                                                                            

                                                    this.setupContent( objectNode, embedNode, paramMap, fakeImage );
                                                    setCustomAlign(editor, this.getValueOf('Upload', 'align'));
                                            }                                   
                                            break;
                              case 'info':
                                            // Clear previously saved elements.
                                            this.videoNode = configItem = null;
                                            if ( fakeImage && fakeImage.data( 'cke-real-element-type' ) && fakeImage.data( 'cke-real-element-type' ) == 'video' )
                                            {
                                                this.fakeImage = fakeImage;
                                                var videoNode = editor.restoreRealElement(fakeImage), videos = [];
                                                if (videoNode.getName() == 'cke:jwvideo' ) {
                                                    if ( videoNode.getId() && videoNode.getId().match(/^playervideo[0-9]*-parent$/)) {
                                                        var divList = videoNode.getElementsByTag('div');
                                                        if ( divList.count() > 0 ) {
                                                            configItem = divList.getItem(0);
                                                            if (configItem.getId().match( /^playervideo[0-9]*-config$/)) {
                                                                var oC = configItem.getText().split(' ');
                                                                for (var o = 0 ; o < oC.length ; o++) {
                                                                    var tmp = oC[o].split( '=' );
                                                                    videos[tmp[0]] = tmp[1];
                                                                }
                                                                is_new_videoplayer = false ;                                                    
                                                            }
                                                        }                                            
                                                    }                                        
                                                }
                                                this.videoNode = videoNode;
                                                this.setupContent(videoNode, videos);
                                                LoadSelection(videos);
                                            }
                                            else {
                                                    this.setupContent( null, [] );
                                            }
                              break;
                            case 'iframe':
                                if (fakeImage) {
                                    var realElement = editor.restoreRealElement(fakeImage);
                                    if (realElement.getName() == 'iframe') {
                                        var url = realElement.getAttribute("src");
                                        var width = realElement.getAttribute("width");
                                        var height = realElement.getAttribute("height");
                                        var align = realElement.getAttribute("align");                                        
                                        align = align=='middle'?'center':align;
                                        
                                        url = url.replace("/v/", "/watch?v=")
                                        CKEDITOR.dialog.getCurrent().getContentElement('iframe', 'pageMediaEmbed').setValue(url);
                                        CKEDITOR.dialog.getCurrent().getContentElement('iframe', 'width').setValue(width);
                                        CKEDITOR.dialog.getCurrent().getContentElement('iframe', 'height').setValue(height);
                                        CKEDITOR.dialog.getCurrent().getContentElement('iframe', 'cmbAlign').setValue(align);
                                    }
                                }
                                break;
                             }
            },
            onOk : function()
            {                               
                               var CurrObj = CKEDITOR.dialog.getCurrent();
                               var tab = CurrObj.definition.dialog._.currentTabId;
                               
                               switch(tab){
                                   case 'Upload':
                                       src_flash = this.getValueOf( 'Upload', 'src' );
                                       if (src_flash){
                                           var         objectNode = null,
                                                    embedNode = null,
                                                    paramMap = null;

                                            if ( !this.fakeImage )
                                            {
                                                    if ( makeObjectTag )
                                                    {
                                                            objectNode = CKEDITOR.dom.element.createFromHtml( '<cke:object></cke:object>', editor.document );
                                                            var attributes = {
                                                                   classid : 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000',
                                                                   codebase : 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0',
                                                                   align: this.getValueOf('Upload', 'align')
                                                            };
                                                            objectNode.setAttributes( attributes );
                                                    }
                                                    if ( makeEmbedTag )
                                                    {
                                                            embedNode = CKEDITOR.dom.element.createFromHtml( '<cke:embed></cke:embed>', editor.document );
                                                            embedNode.setAttributes(
                                                                    {
                                                                            type : 'application/x-shockwave-flash',
                                                                            pluginspage : 'http://www.macromedia.com/go/getflashplayer',
                                                                            align: this.getValueOf('Upload', 'align')
                                                                    } );
                                                            if ( objectNode )
                                                                    embedNode.appendTo( objectNode );
                                                    }
                                            }
                                            else
                                            {
                                                    objectNode = this.objectNode;
                                                    embedNode = this.embedNode;
                                            }

                                            // Produce the paramMap if there's an object tag.
                                            if ( objectNode )
                                            {
                                                paramMap = {};
                                                var paramList = objectNode.getElementsByTag( 'param', 'cke' );
                                                for ( var i = 0, length = paramList.count() ; i < length ; i++ ) {
                                                    paramMap[ paramList.getItem( i ).getAttribute( 'name' ) ] = paramList.getItem( i );
                                                }
                                            }

                                            // A subset of the specified attributes/styles
                                            // should also be applied on the fake element to
                                            // have better visual effect. (#5240)
                                            var extraStyles = {}, extraAttributes = {};
                                            this.commitContent( objectNode, embedNode, paramMap, extraStyles, extraAttributes );                                            
                                            // Refresh the fake image.
                                            var newFakeImage = editor.createFakeElement( objectNode || embedNode, 'cke_flash', 'flash', true );
                                            newFakeImage.setAttributes( extraAttributes );
                                            newFakeImage.setStyles( extraStyles );
                                            if (this.fakeImage) {
                                                newFakeImage.replace( this.fakeImage );
                                                editor.getSelection().selectElement( newFakeImage );
                                            }
                                            else {
                                                editor.insertElement( newFakeImage );                                           
                                            }
                                            setCustomAlign(editor, this.getValueOf('Upload', 'align'));
                                         }
                                   break;                                   
                                   case 'iframe':  
                                        var url = this.getValueOf('iframe', 'pageMediaEmbed');    
                                        var thisWidth = this.getValueOf('iframe', 'width');
                                        var thisHeight = this.getValueOf('iframe', 'height');
                                        var thisAlign = this.getValueOf('iframe', 'cmbAlign');

                                        if (url.length == 0) {
                                            alert('The Url is required.') ;
                                            return false ;
                                        }
                                        if (!(/\.youtube\.com/i.test(url)) && !(/vimeo\.com/i.test(url))) {        
                                           alert('Invalid provider type.') ;
                                           return false ;
                                        }
                                        
                                        var video = parseSocialURL(url);
                                        if (video.provider == 'vimeo') {        
                                            src = 'http://vimeo.com/moogaloop.swf?clip_id='+video.id;
                                        }
                                        else {        
                                            var YoutubeId = GetYoutubeId(url);        
                                            src = YoutubeSite + YoutubeId + HighQualityString;                                            
                                        }
                                        var s = '<iframe width="'+thisWidth+'" align="'+thisAlign+'" height="'+thisHeight+'" src="'+src+'" frameborder="0" allowfullscreen></iframe>';                                        
                                        editor.insertHtml(s);
                                        setCustomAlign(editor, thisAlign);                                   
                                        break;                                   
                                   case 'info':
                                       
                                            src_video = this.getValueOf( 'info', 'src0' );                                              
                                            if (!(/\.(flv|mpg|mpeg|mp4|avi|wmv|mov|asf)/i.test(src_video))) {
                                                alert(editor.lang.video.invalidFileType) ;
                                                return false ;
                                            }
                                            if(src_video){
                                                // If there's no selected element create one. Otherwise, reuse it
                                                UpdateMovie(); 
                                                var randomnumber = generateId();
                                                var videoNode = null;
                                                if ( !this.fakeImage )
                                                {
                                                    videoNode = CKEDITOR.dom.element.createFromHtml( '<cke:jwvideo></cke:jwvideo>', editor.document );
                                                    videoNode.setAttributes(
                                                    {
                                                        id : 'player'+randomnumber+'-parent',
                                                        width: oMedia['width'],
                                                        height: oMedia['height'],
                                                        poster: oMedia['previewUrl']
                                                    });
                                                }
                                                else
                                                {
                                                    this.videoNode.removeAttribute('width');
                                                    this.videoNode.removeAttribute('height');
                                                    this.videoNode.removeAttribute('poster');
                                                    this.videoNode.setAttributes(
                                                    {
                                                            width: oMedia['width'],
                                                            height: oMedia['height'],
                                                            poster: oMedia['previewUrl']
                                                    });
                                                    videoNode = this.videoNode;
                                                }

                                                var extraStyles = {}, videos = [];
                                                this.commitContent(videoNode, extraStyles, videos);                                               
                                                var innerHtml = getVideoInnerHTML(randomnumber);

                                                videoNode.setHtml(innerHtml);                        

                                                // Refresh the fake image.
                                                var newFakeImage = editor.createFakeElement( videoNode, 'cke_jwvideo', 'video', false );
                                                newFakeImage.setStyles( extraStyles );
                                                if ( this.fakeImage )
                                                {
                                                    newFakeImage.replace( this.fakeImage );
                                                    editor.getSelection().selectElement(newFakeImage );
                                                }
                                                else 
                                                    editor.insertElement(newFakeImage);

                                                // alignment
                                                setCustomAlign(editor, oMedia['cmbAlign']);
                                            }
                                   break;


                                                }
                
                
            },
            onHide : function()
            {   
                            var CurrObj = CKEDITOR.dialog.getCurrent();
                            var tab = CurrObj.definition.dialog._.currentTabId;
                            switch(tab){
                               case 'Upload':
                                        if (this.preview) {
                                            this.preview.setHtml('');
                                        }
                                        break;
                               case 'info':
                                   if ( this.previewImage )
                                        {
                                                this.previewImage.removeListener( 'load', onImgLoadEvent );
                                                this.previewImage.removeListener( 'error', onImgLoadErrorEvent );
                                                this.previewImage.removeListener( 'abort', onImgLoadErrorEvent );
                                                this.previewImage.remove();
                                                this.previewImage = null;       // Dialog is closed.
                                        }
                                    this.videoNode = null;                                        
                                    break;    
                            }
                            
                            this.fakeImage = null;                           
            },                        
            contents : [
                                /* TAB VIDEO */
                                {
                                id : 'info',
                                label :  'Video',
                                hidden : true,
                                filebrowser : 'uploadButton2',
                                icon : CKEDITOR.plugins.get('videoplayer').path+ + 'images/icon.png',
                                padding : 0,
                                margin : 0,
                elements :
                [                              
                                    {
                                        type : 'vbox',                                      
                                        children:[
                                        {
                                            type : 'hbox',
                                                                         
                                            style: 'margin-left:25px;margin-bottom:0px;',
                                            widths: [ '110px', ''],
                                            children : 
                                            [

                                                {
                                                    type : 'text',
                                                    id : 'src0',
                                                    label : editor.lang.video.sourceVideo,
                                                    style : 'width:335px;',
                                                    required : true,
                                                    commit : commitSrc,
                                                    setup : loadSrc   
                                                }
                                                ,
                                                {
                                                    type : 'button',
                                                    id : 'browse',
                                                    hidden : 'false',
                                                    style : 'display:inline-block;margin-top:10px;',
                                                    label : editor.lang.common.browseServer ,
                                                    //filebrowser : 'info:src0'
                                                    filebrowser :
                                                    {
                                                            action : 'Browse',
                                                            target: 'info:src0',
                                                            url: editor.config.filebrowserVideoBrowseUrl
                                                    }
                                                    
                                                    // v-align with the 'src' field.
                                                    // TODO: We need something better than a fixed size here.
                                                }
                                                    
                                            ]
                                                },
                                                {
                                            type : 'hbox',
                                            padding :0,
                                            margin : 0,
                                            style: 'margin-left:25px;margin-bottom:10px;',
                                            widths: [ '350px', ''],
                                            children :

                                            [
                                            
                                                {
                                                        type : 'file',
                                                        size : 25,
                                                        id : 'upload2',
                                                        label : editor.lang.common.upload,
                                                        style: 'margin: 0;padding: 0;margin-top:0px;'                  
                                                },
                                                {
                                                        type : 'fileButton',
                                                        id : 'uploadButton2',
                                                        label : editor.lang.common.uploadSubmit,
                                                        style : 'display:inline-block;margin-top:15px!important;height: 17px;padding-top: 0;',                                                        
                                                        //filebrowser :'info:src0',
                                                        filebrowser :
                                                        {
                                                                action : 'QuickUpload',
                                                                target: 'info:src0',
                                                                url: editor.config.filebrowserVideoUploadUrl
                                                        },
                                                        'for' : [ 'info', 'upload2' ]

                                                }
                                            ]
                                        }
                                        ]
                                    }
                                    ,
                    {
                        type : 'hbox',
                        widths: [ '25%', '25%', '25%', '25%'],
                                                style: 'margin-left:25px;margin-bottom:20px;',
                        children : [
                            {
                                type : 'text',
                                id : 'width',
                                label : editor.lang.common.width,
                                'default' : 400,
                                validate : CKEDITOR.dialog.validate.notEmpty(editor.lang.common.widthRequired ),
                                commit : commitValue,
                                setup : loadVideoValue
                            },
                            {
                                type : 'text',
                                id : 'height',
                                label : editor.lang.common.height,
                                'default' : 300,
                                validate : CKEDITOR.dialog.validate.notEmpty(editor.lang.common.heightRequired ),
                                commit : commitValue,
                                setup : loadVideoValue
                            },
                                                        {
                                                            id : 'cmbAlign',
                                                            type : 'select',
                                                            widths : [ '35%','65%' ],
                                                            style : 'width:90px',
                                                            label : editor.lang.common.align,
                                                            'default' : '',
                                                            items :
                                                            [
                                                                    [ editor.lang.common.notSet , ''],
                                                                    [ editor.lang.common.alignLeft , 'left'],
                                                                    [ editor.lang.common.alignRight , 'right'],
                                                                    [ editor.lang.common.alignCenter , 'center']
                                                            ]   
                                                        },
                                                        {
                                                            id : 'cmbBuffer',
                                                            type : 'select',
                                                            widths : [ '35%','65%'],
                                                            style : 'width:90px',
                                                            label : editor.lang.video.bufferVideo,
                                                            'default' : 1,
                                                            items :
                                                            [
                                                                    [1,1],[2,2],[3,3],[4,4],[5,5],
                                                                    [6,6],[7,7],[8,8],[9,9],[10,10],
                                                                    [11,11],[12,12],[13,13],[14,14],[15,15],
                                                                    [16,16],[17,17],[18,18],[19,19],[20,20]
                                                            ]   
                                                        }
                                                    ]
                    },
                                        {
                        type : 'hbox',
                        widths: [ '25%', '25%', ''],
                                                style: 'margin-left:25px;margin-bottom:20px;',
                        children : [
                            {
                                type : 'checkbox',
                                id : 'autoplay',
                                label : editor.lang.video.autoPlay,
                                commit : commitValue,
                                setup : loadVideoValue
                            },
                            {
                                type : 'checkbox',
                                id : 'loop',
                                label : editor.lang.video.chkLoop,
                                //'default' : true,
                                commit : commitValue,
                                setup : loadVideoValue
                            },
                                                        {
                                type : 'checkbox',
                                id : 'fullscreen',
                                label : editor.lang.video.showtoolbars,
                                'default' : true,
                                commit : commitValue,
                                setup : loadVideoValue
                            }
                                                    ]
                    },
                    {
                        type : 'hbox',
                        widths: [ '560px', ''],
                                                style : 'margin-left:25px;',
                                                children : [
                            {
                                type : 'text',
                                id : 'poster',
                                label : editor.lang.video.posterImage,
                                commit : commitValue,
                                setup : loadVideoValue                                                                
                            },
                            {
                                type : 'button',
                                id : 'browse',
                                hidden : 'true',
                                style : 'display:inline-block;margin-top:10px;',
                                filebrowser :
                                {
                                    action : 'Browse',
                                    target: 'info:poster',
                                    url: editor.config.filebrowserImageBrowseUrl || editor.config.filebrowserBrowseUrl
                                },
                                label : editor.lang.common.browseServer
                            }]                      
                    },
                                        { 
                                            type : 'html', 
                                            id : 'iconsBlock', 
                                            label : 'Embed Media', 
                                            style : 'width:100%;height:342px;', 
                                            html : getIconsBlock('video')
                                        }
                ]
            },
                        /* TAB FLASH */
                                {
                    id : 'Upload',
                    hidden : true,
                                        filebrowser : 'uploadButton',
                    label : 'Flash',                                        
                    elements :
                    [
                                            {
                                                type : 'vbox',
                                                label: 'Test',
                                                width: '200px',
                                                style: 'height:221px;',
                                                children : [
                                                    {
                                                         type : 'hbox',
                                                         widths : [ '50%', '50%'],
                                                         children :
                                                            [
                                                                {
                                                                    
                                                                    type : 'vbox',
                                                                    children :
                                                                    [                                                                       
                                                                        {
                                                                                type : 'vbox',
                                                                                padding : 0,
                                                                                children :
                                                                                [
                                                                                    {
                                                                                        type : 'hbox',
                                                                                        widths : [ '320px', '130px' ],
                                                                                        align : 'right',
                                                                                        children :
                                                                                        [
                                                                                                {
                                                                                                        id : 'src',
                                                                                                        type : 'text',
                                                                                                        label : editor.lang.common.url,
                                                                                                        required : true,
                                                                                                        size: 45,
                                                                                                        //validate : CKEDITOR.dialog.validate.notEmpty( editor.lang.flash.validateSrc ),
                                                                                                        setup : loadValue,
                                                                                                        commit : commitValue,
                                                                                                        onLoad : function()
                                                                                                        {
                                                                                                                var dialog = this.getDialog(),
                                                                                                                updatePreview = function( src ){
                                                                                                                        // Query the preloader to figure out the url impacted by based href.                                                                                                                          
                                                                                                                        if (src) {
                                                                                                                        dialog.preview.setHtml( '<embed  height="100%" width="100%" src="'
                                                                                                                                    + CKEDITOR.tools.htmlEncode(src)
                                                                                                                                    + '" type="application/x-shockwave-flash"></embed>' );
                                                                                                        }
                                                                                                                        
                                                                                                                };
                                                                                                                // Preview element
                                                                                                                dialog.preview = dialog.getContentElement( 'Upload', 'preview' ).getElement().getChild( 3 );

                                                                                                                // Sync on inital value loaded.
                                                                                                                this.on( 'change', function( evt ){

                                                                                                                                if ( evt.data && evt.data.value ){
                                                                                                                                        updatePreview( evt.data.value );
                                                                                                                                }
                                                                                                                        } );
                                                                                                                // Sync when input value changed.
                                                                                                                this.getInputElement().on( 'change', function( evt ){

                                                                                                                        updatePreview( this.getValue() );
                                                                                                                }, this );
                                                                                                        }
                                                                                                },
                                                                                                {
                                                                                                        type : 'button',
                                                                                                        id : 'browse5',
                                                                                                        //filebrowser : 'Upload:src',
                                                                                                        filebrowser :
                                                                                                        {
                                                                                                                action : 'Browse',
                                                                                                                target: 'Upload:src',
                                                                                                                url: editor.config.filebrowserFlashBrowseUrl || editor.config.filebrowserBrowseUrl
                                                                                                        },
                                                                                                        hidden : 'false',
                                                                                                        // v-align with the 'src' field.
                                                                                                        // TODO: We need something better than a fixed size here.
                                                                                                        style : 'display:inline-block;margin-top:10px;',
                                                                                                        label : editor.lang.common.browseServer
                                                                                                }

                                                                                        ]
                                                                                    }
                                                                                ]
                                                                        },
                                                                         {
                                                                                type  : 'file',
                                                                                id    : 'upload',
                                                                                label : editor.lang.common.upload,
                                                                                size  : 20,
                                                                                style : 'width:325px;'
                                                                        },
                                                                        {
                                                                                type  : 'fileButton',
                                                                                id    : 'uploadButton',
                                                                                label : editor.lang.common.uploadSubmit,
//                                                                                filebrowser :
//                                                                                                        {
//                                                                                                                action : 'Browse',
//                                                                                                                target: 'Upload:src',
//                                                                                                                url: editor.config.filebrowserFlashBrowseUrl || editor.config.filebrowserBrowseUrl
//                                                                                                        }                             
                                                                                'for' : [ 'Upload', 'upload' ],
                                                                                filebrowser : 'Upload:src'
                                                                        },
                                                                        {
                                                                                type : 'hbox',
                                                                                widths : [ '25%', '25%', '50%'],
                                                                                children :
                                                                                [
                                                                                        {
                                                                                                type : 'text',
                                                                                                id : 'width',
                                                                                                style : 'width:95px',
                                                                                                'default' : 400,
                                                                                                label : editor.lang.common.width,
                                                                                                validate : CKEDITOR.dialog.validate.htmlLength( editor.lang.common.invalidHtmlLength.replace( '%1', editor.lang.common.width ) ),
                                                                                                setup : loadValue,
                                                                                                commit : commitValue
                                                                                        },
                                                                                        {
                                                                                                type : 'text',
                                                                                                id : 'height',
                                                                                                style : 'width:95px',
                                                                                                'default' : 300,
                                                                                                label : editor.lang.common.height,
                                                                                                validate : CKEDITOR.dialog.validate.htmlLength( editor.lang.common.invalidHtmlLength.replace( '%1', editor.lang.common.height ) ),
                                                                                                setup : loadValue,
                                                                                                commit : commitValue
                                                                                        },
                                                                                        {
                                                                                            id : 'align',
                                                                                            type : 'select',
                                                                                            style : 'width:90px',
                                                                                            label : editor.lang.common.align,
                                                                                            'default' : '',
                                                                                            items :
                                                                                            [
                                                                                                    [ editor.lang.common.notSet , ''],
                                                                                                    [ editor.lang.common.alignLeft , 'left'],
                                                                                                    [ editor.lang.common.alignRight , 'right'],
                                                                                                    [ editor.lang.common.alignCenter , 'center']
                                                                                            ]   
                                                                                        } 
                                                                                ]
                                                                        } 
                                                                    ]                                                                     
                                                                },
                                                                {   
                                                                    type : 'vbox',
                                                                    children :
                                                                    [
                                                                        {
                                                                            type : 'html',
                                                                            id : 'preview',
                                                                            style : 'width:95%;height:150px;',
                                                                            html : previewAreaHtml
                                                                        }
                                                                    ] 
                                                                }                                                    
                                                            ]
                                                    }                                                       
                                                ]
                                            },
                                            { 
                                                type : 'html', 
                                                id : 'iconsBlock', 
                                                label : 'Embed Media', 
                                                style : 'width:100%;height:342px;', 
                                                html : getIconsBlock('flash')
                                            }
                    ]
                },
                                
                                /* TAB EMBED */
                                {
                                 id : 'iframe',
                                 label : 'Youtube & Vimeo',
                                 expand : true,
                                 elements :
                                   [
                                        {
                                            type : 'vbox',
                                                    label: 'Test',
                                                    width: '200px',
                                                    style: 'width:700px',                                                    
                                                    children : [
                                                        {
                                                            type : 'text',
                                                            id : 'pageMediaEmbed',
                                                            label : 'URL',
                                                            style : 'width : 400px;'
                                                        },                                        
                                                        {
                                                            type : 'hbox',
                                                            widths : [ '50px', '50px', '*'],
                                                            children :
                                                            [
                                                                    {
                                                                            type : 'text',
                                                                            id : 'width',
                                                                            style : 'width:50px',
                                                                            'default' : 400,
                                                                            label : editor.lang.common.width,
                                                                            validate : CKEDITOR.dialog.validate.htmlLength( editor.lang.common.invalidHtmlLength.replace( '%1', editor.lang.common.width ) ),
                                                                            setup : loadValue,
                                                                            commit : commitValue
                                                                    },
                                                                    {
                                                                            type : 'text',
                                                                            id : 'height',
                                                                            style : 'width:50px',
                                                                            'default' : 300,
                                                                            label : editor.lang.common.height,
                                                                            validate : CKEDITOR.dialog.validate.htmlLength( editor.lang.common.invalidHtmlLength.replace( '%1', editor.lang.common.height ) ),
                                                                            setup : loadValue,
                                                                            commit : commitValue
                                                                    },
                                                                    {
                                                                        id : 'cmbAlign',
                                                                        type : 'select',
                                                                        style : 'width:90px',
                                                                        label : editor.lang.common.align,
                                                                        'default' : '',
                                                                        items :
                                                                        [
                                                                                [ editor.lang.common.notSet , ''],
                                                                                [ editor.lang.common.alignLeft , 'left'],
                                                                                [ editor.lang.common.alignRight , 'right'],
                                                                                [ editor.lang.common.alignCenter , 'center']
                                                                        ]   
                                                                    }                                              
                                                            ]
                                                        }, 
                                                        { 
                                                            type : 'html', 
                                                            id : 'descriptionBlock', 
                                                            label : 'Embed Media', 
                                                            style : 'width:100%;height:342px;', 
                                                            html : getSocialDescription()
                                                        },
                                                        { 
                                                            type : 'html', 
                                                            id : 'iconsBlock', 
                                                            label : 'Embed Media', 
                                                            style : 'width:100%;height:342px;', 
                                                            html : getIconsBlock('social')
                                                        }
                                                    ]
                                        }
                                   ]
                                }  // end iframe
                                
                    ]
        };
    } );


        CKEDITOR.dialog.add( 'videomanagerDialog', function ( editor ) {

                function browseIframe(editor) 
                {                   
                    var url = getBaseUrl() +editor.config.filebrowserVideoBrowseUrl;
                    return '<div style="width:100%;height:342px;"><iframe name="kcfinder_iframe" src="'+url+'" id="kcfinder_iframe" frameborder="0" width="100%" height="342px" style="width:100%;height:342px;" marginwidth="0" marginheight="0" scrolling="no"></iframe></div>';
                }

                function getBaseUrl() 
                {
                    return location.protocol + "//" + location.hostname + (location.port && ":" + location.port) + "/";                    
                }

                var setupDimension = function( type, element )
                {
                    if ( type != IMAGE ) return;
                    
                    function checkDimension( size, defaultValue )
                    {
                            var aMatch  =  size.match( regexGetSize );
                            if ( aMatch )
                            {
                                    if ( aMatch[2] == '%' )             // % is allowed.
                                    {
                                            aMatch[1] += '%';
                                    }
                                    return aMatch[1];
                            }
                            return defaultValue;
                    }
                    
                    var dialog = this.getDialog(),
                    value = '',
                    dimension = this.id == 'txtWidth' ? 'width' : 'height',
                    size = element.getAttribute( dimension );

                        if ( size )
                                value = checkDimension( size, value );
                        value = checkDimension( element.getStyle( dimension ), value );

                        this.setValue( value );
                };

                var previewPreloader;       
                var numbering = function( id )
                    {
                        return CKEDITOR.tools.getNextId() + '_' + id;
                    },
                    btnLockSizesId = numbering( 'btnLockSizes' ),
                    btnResetSizeId = numbering( 'btnResetSize' ),
                    imagePreviewLoaderId = numbering( 'ImagePreviewLoader' ),
                    imagePreviewBoxId = numbering( 'ImagePreviewBox' ),
                    previewLinkId = numbering( 'previewLink' ),
                    previewImageId = numbering( 'previewImage' );

                var setupBrowseIframe = function(type, element) {
                    var element = editor.getSelection().getSelectedElement();
                    var url;

                    if (element && element.is('img')) {                                               
                        var src = element.getAttribute("src");            
                        if (src) {
                            //var dirname = src.replace(/\\/g,'/').replace(/\/[^\/]*$/, '');
                            var ts = new Date().getTime();
                            url = getBaseUrl() + editor.config.filebrowserVideoBrowseUrl+'&path='+encodeURIComponent(src)+'&ts='+ts;                            
                        }   
                    }
                    else {
                        var ts = new Date().getTime();
                        url = getBaseUrl() + editor.config.filebrowserVideoBrowseUrl+'&ts='+ts;
                    }
                    if ($("#kcfinder_iframe").length > 0) {   
                        $("#kcfinder_iframe").attr("src", url); 
                    }
                };

                var onSizeChange = function()
                {
                    var value = this.getValue(),    // This = input element.
                        dialog = this.getDialog(),
                        aMatch  =  value.match( regexGetSize ); // Check value

                    // Only if ratio is locked
                    if ( dialog.lockRatio )
                    {
                        var oImageOriginal = dialog.originalElement;
                        if (oImageOriginal.data)
                        {
                            if ( this.id == 'txtHeight' )
                            {
                                if ( value && value != '0' )
                                    value = Math.round( oImageOriginal.data('widthImage') * ( value  / oImageOriginal.data('heightImage')));
                                if ( !isNaN( value ) )
                                    dialog.setValueOf( 'info', 'txtWidth', value );
                            }
                            else
                            {
                                if ( value && value != '0' )
                                    value = Math.round( oImageOriginal.data('heightImage') * (value  / oImageOriginal.data('widthImage')));
                                if (!isNaN( value ))
                                    dialog.setValueOf( 'info', 'txtHeight', value );
                            }
                        }
                    }
                };                

            return {
                title : 'video explorer',
                minWidth : 800,
                minHeight : 370,

            contents : [
                {               
                        id : 'info',
                        label : editor.lang.image.infoTab,
                        accessKey : 'I',
                        elements :
                        [
                            { 
                                type : 'html', 
                                id : 'pageMediaEmbed', 
                                label : 'Embed Media', 
                                height: '342px',
                                style : 'width:100%;height:342px;', 
                                html : browseIframe(editor),
                                setup: setupBrowseIframe
                            },
                            {
                                type : 'vbox', 
                                padding : 0,
                                children : [
                                        {
                                                type : 'hbox',
                                                widths : [ '60%', '5%', '5%', '20%' ],
                                                align : 'right',
                                                children :
                                                []
                                        }
                                ]
                            },                                        
                            {
                                type : 'vbox',
                                padding : 0,
                                children :
                                [
                                    {
                                        type : 'hbox',
                                        widths : [ '35%', '5%', '5%', '*' ],
                                        align : 'right',
                                        children :
                                        []
                                    }
                                ]
                            }
                       ]
                    }
                                        
                                        
                                    ],
                    onShow: function(){

                    },
                    onOk: function(){
                        //var parentEditor = this.getParentEditor( ) ;
                       /// var x = parentEditor.document.getBody().getName();
                       // var y = parentEditor.document.getContentElement();
                        //var element = $("#src0").attr("value", "dokeos");
                        //alert(element);
                        //var dialog = parentEditor.dialog(); 
                        //dialog.getContentElement( 'tabId', 'elementId' ).setValue( 'Example' );
                        //var t = parentEditor.config.filebrowserVideoBrowseUrl;
                         //var tab = parentEditor.getName() ;
                        //alert(tab);
                        //parent.editor.config.filebrowserVideoBrowseUrl || editor.config.filebrowserBrowseUrl
                        //alert(CKEDITOR.dialog.getCurrent().getContentElement('info', 'src0').getValue());
                    },
                    onLoad: function(){
                    }

            };
        });

   
})();