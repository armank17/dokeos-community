var menuLinkModel = (function(){
    return {
        delete: function(select, e) {
            e.preventDefault();
        
            var url        = select.attr("href"),
                confirmMsn = select.attr("title"),
                webPath    = decodeURIComponent($("#webPath").val()),
                category   = decodeURIComponent($("#category").val());
        
            $.confirm(confirmMsn, getLang('ConfirmationMessage'), function() {
               $.post(url, function(data) {
                    location.href = webPath+"main/index.php?module=node&cmd=MenuLink&func=listMenuLinks&category="+category;
               });
            });
        },
        saveList: function(form, e){
            e.preventDefault();
            
            var url = form.attr('action'),
                data = form.serialize(),
                webPath    = decodeURIComponent($("#webPath").val()),
                category   = decodeURIComponent($("#category").val());
        
             $.post(url, data, function(data) {
                    location.href = webPath+"main/index.php?module=node&cmd=MenuLink&func=listMenuLinks&category="+category;
             });
        }
    }
})();