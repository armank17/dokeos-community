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
      
    function loadValue( objectNode, embedNode, paramMap )
    {
            
            var CurrObj = CKEDITOR.dialog.getCurrent();
            var tab = CurrObj.definition.dialog._.currentTabId;
    
            switch (tab) {
                   case 'info': break;
            }
    }
        
                
    function commitValue( objectNode, embedNode, paramMap )
    {
                              
               var CurrObj = CKEDITOR.dialog.getCurrent();
               var tab = CurrObj.definition.dialog._.currentTabId;
               
               switch (tab) {
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

    CKEDITOR.dialog.add( 'streaming', function( editor )
    {        
                var me = this;
                //var Video
                var src_video;
                var lang = 'Video streaming';
                var JWplayer = CKEDITOR.plugins.get('streaming').path + 'jwplayer/jwplayer.flash.swf';
                var JWplayerJs = CKEDITOR.plugins.get('streaming').path + 'jwplayer/jwplayer.js';
                //var JWplayerSkin = CKEDITOR.plugins.get('streaming').path + "jwplayer/skin/facebook.zip";
                //var JWswfobject = CKEDITOR.plugins.get('streaming').path + 'jwplayer/swfobject.js';
                var oMedia = [] ;
                var is_new_videoplayer = true; 
  
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
                        var streaming = true;

                        var sExt = oMedia['fileUrl'].match( /\.(flv|mpg|mpeg|mp4|avi|wmv|mov|asf)$/i);
                        if ( sExt.length && sExt.length > 0 ) {
                            sExt = sExt[0].replace(".", "");
                        } else {
                            sExt = '';
                        }

                        var hdnVideoParams = 'fileUrl='+oMedia['fileUrl']+' previewUrl='+oMedia['previewUrl']+' width='+oMedia['width']+' height='+oMedia['height']+' cmbAlign='+oMedia['cmbAlign']+' cmbBuffer='+oMedia['cmbBuffer']+' autoplay='+oMedia['autoplay']+' loop='+oMedia['loop']+' fullscreen='+oMedia['fullscreen'];
                        
                        // A hidden div containing setting, added width, height, overflow for MSIE7
                        s += '<div id="player' + playerid + '-config" style="display: none; visibility: hidden; width: 0px; height: 0px; overflow: hidden;">'+$.base64.encode(hdnVideoParams)+'</div>' ;
                        /*s += '<div id="player' + playerid + '-config" style="display: none; visibility: hidden; width: 0px; height: 0px; overflow: hidden;">';                                                        
                        s += 'fileUrl='+oMedia['fileUrl']+' previewUrl='+oMedia['previewUrl']+' width='+oMedia['width']+' height='+oMedia['height']+' cmbAlign='+oMedia['cmbAlign']+' cmbBuffer='+oMedia['cmbBuffer']+' autoplay='+oMedia['autoplay']+' loop='+oMedia['loop']+' fullscreen='+oMedia['fullscreen'];
                        s += '</div>' ;*/
                        s += '<div id="test-' + playerid + '">';
                        s += '<div id="player' + playerid + '" class="thePlayer">';  

                        var exploded = oMedia['fileUrl'].split('/');       
                        var theFile  = exploded[exploded.length-1];
                        var client   = exploded[exploded.length-2];
                        var thePath  = oMedia['fileUrl'].replace("/"+theFile, "");
                        
                        var stream_server = 'dokeos.net';
                        var video_url_base = stream_server + ':1935/vod/_definst_/'+sExt+':'+client+'/'+theFile;
                        
                        var ua = navigator.userAgent.toLowerCase();
                        is_android = ua.indexOf("android") > -1;
                        if (is_android) {
                            android = ua.substring(ua.indexOf("android"));
                            android_version = android.substring(8, android.indexOf("."));
                        }
                        
                        if (is_android && android_version >= 4) {
                            s += '<video '; 
                            if (oMedia['autoplay'] == true) {
                                s += ' autoplay="autoplay"'; 
                            }
                            if (oMedia['fullscreen'] == false) { 
                                s += ' controls'; 
                            }
                            s += ' autobuffer'; 
                            s += ' src="http://' + video_url_base + '/playlist.m3u8"';  
                            s += ' width="' + oMedia['width'] + '"'; 
                            s += ' height="' + oMedia['height'] + '"';  
                            if (oMedia['previewUrl'] != "") { 
                                s += ' poster="'+oMedia['previewUrl']+'"';
                            }
                            s += '></video>'
                        }
                        else {
                           s += '<script src="' + JWplayerJs + '" type="text/javascript"></script>';
                           s += '<div id="player' + playerid + '-parent2">Loading the player ...</div>';    
                           s += '<script type="text/javascript">'; 
                           
                           s += 'jwplayer("player' + playerid + '-parent2").setup({';
                           s += '   playlist: [{';
                           if (oMedia['previewUrl'] != "") { 
                           s += '                   image: "'+oMedia['previewUrl']+'",'; 
                           }
                           s += '                   sources: [';  
                           s += '                               { file: "http://'+video_url_base+'/playlist.m3u8" },';
                           s += '                               { file: "rtmp://'+video_url_base+'" }';                            
                           //if ($.browser.mobile) {
                           //s += '                              ,{ file: "rtsp://'+video_url_base+'" }';
                           //}                           
                           s += '                   ],';
                           s += '                   title : "'+theFile+'"';
                           s += '             }],';                           
                           s += '   bufferlength: "'+oMedia['cmbBuffer']+'",';               
                           s += '   startparam: "starttime",';
                           if (oMedia['autoplay'] == true) {
                           s += '   autostart: true,';
                           }
                           s += '   height: '+oMedia['height']+',';
                           s += '   primary: "flash",';
                           s += '   width: '+oMedia['width'];
                           s += '});';
                           
                           s += '</script>';
                            
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
                                            
            title : 'Video streaming',
            minWidth : 550,
            minHeight : 220,                       
            onShow : function()
            {
                               
                             var fakeImage = this.getSelectedElement();
                             
                             if (fakeImage) {
                                 if (fakeImage.data( 'cke-real-element-type' ) == 'streaming') {
                                     this.selectPage('info');
                                 }                             
                             }
                             else {
                                 this.selectPage('info');
                             }

                             var CurrObj = CKEDITOR.dialog.getCurrent();
                             var tab = CurrObj.definition.dialog._.currentTabId;

                              switch(tab) {
                             
                              case 'info':
                                            // Clear previously saved elements.
                                            this.videoNode = configItem = null;
                                            if ( fakeImage && fakeImage.data( 'cke-real-element-type' ) && fakeImage.data( 'cke-real-element-type' ) == 'streaming' )
                                            {
                                                this.fakeImage = fakeImage;
                                                var videoNode = editor.restoreRealElement(fakeImage), videos = [];
                                                if (videoNode.getName() == 'cke:streaming' ) {
                                                    if ( videoNode.getId() && videoNode.getId().match(/^playervideo[0-9]*-parent$/)) {
                                                        var divList = videoNode.getElementsByTag('div');
                                                        if ( divList.count() > 0 ) {
                                                            configItem = divList.getItem(0);
                                                            if (configItem.getId().match( /^playervideo[0-9]*-config$/)) {
                                                                var hdnHiddenParams = $.base64.decode(configItem.getText());
                                                                var oC = hdnHiddenParams.split(' ');
                                                                //var oC = configItem.getText().split(' ');
                                                                for (var o = 0 ; o < oC.length ; o++) {
                                                                    var tmp = oC[o].split('=');
                                                                    videos[tmp[0]] = tmp[1];
                                                                }
                                                                is_new_videoplayer = false;
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
                            
                             }
            },
            onOk : function()
            {                               
                               var CurrObj = CKEDITOR.dialog.getCurrent();
                               var tab = CurrObj.definition.dialog._.currentTabId;
                               
                               switch(tab){
                                             
                                   case 'info':
                                       
                                            src_video = this.getValueOf( 'info', 'src0' );    
                                            if(src_video){
                                                // If there's no selected element create one. Otherwise, reuse it
                                                UpdateMovie(); 
                                                var randomnumber = generateId();
                                                var videoNode = null;
                                                if ( !this.fakeImage )
                                                {
                                                    videoNode = CKEDITOR.dom.element.createFromHtml( '<cke:streaming></cke:streaming>', editor.document );
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
                                                var newFakeImage = editor.createFakeElement( videoNode, 'cke_streaming', 'streaming', false );
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
                                //filebrowser : 'uploadButton2',
                                icon : CKEDITOR.plugins.get('streaming').path+ + 'images/icon.png',
                                padding : 0,
                                margin : 0,
                                elements :
                                [                              
                                    {
                                        type : 'vbox',                                      
                                        children:[
                                        {
                                            type : 'hbox',                              
                                            style: 'margin-left:25px;margin-bottom:20px;',
                                            widths: [ '80px', ''],
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
                                                },
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
                                                            url: editor.config.filebrowserStreamingBrowseUrl
                                                    }
                                                    
                                                    // v-align with the 'src' field.
                                                    // TODO: We need something better than a fixed size here.
                                                }
                                                    
                                            ]
                                                }
                                        ]
                                    },
                                    {
                                        type : 'hbox',
                                        widths: [ '15%', '15%', '15%', '15%'],
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
                                            //'default' : true,
                                            commit : commitValue,
                                            setup : loadVideoValue
                                        }
                                                                ]
                                    },
                                    {
                                        type : 'hbox',
                                        widths: [ '350px', ''],
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
                                    }
                                ]
                            }            
                    ]
            };
        });


        CKEDITOR.dialog.add( 'streamingmanagerDialog', function ( editor ) {

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