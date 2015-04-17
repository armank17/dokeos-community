var NewsModel = (function(){
    return {
        deleteNew: function(select, e) {
            e.preventDefault();
        
            var url        = select.attr("href"),
                confirmMsn = select.attr("title"),
                webPath    = decodeURIComponent($("#webPath").val());
        
            $.confirm(confirmMsn, getLang('ConfirmationMessage'), function() {
               $.get(url, function(data) {
                    location.href = webPath+"main/index.php?module=node&cmd=News&func=Index";
               });
            });
        },
        setVisible: function(select, e){
            e.preventDefault();
            var url     = select.attr("href"),
                webPath = decodeURIComponent($("#webPath").val());
            $.post(url,function(data){
                    location.href=  webPath+'main/index.php?module=node&cmd=News&func=Index';
            }); 
            
        },
       enableNode:function(select,e){
            e.preventDefault();
            
            var url     = select.attr("href"),
                webPath = decodeURIComponent($("#webPath").val());
        
            $.get(url, function(data) {
                    location.href = webPath+"main/index.php?module=node&cmd=News&func=Index";
            });
        }
    }
})();
