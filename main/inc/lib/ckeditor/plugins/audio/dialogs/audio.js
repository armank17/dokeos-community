CKEDITOR.dialog.add( 'audio', function ( editor )
{
	var lang = editor.lang.common;
        //var lang = editor.lang.audio.audioname
        var JWplayer = CKEDITOR.plugins.get('audio').path + 'jwplayer/player.swf';
        var JWplayerJs = CKEDITOR.plugins.get('audio').path + 'jwplayer/jwplayer.js';
        var oMedia = [] ;

        function UpdateAudio() {
                oMedia['fileUrl']    = CKEDITOR.dialog.getCurrent().getContentElement('info', 'src0').getValue();
                oMedia['width']      = CKEDITOR.dialog.getCurrent().getContentElement('info', 'width').getValue();
                oMedia['height']     = CKEDITOR.dialog.getCurrent().getContentElement('info', 'height').getValue();
                oMedia['cmbAlign']   = CKEDITOR.dialog.getCurrent().getContentElement('info', 'cmbAlign').getValue();
                oMedia['autoplay']   = CKEDITOR.dialog.getCurrent().getContentElement('info', 'autoplay').getValue();
                oMedia['loop']   = CKEDITOR.dialog.getCurrent().getContentElement('info', 'loop').getValue();
        }

        function LoadSelection(e) { 
                CKEDITOR.dialog.getCurrent().getContentElement('info', 'src0').setValue(e['fileUrl']);
                CKEDITOR.dialog.getCurrent().getContentElement('info', 'width').setValue(e['width']);
                CKEDITOR.dialog.getCurrent().getContentElement('info', 'height').setValue(e['height']);
                
                e['autoplay'] = e['autoplay'] == 'true'?true:false;
                e['loop'] = e['loop'] == 'true'?true:false;                                
                
                CKEDITOR.dialog.getCurrent().getContentElement('info', 'cmbAlign').setValue(e['cmbAlign']);
                CKEDITOR.dialog.getCurrent().getContentElement('info', 'autoplay').setValue(e['autoplay']);
                CKEDITOR.dialog.getCurrent().getContentElement('info', 'loop').setValue(e['loop']);                
        }
        
        function getAudioInnerHTML(playerid) {
            var s = '' ;            

            // A hidden div containing setting, added width, height, overflow for MSIE7
            s += '<div id="player' + playerid + '-config" style="display: none; visibility: hidden; width: 0px; height: 0px; overflow: hidden;">' ;                            
            s += 'fileUrl='+oMedia['fileUrl']+' width='+oMedia['width']+' height='+oMedia['height']+' cmbAlign='+oMedia['cmbAlign']+' autoplay='+oMedia['autoplay']+' loop='+oMedia['loop'];
            s += '</div>' ;
            s += '<div id="test-' + playerid + '" style="border-style: none; margin-top:5px; margin-bottom:5px;">';
            s += '<div id="player' + playerid + '" class="theAudioPlayer">';  

            
            s += '<script src="' + JWplayerJs + '" type="text/javascript"></script>';
            s += '<div id="player' + playerid + '-parent2">Loading the player ...</div>';    
            s += '<script type="text/javascript">';   
            s += 'jwplayer("player' + playerid + '-parent2").setup({';
            s +=     'file: "'+oMedia['fileUrl']+'",';
            s +=     'height: '+oMedia['height']+',';
            s +=     'autostart: '+ (oMedia['autoplay'] == true? 'true' : 'false') +',';
            s +=     'repeat: '+ (oMedia['loop'] == true? 'true' : 'false') +',';
            s +=     'width: '+oMedia['width']+', ';
            s +=     'primary: "flash"';
            s += '});';  
            s += '</script>';                
            s += '</div>';    
            s += '</div>';                                            
            return s;
        }


	function commitValue( audioNode, extraStyles )
	{
		var value=this.getValue();

		if ( !value && this.id=='id' )
			value = generateId();

		audioNode.setAttribute( this.id, value);

		//if ( !value ) return;
		switch( this.id )
		{
			case 'width':
				extraStyles.width = value + 'px';
				break;
			case 'height':
				extraStyles.height = value + 'px';
				break;
                        case 'autoplay':
                        case 'loop':
                                value = value=='true'?true:false;
                                break; 
		}
	}

	function commitSrc( audioNode, extraStyles, audios )
	{
		var match = this.id.match(/(\w+)(\d)/),
			id = match[1],
			number = parseInt(match[2], 10);

		var audio = audios[number] || (audios[number]={});
		audio[id] = this.getValue();
	}

	function loadValue( audioNode )
	{
		if ( audioNode )
			this.setValue( audioNode.getAttribute( this.id ) );
		else
		{
			if ( this.id == 'id')
				this.setValue( generateId() );
		}
	}

	function loadSrc( audioNode, audios )
	{
		var match = this.id.match(/(\w+)(\d)/),
			id = match[1],
			number = parseInt(match[2], 10);

		var audio = audios[number];
		if (!audio)
			return;
		this.setValue( audio[ id ] );
	}

	function generateId()
	{
		var now = new Date();
		return 'audio' + now.getFullYear() + now.getMonth() + now.getDate() + now.getHours() + now.getMinutes() + now.getSeconds();
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
                        block.removeStyle('text-align');
                        block.setAttribute('align', alignment);
                    }
            }
            editor.focus();
            editor.forceNextSelectionCheck();
            selection.selectBookmarks( bookmarks );
        }

	return {
		//title : editor.lang.common.audio,
                title : editor.lang.audio.audioname,
		minWidth : 400,
		minHeight : 180,
		onShow : function()
		{
                    // Clear previously saved elements.
                    this.fakeImage = this.audioNode = configItem = null;
                    var fakeImage = this.getSelectedElement();
                    if ( fakeImage && fakeImage.data('cke-real-element-type') && fakeImage.data('cke-real-element-type') == 'audio' )
                    {
                        this.fakeImage = fakeImage;
                        var audioNode = editor.restoreRealElement(fakeImage), audios = [];
                        if (audioNode.getName() == 'cke:audioplayer' ) {
                            if ( audioNode.getId() && audioNode.getId().match(/^playeraudio[0-9]*-parent$/)) {
                                var divList = audioNode.getElementsByTag('div');
                                if ( divList.count() > 0 ) {
                                    configItem = divList.getItem(0);
                                    if (configItem.getId().match( /^playeraudio[0-9]*-config$/)) {
                                        var oC = configItem.getText().split(' ');
                                        for (var o = 0 ; o < oC.length ; o++) {
                                            var tmp = oC[o].split( '=' );
                                            audios[tmp[0]] = tmp[1];
                                        }                                             
                                    }
                                }                                            
                            }                                        
                        }
                        this.audioNode = audioNode;
                        this.setupContent(audioNode, audios);
                        LoadSelection(audios);
                    }
                    else
                            this.setupContent( null, [] );                                        
		},
		onOk : function()
		{
                        UpdateAudio();
                        // If there's no selected element create one. Otherwise, reuse it
                        var randomnumber = generateId();
			var audioNode = null;
			if ( !this.fakeImage )
			{
				audioNode = CKEDITOR.dom.element.createFromHtml( '<cke:audioplayer></cke:audioplayer>', editor.document );
                                audioNode.setAttributes(
                                {
                                        id : 'player'+randomnumber+'-parent',
                                        width: oMedia['width'],
                                        height: oMedia['height']
                                });
			}
			else
			{
                                this.audioNode.removeAttribute('width');
                                this.audioNode.removeAttribute('height');
                                this.audioNode.setAttributes(
                                {
                                        width: oMedia['width'],
                                        height: oMedia['height']
                                });
				audioNode = this.audioNode;
			}

			var extraStyles = {}, audios = [];
			this.commitContent( audioNode, extraStyles, audios );                                                
			var innerHtml = getAudioInnerHTML(randomnumber);
			
			audioNode.setHtml(innerHtml);

			// Refresh the fake image.
			var newFakeImage = editor.createFakeElement( audioNode, 'cke_audioplayer', 'audio', false );
			newFakeImage.setStyles( extraStyles );
			if ( this.fakeImage )
			{
				newFakeImage.replace( this.fakeImage );
				editor.getSelection().selectElement(newFakeImage);
			}
			else
				editor.insertElement( newFakeImage );

                         // alignment
                         setCustomAlign(editor, oMedia['cmbAlign']);
                    
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
								label : '',
                                                                required : true,
                                                                validate : function()
                                                                {                                                                    
                                                                    if (this.getValue().length == 0) {
                                                                        alert(editor.lang.allMedias.validateSrc);
                                                                        return false;
                                                                    }
                                                                    
                                                                    if (!(/\.(mp3|oga)/i.test(this.getValue()))) {
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
									url: editor.config.filebrowserAudioBrowseUrl || editor.config.filebrowserBrowseUrl
								},
								label : editor.lang.common.browseServer
							}]
					},
					{
						type : 'hbox',
						widths: [ '33%', '33%', ''],
						children : [
							{
								type : 'text',
								id : 'width',
								label : editor.lang.common.width,
								'default' : 400,
								validate : CKEDITOR.dialog.validate.notEmpty( lang.widthRequired ),
								commit : commitValue,
								setup : loadValue
							},
							{
								type : 'text',
								id : 'height',
								label : editor.lang.common.height,
								'default' : 24,
								validate : CKEDITOR.dialog.validate.notEmpty(lang.heightRequired ),
								commit : commitValue,
								setup : loadValue
							},
                                                        {
                                                            id : 'cmbAlign',
                                                            type : 'select',
                                                            widths : ['35%','65%'],
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
						type : 'hbox',
						widths: [ '30%', ''],
						children : [
							{
								type : 'checkbox',
								id : 'autoplay',
								label : editor.lang.audio.autoPlay,
								commit : commitValue,
								setup : loadValue
							},
							{
								type : 'checkbox',
								id : 'loop',
								label : editor.lang.audio.chkLoop,
								commit : commitValue,
								setup : loadValue
							}
                                                    ]
					}
				]
			}

		]
	};
} );
