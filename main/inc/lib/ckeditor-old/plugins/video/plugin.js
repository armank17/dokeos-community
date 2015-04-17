/*
 * @file Video plugin for CKEditor
 * Copyright (C) 2011 Alfonso Martínez de Lizarrondo
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 */

CKEDITOR.plugins.add( 'video',
{
		lang: 'af,ar,bg,bn,bs,ca,cs,cy,da,de,el,en-au,en-ca,en-gb,en,eo,es,et,eu,fa,fi,fo,fr-ca,fr,gl,gu,he,hi,hr,hu,is,it,ja,ka,km,ko,ku,lt,lv,mk,mn,ms,nb,nl,no,pl,pt-br,pt,ro,ru,sk,sl,sr-latn,sr,sv,th,tr,ug,uk,vi,zh-cn,zh', // %REMOVE_LINE_CORE%
		icons: 'video', // %REMOVE_LINE_CORE%

	init : function( editor )
	{
		var lang = editor.lang.common;
                var pluginName = 'video';
		// Check for CKEditor 3.5
		if (typeof editor.element.data == 'undefined')
		{
			alert('The "video" plugin requires CKEditor 3.5 or newer');
			return;
		}

		CKEDITOR.dialog.add(pluginName, this.path + 'dialogs/video.js' );
		// Register the command.
		editor.addCommand( pluginName, new CKEDITOR.dialogCommand( pluginName ) );
                
		editor.ui.addButton( 'Video',
			{
				label : lang.toolbar,
				command : 'Video',
				icon : this.path + 'images/icon.png'
			} );

		CKEDITOR.addCss(
			'img.cke_jwvideo, img.cke_streaming' +
			'{' +
				'background-image: url(' + CKEDITOR.getUrl( this.path + 'images/placeholder.png' ) + '); ' +
				'background-position: center center;' +
				'background-repeat: no-repeat;' +
				'background-color:gray;'+
				'border: 1px solid #a9a9a9;' +
				'width: 80px;' +
				'height: 80px;' +
			'}');


			// If the "menu" plugin is loaded, register the menu items.
			if ( editor.addMenuItems )
			{
				editor.addMenuItems(
					{
						video :
						{
							label : lang.properties,
							command : 'Video',
							group : 'flash'
						}
					});
			}

			editor.on( 'doubleclick', function( evt )
				{
					var element = evt.data.element;
					if ( element.is( 'img' ) && element.data( 'cke-real-element-type' ) == 'video' )
						evt.data.dialog = 'video';
				});

			// If the "contextmenu" plugin is loaded, register the listeners.
			if ( editor.contextMenu )
			{
				editor.contextMenu.addListener( function( element, selection )
					{
						if ( element && element.is( 'img' ) && !element.isReadOnly()
								&& element.data( 'cke-real-element-type' ) == 'video' )
							return { video : CKEDITOR.TRISTATE_OFF };
					});
			}

		// Add special handling for these items
		CKEDITOR.dtd.$empty['cke:source']=1;
		CKEDITOR.dtd.$empty['source']=1;

		editor.lang.fakeobjects.video = lang.fakeObject;


	}, //Init

	afterInit: function( editor )
	{

		var dataProcessor = editor.dataProcessor,
			htmlFilter = dataProcessor && dataProcessor.htmlFilter,
			dataFilter = dataProcessor && dataProcessor.dataFilter;

		// dataFilter : conversion from html input to internal data
		dataFilter.addRules(
			{

			elements : {
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
                                                else if ( realElement.name == 'streaming')
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

			}
		);

	} // afterInit

} ); // plugins.add