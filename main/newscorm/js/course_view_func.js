/**
 * many functions from lp_view.php and refactoring with Jquery
 *
*/

var leftZoneHeightOccupied = 0;
var rightZoneHeightOccupied = 0;
var initialLeftZoneHeight = 0;
var initialRightZoneHeight = 0;

function onLoadCallBack(content_id) {
	screen_height = screen.height;
	screen_width = screen.height;

	$('#learning_path_left_zone').height("100%");
	$('#learning_path_toc').height("60%");
	$('#learning_path_toc').width("100%");
	$('#learning_path_right_zone').height("100%");
	$('#content_id').height("100%");

	if (screen_height <= 600) {
		$('#inner_lp_toc').height(100);
		$('#learning_path_left_zone').height(415);
	}

	initialLeftZoneHeight = $('#learning_path_toc').outerHeight();
	initialRightZoneHeight = $('#learning_path_right_zone').outerHeight();
//	docHeight = document.body.clientHeight;
	docHeight = $(document).height();
	leftZoneHeightOccupied = docHeight - initialLeftZoneHeight;
	rightZoneHeightOccupied = docHeight - initialRightZoneHeight;
	document.body.style.overflow = 'hidden';
	
	// Does call's order is important ? 2 different one in 
	switch(content_id){
		case 'content_id':if($('#toc_id').length != 0){
										var viewportheight = $(window).height() - 200;
										$('#toc_id').height(viewportheight);
									}
									updateContentHeight(content_id);break;
									
		case 'content_id_blank':updateContentHeight(content_id);
									if($('#toc_id').length != 0){
										var viewportheight = parseInt(window.innerHeight) - 200;
										$('#toc_id').height(viewportheight);
									}break;
									
		default:break;
	}
	
	// set iframe height
	var heightAvailable = $(window).height();
	var headerHeight = $('#courseHeader').height();
	
	if($('#content_id').length != 0)
		$('#content_id').height(heightAvailable - headerHeight);
	
}

function updateContentHeight(content_id){
//	winHeight = (window.innerHeight != undefined ? window.innerHeight : document.documentElement.clientHeight);
	var winHeight = $(window).height();

	newLeftZoneHeight = winHeight - leftZoneHeightOccupied;
	newRightZoneHeight = winHeight - rightZoneHeightOccupied;
	if (newLeftZoneHeight <= initialLeftZoneHeight) {
		newLeftZoneHeight = initialLeftZoneHeight;
		newRightZoneHeight = newLeftZoneHeight + leftZoneHeightOccupied - rightZoneHeightOccupied;
	}
	if (newRightZoneHeight <= initialRightZoneHeight) {
		newRightZoneHeight = initialRightZoneHeight;
		newLeftZoneHeight = newRightZoneHeight + rightZoneHeightOccupied - leftZoneHeightOccupied;
	}
	if($('#learning_path_toc').length != 0)			$('#learning_path_toc').height(newLeftZoneHeight);
	if($('#learning_path_right_zone').length != 0)	$('#learning_path_right_zone').height(newRightZoneHeight);
	
	if($('#learning_path_left_zone:visible')  ){	
             //$('#'+content_id).height(newRightZoneHeight);
            //$('#'+content_id).height($('#content_with_secondary_actions').height());
        }
	else {	
            $('#'+content_id).height(newRightZoneHeight);
        }
	
	if (document.body.clientHeight > winHeight)		document.body.style.overflow = 'auto';
	else											document.body.style.overflow = 'hidden';
};

/**
 * Hide Course toggle (only if not hidden)
 * @return null
 */
function hideCourseMenu(){
	var menu = $("#courseToggleMenu");
	if(!menu) return false;	
        /*if ($("#lp-menu-right-collapsable").length > 0) {
            $("#lp-menu-right-collapsable").attr("width", "0%");
            $("#learning_path_right_zone").css("width", "100%");            
        } else {
            if(menu.is(':visible'))	$("#courseToggleMenu").hide("slow");
        }*/	
        return false;
}

/**
 * Refresh new orange progress bar
 * @param nbr_complete
 * @param nbr_total
 * @param mode
 * @return
 */
function refreshProgressBar(nbr_complete, nbr_total, mode){
	if(mode == ''){mode='%';}
	if(nbr_total == 0){nbr_total=1;}
	var percentage = Math.round((nbr_complete/nbr_total)*100);

	$('#progressBar #percent').width(percentage+mode);
	
}

/**
 * Add the parameter "transparent" to flash movies
 */
function transparent_flash() {    
  $("#content_id").load(function() {
        var count_elements = parseInt($("#content_id").contents().find('body').children().length);
        if (count_elements == 1) {
            $("#content_id").contents().find("embed").each(function() {
                if($(this).parent()[0]['tagName'].toLowerCase() != 'object'){
                      h = $(this).height();
                      w = $(this).width();
                      src = $(this).attr('src');
                      rc = src.toLowerCase();
                      if(rc.indexOf('.swf') != -1) {
                       obj = '<object width="'+w+'" height="'+h+'">\n\
                            <param name="wmode" value="transparent" >\n\
                            <param value="true" name="allowfullscreen" >\n\
                            <param value="always" name="allowscriptaccess" >\n\
                            <param name="movie" value="'+src+'" >\n\
                                <embed height="'+h+'" width="'+w+'"\n\
                                type="application/x-shockwave-flash"\n\
                                src="'+src+'" \n\
                                wmode="transparent" play="true" loop="true" menu="true">\n\
                                </embed>\n\
                            </object>';        
                        $("#learning_path_right_zone").html(obj);
                      }	      	     
                }
            }) ;        
        }
 });
}


 /**
  * Load lp content
  */
 function loadLpContent(src) {
     $("#learning_path_right_zone").html('<iframe id="content_id" name="content_name" src="'+src+'" border="0" frameborder="0" class="" style="width:100%;height:680px" ></iframe>');
     transparent_flash();
 }
 


/**
* Load content in lp view
 */
 function load_lp_content(src) {
          rc = src.toLowerCase();
          if(rc.indexOf('.swf') != -1) {
           h = '100%';//$(this).height();
           w = '100%';//$(this).width();   
           obj = '<object width="'+w+'" height="'+h+'">\n\
	        <param name="wmode" value="transparent" >\n\
	        <param value="true" name="allowfullscreen" >\n\
	        <param value="always" name="allowscriptaccess" >\n\
	        <param name="movie" value="'+src+'" >\n\
	            <embed height="'+h+'" width="'+w+'"\n\
	            type="application/x-shockwave-flash"\n\
	            src="'+src+'" \n\
	            wmode="transparent" play="true" loop="true" menu="true">\n\
	            </embed>\n\
	        </object>';
            $("#learning_path_right_zone").html(obj);
          }
          else {
            $("#learning_path_right_zone").html('<iframe id="content_id" name="content_name" src="'+src+'" border="0" frameborder="0" class="" style="width:100%;height:680px" ></iframe>');  
          }
 
 }
