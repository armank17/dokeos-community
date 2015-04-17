/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

(function()
{
	var flashFilenameRegex = /\.swf(?:$|\?)/i;

	function isFlashEmbed( element )
	{
		var attributes = element.attributes;

		return ( attributes.type == 'application/x-shockwave-flash' || flashFilenameRegex.test( attributes.src || '' ) );
	}

	function createFakeElement( editor, realElement )
	{
		return editor.createFakeParserElement( realElement, 'cke_flash', 'flash', true );
	}
        
	CKEDITOR.plugins.add( 'streaming',
	{		
		icons: 'streaming', // %REMOVE_LINE_CORE%
		init : function( editor )
		{ 
			editor.addCommand( 'streaming', new CKEDITOR.dialogCommand('streaming'));

	        //The dialog window that will be called from the main dialog window
	        editor.addCommand('streamingmanager', new CKEDITOR.dialogCommand('streamingmanagerDialog'));

			editor.ui.addButton( 'Streaming',
				{
					label : 'Video streaming',
					command : 'streaming',
                                        icon : this.path + 'images/icon.png'
				});

                        CKEDITOR.scriptLoader.load( this.path + 'jquery.base64.js' );

			CKEDITOR.dialog.add( 'streamingDialog', this.path + 'dialogs/streaming.js' );

			CKEDITOR.dialog.add( 'streaming', this.path + 'dialogs/streaming.js' );

			CKEDITOR.addCss(
				'img.cke_flash' +
				'{' +
					'background-image: url(' + CKEDITOR.plugins.get('flash').path + 'images/placeholder.png'  + ') !important;' +
					'background-position: center center;' +
					'background-repeat: no-repeat;' +
					'border: 1px solid #a9a9a9;' +
					'width: 400px' +
					'height: 300px' +
				'}' +                                                        
                                'img.cke_streaming' +                            
                                '{' +                            
                                    'background-color: gray;' +
                                    'background-image: url(' + CKEDITOR.plugins.get('videoplayer').path + 'images/placeholder.png'  + ') !important;' +
                                    'background-position: center center;' +
                                    'background-repeat: no-repeat;' +
                                    'border: 1px solid #A9A9A9;' +
                                    'width: 400px !important;' +
				    'height: 300px !important;' +
                                '}'                            
				);

			// If the "menu" plugin is loaded, register the menu items.
			if ( editor.addMenuItems )
			{
				editor.addMenuItems({});
			}

			editor.on( 'doubleclick', function( evt )
				{
                                   
					var element = evt.data.element;
                                        if (element.is( 'img' ) && element.data('cke-real-element-type') == 'streaming') {
                                            evt.data.dialog = 'streaming';
                                        }
				});

			// If the "contextmenu" plugin is loaded, register the listeners.
			if ( editor.contextMenu )
			{
				editor.contextMenu.addListener( function( element, selection )
                                {
                                        if (element && element.is('img') && !element.isReadOnly() && element.data( 'cke-real-element-type' ) == 'streaming') {
                                                return { streaming : CKEDITOR.TRISTATE_OFF };                                            
                                        }
                                });
			}
		},

		afterInit : function( editor )
		{
			var dataProcessor = editor.dataProcessor,
				dataFilter = dataProcessor && dataProcessor.dataFilter;
			if ( dataFilter )
			{
				dataFilter.addRules(
					{
						elements :
						{
							'cke:object' : function( element )
							{
								var attributes = element.attributes,
									classId = attributes.classid && String( attributes.classid ).toLowerCase();

								if ( !classId && !isFlashEmbed( element ) )
								{
									// Look for the inner <embed>
									for ( var i = 0 ; i < element.children.length ; i++ )
									{
										if ( element.children[ i ].name == 'cke:embed' )
										{
											if ( !isFlashEmbed( element.children[ i ] ) )
												return null;

											return createFakeElement( editor, element );
										}
									}
									return null;
								}

								return createFakeElement( editor, element );
							},
							'cke:embed' : function( element )
							{
								if ( !isFlashEmbed( element ) )
									return null;

								return createFakeElement( editor, element );
							},
                                                        $ : function( realElement )
                                                        {
                                                            if ( realElement.name == 'streaming')
                                                            {
                                                                    realElement.name = 'cke:streaming';
                                                                    var fakeElement = editor.createFakeParserElement( realElement, 'cke_streaming', 'streaming', false ),
                                                                            fakeStyle = fakeElement.attributes.style || '';

                                                                    var width = realElement.attributes.width,
                                                                            height = realElement.attributes.height,
                                                                            poster = realElement.attributes.poster;                                                        

                                                                    if ( typeof width != 'undefined' )
                                                                            fakeStyle = fakeElement.attributes.style = fakeStyle + 'width:' + CKEDITOR.tools.cssLength( width ) + ';';

                                                                    if ( typeof height != 'undefined' )
                                                                            fakeStyle = fakeElement.attributes.style = fakeStyle + 'height:' + CKEDITOR.tools.cssLength( height ) + ';';

                                                                    if ( poster )
                                                                            fakeStyle = fakeElement.attributes.style = fakeStyle + 'background-image:url(' + poster + ');';

                                                                    return fakeElement;
                                                            }
                                                        }                                                       
						}
					},
					5);
			}
		},

		requires : [ 'fakeobjects' ]
	});
})();
