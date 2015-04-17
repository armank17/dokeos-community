var PlayerModel = function() {
    return {
        stickLeftBlock: function(select) {
//            var currTop , newtop;
//            var baseTop = 88;        
//            if ($("#player-view-top").css("display") == "none") {
//                baseTop = 18;
//            }        
//            currTop = select.scrollTop()
//            if (currTop < baseTop) {
//                newtop = (baseTop - currTop)+"px";            
//                $(".player-actions").css("top", newtop);
//                $(".toogle-slide-left").css("height", "85%");
//            }        
//            else {
//                $(".player-actions").css("top", "0px");
//                $(".toogle-slide-left").css("height", "97%");
//            }
        },
        hideTopBlock: function(select) {            
            $("#slideed").css({'overflow':'hidden'});
            var n_h = "95%";
            $("#content_sup").animate({height:"3%",minHeight: "20px"}, 500, "linear");
            $("#content_mid").animate({height:n_h,marginTop: "0px"},300, "linear");            
            $(".player-actions").animate({top: "25px"}, 300, "linear");            
            $("#player-view-top").hide("blind", "", "100");
            $(".toogle-slide-left").animate({height:n_h}, 500, "linear");
            select.removeClass("arrow_up").addClass("arrow_down");     
        },
        showTopBlock: function(select) {            
            $("#slideed").css({'overflow':'hidden'});
            var n_h = "87%";
            $("#content_sup").animate({height:"10%"}, 500, "linear");
            $("#content_mid").animate({height:n_h,marginTop: "60px"},300, "linear");
            $(".player-actions").animate({top: "88px"}, 500, "linear");                              
            $("#player-view-top").show("blind", "", "500");
            $(".toogle-slide-left").animate({height:n_h}, 500, "linear");
            select.removeClass("arrow_down").addClass("arrow_up");  
        },
        hideLeftBlock: function(select) {
//            $(".toogle-slide-left").animate({width: "250px"}, 100, "linear",function() {
                $("#ascrail2000").css({left:"205px"});
//            });
            select.animate({left:"213px"}, 500, "linear");
            if (device_is_mobile === true)
                $(".player-actions").animate({width: "230px", overflow: 'hidden'}, 500, "linear");
            else
                $(".player-actions").animate({width: "214px", overflow: 'hidden'}, 500, "linear");
            $("#player-view-middle-right").animate({width: "80.8%", marginLeft: "240px"}, 500, "linear");
//          $("#player-view-middle-right").animate({width: "70.8%", marginLeft: "240px"}, 500, "linear");
            $("#player-view-middle-left").show("blind", {direction: "left"}, "500");
            select.removeClass("arrow_right").addClass("arrow_left");
        },
        showLeftBlock: function(select) {
//	    $("#slider").hide();
//            $(".nicescroll-rails").hide();
            $("#player-view-middle-left").hide("blind", {direction: "left"}, "500");
            $("#player-view-middle-right").animate({width: "97.5%", marginLeft: "2%"}, 500, "linear");
            select.animate({left:"3px"}, 500, "linear");
            $(".toogle-slide-left").animate({width: "23px"}, 500, "linear");
            $(".player-actions").animate({width: "23px"}, 500, "linear");
            $(".player-actions").css("overflow", "hidden");
            select.removeClass("arrow_left").addClass("arrow_right");
        },
        saveLpItemTime: function(e, select, redirect) {
           e.preventDefault();
           
           var redirectUrl = typeof redirect !== 'undefined'?redirect:false;                      
           var cidReq = decodeURIComponent($("#cid-req").val());           
           var webPath = decodeURIComponent($("#web-path").val());
           var lpId = $("#lp-id").val();
           var lpItemId = $("#current-item-id").val();
          
           var myurl = webPath+'main/index.php?module=author&cmd=PlayerAjax&func=saveLpItemTime&'+cidReq+'&lpId='+lpId+'&lpItemId='+lpItemId;          
           // save time
           $.get(myurl, function(data){
               if (redirectUrl) {
                    var thisHref = $(select).attr("href");
                    location.href = thisHref;
                    return true;
               }
           });
           
                      
        }        
    };
    
}();


