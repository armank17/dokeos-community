(function() {
	CKEDITOR.plugins.add('divresize', {
		init: function( editor ) {
			editor.on( 'contentDom', function() {
                                CKEDITOR.addCss(".delete-block{float:right !important;}");
				var pluginPath = CKEDITOR.plugins.get('divresize').path;
                                var contents = $(editor.window.getFrame().$).contents(); 
                                var element = contents.find(".cols"); 
                                contents.find(".delete-block").remove();
                                element.on( 'click', function( evt )
                                {   
                                    //$(this).resizable().draggable();
                                    var attrId = $(this).attr("id"); 
                                    contents.find(".delete-block").remove();                                        
                                    $(this).append("<img src='"+pluginPath+"close.png' id='del-"+attrId+"' class='delete-block' style='float:right;'/>");                                      
                                    var element2 = editor.document.getById("del-"+attrId);                                   
                                    if (element2) {
                                        element2.on('click', function(evt){     
                                            evt = evt.data;
                                            evt.preventDefault();
                                            var target = evt.getTarget();
                                            var mydiv = target.getAscendant('div', 1 );
                                            mydiv.remove();
                                        });
                                    }
                                });    
                               
			});
		}
	});

})();
