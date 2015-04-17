$(document).ready(function(){

    if ($("#pricing-account").length > 0) {        
        SuiteManagerModel.updateAccount($("#pricing-account"));
    }

    if ($("#pricing_product_list").length) {
        SuiteManagerModel.tableSelectable($("#pricing_product_list"));
    }
    
    if ($(".attributes_select").length) {
        $(".attributes_select").change(function(e) {
            SuiteManagerModel.selectPrice($(this));
        });
    }
    
    if ($("#pricing-button").length) {
        $("#pricing-button").click(function() {
            if ($(".ui-selected").length) {                
                $.confirm(getLang('ConfirmYourDemand'), getLang('TitleConfirm'), function(){
                   SuiteManagerModel.sendPricing(); 
                });
            }
            else {
                $.alert(getLang('SelectOneProduct'), getLang('Error'), "error");
            }
        });
    }
 
    var webPath = decodeURIComponent($("#webPath").val());
    $.extend($.validator.messages, {
        required: "<img src='"+webPath+"main/img/exclamation.png' title='"+getLang('Required')+"' />",
        email: "<img src='"+webPath+"main/img/exclamation.png' title='"+getLang('FormHasErrorsPleaseComplete')+"' />"
    });
 
});
