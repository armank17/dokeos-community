<?php

/* For licensing terms, see /license.txt */

// name of the language file that needs to be included
$language_file = array('link', 'admin');

// setting the help
$help_content = 'links';

// including the global dokeos file
require_once '../../../inc/global.inc.php';

// including additional libraries
require_once api_get_path(SYS_MODEL_PATH) . 'link/LinkModel.php';
require_once api_get_path(SYS_CONTROLLER_PATH) . 'link/LinkController.php';
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
require_once api_get_path(SYS_CODE_PATH) . 'newscorm/learnpath.class.php';
require_once api_get_path(SYS_CODE_PATH) . 'newscorm/learnpathItem.class.php';

$action = (!empty($_GET['action']) ? $_GET['action'] : '');

// Variable
$lp_id = Security::remove_XSS($_GET['lp_id']);
// Lp object
if (isset($_SESSION['lpobject'])) {
    if ($debug > 0)
        error_log('New LP - SESSION[lpobject] is defined', 0);
    $oLP = unserialize($_SESSION['lpobject']);
    if (is_object($oLP)) {
        if ($debug > 0)
            error_log('New LP - oLP is object', 0);
        if ($myrefresh == 1 OR (empty($oLP->cc)) OR $oLP->cc != api_get_course_id()) {
            if ($debug > 0)
                error_log('New LP - Course has changed, discard lp object', 0);
            if ($myrefresh == 1) {
                $myrefresh_id = $oLP->get_id();
            }
            $oLP = null;
            api_session_unregister('oLP');
            api_session_unregister('lpobject');
        } else {
            $_SESSION['oLP'] = $oLP;
            $lp_found = true;
        }
    }
}
// Add the extra lp_id parameter to some links
$add_params_for_lp = '';
if (isset($_GET['lp_id'])) {
    $add_params_for_lp = "&amp;lp_id=" . $lp_id;
}

global $linkCounter, $catcounter;
$linkCounter = 1;

$link_id = Security::remove_XSS($_GET['id']);
isset($_REQUEST['category_id']) ? $categoryId = Security :: remove_XSS($_REQUEST['category_id']) : $categoryId = '';
$disporder = Security :: remove_XSS($_GET['disporder']);
$type = Security :: remove_XSS($_GET['type']);
$itemId = Security::remove_XSS($_GET["itemId"]);

//display_link_form
// link_to_course
if (api_is_allowed_to_edit()) {
    $htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {

      $(".display_link_form").click(function () {
        var mylink_id = $(this).attr("id");
        $.ajax({
            url: "' . api_get_path(WEB_AJAX_PATH) . 'link.ajax.php?' . api_get_cidreq() . '&"+mylink_id,
            success: function(data){
                $(".link_to_course_dialog").html(data);
                $(".link_to_course_dialog").dialog({
                modal: true,
                title: "' . get_lang('IntegrateLinkInCourse') . '",
                width: 500,
                height : "auto",
                position : "center",
                resizable:false
                }); 
            }   
        });
     });
    
    // toogle category
    $(".toogle-category").click(function() {
      var thisId = $(this).attr("id");
      var explodedId = thisId.split("-");
      var categoryId = parseInt(explodedId[3]);
      $(".category_"+categoryId).toggle("slow", function() {
        // Animation complete.
        if ($(this).css("display") == "none") {
            $("#toogle-icon-category-"+categoryId+" img").attr("src", "' . api_get_path(WEB_IMG_PATH) . 'action-slide-down.png");
        }
        else {
            $("#toogle-icon-category-"+categoryId+" img").attr("src", "' . api_get_path(WEB_IMG_PATH) . 'action-slide-up.png");
        }        
      });
    });
    
    var category_before = "";
    var category_after = "";
    var link_sorted = 0;
    $(".category_0, #categories ul").sortable({
       //Allow to put any link in any category
       connectWith: ".category_0, #categories ul",
       handle   :   $(".move1"),
       cursor   :  "move",
       cancel: ".nodrag",
       
       //Event thrown when the drag n drop start
       start: function(event, ui) {
            parentElement = ui.item.parent();
            //We keep into the memory the current link category
            category_before = getCategoryId(parentElement);
            //var viewParameter = getUrlViewParameter();	
            /*if(category_before == 0 && viewParameter == "") {
                window.location.href = "index.php?urlview=1111111111&fold=Y";
                return;
            }*/	
       },

       //Event thrown when a link is moved in another category
       receive: function(event, ui) {

            parentElement = ui.item.parent();
            //Check if the parent ul has a category
            //We will compare the id of the parent category and the category id after moved
            category_after = getCategoryId(parentElement);
            itemId = getItemId(ui.item);
            if (!category_after) {
                category_after = 0;
            }
            //alert(category_before);

            var order = $(this).sortable("serialize") + "&amp;action=updateRecordsListings";
            var record = order.split("&");
            var recordlen = record.length;
            var disparr = new Array();
            for(var i=0;i<(recordlen-1);i++){
             var recordval = record[i].split("=");
             disparr[i] = recordval[1];
            }

            //Links category has changed
            if (!category_after) {
                category_after = 0;
                link_sorted = 1;
            } 
            if(category_before != category_after){                   
                   $.get("' . api_get_path(WEB_AJAX_PATH) . 'link.ajax.php", {cidReq: "' . api_get_course_id() . '", action:"updateRecordsListings", disporder: disparr, itemId: itemId, category_id: category_after, type: "item"});
                    //window.location.href = "index.php?action=updateRecordsListings&amp;itemId="+itemId+"&amp;category_id="+category_after+"&disporder="+disparr+viewString;	
            }

       },

       stop: function(event, ui) {       
        var order = $(this).sortable("serialize") + "&amp;action=updateRecordsListings";
        var record = order.split("&");
        var recordlen = record.length;
        var disparr = new Array();
        for(var i=0;i<(recordlen-1);i++){
         var recordval = record[i].split("=");
         disparr[i] = recordval[1];
        }        
        var parentElement = ui.item.parent();
        var categoryId = getCategoryId(parentElement);
        var itemId = getItemId(ui.item);
        //Update the links order in the category
        if (!category_after && !link_sorted) {
            $.get("' . api_get_path(WEB_AJAX_PATH) . 'link.ajax.php", {cidReq: "' . api_get_course_id() . '", action:"updateRecordsListings", disporder: disparr, itemId: itemId, category_id: categoryId, type: "item"});
        }
       }
    }).disableSelection();

    //Allow th change the categories order
  $("#categories").sortable({
    connectWith: "#categories",
    cursor   :  "move",
    handle   :  $(".move"),
    cancel: ".nodrag",
    update: function(event, ui) {
    var order = $(this).sortable("serialize") + "&amp;action=updateRecordsListings";
    var record = order.split("&");
    var recordlen = record.length;
    var disparr = new Array();
    for(var i=0;i<(recordlen-1);i++)
    {
     var recordval = record[i].split("=");
     disparr[i] = recordval[1];
    }
    //update the order of all categories
    $.get("' . api_get_path(WEB_AJAX_PATH) . 'link.ajax.php", {cidReq: "' . api_get_course_id() . '", action:"updateRecordsListings", disporder: disparr, type: "categories"});
   }
  }).disableSelection(); 


});

