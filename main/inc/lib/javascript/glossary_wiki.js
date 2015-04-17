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
                $('#content').highlight(real_term, false, real_code);
            }
      
            // Event for when mouse over of the term then the dialog will be showed
            $("#content .glossary-ajax").mouseover(function(){
        
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

                // Perform an asynchronous HTTP (Ajax) request           
                $.ajax({
                    contentType: "application/x-www-form-urlencoded",
                    beforeSend: function(content_object) {},
                    type: "POST",
                    url: my_protocol+"//"+location.host+work_path+"/main/glossary/glossary_ajax_request.php",
                    data: "glossary_id="+my_glossary_id,
                    success: function(response) {

                        // Set the options for the dialog      
                        $('#msgGlossary').dialog({  
                            modal: true, // If set to true, the dialog will have modal behavior; other items on the page will be disabled.
                            title: text_box, 
                            height: 'auto', 
                            width: '500px', 
                            resizable: false, // If set to true, the dialog will be resizable.
                            draggable: true , // If set to true, the dialog will be draggable will be draggable by the titlebar.
                            position: ['center',240], // Specifies where the dialog should be displayed. A single string representing position within viewport: 'center', 'left', 'right', 'top', 'bottom'. 
                            closeOnEscape: true // Specifies whether the dialog should close when it has focus and the user presses the esacpe (ESC) key.
                        });

                        // Set the description of the term inside of the div msgGlossary
                        $('#msgGlossary').html('<div style="text-align:justify;width:100%">'+response+'</div>');

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
                    }
                });// End of asynchronous HTTP (Ajax) request.
      
            });  // End of mouseover function used for $("#content .glossary-ajax").     
        }
     }); // End of asynchronous HTTP (Ajax) request.
});
