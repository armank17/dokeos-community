var pageHomeModel = (function(){
    return {
        delete: function(select, e) {
            e.preventDefault();
        
            var url        = select.attr("href"),
                confirmMsn = select.attr("title"),
                webPath    = decodeURIComponent($("#webPath").val()),
                target     = decodeURIComponent($("#target").val());
        
            $.confirm(confirmMsn, getLang('ConfirmationMessage'), function() {
               $.get(url, function(data) {
                    location.href = webPath+"main/index.php?module=node&cmd=PageHome&func=Index";
               });
            });
        },
        createlink: function(select, e){
            e.preventDefault();
            var titlePage=$("#node_title").val();            
             if($(".createlink").is(':checked')) { 
                if($("#menu_link_title_id").val()===''){
                    $("#menu_link_title_id").val(titlePage);
                }
                $("#linkcreation").show("3000");
             }
             else {
                $("#linkcreation").hide("1000");
             } 
        },
        enableNode: function(select,e){
        e.preventDefault();
        var url         = select.attr("href"),
            webPath     = decodeURIComponent($("#webPath").val());
            $.get(url, function(data) {
                    location.href = webPath+"main/index.php?module=node&cmd=PageHome&func=Index";
               });
        },
        getTemplate: function(select, e){
            e.preventDefault();
            
            var url = select.attr('href');

            $.get(url, function(data) {
                CKEDITOR.instances['node_editor'].setData(data);
            });
        }
    }
})();