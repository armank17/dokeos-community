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

	CKEDITOR.plugins.add( 'videoplayer',
	{
		//lang: 'af,ar,bg,bn,bs,ca,cs,cy,da,de,el,en-au,en-ca,en-gb,en,eo,es,et,eu,fa,fi,fo,fr-ca,fr,gl,gu,he,hi,hr,hu,is,it,ja,ka,km,ko,ku,lt,lv,mk,mn,ms,nb,nl,no,pl,pt-br,pt,ro,ru,sk,sl,sr-latn,sr,sv,th,tr,ug,uk,vi,zh-cn,zh', // %REMOVE_LINE_CORE%
		icons: 'videoplayer', // %REMOVE_LINE_CORE%
		init : function( editor )
		{ 
			editor.addCommand( 'videoplayer', new CKEDITOR.dialogCommand( 'videoplayer' ) );

	        //The dialog window that will be called from the main dialog window
	        editor.addCommand('videomanager', new CKEDITOR.dialogCommand('videomanagerDialog'));

			editor.ui.addButton( 'VideoPlayer',
				{
					label : editor.lang.video.videoplayer,
					command : 'videoplayer',
                                        icon : this.path + 'images/icon.png'
				});
			
			CKEDITOR.dialog.add( 'videomanagerDialog', this.path + 'dialogs/videoplayer.js' );
			CKEDITOR.dialog.add( 'videoplayer', this.path + 'dialogs/videoplayer.js' );
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
                                'img.cke_jwvideo' +                            
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
				editor.addMenuItems(
					{
						flash :
						{
							label : editor.lang.common.flash,
							command : 'videoplayer',
							group : 'videoplayer'
						}
					});
			}

			editor.on( 'doubleclick', function( evt )
				{
					var element = evt.data.element;
					if (element.is( 'img' ) && element.data('cke-real-element-type') == 'flash') {                                            
                                            evt.data.dialog = 'flash';
                                        }
                                        if (element.is( 'img' ) && element.data('cke-real-element-type') == 'video') {
                                            evt.data.dialog = 'videoplayer';
                                        }
                                        if (element.is( 'img' ) && element.data('cke-real-element-type') == 'iframe') {
                                            evt.data.dialog = 'iframe';
                                        }
				});

			// If the "contextmenu" plugin is loaded, register the listeners.
			if ( editor.contextMenu )
			{
				editor.contextMenu.addListener( function( element, selection )
                                {
                                        if (element && element.is('img') && !element.isReadOnly() && element.data( 'cke-real-element-type' ) == 'flash') {                                            
                                                return { flash : CKEDITOR.TRISTATE_OFF };                                            
                                                
                                        }
                                        if (element && element.is('img') && !element.isReadOnly() && element.data( 'cke-real-element-type' ) == 'video') {
                                                return { video : CKEDITOR.TRISTATE_OFF };                                            
                                        }
                                        if (element && element.is('img') && !element.isReadOnly() && element.data( 'cke-real-element-type' ) == 'iframe') {
                                                return { iframe : CKEDITOR.TRISTATE_OFF };                                                
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
                                                            if ( realElement.name == 'jwvideo')
                                                            {
                                                                    realElement.name = 'cke:jwvideo';
                                                                    var fakeElement = editor.createFakeParserElement( realElement, 'cke_jwvideo', 'video', false ),
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

CKEDITOR.tools.extend( CKEDITOR.config,
{
	/**
	 * Save as EMBED tag only. This tag is unrecommended.
	 * @type Boolean
	 * @default false
	 */
	flashEmbedTagOnly : false,

	/**
	 * Add EMBED tag as alternative: &lt;object&gt&lt;embed&gt&lt;/embed&gt&lt;/object&gt
	 * @type Boolean
	 * @default false
	 */
	flashAddEmbedTag : true,

	/**
	 * Use embedTagOnly and addEmbedTag values on edit.
	 * @type Boolean
	 * @default false
	 */
	flashConvertOnEdit : false
} );