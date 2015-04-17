function configureHtmlOutput( ev )
{
   var editor = ev.editor,
      dataProcessor = editor.dataProcessor,
      htmlFilter = dataProcessor && dataProcessor.htmlFilter;

   // Out self closing tags the HTML4 way, like <br>.
   dataProcessor.writer.selfClosingEnd = '>';

   // Make output formatting behave similar to FCKeditor
   var dtd = CKEDITOR.dtd;
   for ( var e in CKEDITOR.tools.extend( {}, dtd.$nonBodyContent, dtd.$block, dtd.$listItem, dtd.$tableContent ) )
   {
      dataProcessor.writer.setRules( e,
         {
            indent : true,
            breakBeforeOpen : true,
            breakAfterOpen : false,
            breakBeforeClose : !dtd[ e ][ '#' ], 
            breakAfterClose : true
         });
   }

   // Output properties as attributes, not styles.
   htmlFilter.addRules(
      {
         elements :
         {
            $ : function( element )
            {
               // Output dimensions of images as width and height
               if ( element.name == 'img' )
               {
                  var style = element.attributes.style;

                  if ( style )
                  {
                     // Get the width from the style.
                     var match = /(?:^|\s)width\s*:\s*(\d+)px/i.exec( style ),
                     width = match && match[1];

                     // Get the height from the style.
                     match = /(?:^|\s)height\s*:\s*(\d+)px/i.exec( style );
                     var height = match && match[1];
                     
                     // Get the border from the style.
                     match = /(?:^|\s)border-width\s*:\s*(\d+)px/i.exec( style );
                     var border = match && match[1];
                     
                     // Get the float from the style.
                     match = /(?:^|\s)float\s*:\s*(\D+);/i.exec( style );
                     var float = match && match[1];

                     if ( width )
                     {
                        element.attributes.width = width;
                     }

                     if ( height )
                     {
                        element.attributes.height = height;
                     }
                     
                     if ( border )
                     {
                        element.attributes.border = border;
                     }
                     
                     if ( float )
                     {
                        element.attributes.align = float;
                     }
                  }
               }               
               if ( !element.attributes.style )
                  delete element.attributes.style;

               return element;
            }
         }
      } );
}
