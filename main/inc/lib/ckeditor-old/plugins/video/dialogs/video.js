CKEDITOR.dialog.add( 'video', function ( editor )
{
	var lang = editor.lang.video.videoplayer;
        var JWplayer = CKEDITOR.plugins.get('video').path + 'jwplayer/player.swf';
        var JWplayerJs = CKEDITOR.plugins.get('video').path + 'jwplayer/jwplayer.js';
        var JWplayerSkin = CKEDITOR.plugins.get('video').path + "jwplayer/skin/facebook.zip";
        var JWswfobject = CKEDITOR.plugins.get('video').path + 'jwplayer/swfobject.js';
        var oMedia = [] ;
        var is_new_videoplayer = true;        

	function commitValue( videoNode, extraStyles )
	{
            var value=this.getValue();
            if (!value && this.id=='id') value = generateId();
            //videoNode.setAttribute( this.id, value);
            //if (!value) return;
            switch( this.id )
            {
                case 'poster':
                        extraStyles.backgroundImage = 'url(' + value + ')';
                        break;
                case 'width':
                        extraStyles.width = value + 'px';
                        break;
                case 'height':
                        extraStyles.height = value + 'px';
                        break;
                case 'autoplay':
                case 'loop':
                case 'fullscreen':
                        value = value=='true'?true:false;
                        break;                    
            }
	}

	function commitSrc( videoNode, extraStyles, videos )
	{
            var match = this.id.match(/(\w+)(\d)/),
            id = match[1],
            number = parseInt(match[2], 10);
            var video = videos[number] || (videos[number]={});
            video[id] = this.getValue();
	}

	function loadValue(videoNode)
	{
            if ( videoNode ) {
                this.setValue( videoNode.getAttribute( this.id ) );
            }
            else {
                if ( this.id == 'id') this.setValue(generateId());
            }
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

	function generateId()
	{
		var now = new Date();
		return 'video' + now.getFullYear() + now.getMonth() + now.getDate() + now.getHours() + now.getMinutes() + now.getSeconds();
	}
        
        function UpdateMovie() {
                oMedia['fileUrl']    = CKEDITOR.dialog.getCurrent().getContentElement('info', 'src0').getValue();
                oMedia['fileUrlHtml']    = CKEDITOR.dialog.getCurrent().getContentElement('info', 'src1').getValue();
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
                CKEDITOR.dialog.getCurrent().getContentElement('info', 'src0').setValue(e['fileUrl']);
                CKEDITOR.dialog.getCurrent().getContentElement('info', 'src1').setValue(e['fileUrlHtml']);
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
            s += '<div id="test-' + playerid + '">';
            s += '<div id="player' + playerid + '" class="thePlayer">';  

            if (sExt == '.flv' || sExt == '.mp4' || sExt == '.mov') {                
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
                    s +=     'width: '+oMedia['width']+' ';
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
	return {
		title : editor.lang.video.dialogTitle,
		minWidth : 700,
		minHeight : 400,
		onShow : function()
		{
			// Clear previously saved elements.
			this.fakeImage = this.videoNode = configItem = null;
			var fakeImage = this.getSelectedElement();
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
			else
				this.setupContent( null, [] );
		},
		onOk : function()
		{                    
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
			this.commitContent( videoNode, extraStyles, videos );                                               
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
		},
		onHide : function()
		{
			if ( this.previewImage )
			{
				this.previewImage.removeListener( 'load', onImgLoadEvent );
				this.previewImage.removeListener( 'error', onImgLoadErrorEvent );
				this.previewImage.removeListener( 'abort', onImgLoadErrorEvent );
				this.previewImage.remove();
				this.previewImage = null;		// Dialog is closed.
			}
		},

		contents :
		[
			{
				id : 'info',
				elements :
				[
					{
						type : 'hbox',
						widths: [ '', '100px'],
                                                children : [
							{
								type : 'text',
								id : 'src0',
								label : editor.lang.video.sourceVideo,
                                                                required : true,
                                                                validate : function()
                                                                {
                                                                    
                                                                    if (this.getValue().length == 0) {
                                                                        alert(editor.lang.flash.validateSrc);
                                                                        return false;
                                                                    }
                                                                    
                                                                    if (!(/\.(flv|mpg|mpeg|mp4|avi|wmv|mov|asf)/i.test(this.getValue()))) {
                                                                        alert(editor.lang.video.invalidFileType) ;
                                                                        return false ;
                                                                    }
                                                                        
                                                                },
								commit : commitSrc,
								setup : loadSrc
							},
							{
								type : 'button',
								id : 'browse',
								hidden : 'true',
								style : 'display:inline-block;margin-top:10px;',
								filebrowser :
								{
									action : 'Browse',
									target: 'info:src0',
									url: editor.config.filebrowserVideoBrowseUrl || editor.config.filebrowserBrowseUrl
								},
								label : editor.lang.common.browseServer
							}
                                                ]
					},
                                        {
						type : 'hbox',
						widths: [ '', '100px'],
                                                children : [
							{
								type : 'text',
								id : 'src1',
								label : editor.lang.video.sourceVideoHtml,
                                                                required : true,
                                                                validate : function()
                                                                {
                                                                    if (this.getValue().length > 0 && !(/\.(ogg|ogv|mp4|webm)/i.test(this.getValue()))) {
                                                                        alert(editor.lang.video.invalidFileType) ;
                                                                        return false ;
                                                                    }                                                                        
                                                                },
								commit : commitSrc,
								setup : loadSrc
							},
							{
								type : 'button',
								id : 'browse',
								hidden : 'true',
								style : 'display:inline-block;margin-top:10px;',
								filebrowser :
								{
									action : 'Browse',
									target: 'info:src1',
									url: editor.config.filebrowserVideoBrowseUrl || editor.config.filebrowserBrowseUrl
								},
								label : editor.lang.common.browseServer
							}
                                                ]
					},
					{
						type : 'hbox',
						widths: [ '25%', '25%', '25%', '25%'],
						children : [
							{
								type : 'text',
								id : 'width',
								label : editor.lang.common.width,
								'default' : 400,
								validate : CKEDITOR.dialog.validate.notEmpty( editor.lang.common.widthRequired ),
								commit : commitValue,
								setup : loadValue
							},
							{
								type : 'text',
								id : 'height',
								label : editor.lang.common.height,
								'default' : 300,
								validate : CKEDITOR.dialog.validate.notEmpty(editor.lang.common.heightRequired ),
								commit : commitValue,
								setup : loadValue
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
						children : [
							{
								type : 'checkbox',
								id : 'autoplay',
								label : editor.lang.video.autoPlay,
								//'default' : true,
								commit : commitValue,
								setup : loadValue
							},
							{
								type : 'checkbox',
								id : 'loop',
								label : editor.lang.video.chkLoop,
								//'default' : true,
								commit : commitValue,
								setup : loadValue
							},
                                                        {
								type : 'checkbox',
								id : 'fullscreen',
								label : editor.lang.video.showtoolbars,
								//'default' : true,
								commit : commitValue,
								setup : loadValue
							}
                                                    ]
					},
					{
						type : 'hbox',
						widths: [ '', '10px'],
                                                children : [
							{
								type : 'text',
								id : 'poster',
								label : editor.lang.video.posterImage,
								commit : commitValue,
								setup : loadValue                                                                
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
} );