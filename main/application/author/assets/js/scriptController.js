$(document).ready(function() {
	
	var deviceType = $("#device_type").val();
	if(deviceType=="0" || deviceType=="")	{
		//AuthorModel.hideLeftBlock($(".toogle-slide-left a.ssOpenG"));
		//AuthorModel.hideRightBlock($(".toogle-slide-right a.ssOpenGR"));
	
		$("#layout").val("2");
	
		$(".toogle-slide-left a.ssOpenG").toggle(function(){                 
			AuthorModel.showLeftBlock($(this));
									  
		},function(){
			AuthorModel.hideLeftBlock($(this));          
		});
		
		$(".toogle-slide-right a.ssOpenGR").toggle(function(){                 
			AuthorModel.showRightBlock($(this));          
		},function(){
			AuthorModel.hideRightBlock($(this));                
			
		});
		
	}else	{
		$(".toogle-slide-left a.ssOpenG").toggle(function(){                 
			AuthorModel.hideLeftBlock($(this));
									  
		},function(){
			AuthorModel.showLeftBlock($(this));
			          
		});
		
		$(".toogle-slide-right a.ssOpenGR").toggle(function(){                 
			AuthorModel.hideRightBlock($(this)); 
			         
		},function(){
			AuthorModel.showRightBlock($(this));                
			
		});
	}
	
    var iframe;
    AuthorModel.sortMenuItems();
    AuthorModel.sortModules();
    AuthorModel.setHeightAuthoringiframe();
    AuthorModel.submitAuthoringForm();
    
    if ($("#courseToggleMenu").length > 0) {
        //$("#courseToggleMenu").niceScroll({cursorcolor: "#60C150"});
    }
    
    $(".author-btnhome").click(function(e){
       AuthorModel.confirmExit(e, $(this)); 
    });        
            
    $('.item-action-delete').click(function(e){
        AuthorModel.deleteItem();
    });
    
    $(".item-action-delaudio").click(function() {
        AuthorModel.deleteItem($(this));
    });
    
    $("input#txt-item-title").focus(function() {
       AuthorModel.hideTitleBlockError($(this)); 
    });
    
    $(".action-dialog").unbind("click").click(function(e) {
        AuthorModel.showActionDialog(e, $(this));
    });
    
    $('#form-items').submit(function(e) {
        AuthorModel.submitItemsForm(e, $(this));
    });
    
    $("#author-next").click(function(e) {
        AuthorModel.submitAndNextItem(e);
    });
    
    $("#author-next-ext").click(function(e) {
        AuthorModel.submitAndNextItem(e);
    });
    
    $("#author-submit").click(function(e) {
        AuthorModel.submitCurrentItem(e);
    });
    
     $("#author-save").click(function(e) {
        AuthorModel.submitCurrentItem(e);
    });
    
    
    $("#show-items-form").click(function(){
        AuthorModel.showItemsForm();
    });
    
    $(".course_menu_button").click(function(){
        AuthorModel.showHideCourseMenuBtn();
            setInterval(function() {
                setMenuTime();
            }, 2000);
    });
    
    $("#bt-items-cancel").click(function(){
        AuthorModel.showHideItemsCancelBtn();
    });
    
    $("#txt-item-title").keyup(function() {
        AuthorModel.saveItemTitle($(this));
    });
    
    $(".course_menu_button").click(function(e) {
        AuthorModel.toogleItemsMenu(e);
    });
        
    $(".audio-actions").click(function(e){
        AuthorModel.switchAudioPlayer(e, $(this));
    });
    
    $(".embed-tpl").click(function(e) {
        AuthorModel.updateTemplateEditor(e, $(this));
    });   
    
});

function setMenuTime() {
    $("#courseToggleMenu").mouseleave(function(){       
        $(this).fadeOut("slow");
    })        
}

