var CourseNode = (function(){
    return {
        delete: function(select, e) { //now is from functionsAlerts.js//
//            e.preventDefault();
        
//            var url        = select.attr("href"),
//                confirmMsn = select.attr("title"),
//                webPath    = decodeURIComponent($("#webPath").val()),
//                target     = decodeURIComponent($("#target").val());
        
//            $.confirm(confirmMsn, getLang('ConfirmationMessage'), function() {
//               $.get(url, function(data) {
//                    location.href = webPath+"main/index.php?module=node&cmd=CourseNode&func=Index";
//               });
//            });
        },
       enableNode:function(select,e){
        e.preventDefault();
        var url         = select.attr("href"),
            webPath     = decodeURIComponent($("#webPath").val()),
            cidReq      = decodeURIComponent($("#cidReq").val());
            $.get(url, function(data) {
                   location.href = webPath+"main/index.php?module=node&cmd=CourseNode&func=Index&"+cidReq;
               });
        }
    }
})();