function getCategoryId(parentElement){
    if(parentElement.is("[class*=\'category\']")) {
        var classList =$(parentElement).attr("class").split(/\s+/);
        var category_id;
        for(var i= 0; i < classList.length; i++){
            var index = classList[i].indexOf("category_");
            if (index != -1) {
             return classList[i].substr(9,classList[i].length);
            }
        }
        return;
    }
 }
 
function getItemId(item){
    var arrayTemp = $(item).attr("id").split("_");
    return arrayTemp[1];
}

function getUrlViewParameter(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf("?") + 1).split("&");
    for(var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split("=");
        if(hash[0] == "urlview"){
            return hash[1];
        }
    }
    return "";
}

 </script>';
}

$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {

	$("#div_target").attr("style","display:none;")//hide

	$("#id_check_target").click(function () {		
      if($("#id_check_target").attr("checked")) {
      	$("#div_target").attr("style","display:block;")//show
      } else {
     	 $("#div_target").attr("style","display:none;")//hide
      }
    });

    $(window).load(function () {
      if($("#id_check_target").attr("checked")) {
      	$("#div_target").attr("style","display:block;")//show
      } else {
     	 $("#div_target").attr("style","display:none;")//hide
      }
    });

 } );

 </script>';

// get actions
$actions = array('listing', 'addcategory', 'editcategory', 'deletecategory', 'addlink', 'editlink', 'deletelink', 'visible', 'invisible', 'integrateincourse', 'updateRecordsListings');
$action = 'listing';
if (isset($_GET['action']) && in_array($_GET['action'], $actions)) {
    $action = $_GET['action'];
}

// work controller object
$linkController = new LinkController($link_id);

// distpacher actions to controller
switch ($action) {
    case 'listing':
        $linkController->listing();
        break;
    case 'addcategory':
        $linkController->addcategory();
        break;
    case 'editcategory':
        $linkController->editcategory($categoryId);
        break;
    case 'deletecategory':
        $linkController->deletecategory($categoryId);
        break;
    case 'addlink':
        $linkController->addlink($add_params_for_lp);
        break;
    case 'editlink':
        $linkController->editlink($add_params_for_lp);
        break;
    case 'deletelink':
        $linkController->deletelink();
        break;
    case 'visible':
        $linkController->changeVisibility();
        break;
    case 'invisible':
        $linkController->changeVisibility();
        break;
    case 'integrateincourse':
        $linkController->integrateLinkInCourse();
        break;
    case 'updateRecordsListings':
        $linkController->updateRecordsListings($disporder, $type);
        break;
    default:
        $linkController->listing();
}
?>