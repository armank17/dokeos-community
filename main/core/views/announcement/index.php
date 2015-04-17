<?php
/* For licensing terms, see /license.txt */

// name of the language file that needs to be included
$language_file = array('announcements','group','survey');

// including the global dokeos file
require_once '../../../inc/global.inc.php';
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
require_once(api_get_path(LIBRARY_PATH).'mail.lib.inc.php');
require_once(api_get_path(LIBRARY_PATH).'groupmanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'timezone.lib.php');

require_once api_get_path(SYS_MODEL_PATH).'announcement/AnnouncementModel.php';
require_once api_get_path(SYS_CONTROLLER_PATH).'announcement/AnnouncementController.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
//require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';

// additional javascript
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_CODE_PATH) . 'appcore/library/jquery/select2/select2.css" />';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_CODE_PATH) . 'appcore/library/jquery/jPaginate/css/style.css" />';
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_CODE_PATH).'course_home/script/jquery.jscrollpane.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_CODE_PATH).'course_home/script/jquery.mousewheel.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_CODE_PATH).'appcore/library/jquery/select2/select2.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jPaginate/jquery.paginate.js"></script>';
$htmlHeadXtra[] = '
    <style type="text/css">
	form {
		border:0px;
	}
	div.row div.label{
		width: 10%;
	}
	div.row div.formw{
		width: 98%;
	}
    #footer {
        background-color:transparent !important;
        }
	.ui-dialog-titlebar-close:hover,
	.ui-dialog-titlebar-close:focus {
		background-color:transparent !important;
		border:none !important;
	   
	}	
	.scroll-pane11
	{
		width: auto;
		height: 250px;
		overflow: auto;
	}
    </style>
';
$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {

	$(".scroll-pane11").jScrollPane();

	$(".receivers_scenario").ready(function() {

		$.ajax({
			url: "'.api_get_path(WEB_AJAX_PATH).'get_ajax_scenario_filter_edit.php",
			success: function(data) {

				$(".receivers_scenario").append(data);
			}
		});		
	});

	$("input.checkbox").click(function () {

        // Loop all these checkboxes which are checked
        var values = $("input[type=\'checkbox\']:checked").map(function(){
			return this.value
		}).get().join();		
		$("#scenario_filter").val(values);

		$.ajax({
			url: "'.api_get_path(WEB_AJAX_PATH).'get_ajax_scenario_filter.php?action=get_num_users&steps="+values+"&course_code='.api_get_course_id().'",
			success: function(data) {				
				$("#ajax_users").html("");
				$("#ajax_users").html(data);				
			}
		});
    });

	$("#see_list_users").click(function (e) {

		var values = $("input[type=\'checkbox\']:checked").map(function(){
			return this.value
		}).get().join();

		$.ajax({
		  type: "GET",
		  url: "'.api_get_path(WEB_AJAX_PATH).'get_ajax_scenario_filter.php?action=get_users_data&steps="+values+"&course_code='.api_get_course_id().'",
		  success: function(data){	
				
			  $(".scenario_dialog").html(data);
			  $(".scenario_dialog").dialog({
									open: function(event, ui) {  
										jQuery(".ui-dialog-titlebar-close").css("width","85px");
										jQuery(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\">'.get_lang("CloseX").'</span>");  											
									},
									modal: true,
									title: "'.get_lang("EmailTargetAudience").'",
									width: 800,
									height : 400,
									resizable:false
				}); 
				$(".scroll-pane1").jScrollPane();
				var bgcolor = window.parent.$("#header_background").css("background-color");
				$(".jspDrag").css("background",bgcolor);
		  }

		  });
		  e.preventDefault();
	});

	$("#feedback-tokens").select2({minimumResultsForSearch: -1});
	  $("#feedback-tokens").on("change", function() {
		  
		  var editor = getCKInstance("description");
            editor.insertHtml($(this).val());
	  });
      $(".user_info").click(function () {
        var myuser_id = $(this).attr("id");
        var user_info_id = myuser_id.split("user_id_");
        my_user_id = user_info_id[1];
        $("<div style=\'display:none;\' title=\''.get_lang('UserInfo').'\' id=\'html_user_info\'></div>").insertAfter(this);
        $.ajax({
            url: "'.api_get_path(WEB_AJAX_PATH).'user_manager.ajax.php?a=get_user_info&user_id="+my_user_id,
            success: function(data){
                //var dialog_div = $("<div id=\'html_user_info\'></div>");
                //dialog_div.html(data);
                $(\'#html_user_info\').html(\'<div style="text-align:justify;width:100%;max-height:580px">\'+data+\'</div>\');
                $("#html_user_info").dialog({
                    open: function(event, ui) {  
                                            $(".ui-dialog-titlebar-close").css("width","0px");
                                            $(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\">'.get_lang("Close").'</span>");  											
                                    },
                    autoOpen: true,
                modal: true,
                title: "'.get_lang('UserInfo').'",
                    closeText: "'.get_lang('Close').'",
                width: 640,
                height : 240,
                resizable:false
                });
            }
        });
    });
    
    
    function removeParams(url)
    {
		
		var items = (url.split("?"));
		return items[0];
	}
	
	var start = '.($_GET['page']?$_GET['page']:"1").';
    var pages = $("#pages").val();
    var cid = "'.api_get_course_id().'";    
	if(pages>1){
		$("#pager").paginate({
				count 		: pages,
				start 		: start,
				rotate		: true,
				display     : 1,
				border			: false,
				text_color  		: "#888",
				background_color    	: "#EEE",	
				text_hover_color  	: "black",
				background_hover_color	: "#CFCFCF",
				onChange                : function(page){
											var url = removeParams(window.location.href);
											window.location.href = url + "?page=" + page+"&cidReq="+cid;			
										}
		});
	}
        
     
    
    
});

