/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){
    /*** getForm ***/
    if ($("#menulink-form").length > 0) {
        
        // validate
        $.validator.addMethod('cb_select_one', function(value, element){
            var fields = $("input[name^='menulink_visibility_']:checked");
            return (fields.length > 0);
        }, 'Select an option');
        
        $("#menulink-form").validate({
            ignore: '',
            rules: {
                menulink_title: 'required',
                menulink_path: {
                    required: true,
                    url: true
                },
                menulink_description: 'required',
                menulink_visibility: {
                    cb_select_one: true
                }
            }
        });
   
    }
    
    
    
    /*** listMenuLinks ***/
    if($("#table_lp_list").length > 0) {
        $("#table_lp_list tbody").sortable({
            //tolerance: "pointer",
            handle: 'td:first',
            helper: function(e, ui) {
                        ui.children().each(function() {
                                _self = $(this);
                                _self
                                     .width(_self.width())
                                     .height(_self.height());
                        });
                        return ui;
                    },
            beforeStop: function( event, ui ) {
                $('#table_lp_list tbody tr td.sort-handle input.menulink-hidden-weight').each(function(index){
                    $(this).val(index-50);
                });
                
                menuLinkModel.saveList($("#menu-list-form"), event);
            }
        }).disableSelection();

        // check enable
        $('#table_lp_list tbody tr td input.menulink-check-enabled').click(function(e){
            var _self = $(this),
                value = (_self.attr('checked') == undefined)? 0 : 1;
            _self.closest('tr').find('td.sort-handle input.menulink-hidden-enabled').val( value );
            
            menuLinkModel.saveList($("#menu-list-form"), e);
        });

        // delete
        if($(".menulink-delete-link").length > 0){
            $(".menulink-delete-link").click(function(e){
                var _self = $(this);
                if(_self.find('img.actiondelete.invisible').length == 0)
                    menuLinkModel.delete(_self, e); 
            });
        }
    }
});