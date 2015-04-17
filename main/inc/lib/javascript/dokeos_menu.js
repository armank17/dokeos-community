/*$(document).ready(function(){
        
    indexCurrent = 0; //indice of location to  focus
    var posini = 0;
    var max_menu = 5;
    $("#dokeostabs li").each(function (i,o) {
        if($(o).attr("id")=="current"){
            indexCurrent = i;
        };
            
            
    });
    while(indexCurrent >= posini && indexCurrent > max_menu){
        max_menu += 1;
        mnu = $("#dokeostabs").find("a").get(posini);
        $(mnu).find("li").hide();
        posini += 1;
    };
        
    var count_menu = $("#dokeostabs").find("a").length;
    var menu_posini = posini;
    var menu_posfin = (max_menu < count_menu) ? max_menu : count_menu-1;
                    
    if(count_menu <= 6){
        $("#btn-left").hide();
        $("#btn-right").hide();
    }else{
        if(menu_posini == 0 && indexCurrent <= max_menu){
            $("#btn-left").hide();
        }
        if(menu_posfin > count_menu-2){
            $("#btn-right").hide();
        }
    }
    $("#btn-left").click(function(){
        animationMenu(-1);
    });

        
    $("#btn-right").click(function(){
        animationMenu(1);
    });
        
        
    function animationMenu(direction){
        
        if(direction == 1){//next
            if(menu_posfin < count_menu-1){
                if (menu_posini < 0) {
                    menu_posini = 0
                }
                if (menu_posfin > count_menu - 1) {
                    menu_posfin = count_menu - 2;
                }
                //show tab right
                mnu = $("#dokeostabs").find("a").get(menu_posfin+1);
                $(mnu).find("li").show(400);
                        
                mnu = $("#dokeostabs").find("a").get(menu_posfin);
                $(mnu).find("li").css("margin-left","0px");
                
                //show button left
                if(menu_posini == 0 && indexCurrent <= max_menu){
                    $("#btn-left").show(400);
                }
                //hide button right
                if(menu_posfin >= count_menu - 2){
                    $("#btn-right").hide();
                }
                //hide tab left
                mnu = $("#dokeostabs").find("a").get(menu_posini);
                $(mnu).find("li").hide(400);
                menu_posfin += 1;
                menu_posini += 1;  

                mnu = $("#dokeostabs").find("a").get(menu_posini);
                $(mnu).find("li").css("margin-left","25px");
                    
            }
        } else { //back
 
            if(menu_posini > 0){
                mnu = $("#dokeostabs").find("a").get(menu_posini);
                $(mnu).find("li").css("margin-left","0px");
                if (menu_posini < 0) {
                    menu_posini = 1;
                }
                if (menu_posfin > count_menu - 1) {
                    menu_posfin = count_menu - 1;
                }
                if (menu_posini > 0) { 
                    menu_posini -= 1;
                }
                //show tab left
                mnu = $("#dokeostabs").find("a").get(menu_posini);
                $(mnu).find("li").show(400);
                
                //hide button left    
                if(menu_posini == 0 && menu_posfin >= max_menu){
                    $("#btn-left").hide(400);
                }
                //show button right
                if(menu_posfin >= 5){
                    $("#btn-right").show(400);
                }
                //hide tab right
                mnu = $("#dokeostabs").find("a").get(menu_posfin);
                $(mnu).find("li").hide(); 
                    
                menu_posfin -= 1;
            }
        }
            
    }
});*/
