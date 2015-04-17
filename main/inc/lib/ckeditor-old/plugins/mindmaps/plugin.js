/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @file Image plugin
 */

CKEDITOR.plugins.add( 'mindmaps',
{
        lang: 'af,ar,bg,bn,bs,ca,cs,cy,da,de,el,en-au,en-ca,en-gb,en,eo,es,et,eu,fa,fi,fo,fr-ca,fr,gl,gu,he,hi,hr,hu,is,it,ja,ka,km,ko,ku,lt,lv,mk,mn,ms,nb,nl,no,pl,pt-br,pt,ro,ru,sk,sl,sr-latn,sr,sv,th,tr,ug,uk,vi,zh-cn,zh', // %REMOVE_LINE_CORE%
        icons: 'mindmaps', // %REMOVE_LINE_CORE%
	init : function( editor )
	{
		var pluginName = 'mindmaps';

		// Register the dialog.
		CKEDITOR.dialog.add( pluginName, this.path + 'dialogs/mindmaps.js' );

		// Register the command.
		editor.addCommand( pluginName, new CKEDITOR.dialogCommand( pluginName ) );

		// Register the toolbar button.
		editor.ui.addButton( 'Mindmaps',
			{
				label : editor.lang.mindmaps.mindmapsname,
				command : pluginName
			});

		editor.on( 'doubleclick', function( evt )
			{
                        var element = evt.data.element;                                                                
                        var src = element.getAttribute("src");
                         if (element.is('img')) {
                            if (src.search("mascot/") != -1) {
                                evt.data.dialog = 'mascotmanager';
                            }
                            else if (src.search("mindmaps/") != -1) {
                                evt.data.dialog = 'mindmaps';
                            }
                            else {
                                if (!element.data('cke-real-element-type')) {
                                    evt.data.dialog = 'imagemanager';
                                }
                            }
                       }
			});

		// If the "menu" plugin is loaded, register the menu items.
		if ( editor.addMenuItems )
		{
			editor.addMenuItems(
				{
					mindmaps :
					{
						label : 'Mindmaps',
						command : 'mindmaps',
						group : 'mindmaps'
					}
				});
		}

		// If the "contextmenu" plugin is loaded, register the listeners.
		if ( editor.contextMenu )
		{
			editor.contextMenu.addListener( function( element, selection )
				{
					if ( !element || !element.is( 'img' ) || element.data( 'cke-realelement' ) || element.isReadOnly() )
						return null;

					return { mindmaps : CKEDITOR.TRISTATE_OFF };
				});
		}
	}
} );

/**
 * Whether to remove links when emptying the link URL field in the image dialog.
 * @type Boolean
 * @default true
 * @example
 * config.image_removeLinkByEmptyURL = false;
 */
CKEDITOR.config.image_removeLinkByEmptyURL = true;

/**
 *  Padding text to set off the image in preview area.
 * @name CKEDITOR.config.image_previewText
 * @type String
 * @default "Lorem ipsum dolor..." placehoder text.
 * @example
 * config.image_previewText = CKEDITOR.tools.repeat( '___ ', 100 );
 */
