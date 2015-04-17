/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

(function()
{
	var mindmapDialog = function( editor, dialogType )
	{
		// Load image preview.
		var IMAGE = 1,
			LINK = 2,
			PREVIEW = 4,
			CLEANUP = 8,
			regexGetSize = /^\s*(\d+)((px)|\%)?\s*$/i,
			regexGetSizeOrEmpty = /(^\s*(\d+)((px)|\%)?\s*$)|^$/i,
			pxLengthRegex = /^\d+px$/;


                function getBaseUrl() 
                {
                    return location.protocol + "//" + location.hostname + (location.port && ":" + location.port) + "/";                    
                }

		var onSizeChange = function()
		{
			var value = this.getValue(),	// This = input element.
				dialog = this.getDialog(),
				aMatch  =  value.match( regexGetSize );	// Check value

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

		// Custom commit dialog logic, where we're intended to give inline style
		// field (txtdlgGenStyle) higher priority to avoid overwriting styles contribute
		// by other fields.
		function commitContent()
		{
			var args = arguments;
			var inlineStyleField = this.getContentElement( 'advanced', 'txtdlgGenStyle' );
			inlineStyleField && inlineStyleField.commit.apply( inlineStyleField, args );

			this.foreach( function( widget )
			{
				if ( widget.commit &&  widget.id != 'txtdlgGenStyle' )
					widget.commit.apply( widget, args );
			});
		}

		// Avoid recursions.
		var incommit;

		// Synchronous field values to other impacted fields is required, e.g. border
		// size change should alter inline-style text as well.
		function commitInternally( targetFields )
		{
			if ( incommit )
				return;

			incommit = 1;

			var dialog = this.getDialog(),
				element = dialog.imageElement;
			if ( element )
			{
				// Commit this field and broadcast to target fields.
				this.commit( IMAGE, element );

				targetFields = [].concat( targetFields );
				var length = targetFields.length,
					field;
				for ( var i = 0; i < length; i++ )
				{
					field = dialog.getContentElement.apply( dialog, targetFields[ i ].split( ':' ) );
					// May cause recursion.
					field && field.setup( IMAGE, element );
				}
			}

			incommit = 0;
		}

		var resetSize = function( dialog )
		{
			var oImageOriginal = dialog.originalElement;
			if ( oImageOriginal.data )
			{
				var widthField = dialog.getContentElement( 'info', 'txtWidth' ),
					heightField = dialog.getContentElement( 'info', 'txtHeight' );
				widthField && widthField.setValue( oImageOriginal.data('widthImage') );
				heightField && heightField.setValue( oImageOriginal.data('heightImage') );
			}
		};
                
                var setupBrowseIframe = function(type, element) {
                   
                    var element = editor.getSelection().getSelectedElement();
                    var url;

                    if (element && element.is('img')) {                                               
                        var src = element.getAttribute("src");            
                        if (src) {
                            //var dirname = src.replace(/\\/g,'/').replace(/\/[^\/]*$/, '');
                            var ts = new Date().getTime();
                            url = getBaseUrl() + editor.config.filebrowserMindmapsBrowseUrl+'&path='+encodeURIComponent(src)+'&ts='+ts;                            
                        }   
                    }
                    else {
                        var ts = new Date().getTime();
                        url = getBaseUrl() + editor.config.filebrowserMindmapsBrowseUrl+'&ts='+ts;
                    }

                    if ($("#kcfinder_mindmaps_iframe").length > 0) {   
                        $("#kcfinder_mindmaps_iframe").attr("src", url); 
                    }
                };

                
                function browseIframe(editor) 
                {                   
                    var url = getBaseUrl() +editor.config.filebrowserMindmapsBrowseUrl;
                    return '<div style="width:100%;height:342px;"><iframe name="kcfinder_mindmaps_iframe" src="'+url+'" id="kcfinder_mindmaps_iframe" frameborder="0" width="100%" height="342px" style="width:100%;height:342px;" marginwidth="0" marginheight="0" scrolling="no"></iframe></div>';
                }
                
                
                var setupDimension = function( type, element )
		{
                    if ( type != IMAGE ) return;
                    
                    function checkDimension( size, defaultValue )
                    {
                            var aMatch  =  size.match( regexGetSize );
                            if ( aMatch )
                            {
                                    if ( aMatch[2] == '%' )				// % is allowed.
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

		return {
			title : editor.lang.image[ dialogType == 'mindmaps' ? 'title' : 'titleButton' ],
			minWidth : 850,
			minHeight : 450,
			onShow : function()
			{
				this.imageElement = false;
				this.linkElement = false;

				// Default: create a new element.
				this.imageEditMode = false;
				this.linkEditMode = false;

				this.lockRatio = true;
				this.userlockRatio = 0;
				this.dontResetSize = false;
				this.firstLoad = true;
				this.addLink = false;

				var editor = this.getParentEditor(),
					sel = this.getParentEditor().getSelection(),
					element = sel.getSelectedElement(),
					link = element && element.getAscendant( 'a' );

				if ( link )
				{
					this.linkElement = link;
					this.linkEditMode = true;

					// Look for Image element.
					var linkChildren = link.getChildren();
					if ( linkChildren.count() == 1 )			// 1 child.
					{
						var childTagName = linkChildren.getItem( 0 ).getName();
						if ( childTagName == 'img' || childTagName == 'input' )
						{
							this.imageElement = linkChildren.getItem( 0 );
							if ( this.imageElement.getName() == 'img' )
								this.imageEditMode = 'img';
							else if ( this.imageElement.getName() == 'input' )
								this.imageEditMode = 'input';
						}
					}
					// Fill out all fields.
					if ( dialogType == 'mindmaps' )
						this.setupContent( LINK, link );
				}

				if ( element && element.getName() == 'img' && !element.data( 'cke-realelement' )
					|| element && element.getName() == 'input' && element.getAttribute( 'type' ) == 'image' )
				{
					this.imageEditMode = element.getName();
					this.imageElement = element;
				}

				if ( this.imageEditMode )
				{
					// Use the original element as a buffer from  since we don't want
					// temporary changes to be committed, e.g. if the dialog is canceled.
					this.cleanImageElement = this.imageElement;
					this.imageElement = this.cleanImageElement.clone( true, true );

					// Fill out all fields.
					this.setupContent( IMAGE, this.imageElement );
				}
				else
					this.imageElement =  editor.document.createElement( 'img' );
			},
			onOk : function()
			{
				// Edit existing Image.
				if ( this.imageEditMode )
				{
					var imgTagName = this.imageEditMode;

					// Image dialog and Input element.
					if ( dialogType == 'mindmaps' && imgTagName == 'input' && confirm( editor.lang.image.button2Img ) )
					{
						// Replace INPUT-> IMG
						imgTagName = 'img';
						this.imageElement = editor.document.createElement( 'img' );
						this.imageElement.setAttribute( 'alt', '' );
						editor.insertElement( this.imageElement );
					}
					// ImageButton dialog and Image element.
					else if ( dialogType != 'mindmaps' && imgTagName == 'img' && confirm( editor.lang.image.img2Button ))
					{
						// Replace IMG -> INPUT
						imgTagName = 'input';
						this.imageElement = editor.document.createElement( 'input' );
						this.imageElement.setAttributes(
							{
								type : 'image',
								alt : ''
							}
						);
						editor.insertElement( this.imageElement );
					}
					else
					{
						// Restore the original element before all commits.
						this.imageElement = this.cleanImageElement;
						delete this.cleanImageElement;
					}
				}
				else	// Create a new image.
				{
					// Image dialog -> create IMG element.
					if ( dialogType == 'mindmaps' )
						this.imageElement = editor.document.createElement( 'img' );
					else
					{
						this.imageElement = editor.document.createElement( 'input' );
						this.imageElement.setAttribute ( 'type' ,'image' );
					}
					this.imageElement.setAttribute( 'alt', '' );
				}

				// Create a new link.
				if ( !this.linkEditMode )
					this.linkElement = editor.document.createElement( 'a' );

				// Set attributes.
				this.commitContent( IMAGE, this.imageElement );
				this.commitContent( LINK, this.linkElement );

				// Remove empty style attribute.
				if ( !this.imageElement.getAttribute( 'style' ) )
					this.imageElement.removeAttribute( 'style' );

				// Insert a new Image.
				if ( !this.imageEditMode )
				{
					if ( this.addLink )
					{
						//Insert a new Link.
						if ( !this.linkEditMode )
						{
							editor.insertElement( this.linkElement );
							this.linkElement.append( this.imageElement, false );
						}
						else	 //Link already exists, image not.
							editor.insertElement( this.imageElement );
					}
					else
						editor.insertElement( this.imageElement );
				}
				else		// Image already exists.
				{
					//Add a new link element.
					if ( !this.linkEditMode && this.addLink )
					{
						editor.insertElement( this.linkElement );
						this.imageElement.appendTo( this.linkElement );
					}
					//Remove Link, Image exists.
					else if ( this.linkEditMode && !this.addLink )
					{
						editor.getSelection().selectElement( this.linkElement );
						editor.insertElement( this.imageElement );
					}
				}
			},
			onLoad : function()
			{
				if ( dialogType != 'mindmaps' )
					this.hidePage( 'Link' );		//Hide Link tab.
				var doc = this._.element.getDocument();

				if ( this.getContentElement( 'info', 'ratioLock' ) )
				{
					this.addFocusable( doc.getById( btnResetSizeId ), 5 );
					this.addFocusable( doc.getById( btnLockSizesId ), 5 );
				}

				this.commitContent = commitContent;
                                
                                var ts = new Date().getTime();
                                var src = getBaseUrl() + editor.config.filebrowserMindmapsBrowseUrl+'&ts='+ts;
                                if ($("#kcfinder_mindmaps_iframe").length > 0) {
                                    $("#kcfinder_mindmaps_iframe").attr("src", src);
                                }
                                
			},			
                        onHide : function() {},			
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
                                                            [
                                                                    {
                                                                            id : 'txtUrl',
                                                                            type : 'text',
                                                                            label : editor.lang.common.url,
                                                                            required: true,                                                                            
                                                                            setup : function( type, element )
                                                                            {
                                                                                    if ( type == IMAGE )
                                                                                    {
                                                                                            var url = element.data( 'cke-saved-src' ) || element.getAttribute( 'src' );
                                                                                            var field = this;

                                                                                            this.getDialog().dontResetSize = true;

                                                                                            field.setValue( url );		// And call this.onChange()
                                                                                            // Manually set the initial value.(#4191)
                                                                                            field.setInitValue();
                                                                                    }
                                                                            },
                                                                            commit : function( type, element )
                                                                            {
                                                                                    if ( type == IMAGE && ( this.getValue() || this.isChanged() ) )
                                                                                    {
                                                                                            element.data( 'cke-saved-src', this.getValue() );
                                                                                            element.setAttribute( 'src', this.getValue() );
                                                                                    }
                                                                                    else if ( type == CLEANUP )
                                                                                    {
                                                                                            element.setAttribute( 'src', '' );	// If removeAttribute doesn't work.
                                                                                            element.removeAttribute( 'src' );
                                                                                    }
                                                                            },
                                                                            validate : CKEDITOR.dialog.validate.notEmpty( editor.lang.image.urlMissing )
                                                                    },
                                                                    {
                                                                        type : 'text',
                                                                        width: '40px',
                                                                        id : 'txtWidth',
                                                                        label : editor.lang.common.width,
                                                                        onKeyUp : onSizeChange,
                                                                        setup: setupDimension,
                                                                        onChange : function()
                                                                        {
                                                                                commitInternally.call( this, 'advanced:txtdlgGenStyle' );
                                                                        },
                                                                        validate : function()
                                                                        {
                                                                                var aMatch  =  this.getValue().match( regexGetSizeOrEmpty ),
                                                                                        isValid = !!( aMatch && parseInt( aMatch[1], 10 ) !== 0 );
                                                                                if ( !isValid )
                                                                                        alert( editor.lang.common.invalidWidth );
                                                                                return isValid;
                                                                        },
                                                                        commit : function( type, element, internalCommit )
                                                                        {
                                                                                var value = this.getValue();
                                                                                if ( type == IMAGE )
                                                                                {
                                                                                        if ( value )
                                                                                                element.setStyle( 'width', CKEDITOR.tools.cssLength( value ) );
                                                                                        else
                                                                                                element.removeStyle( 'width' );

                                                                                        !internalCommit && element.removeAttribute( 'width' );
                                                                                }                                                                               
                                                                                else if ( type == CLEANUP )
                                                                                {
                                                                                        element.removeAttribute( 'width' );
                                                                                        element.removeStyle( 'width' );
                                                                                }
                                                                        }
                                                                    },
                                                                
                                                                    {
                                                                        type : 'text',
                                                                        id : 'txtHeight',
                                                                        width: '40px',
                                                                        label : editor.lang.common.height,                                                                        
                                                                        onKeyUp : onSizeChange,           
                                                                        setup: setupDimension,
                                                                        validate : function()
                                                                        {
                                                                                var aMatch = this.getValue().match( regexGetSizeOrEmpty ),
                                                                                        isValid = !!( aMatch && parseInt( aMatch[1], 10 ) !== 0 );
                                                                                if ( !isValid )
                                                                                        alert( editor.lang.common.invalidHeight );
                                                                                return isValid;
                                                                        },
                                                                        commit : function( type, element, internalCommit )
                                                                        {
                                                                                var value = this.getValue();
                                                                                if ( type == IMAGE )
                                                                                {
                                                                                        if ( value )
                                                                                                element.setStyle( 'height', CKEDITOR.tools.cssLength( value ) );
                                                                                        else
                                                                                                element.removeStyle( 'height' );

                                                                                        !internalCommit && element.removeAttribute( 'height' );
                                                                                }                                                                               
                                                                                else if ( type == CLEANUP )
                                                                                {
                                                                                        element.removeAttribute( 'height' );
                                                                                        element.removeStyle( 'height' );
                                                                                }
                                                                        }
                                                                    },
                                                                
                                                                    {
                                                                        id : 'ratioLock',
                                                                        type : 'html',
                                                                        style : 'margin-top:10px;width:40px;height:40px;',
                                                                        onLoad : function()
                                                                        {
                                                                            // Activate Reset button
                                                                            var	resetButton = CKEDITOR.document.getById( btnResetSizeId ),
                                                                                    ratioButton = CKEDITOR.document.getById( btnLockSizesId );
                                                                            if ( resetButton )
                                                                            {
                                                                                    resetButton.on( 'click', function( evt )
                                                                                            {
                                                                                                    resetSize( this );
                                                                                                    evt.data && evt.data.preventDefault();
                                                                                            }, this.getDialog() );
                                                                                    resetButton.on( 'mouseover', function()
                                                                                            {
                                                                                                    this.addClass( 'cke_btn_over' );
                                                                                            }, resetButton );
                                                                                    resetButton.on( 'mouseout', function()
                                                                                            {
                                                                                                    this.removeClass( 'cke_btn_over' );
                                                                                            }, resetButton );
                                                                            }
                                                                            // Activate (Un)LockRatio button
                                                                            if ( ratioButton )
                                                                            {
                                                                                ratioButton.on( 'click', function(evt)
                                                                                {                                                                                    
                                                                                    if (!this.lockRatio)                                                                                     
                                                                                        this.lockRatio = true;                                                                                    
                                                                                    else 
                                                                                        this.lockRatio = false;
                                                                                                                                                                        
                                                                                    if (this.lockRatio)
                                                                                        ratioButton.removeClass( 'cke_btn_unlocked' );
                                                                                    else
                                                                                        ratioButton.addClass( 'cke_btn_unlocked' );

                                                                                    ratioButton.setAttribute( 'aria-checked', this.lockRatio );
                                                                                    
                                                                                }, this.getDialog());
                                                                                ratioButton.on( 'mouseover', function()
                                                                                {
                                                                                    this.addClass( 'cke_btn_over' );
                                                                                }, ratioButton );
                                                                                ratioButton.on( 'mouseout', function()
                                                                                {
                                                                                    this.removeClass( 'cke_btn_over' );
                                                                                }, ratioButton );
                                                                            }
                                                                        },
                                                                        html : '<div>'+
                                                                                '<a href="javascript:void(0)" tabindex="-1" title="' + editor.lang.image.lockRatio +
                                                                                '" class="cke_btn_locked" id="' + btnLockSizesId + '" role="checkbox"><span class="cke_icon"></span><span class="cke_label">' + editor.lang.image.lockRatio + '</span></a>' +
                                                                                '<a href="javascript:void(0)" tabindex="-1" title="' + editor.lang.image.resetSize +
                                                                                '" class="cke_btn_reset" id="' + btnResetSizeId + '" role="button"><span class="cke_label">' + editor.lang.image.resetSize + '</span></a>'+
                                                                                '</div>'
                                                                    }
                                                                
                                                            ]
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
                                                            [
                                                                    {
                                                                        id : 'txtAlt',
                                                                        type : 'text',
                                                                        label : editor.lang.image.alt,
                                                                        accessKey : 'T',
                                                                        'default' : '',
                                                                        onChange : function()
                                                                        {
                                                                                //updatePreview( this.getDialog() );
                                                                        },
                                                                        setup : function( type, element )
                                                                        {
                                                                                if ( type == IMAGE )
                                                                                        this.setValue( element.getAttribute( 'alt' ) );
                                                                        },
                                                                        commit : function( type, element )
                                                                        {
                                                                                if ( type == IMAGE )
                                                                                {
                                                                                        if ( this.getValue() || this.isChanged() )
                                                                                                element.setAttribute( 'alt', this.getValue() );
                                                                                }
                                                                                else if ( type == PREVIEW )
                                                                                {
                                                                                        element.setAttribute( 'alt', this.getValue() );
                                                                                }
                                                                                else if ( type == CLEANUP )
                                                                                {
                                                                                        element.removeAttribute( 'alt' );
                                                                                }
                                                                        }
                                                                    },                                                                   
                                                                    
                                                                    {
                                                                        type : 'text',
                                                                        id : 'txtHSpace',
                                                                        width: '60px',
                                                                        label : editor.lang.image.hSpace,
                                                                        'default' : '',
                                                                        onKeyUp : function()
                                                                        {
                                                                                //updatePreview( this.getDialog() );
                                                                        },
                                                                        onChange : function()
                                                                        {
                                                                                commitInternally.call( this, 'advanced:txtdlgGenStyle' );
                                                                        },
                                                                        validate : CKEDITOR.dialog.validate.integer( editor.lang.image.validateHSpace ),
                                                                        setup : function( type, element )
                                                                        {
                                                                                if ( type == IMAGE )
                                                                                {
                                                                                        var value,
                                                                                                marginLeftPx,
                                                                                                marginRightPx,
                                                                                                marginLeftStyle = element.getStyle( 'margin-left' ),
                                                                                                marginRightStyle = element.getStyle( 'margin-right' );

                                                                                        marginLeftStyle = marginLeftStyle && marginLeftStyle.match( pxLengthRegex );
                                                                                        marginRightStyle = marginRightStyle && marginRightStyle.match( pxLengthRegex );
                                                                                        marginLeftPx = parseInt( marginLeftStyle, 10 );
                                                                                        marginRightPx = parseInt( marginRightStyle, 10 );

                                                                                        value = ( marginLeftPx == marginRightPx ) && marginLeftPx;
                                                                                        isNaN( parseInt( value, 10 ) ) && ( value = element.getAttribute( 'hspace' ) );

                                                                                        this.setValue( value );
                                                                                }
                                                                        },
                                                                        commit : function( type, element, internalCommit )
                                                                        {
                                                                                var value = parseInt( this.getValue(), 10 );
                                                                                if ( type == IMAGE || type == PREVIEW )
                                                                                {
                                                                                        if ( !isNaN( value ) )
                                                                                        {
                                                                                                element.setStyle( 'margin-left', CKEDITOR.tools.cssLength( value ) );
                                                                                                element.setStyle( 'margin-right', CKEDITOR.tools.cssLength( value ) );
                                                                                        }
                                                                                        else if ( !value && this.isChanged( ) )
                                                                                        {
                                                                                                element.removeStyle( 'margin-left' );
                                                                                                element.removeStyle( 'margin-right' );
                                                                                        }

                                                                                        if ( !internalCommit && type == IMAGE )
                                                                                                element.removeAttribute( 'hspace' );
                                                                                }
                                                                                else if ( type == CLEANUP )
                                                                                {
                                                                                        element.removeAttribute( 'hspace' );
                                                                                        element.removeStyle( 'margin-left' );
                                                                                        element.removeStyle( 'margin-right' );
                                                                                }
                                                                        }
                                                                },
                                                                   
                                                                {
                                                                    type : 'text',
                                                                    id : 'txtVSpace',
                                                                    width : '60px',
                                                                    label : editor.lang.image.vSpace,
                                                                    'default' : '',
                                                                    onKeyUp : function()
                                                                    {
                                                                            //updatePreview( this.getDialog() );
                                                                    },
                                                                    onChange : function()
                                                                    {
                                                                            commitInternally.call( this, 'advanced:txtdlgGenStyle' );
                                                                    },
                                                                    validate : CKEDITOR.dialog.validate.integer( editor.lang.image.validateVSpace ),
                                                                    setup : function( type, element )
                                                                    {
                                                                            if ( type == IMAGE )
                                                                            {
                                                                                    var value,
                                                                                            marginTopPx,
                                                                                            marginBottomPx,
                                                                                            marginTopStyle = element.getStyle( 'margin-top' ),
                                                                                            marginBottomStyle = element.getStyle( 'margin-bottom' );

                                                                                    marginTopStyle = marginTopStyle && marginTopStyle.match( pxLengthRegex );
                                                                                    marginBottomStyle = marginBottomStyle && marginBottomStyle.match( pxLengthRegex );
                                                                                    marginTopPx = parseInt( marginTopStyle, 10 );
                                                                                    marginBottomPx = parseInt( marginBottomStyle, 10 );

                                                                                    value = ( marginTopPx == marginBottomPx ) && marginTopPx;
                                                                                    isNaN ( parseInt( value, 10 ) ) && ( value = element.getAttribute( 'vspace' ) );
                                                                                    this.setValue( value );
                                                                            }
                                                                    },
                                                                    commit : function( type, element, internalCommit )
                                                                    {
                                                                            var value = parseInt( this.getValue(), 10 );
                                                                            if ( type == IMAGE || type == PREVIEW )
                                                                            {
                                                                                    if ( !isNaN( value ) )
                                                                                    {
                                                                                            element.setStyle( 'margin-top', CKEDITOR.tools.cssLength( value ) );
                                                                                            element.setStyle( 'margin-bottom', CKEDITOR.tools.cssLength( value ) );
                                                                                    }
                                                                                    else if ( !value && this.isChanged( ) )
                                                                                    {
                                                                                            element.removeStyle( 'margin-top' );
                                                                                            element.removeStyle( 'margin-bottom' );
                                                                                    }

                                                                                    if ( !internalCommit && type == IMAGE )
                                                                                            element.removeAttribute( 'vspace' );
                                                                            }
                                                                            else if ( type == CLEANUP )
                                                                            {
                                                                                    element.removeAttribute( 'vspace' );
                                                                                    element.removeStyle( 'margin-top' );
                                                                                    element.removeStyle( 'margin-bottom' );
                                                                            }
                                                                    }
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
                                                                            [ 'Abs Bottom', 'absBottom'],
                                                                            [ 'Abs Middle', 'absMiddle'],
                                                                            [ 'Baseline' , 'baseline'],
                                                                            [ 'Text Top', 'text-top'],
                                                                            [ editor.lang.common.alignBottom , 'bottom'],
                                                                            [ editor.lang.common.alignMiddle , 'middle'],
                                                                            [ editor.lang.common.alignTop , 'top']
                                                                            
                                                                    ],
                                                                    onChange : function()
                                                                    {
                                                                            //updatePreview( this.getDialog() );
                                                                            commitInternally.call( this, 'advanced:txtdlgGenStyle' );
                                                                    },
                                                                    setup : function( type, element )
                                                                    {
                                                                            if ( type == IMAGE )
                                                                            {
                                                                                    var value = element.getStyle( 'float' );
                                                                                    switch( value )
                                                                                    {
                                                                                            // Ignore those unrelated values.
                                                                                            case 'inherit':
                                                                                            case 'none':
                                                                                                    value = '';
                                                                                    }

                                                                                    !value && ( value = ( element.getAttribute( 'align' ) || '' ).toLowerCase() );
                                                                                    this.setValue(value);
                                                                            }
                                                                    },
                                                                    commit : function( type, element, internalCommit )
                                                                    {
                                                                            var value = this.getValue();
                                                                            if ( type == IMAGE || type == PREVIEW )
                                                                            {
                                                                                    if ( value )
                                                                                            element.setAttribute('align', value);
                                                                                    else
                                                                                            element.removeAttribute('align');
                                                                                    
                                                                            }
                                                                            else if ( type == CLEANUP )
                                                                                     element.removeAttribute('align');
                                                                    }
                                                            }  
                                                                   
                                                                   
                                                                ]
                                                            }
                                                        ]
                                                    }
                                            ]
					}
                                        
                                        
                                    ]                                
			
		};
	};

         
	CKEDITOR.dialog.add( 'mindmaps', function( editor )
		{
			return mindmapDialog( editor, 'mindmaps' );
		});

	CKEDITOR.dialog.add( 'imagebutton', function( editor )
		{
			return mindmapDialog( editor, 'imagebutton' );
		});
})();
