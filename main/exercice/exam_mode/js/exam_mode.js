$(window).load(function(){
    //resizePlayer();
    if ($(".matching-drag").length > 0) {
        equalHeight($(".matching-drag"));        
    }
});
    
$(document).ready(function(e) {
    var cidReq = decodeURIComponent($("#cidReq").val());
    var webPath = decodeURIComponent($("#webPath").val());
    if ($(".question-answers input").length > 0) {
        $(".question-answers input[type='radio'], .question-answers input[type='checkbox']").click(function(e) {            
            saveQuiz(webPath, cidReq);
        })
        $(".question-answers input[type='text']").keyup(function(e) { 
            saveQuiz(webPath, cidReq);          
        })
    }    
    if ($(".print_textarea").length > 0) {
        CKEDITOR.instances["newchoice"].on('key', function(evt) {
            saveQuiz(webPath, cidReq);
        });
    }    
    $(".quizSubmit").click(function(e) {
        e.preventDefault();       
        var heading = 'Confirmation';
        var question = 'Please confirm that you wish to complete this quiz';
        var cancelButtonTxt = 'Cancel';
        var okButtonTxt = 'Confirm';
        var exerciseId = $("#exerciseId").val();
        var callback = function() {
          $.post(webPath+"main/exercice/exam_mode/exam_mode.ajax.php?action=complete&"+cidReq, $("#quiz-form").serialize(), function(data) {
              var exeId = parseInt(data);
              location.href = webPath+"main/exercice/exam_mode/exam_mode_result.php?"+cidReq+"&exerciseId="+exerciseId+"&exeId="+exeId+"&status=complete";
          });
        };
        confirm(heading, question, cancelButtonTxt, okButtonTxt, callback);       
    });    
    
    // jwplayer response design
    resizePlayer();
    $(window).resize(function() {
        resizePlayer();
    });
});

// Listen for orientation changes
window.addEventListener("orientationchange", function() {
	resizePlayer();
}, false);
// Listen for resize changes
window.addEventListener("resize", function() {
	resizePlayer();	
}, false);

function saveQuiz(webPath, cidReq) {    
    CKupdate();
    $.post(webPath+"main/exercice/exam_mode/exam_mode.ajax.php?action=save&"+cidReq, $("#quiz-form").serialize(), function(data) {
        $('.result').html(data);
    });
}

function CKupdate() {
    if ($(".print_textarea").length > 0) {
        for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
    }
}

function confirm(heading, question, cancelButtonTxt, okButtonTxt, callback) {
    var confirmModal = $('<div class="modal hide fade">' +    
                            '<div class="modal-header">' +
                              '<a class="close" data-dismiss="modal" >&times;</a>' +
                              '<h3>' + heading +'</h3>' +
                            '</div>' +

                            '<div class="modal-body">' +
                              '<p>' + question + '</p>' +
                            '</div>' +

                            '<div class="modal-footer">' +
                              '<a href="#" class="btn" data-dismiss="modal">' + 
                                cancelButtonTxt + 
                              '</a>' +
                              '<a href="#" id="okButton" class="btn btn-primary">' + 
                                okButtonTxt + 
                              '</a>' +
                            '</div>' +
                          '</div>');
    confirmModal.find('#okButton').click(function(event) {
        callback();
        confirmModal.modal('hide');
    });
    confirmModal.modal('show');    
  };
  
  function resizePlayer() {
    var config, current_player, screenSize, newVideoWidth, newVideoHeight, player_id, config_temp, config_split, player_container;  
    // video players  
    if ($(".thePlayer").length > 0) { 
        $(".thePlayer").each(function() {     
            current_player = $(this);
            screenSize = current_player.parents('div[class^="span5"]').width();
            config = current_player.parent().parent().find("div[id$=-config]").text();
            config = config.split(" ");
            config_temp = new Array();
            for (var i = 0; i < config.length; i++) {
                config_split = config[i].split("=");
                config_temp[config_split[0]] = config_split[1];
            }
            config = config_temp;
            if (screenSize > config['width']) {screenSize = config['width'];}
            newVideoWidth = screenSize;
            newVideoHeight = (screenSize * 270) / 480;
            player_id = current_player.attr("id")+"-parent2";
            player_container = current_player.attr("id")+"-parent2_wrapper";
            $("#"+player_id).width(newVideoWidth);
            $("#"+player_id).height(newVideoHeight);
            $("#"+player_container).width(newVideoWidth);
            $("#"+player_container).height(newVideoHeight);
        });        
    }  
    // audio players
    if ($(".theAudioPlayer").length > 0) { 
        $(".theAudioPlayer").each(function() {                 
            current_player = $(this);
            screenSize = current_player.parents('div[class^="span5"]').width();
            config = current_player.parent().parent().find("div[id$=-config]").text();
            config = config.split(" ");
            config_temp = new Array();
            for (var i = 0; i < config.length; i++) {
                config_split = config[i].split("=");
                config_temp[config_split[0]] = config_split[1];
            }
            config = config_temp;
            if (screenSize > config['width']) {screenSize = config['width'];}
            newVideoWidth = screenSize;
            player_id = current_player.attr("id")+"-parent2";
            player_container = current_player.attr("id")+"-parent2_wrapper";
            $("#"+player_id).width(newVideoWidth);
            $("#"+player_container).width(newVideoWidth);
        });        
    }  
  }
  
function getMaxHeight(group) {
   var thisHeight, tallest = 0;            
   group.each(function(i) {
      thisHeight = $(group[i]).height();
      if(thisHeight > tallest) {
         tallest = thisHeight;         
      }      
   });   
   return tallest;
}



function equalHeight(columns) {   
   columns.each(function(i) {
      var classes = $(this).attr("class");
      var explodClasses = classes.split(' ');
      var questionClassName = "matching-drag-"+parseInt(explodClasses[2].replace("matching-drag-", ""));
      $("."+questionClassName).height(getMaxHeight($("."+questionClassName)));     
   });
}

function selectMatchingAnswer(questionId, ans, ind) {
    var cntOption = $("input[name=\'cntOption-"+questionId+"\']").val();	
    var dragid = "a"+questionId+"-"+ans;		
    var ansidarr = dragid.split("-");
    var ansid = ansidarr[1];
    var dropid = "q"+questionId+"-"+ind;
    var numericIdarr = dropid.split("-");
    var numericId = numericIdarr[1];
    var ansOption = (numericId*1) + (cntOption*1);
    var answer = document.getElementById("choice["+questionId+"]["+ansid+"]").value;
    var h = $(".matching-drag-"+questionId).height();
    $("#"+dropid).html("<div class=\"drop-answer\">"+answer+"</div>"); 
    $(".drop-answer").css("border", "none");
    $(".drop-answer").parent().css({"border":"1px solid #000","background-color":"#FFF"});
    document.getElementById("choice["+questionId+"]["+ansOption+"]").value = ansid;
}