function getCKInstance(editorname) {
	
    var myinstances = [];
        if ($("#cke_"+editorname).length > 0) {

            for(var i in CKEDITOR.instances) {
                CKEDITOR.instances[i];
                CKEDITOR.instances[i].name;
                CKEDITOR.instances[i].value;
                CKEDITOR.instances[i].updateElement();
                myinstances[CKEDITOR.instances[i].name] = CKEDITOR.instances[i]; 
            }
            return myinstances[editorname];
        }
        return false;  
}
</script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js"></script>';
$htmlHeadXtra[] = '
    <style type="text/css">
        input.error { border: 1px solid red; }
    </style>

    <script type="text/javascript">

    $(document).ready(function(){
        if ($("#announcement_form").length > 0) {
            $("#announcement_form").validate({
                rules: {
                    title: {
                      required: true
                    }
                },
                messages: {
                    title: {
                        required: "<img src=\"'.  api_get_path(WEB_IMG_PATH).'exclamation.png\" title=\''.get_lang('Required').'\' />"
                    }
                }
            });
        }
   });



</script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/customInput1.jquery.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript">        
	// Run the script on DOM ready:

	$(function(){
                try {
		$("input").customInput();
                } catch(e){}
	});
	</script>';
// Funciton Alerts Confirm
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/functionsAlerts.js"></script>';

// get actions
$actions = array('listing', 'add', 'view', 'edit', 'delete', 'showAll');
$action = 'listing';
if (isset($_GET['action']) && in_array($_GET['action'],$actions)) {
	$action = $_GET['action'];
}

// set announcement id
$announcementId = isset($_GET['id']) && is_numeric($_GET['id'])?intval($_GET['id']):null;

// announcement controller object
$announcementController = new AnnouncementController($announcementId);

// distpacher actions to controller
switch ($action) {
	case 'listing':
		$announcementController->listing();
		break;
	case 'add':
           	$announcementController->add();
		break;
	case 'view':
		$announcementController->showannouncement($announcementId);
		break;
	case 'edit':
		$announcementController->edit();
		break;
	case 'delete':
		$announcementController->destroy();
		break;
	case 'showAll':
		$announcementController->showallannouncement();
		break;
	default:
		$announcementController->listing();



}
?>
