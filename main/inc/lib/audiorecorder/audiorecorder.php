<?php 
require_once '../../global.inc.php';
require_once api_get_path(SYS_CODE_PATH).'inc/lib/audiorecorder/audiorecorder_conf.php';

$id = intval($_REQUEST['id']);
$action = Security::remove_XSS($_REQUEST['action']);
$title = Security::remove_XSS($_GET['title']);
$lp_id  = intval($_REQUEST['lp_id']);
?>
  
var popupStatus = 0;  
function loadPopup(){  
	//loads popup only if it is disabled  
	if(popupStatus==0){  
		$("#backgroundPopup").css({  
			"opacity": "0.7"  
		});  
		$("#backgroundPopup").fadeIn("slow");  
		$("#popupContact").fadeIn("slow");  
		popupStatus = 1;  
	}  
}

function disablePopup(){  
	//disables popup only if it is enabled  
	if(popupStatus==1){  
		$("#backgroundPopup").fadeOut("slow");  
		$("#popupContact").fadeOut("slow");  
		popupStatus = 0;  
	}  
}

function loadAudioRecorder()
{
	AC_FL_RunContent(
			"src", "audioRecorder",
			"width", "323",
			"height", "150",
			"align", "middle",
			"id", "audioRecorder",
			"quality", "high",
			"bgcolor", "#869ca7",
			"name", "audioRecorder",
			"allowScriptAccess","sameDomain",
			"type", "application/x-shockwave-flash",
			"pluginspage", "http://www.adobe.com/go/getflashplayer",
			"flashvars", "myServer=rtmp://<?php echo $url['host'] ?>/oflaDemo&amp;timeLimit=<?php echo $time_limit ?>&amp;urlDokeos=<?php echo api_get_path(WEB_PATH) ?>main/document/upload_audio.php"
	);
}

function centerPopup(){  
	//request data for centering  
	var windowWidth = document.documentElement.clientWidth;  
	var windowHeight = document.documentElement.clientHeight;  
	var popupHeight = $("#popupContact").height();  
	var popupWidth = $("#popupContact").width(); 

	//centering  
	$("#popupContact").css({  
		"position": "absolute",  
		"top": windowHeight/2-popupHeight*2,  
		"left": windowWidth/2-popupWidth/2  
	});  
	//only need force for IE6  
	  
	$("#backgroundPopup").css({  
		"height": windowHeight  
	});  
}

$(document).ready(function(){

	//LOADING POPUP  
	//Click the button event!  
	$("#button").click(function(){

	//centering with css  
		centerPopup();  
	//load popup  
		loadPopup();
		loadAudioRecorder();
	});
	
	$("#audiolink").click(function(){
	//centering with css  
		centerPopup();  
	//load popup  
		loadPopup();  
	});
	
	$("#popupContactClose").click(function(){  
		disablePopup();  
	});  
	
	//Click out event!  
	$("#backgroundPopup").click(function(){  
		disablePopup();  
	});  
	//Press Escape event!  
	$(document).keypress(function(e){  
		if(e.keyCode==27 && popupStatus==1){  
			disablePopup();  
		}  
	});
});

function audioRedirect()
{
	var url = '../document/document.php?cidReq=<?php echo api_get_course_path();  ?>&rec=rec&amp;curdirpath=/audio';
        var redirect = setTimeout("window.location.href = '<?php echo api_get_path(WEB_PATH); ?>main/document/upload_audio.php?<?php echo api_get_cidreq(); ?>&amp;id=<?php echo $id; ?>&amp;action=<?php echo $action; ?>&amp;lp_id=<?php echo $lp_id; ?>&title=<?php echo $title?>&option=rec'",4000);
}
