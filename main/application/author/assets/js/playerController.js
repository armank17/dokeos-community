var back2home = false;
$(document).ready(function() {
        
    //showNiceScroll($(".player-actions"));
    PlayerModel.hideLeftBlock($(".toogle-slide-left a.ssOpenG"));

    $(document).scroll(function() {
        PlayerModel.stickLeftBlock($(this));
    });
    
    $(".toogle-slide-top a.ssOpen").toggle(function() {
        PlayerModel.hideTopBlock($(this));          
    },function() {
        PlayerModel.showTopBlock($(this));        
    });
    
    $(".toogle-slide-left a.ssOpenG").toggle(function(){                 
        PlayerModel.showLeftBlock($(this));                
    },function(){          
        PlayerModel.hideLeftBlock($(this));          
    });
    
    
    if ($(".oogie-image").length) {
        $(".oogie-image").removeAttr('width');
    }  

    var oogie_height = $(".oogie-image").height();
    $(window).load(function() {           
       oogie_height = $(".oogie-image").height();
       updateOogieImageHeight(oogie_height); 
    });

    $(window).resize(function(){
       updateOogieImageHeight(oogie_height); 
    });    
    
    if ($(".back2home-from-author").length) {
        $(".back2home-from-author").click(function(e) { 
            back2home = true;
           PlayerModel.saveLpItemTime(e, $(this), true);           
        });
    }

    

});

window.onbeforeunload = function (e) {
    
   
   //PlayerModel.saveLpItemTime(e, $(this), true);
   
};


