$(document).ready(function() {
  
    // Define the protocol, pathname and workpath 
    my_protocol = location.protocol;
    my_pathname=location.pathname;
    work_path = my_pathname.substr(0,my_pathname.indexOf('/courses/'));
    
    // Perform an asynchronous HTTP (Ajax) request  
    $.ajax({
        contentType: "application/x-www-form-urlencoded",
        beforeSend: function(content_object) {},
        type: "POST",
        url: my_protocol+"//"+location.host+work_path+"/main/glossary/glossary_ajax_request.php",
        data: "glossary_data=true",
        success: function(response) {
            if (response.length==0) {
                return false;
            }
            data_terms=response.split("[|.|_|.|-|.|]");
            for(i=0;i<data_terms.length;i++) {
                specific_terms=data_terms[i].split("__|__|");
                var real_term = specific_terms[1];
                var real_code = specific_terms[0];        
                $('body').highlight(real_term, false, real_code);
            }
                             
            // Event for when mouse over of the term then the dialog will be showed
            $("body .glossary-ajax").mouseover(function(e) { 

                // If the div msgGlossary exist  
                if ($("#msgGlossary").length > 0) {
                    // Then remove this div msgGlossary
                    $("#msgGlossary").remove();
                } 

                // Get the title of term 
                var text_box = $(this).text();

                // Create the div msgGlossary
                $("<div style='display:none;' title='"+ text_box +"' id='msgGlossary'></div>").insertAfter(this); 

                notebook_id = $(this).attr("name");				
                data_notebook = notebook_id.split("link");
                my_glossary_id=data_notebook[1];

                // Get the current position of the term
                var glossary_position = $(this).offset();

                // Set the horizontal and vertical position of the dialog
                var pos_hor = glossary_position.left + $(this).width()+5;
                var pos_ver = glossary_position.top + 20;				
                
                // Perform an asynchronous HTTP (Ajax) request  
                $.ajax({          
                    contentType: "application/x-www-form-urlencoded",
                    type: "POST",
                    url: my_protocol+"//"+location.host+work_path+"/main/glossary/glossary_ajax_request.php",
                    data: "glossary_id="+my_glossary_id,
                    success: function(response) {     

                        // Set the options for the dialog      
                        $('#msgGlossary').dialog({  
                            autoOpen: false,
                            modal: false, // If set to true, the dialog will have modal behavior; other items on the page will be disabled.
                            title: text_box, 
                            height: 'auto', 
                            width: '500px', //The width of the dialog, in pixels.
                            resizable: false, // If set to true, the dialog will be resizable.
                            draggable: false , // If set to true, the dialog will be draggable will be draggable by the titlebar.
                            position: [pos_hor,pos_ver], // Specifies where the dialog should be displayed. A single string representing position within viewport: 'center', 'left', 'right', 'top', 'bottom'. 
                            closeOnEscape: true // Specifies whether the dialog should close when it has focus and the user presses the escape (ESC) key.
                        });
                        $(".ui-dialog").css({"font-size": "12px"});
                        // Hide the icon close in the dialog
                        $(".ui-icon-closethick").css({"display":"none"});

                        // Set the description of the term inside of the div msgGlossary
                        $('#msgGlossary').html('<div style="text-align:justify;width:100%;max-height:580px">'+response+'</div>');
                            $('#msgGlossary').dialog("option", "position", {
                                my: "left top",
                                at: "right bottom",
                                of: e,
                                offset: "20 20"
                              });
                              $('#msgGlossary').dialog("open");
                        // Event for when mouse out of the div then the dialog will be closed
                        /*$('#msgGlossary').mouseout(function(){                             
                                // Then remove this div msgGlossary
                                $("#msgGlossary").remove();
                        });*/

                        // Event for when make click inside of the div then the dialog will be closed
                        $('#msgGlossary').mousedown(function(){                               
                                // Then remove this div msgGlossary
                                $("#msgGlossary").remove();
                        }); 
                    },global: false,
                });// End of asynchronous HTTP (Ajax) request.        
       
            }) // End of mouseover function used for $("body .glossary-ajax").   
                .mousemove(function(event) {
                    $('#msgGlossary').dialog("option", "position", {
                        my: "left top",
                        at: "right bottom",
                        of: event,
                        offset: "20 20"
                      });
                      $('#msgGlossary').dialog("open");
                }).mouseout(function() {
                    $("#msgGlossary").remove();
                });
            // Event for when mouse out of the term then the dialog will be closed
//            $("body .glossary-ajax").mouseout(function(){                             
//
//                // Set a timer for close the dialog in a time defined (1,5 seconds).                 
//                setTimeout(function() { 
//                    // Then remove this div msgGlossary
//                    $("#msgGlossary").remove();        
//                }, 0.1 * 1000);
//
//            });  // End of mouseout function used for $("body .glossary-ajax").     

          } 
     });// End of asynchronous HTTP (Ajax) request.                    
 });  
