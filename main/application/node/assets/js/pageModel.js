var pageModel= function(){
    return {
        delete: function(select, e) {
            e.preventDefault();
            var url = select.attr("href");
            var confirmMsn = select.attr("title");
            var cidReq = decodeURIComponent($("#cidReq").val());
            var webPath = decodeURIComponent($("#webPath").val());             
            $.confirm(confirmMsn, getLang('ConfirmationMessage'), function() {
               $.get(url, function(data) {
                    location.href = webPath+"main/index.php?module=node&cmd=Page&func=Index&cidReq=&"+cidReq;
               });
            });
          
        }
    }
}();

