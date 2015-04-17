$(document).ready(function() {         
    $('#fileToUpload').change(function() {
                    var oEditor = CKEDITOR.instances['answer'];
                    $("#question_admin_form2").ajaxForm({                            
                        url:        'upload.php',
                        beforeSubmit: function() {                              
                                oEditor.setData(html_entity_decode('Loading data...'), function()
                                {
                                    updateBlanks();  // true
                                    $('#error_upl').hide();
                                });
                        },
                        success:    function(res) {                                      
                                
                                oEditor.setData(html_entity_decode(res), function()
                                {
                                    updateBlanks();  // true
                                    $('#error_upl').hide();
                                });
                               
                                                            
                            }
                    }).submit();
    });
});