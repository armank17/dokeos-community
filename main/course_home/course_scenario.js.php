<?php
// $cidReset is mandatory
//$cidReset= TRUE;
require_once '../../main/inc/global.inc.php';
?>
jQuery(document).ready( function($) {

	var myglobal, mydropdown;

	var numCols = $("#mtable").find('tr')[0].cells.length;
	

	if(numCols > 6){
		$("#content-slider").show();				
		$("#content-scroll").animate({scrollLeft: 0 }, 200);
	}
	$("#content-scroll").animate({scrollLeft: 0 }, 200);
	$("#addcolumn").click(function() {
		
		var step_no = $("#hid_numcols").val();
		if(step_no > 0){
		$('#mtable tr').append($("<td>"));		
		$('#mtable tbody tr').each(function(){$(this).children('td:last').append()});
		}
		//$('#mtable tr:eq(5) td:last').html('<img class="sample" alt="plus" src="../img/add_32.png">');
		var oRows = document.getElementById('mtable').getElementsByTagName('tr');
		var iRowCount = oRows.length;
		var step_color = $("#default_step_color").val();
		step_color = step_color.replace("#", "");

		var hide_border = $("#hid_hide_border").val();
		if(hide_border == 1){
			var border_px = "border:0px";
		}
		else {
			var border_px = "";
		}
		
		var numCols = $("#mtable").find('tr')[0].cells.length;

		if(navigator.userAgent.indexOf(".NET") != -1){
			if(step_no == 0){
			numCols = 2;
			}
			else {
			numCols = numCols + 1;
			}
		}

		var iIndex = $('#mtable tr:eq(0) td:eq('+(numCols - 2)+')').html();
		if(iIndex == '&nbsp;' || iIndex == ''){		
			//$('#mtable tr td:eq('+(numCols - 2)+')').remove();
			numCols = numCols - 1;
		}	
		
		$.ajax({
            type: "GET",
            url: "get_resources.php?action=get_steps_dropdown&colIndex="+(numCols - 1),
			async: false
          }).done(function(data) {
				 mydropdown = data;		 
         });		

		$.ajax({
		  type: "GET",
		  url: "add_step.php?numCols="+(numCols - 1)+"&step_color="+step_color+"&hide_border="+hide_border,
		  success: function(data){
				
				$("#hid_numcols").val((numCols - 1));
			  //New step created
			    $('#mtable tr:eq(0) td:eq('+(numCols - 1)+')').html('<div id="icondiv_'+(numCols - 1)+'" class="icon_block" style="border-color:#'+step_color+';'+border_px+'"><div class="icon_block_120_80"><img style="display: block; margin: 0 auto;vertical-align:middle;text-align:center;height:80px;" src="icons/00.png" /></div></div>');
				$('#mtable tr:eq(1) td:eq('+(numCols - 1)+')').html('<div class="div_txt" id="step_'+(numCols - 1)+'"><?php echo addslashes(get_lang("Step")).' '; ?>'+(numCols - 1)+'</div>&nbsp;<span class="edit_step" id="step_'+(numCols - 1)+'" style="padding-left:80px;"><?php echo Display::return_icon('pixel.gif', addslashes(get_lang('EditStep')), array('class' => 'actionplaceholdericon actionediticon')); ?></span><span class="delete_step" id="delete_'+data+'_'+(numCols - 1)+'" ><?php echo Display::return_icon('pixel.gif', addslashes(get_lang('DeleteStep')), array('class' => 'actionplaceholdericon actiondeleteicon')); ?></span>');
				$('#mtable tr:eq(2) td:eq('+(numCols - 1)+')').html('<script type="text/javascript" src="js/jscolor.js"></script><input class="color" id="stepcolor_'+(numCols - 1)+'" onchange="changeColor(this, this.color)"><script type="text/javascript">var myPicker = new jscolor.color(document.getElementById("stepcolor_'+(numCols - 1)+'"), {});myPicker.fromString("#'+step_color+'");  </script>');
				$('#mtable tr:eq(3) td:eq('+(numCols - 1)+')').html('<div id="prereq_select_'+(numCols - 1)+'">'+mydropdown+'</div>');
                                $('#mtable tr:eq(4) td:eq('+(numCols - 1)+')').html('<div class="completion_class"><?php echo addslashes(get_lang("Free")); ?></div>&nbsp;<span class="edit_criteria" id="+data+" style="padding-left:80px;"><?php echo Display::return_icon('pixel.gif', addslashes(get_lang('EditCriteria')), array('class' => 'actionplaceholdericon actionediticon')); ?></span><span class="delete_criteria" id="+data+" ><?php echo Display::return_icon('pixel.gif', addslashes(get_lang('DeleteCriteria')), array('class' => 'actionplaceholdericon actiondeleteicon')); ?></span>');		
                                $('#mtable tr:eq(5) td:eq('+(numCols - 1)+')').html('<div class="center"><img class="sample" title="<?php echo addslashes(get_lang("MoreActivities")); ?>" alt="plus" style="display:none;" src="images/add.png"><button id="plus" class="savemore" name="plus"><?php echo addslashes(get_lang('MoreActivities')); ?></button></div>');
				//$('#mtable tr:eq(5) td:eq('+(numCols - 1)+')').attr('style','text-align:center');
				if(iRowCount > 6) {			
					for(k=5;k<iRowCount;k++) {
						$('#mtable tr:eq('+k+') td:eq('+(numCols - 1)+')').attr('class','tdbg');
					}
				}


				var tmp = numCols - 1;

				if(tmp > 0){
					for(k=1;k<=tmp;k++){

						$('#mtable tr td:nth-child('+(k+1)+')').removeClass();
						$('#mtable tr td:nth-child('+(k+1)+')').attr('class','tdstatic');
					}
				}
				//$('#mtable tr:eq(0) td:eq(0)').addClass('highlighted');
				//$('#mtable tr td:nth-child('+(numCols)+')').removeClass();
				//$('#mtable tr td:nth-child('+(numCols)+')').attr('class','highlighted');
				//$('#mtable tr:eq(1) td:eq('+(numCols - 1)+')').removeClass();
				//$('#mtable tr:eq(1) td:eq('+(numCols - 1)+')').attr('class','highlighted');					
				
				if(numCols > 6) {
					$("#content-slider").show();
					$("#content-slider").slider("value","100");
				}
				
				if(numCols > 2) {
					/*var scroll_width = $('.double-scroll')[0].scrollWidth;
					var final_width = scroll_width - 100;
					$(".suwala-doubleScroll-scroll").css("width",scroll_width);
					$(".double-scroll").animate({scrollLeft : + final_width },'fast');*/
				}
		  }
	  });
          window.location.reload();
	});
	
	//$('.div_txt').editable('updatedata.php');	
	
	$(".sample1").live("click",function() {	

		var oRows = document.getElementById('mtable').getElementsByTagName('tr');
		var iRowCount = oRows.length;

		var tmp = (iRowCount*1) - 1;

		$('#mtable tbody').append($("#mtable tbody tr:eq("+tmp+")").clone());
		$("#mtable tbody tr:eq("+tmp+") td:eq(1)").html('MYTEXTCHOSEN');
		$("#mtable tbody tr:last td").html('');
		$('#mtable tr:last td:eq(1)').html('<div class="center"><img class="sample" title="<?php echo get_lang("MoreActivities"); ?>" alt="plus" style="display:none;" src="images/add.png"><button id="plus" class="savemore" name="plus"><?php echo get_lang('MoreActivities'); ?></button></div>');
		//$('#mtable tr:last td:eq(1)').attr('style','text-align:center');

		var numCols = $("#mtable").find('tr')[0].cells.length;

		/*for(i=0;i<numCols;i++){
			if(i>1){
			$("#mtable tbody tr:eq("+tmp+") td:eq("+i+")").text($("#mtable tbody tr:last td:eq("+i+")").remove().text()).attr('rowspan','2');
			}

		}*/
		
	});

	

	/*$("#mtable td").live("mouseover",function(e) {
		var colIndex = $(this).parent().children().index($(this));
		var rowIndex = $(this).parent().parent().children().index($(this).parent());
		if(rowIndex == 1) {

			$('#step_'+colIndex).editable('updatedata.php', {
				indicator : 'Saving...',
				style   : 'display:block;',
				type : 'textarea',
				event     :   'mouseover',
				onblur    :   'submit',
				submitdata : function() {
					return {"row_id" : rowIndex,"col_id" : colIndex }; 
				},
			"height": "55px",
			"width": "175px",
			});
		}
	});*/

	/*$('#mtable td').live("hover",function(){
		var colIndex = $(this).parent().children().index($(this));
		for(k=1;k<colIndex;k++){
			$('#mtable tr td:nth-child('+(k)+')').removeClass();
			$('#mtable tr td:nth-child('+(k)+')').attr('class','tdstatic');
		}

		$('#mtable tr td:nth-child('+(colIndex + 1)+')').removeClass();
		$('#mtable tr td:nth-child('+(colIndex + 1)+')').attr('class','highlighted');
	});*/
		

	$('#mtable td').live("click",function(e){

    var colIndex = $(this).parent().children().index($(this));
    var rowIndex = $(this).parent().parent().children().index($(this).parent());
    //alert('Row: ' + rowIndex + ', Column: ' + colIndex);

	var godid = $('#del_activity').val();
	var stepid_chkdel = $('#del_step').val();
	var criteria_chkdel = $('#del_criteria').val();

	//var step_name = testAjax(colIndex);
	$.ajax({
            type: "GET",
            url: "myfile.php?param="+colIndex,
			async: false
          }).done(function(data) {
				 myglobal = data;		 
         });	

	if(rowIndex == 1 && stepid_chkdel != '1') {

			$('#step_'+colIndex).editable('update_table.php', {
				indicator : 'Saving...',
				style   : 'display:block;',
				type : 'textarea',
				event     :   'click',
				onblur    :   'submit',
				submitdata : function() {
					return {"colIndex" : colIndex,"action" : "update_stepname" }; 
				},
			"height": "55px",
			"width": "125px"
			});
	}
	else {
		$('#del_step').val("0");
	}

	if(rowIndex == 3) {
		//$('#prereq1_select_'+colIndex).live("change",function(){
			var preoption = $('#prereq1_select_'+colIndex).val();

			$.ajax({
			  type: "GET",
			  url: "update_table.php?action=update_prerequisite&colIndex="+colIndex+"&preoption="+preoption,
			  success: function(data1){	


				}
				});
		//});
		/*$.ajax({
		  type: "GET",
		  url: "get_resources.php?action=get_steps&colIndex="+colIndex,
		  success: function(data1){	

			$('#prereq_select_'+colIndex).editable('update_table.php', {
				type: 'select',
				data: data1,
				event     :   'click',
				onblur    :   'submit',
				submitdata : function() {
					return {"colIndex" : colIndex,"action" : "update_prerequisite" }; 
				},			
			});
				
			
		  }
	  });  */
		
	}
	
	
	var row_class = $('#mtable tbody tr:eq('+rowIndex+')').attr('class');
	var img_class = $('#mtable tr:eq('+rowIndex+') td:eq('+colIndex+') img').attr('class');
	

	/*if(img_class == 'delete_class'){

		$('#mtable tr:eq('+rowIndex+') td:eq('+colIndex+')').hide();
	}*/

	if(img_class == 'sample') {

		$('#mtable tbody').append($("#mtable tbody tr:eq(0)").clone());		
		$("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html('<?php echo get_lang("EmptyActivity"); ?>&nbsp;<div><span class="edit_activity" style="padding-left:90px;"><?php echo Display::return_icon('pixel.gif', get_lang('EditActivity'), array('class' => 'actionplaceholdericon actionediticon')); ?></span>&nbsp;<span class="delete_activity" ><?php echo Display::return_icon('pixel.gif', get_lang('DeleteActivity'), array('class' => 'actionplaceholdericon actiondeleteicon')); ?></span></div>');
		if ($("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").hasClass("tdbg")) {
			$("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").removeClass("tdbg");
			$("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").addClass("tdstatic");
		}
		$("#mtable tbody tr:last td").html('');
		$('#mtable tr:eq('+(rowIndex + 1)+') td:eq('+colIndex+')').html('<div class="center"><img class="sample" title="<?php echo get_lang("MoreActivities"); ?>" alt="plus" style="display:none;" src="images/add.png"><button id="plus" class="savemore" name="plus"><?php echo get_lang('MoreActivities'); ?></button></div>');
		$('#mtable tr:eq('+(rowIndex + 1)+') td:eq(0)').html('<hr style="margin:0px !important;color:#CCC;"><?php echo get_lang("Activity")." "; ?>'+(rowIndex - 3));
		$('#mtable tr:eq('+(rowIndex + 1)+')').attr('class','activity_class');
		//$('#mtable tr:eq('+(rowIndex + 1)+') td:eq('+colIndex+')').attr('rowspan','2');

		var oRows = document.getElementById('mtable').getElementsByTagName('tr');
		var iRowCount = oRows.length;


		var numCols = $("#mtable").find('tr')[0].cells.length;


		for(i=0;i<numCols;i++){
			for(j=0;j<iRowCount;j++) {
				var text = $('#mtable tr:eq('+j+') td:eq('+i+')').html();

				if($('#mtable tr:eq('+j+') td:eq('+i+') img[alt="plus"]').length){				
					if(j < (iRowCount-1)){
						for(k=j;k<iRowCount;k++) {
						$('#mtable tr:eq('+k+') td:eq('+i+')').attr('class','tdbg');
						}
						//$("#mtable tbody tr:eq("+j+") td:eq("+i+")").text($("#mtable tbody tr:eq("+j+") td:eq("+i+")").remove().text());
						//$('#mtable tr:eq('+(j+1)+') td:eq('+i+')').hide();
						//$('#mtable tr:eq('+(j+1)+') td:eq('+i+')').hide();
						//$('#mtable tr:eq('+j+') td:eq('+i+')').attr('rowspan','2');
						//$('#mtable tr:eq('+(j+1)+') td:eq('+(i+1)+')').remove();
					}
				}
			}
		}

		var emptyRow;
		for(m=7;m<iRowCount;m++) {
		emptyRow = "Y";
			for(n=1;n<numCols;n++){
				var samtext = $('#mtable tr:eq('+m+') td:eq('+n+')').html();
				if(samtext != ""){emptyRow = "N";}
			}
			if(emptyRow == "Y"){			
			$('#mtable tr:last').remove();
			}
		}

		/*$.ajax({
		  type: "GET",
		  url: "resources.php?rowIndex="+rowIndex+"&colIndex="+colIndex,
		  success: function(data){	

			  $(".scenario_dialog").html(data);
			  $(".scenario_dialog").dialog({
									modal: true,
									title: "Resources",
									width: 800,
									height : 400,
									resizable:false
				}); 
		  }
	  });  */

		
	}	


	if(row_class == 'icon_class'){
	
	var theme_color = $("#default_step_color").val();
	theme_color = theme_color.replace("#", "");
	if(colIndex != '0'){
			$.ajax({
			  type: "GET",
			  url: "get_popup_icons.php?rowIndex="+rowIndex+"&colIndex="+colIndex+"&theme_color="+theme_color,
			  success: function(data){	
				  $(".scenario_dialog").html(data);
				  $(".scenario_dialog").dialog({
							open: function(event, ui) {  
								jQuery('.ui-dialog-titlebar-close').css("width","85px");
								jQuery('.ui-dialog-titlebar-close').html('<span style="float:right;margin-right:5px;"><?php echo get_lang('CloseX'); ?></span>');  											
							},
							modal: false,
							title: "<?php echo get_lang('IconsGallery'); ?>",
							width: 800,
							height : 600,
							resizable:false

					}); 
				

			  }
		  });  
		  }
	}

	if(row_class == 'completion_class' && criteria_chkdel == '0'){
			var theme_color = $("#default_step_color").val();
			var numCols = $("#mtable").find('tr')[0].cells.length;

			$.ajax({
			  type: "GET",
			  url: "get_completion.php?rowIndex="+rowIndex+"&colIndex="+colIndex,
			  success: function(data){	

				  $("#content-slider").hide();	
				  $(".scenario_dialog").html(data);
				  $(".scenario_dialog").dialog({
										open: function(event, ui) {  
										jQuery('.ui-dialog-titlebar-close').css("width","85px");										
										jQuery('.ui-dialog-titlebar-close').html('<span style="float:right;margin-right:5px;"><?php echo get_lang('CloseX'); ?></span>');  		
										$("#min-score").select2({minimumResultsForSearch: -1});
									},
										modal: true,
										title: "<?php echo get_lang('Completion'); ?>",
										width: 500,
										height : 320,
										resizable:false,
										close: function(event, ui) {
											//window.location.reload();
											if(numCols > 6){
											$("#content-slider").show();
											}
											$('.ui-slider-handle').css('width','70px');
											$('.ui-slider-handle').css('height','23px');
											$('.ui-slider-handle').css('background',theme_color);
										}
					}); 
			  }
		  });  
	}

	if(row_class == 'activity_class' && godid == '0') {
		 //var screen_width = $(window).width();
		 //var screen_height = $(window).height();
		 //var tmp_width = (screen_width / 3) - 120;
		 //var tmp_height = (screen_height / 3) - 70;
		var tmpRow = rowIndex - 4;
		
		var id = $('#activity_'+colIndex+'_'+tmpRow).val();
		
		if(id === undefined){
			id = 0;
		}

		$.ajax({
		  type: "GET",
		  url: "resources.php?rowIndex="+rowIndex+"&colIndex="+colIndex+"&param="+id,
		  success: function(data){	
			  //var screen_width = $(window).width();
			  //var tmp_width = (screen_width / 3) - 140;

			  $(".scenario_dialog").html(data);
			  $(".scenario_dialog").dialog({
									open: function(event, ui) {  
										jQuery('.ui-dialog-titlebar-close').css("width","85px");
										jQuery('.ui-dialog-titlebar-close').html('<span style="float:right;margin-right:5px;"><?php echo get_lang('CloseX'); ?></span>');  											
									},
									modal: false,
									title: myglobal,
									width: 850,
									height : 450,
									resizable:false
									
				}); 
			
		  }
	  }); 
	  
	}
	else {
		$('#del_activity').val("0");
	}

});

$('#add_gallery_icons').live("click",function(){	
	$("#add_gallery_icons").hide();
	$("#upload_form_div").show();
	$("#image_file").focus();	
});



$('#close_upload').live("click",function(){	
	var rowIndex = $('#rowIndex').val();
	var colIndex = $('#colIndex').val();

	$("#upload_form_div").hide();
	$('.scenario_dialog').dialog('close');
	$.ajax({
			  type: "GET",
			  url: "get_icons.php?rowIndex="+rowIndex+"&colIndex="+colIndex,
			  success: function(data){	
				  $(".scenario_dialog").html(data);
				  $(".scenario_dialog").dialog({
										modal: true,
										title: "Icons",
										width: 800,
										height : 600,
										resizable:false										
					});
				
				
			  }
		  });  
	/*$.ajax({
		  type: "GET",
		  url: "get_icons.php",
		  success: function(data){	

			 $("#upload_form_div").html(data); 
		  }
	  });  */
});

$('.icon_display').live("click",function(){

	var rowIndex = $("#rowIndex").val();
	var colIndex = $("#colIndex").val();
	
	var src = $(this).attr("id");	
	$("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+") img").attr('src',src);
	 $('.scenario_dialog').dialog('close');
	
	$.ajax({
		  type: "GET",
		  url: "update_table.php?action=update_icons&colIndex="+colIndex+"&src="+src,
		  success: function(data){	
		  
			 //window.location.reload();
		  }
	  });
});

$("#submitbtn").live("click",function(e) {

	if ($("#checkbox2").is(":checked"))
	{		
		var option = "Quiz";
	  // it is checked
	}
	else {	
		var option = "Module";
	}	
	if ($("#checkbox3").is(":checked"))
	{		
		var sub_option = "Progress";
	  // it is checked
	}
	else {	
		var sub_option = "Score";
	}	
	var min_score = $("#min-score").val();
	var colIndex = $("#colIndex").val();
	var rowIndex = $("#rowIndex").val();	
	
	var text = option + ":" + sub_option + ":" + min_score;
	var display_text = option + " : " + min_score + " %";

	$.ajax({
		  type: "GET",
		  url: "update_table.php?action=update_completion&colIndex="+colIndex+"&text="+text+"&display_text="+display_text,
		  success: function(data){	
			  $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
			  $('.scenario_dialog').dialog('close');
		  }
	  });
	
	e.preventDefault();
});

$('#doc_resourses').live("click",function(e){
	$('#navigation ul.top-level li').removeClass("active");
	$(this).addClass("active");

	$.ajax({
		  type: "GET",
		  url: "get_resources.php?action=get_doc_list",
		  success: function(data){	
			 $("#right").html(data); 
		  }
	  }); 
	  e.preventDefault();
});
$('#page_resourses').live("click",function(e){
	$('#navigation ul.top-level li').removeClass("active");
	$(this).addClass("active");

	$.ajax({
		  type: "GET",
		  url: "get_resources.php?action=get_page_list",
		  success: function(data){	
			 $("#right").html(data); 
		  }
	  }); 
	  e.preventDefault();
});
$('#quiz_resourses').live("click",function(e){
	$('#navigation ul.top-level li').removeClass("active");
	$(this).addClass("active");

	$.ajax({
		  type: "GET",
		  url: "get_resources.php?action=get_quiz_list",
		  success: function(data){	

			 $("#right").html(data); 
		  }
	  });  
	  e.preventDefault();
});

$('#exam_resourses').live("click",function(e){
	$('#navigation ul.top-level li').removeClass("active");
	$(this).addClass("active");

	$.ajax({
		  type: "GET",
		  url: "get_resources.php?action=get_exam_list",
		  success: function(data){	

			 $("#right").html(data); 
		  }
	  });  
	  e.preventDefault();
});

$('#module_resourses').live("click",function(e){

	$('#navigation ul.top-level li').removeClass("active");
	$(this).addClass("active");

	$.ajax({
		  type: "GET",
		  url: "get_resources.php?action=get_modules_list",
		  success: function(data){	
			 $("#right").html(data); 
		  }
	  });  
	  e.preventDefault();
});

$('#assign_resourses').live("click",function(e){

	$('#navigation ul.top-level li').removeClass("active");
	$(this).addClass("active");

	$.ajax({
		  type: "GET",
		  url: "get_resources.php?action=get_assignment_list",
		  success: function(data){	
			 $("#right").html(data); 
		  }
	  }); 
	  e.preventDefault();
});

$('#survey_resourses').live("click",function(e){
	$('#navigation ul.top-level li').removeClass("active");
	$(this).addClass("active");

	$.ajax({
		  type: "GET",
		  url: "get_resources.php?action=get_survey_list",
		  success: function(data){	
			 $("#right").html(data); 
		  }
	  });  
	  e.preventDefault();
});

$('#facetoface_resourses').live("click",function(e){
	$('#navigation ul.top-level li').removeClass("active");
	$(this).addClass("active");
	var colIndex = $("#colIndex").val();
	var rowIndex = $("#rowIndex").val();
	var param_id = $("#param_id").val();

	$.ajax({
		  type: "GET",
		  url: "get_resources.php?action=get_facetoface_list&rowIndex="+rowIndex+"&colIndex="+colIndex+"&param_id="+param_id,
		  success: function(data){	
			 $("#right").html(data); 
		  }
	  });  
	  e.preventDefault();
});

$('.delete_activity').live("click",function(){

	var id = $(this).attr('id');

	$("#del_activity").val("1");

	$("#activity_dialog_box").dialog({
	open: function(event, ui) {  
		/*
			jQuery(".ui-dialog-titlebar-close").css("width","85px");
			jQuery(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\"><?php echo get_lang("CloseX"); ?></span>");								
			*/
			$(this).parent().children().children("a.ui-dialog-titlebar-close").hide(); 
		},
	   title: "<?php echo get_lang('DeleteActivity'); ?>",
	  width: 350,
	  height: 185,
	  modal: true,
	  resizable: false,
	  draggable: true,
	   buttons: {
			'<?php echo get_lang("No"); ?>' : function() {
				   $(this).dialog('close');
			 },
			'<?php echo get_lang("Yes"); ?>': function() { 
				 
				 if(id === undefined){
					window.location.reload();
				}
				else {
					   $.ajax({
						  type: "GET",
						  url: "update_table.php?action=delete_activity&id="+id,
						  success: function(data){	

							 window.location.reload();
						  }
					  });  
				  }
			 }
			 
	   }
	});
	
});

$('.delete_criteria').live("click",function(){

	var id = $(this).attr('id');

	$("#del_criteria").val("1");
	
	$.ajax({
		  type: "GET",
		  url: "update_table.php?action=delete_criteria&id="+id,
		  success: function(data){	

			 window.location.reload();
		  }
	  });  
});

$('.delete_step').live("click",function(){
	var id = $(this).attr('id');
	$("#del_step").val("1");

	$("#step_dialog_box").dialog({
	open: function(event, ui) {  
		/*
			jQuery(".ui-dialog-titlebar-close").css("width","85px");
			jQuery(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\"><?php echo get_lang("CloseX"); ?></span>");								
			*/
			$(this).parent().children().children("a.ui-dialog-titlebar-close").hide(); 							
		},
	   title: "<?php echo get_lang('DeleteStep'); ?>",
	  width: 350,
	  height: 185,
	  modal: true,
	  resizable: false,
	  draggable: true,
	   buttons: {
			'<?php echo get_lang("No"); ?>' : function() {
				   $(this).dialog('close');
			 },
			'<?php echo get_lang("Yes"); ?>': function() { // remove what you want to remove
				   $.ajax({
					  type: "GET",
					  url: "update_table.php?action=delete_step&id="+id,
					  success: function(data){	

						 window.location.reload();
					  }
				  });
			 }
			 
	   }
	});
});

$('.edit_step').live("click",function(){

	var id = $(this).attr('id');

	$("#"+id).trigger("click");
	$("#"+id).trigger("click");
});


$('.user_score').live("change",function(){
	var id = $(this).attr('id');

	var strtmp = id.split("_");		
	var step_id = strtmp[2];
	var user_id = strtmp[3];
	var face2face_id = strtmp[4];
	var score = $("#user_score_"+step_id+"_"+user_id+"_"+face2face_id).val();
	
	$.ajax({
		  type: "GET",
		  url: "update_table.php?action=update_score&step_id="+step_id+"&user_id="+user_id+"&face2face_id="+face2face_id+"&score="+score,
		  success: function(data){	

			if(data == 'completed'){
				 $("#img_"+step_id+"_"+user_id+"_"+face2face_id).attr("src","../img/checked.png");
			}			
		  }
	  });  

});


$('.doc_class').live("click",function(){

	var rowIndex = $('#rowIndex').val();
	var colIndex = $('#colIndex').val();

	var res_id = $(this).attr("id");

	if(res_id != ''){
				var doc_id = res_id.split("_");

				$.ajax({
				  type: "GET",
				  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&doc_id="+doc_id[1]+"&type=document",
				  success: function(data){

					 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
					 $('.scenario_dialog').dialog('close');
				  }
			  });
				
				
		}
	

	/*if($("input[type='radio'].doc_class").is(':checked')) {
        var doc_id = $(this).val();

		$.ajax({
		  type: "GET",
		  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&doc_id="+doc_id+"&type=document",
		  success: function(data){	
			 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
		  }
	  });
		
		$('.scenario_dialog').dialog('close');
}*/
});

$('.page_class').live("click",function(){

	var rowIndex = $('#rowIndex').val();
	var colIndex = $('#colIndex').val();

	var res_id = $(this).attr("id");

	if(res_id != ''){
				var page_id = res_id.split("_");

				$.ajax({
				  type: "GET",
				  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&page_id="+page_id[1]+"&type=page",
				  success: function(data){

					 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
					 $('.scenario_dialog').dialog('close');
				  }
			  });
				
				
		}
	

	
});

$('.quiz_class').live("click",function(){

	var rowIndex = $('#rowIndex').val();
	var colIndex = $('#colIndex').val();

	var res_id = $(this).attr("id");

	if(res_id != ''){
		var quiz_id = res_id.split("_");

		$.ajax({
		  type: "GET",
		  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&quiz_id="+quiz_id[1]+"&type=quiz",
		  success: function(data){	
			 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
			 $('.scenario_dialog').dialog('close');
		  }
	  });
		
		
	}
	
	/*if($("input[type='radio'].quiz_class").is(':checked')) {
        var quiz_id = $(this).val();

		$.ajax({
		  type: "GET",
		  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&quiz_id="+quiz_id+"&type=quiz",
		  success: function(data){	
			 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
		  }
	  });
		
		$('.scenario_dialog').dialog('close');
}*/
});

$('.exam_class').live("click",function(){

	var rowIndex = $('#rowIndex').val();
	var colIndex = $('#colIndex').val();

	var res_id = $(this).attr("id");

	if(res_id != ''){
		var exam_id = res_id.split("_");

		$.ajax({
		  type: "GET",
		  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&exam_id="+exam_id[1]+"&type=exam",
		  success: function(data){	
			 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
			 $('.scenario_dialog').dialog('close');
		  }
	  });
		
		
	}
	
});

$('.module_class').live("click",function(){

	var rowIndex = $('#rowIndex').val();
	var colIndex = $('#colIndex').val();

	var res_id = $(this).attr("id");

	if(res_id != ''){
		var module_id = res_id.split("_");

		$.ajax({
		  type: "GET",
		  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&module_id="+module_id[1]+"&type=module",
		  success: function(data){	

			 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
			 $('.scenario_dialog').dialog('close');
		  }
	  });
		
		
	}

	/*if($("input[type='radio'].module_class").is(':checked')) {        
		var module_id = res_id.split("_");

		$.ajax({
		  type: "GET",
		  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&module_id="+module_id[1]+"&type=module",
		  success: function(data){	

			 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
		  }
	  });
		
		$('.scenario_dialog').dialog('close');
}*/
});

$('.assignment_class').live("click",function(){

	var rowIndex = $('#rowIndex').val();
	var colIndex = $('#colIndex').val();

	var res_id = $(this).attr("id");

	if(res_id != ''){		
		var assignment_id = res_id.split("_");

		$.ajax({
		  type: "GET",
		  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&assignment_id="+assignment_id[1]+"&type=assignment",
		  success: function(data){	
			 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
			 $('.scenario_dialog').dialog('close');
		  }
	  });
		
		
	}

	/*if($("input[type='radio'].assignment_class").is(':checked')) {
        var assignment_id = $(this).val();

		$.ajax({
		  type: "GET",
		  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&assignment_id="+assignment_id+"&type=assignment",
		  success: function(data){	
			 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
		  }
	  });
		
		$('.scenario_dialog').dialog('close');
}*/
});

$('.survey_class').live("click",function(){

	var rowIndex = $('#rowIndex').val();
	var colIndex = $('#colIndex').val();

	var res_id = $(this).attr("id");

	if(res_id != ''){		
		var survey_id = res_id.split("_");

		$.ajax({
		  type: "GET",
		  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&survey_id="+survey_id[1]+"&type=survey",
		  success: function(data){	
			 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
			 $('.scenario_dialog').dialog('close');
		  }
	  });
		
		
	}

	/*if($("input[type='radio'].survey_class").is(':checked')) {
        var survey_id = $(this).val();

		$.ajax({
		  type: "GET",
		  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&survey_id="+survey_id+"&type=survey",
		  success: function(data){	
			 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
		  }
	  });
		
		$('.scenario_dialog').dialog('close');
}*/
});

$('.close_dialog').live("click",function(){
	$('.scenario_dialog').dialog('close');
});

$("#preview_scenario").live("click",function() {

		$.ajax({
			  type: "GET",
			  url: "<?php echo 'get_scenario_preview.php'; ?>",
			  success: function(data){	
				  $(".scenario_dialog").html(data);
				  $(".scenario_dialog").dialog({
										open: function(event, ui) {  
										jQuery('.ui-dialog-titlebar-close').css("width","85px");
										jQuery('.ui-dialog-titlebar-close').html('<span style="float:right;margin-right:5px;"><?php echo get_lang('CloseX'); ?></span>');  											
									},
										modal: true,
										title: "<?php echo get_lang('PreviewScenarioBlender'); ?>",
										width: 950,
										height : 450,
										resizable:false
					}); 
			  }
		  }); 
});

$("#submit_face2face").live("click",function(e) {
	var rowIndex = $('#rowIndex').val();
	var colIndex = $('#colIndex').val();

	var name = $("#name").val();
	var face_score = $("#face_score").val();
	var ff_type = $("input[type=\'radio\'].radio:checked").val();
	var ff_id = $("#hid_id").val();

	if(name != ''){
		$.ajax({
			  type: "GET",
			  url: "update_table.php?action=add_activity&rowIndex="+rowIndex+"&colIndex="+colIndex+"&name="+name+"&score="+face_score+"&type=face2face&ff_id="+ff_id+"&ff_type="+ff_type,
			  success: function(data){	

				 $("#mtable tbody tr:eq("+rowIndex+") td:eq("+colIndex+")").html(data);
				 $('.scenario_dialog').dialog('close');
			  }
		  });
		  
	}
	else {
		$("#name").focus();
	}
	 e.preventDefault();
		
});

$(".icon_delete_class").live("click",function(e) {

	var filename = $(this).attr("id");

	$("#icon_dialog_box").dialog({
	open: function(event, ui) {  
			jQuery(".ui-dialog-titlebar-close").css("width","85px");
			jQuery(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\"><?php echo get_lang("CloseX"); ?></span>");								
		},
	   title: "<?php echo get_lang('Delete'); ?>",
	  width: 500,
	  height: 250,
	  modal: false,
	  resizable: false,
	  draggable: false,
	   buttons: {
			'<?php echo get_lang("No"); ?>' : function() {
				   $(this).dialog('close');
			 },
			'<?php echo get_lang("Yes"); ?>': function() { // remove what you want to remove
					$(this).dialog('close');
				   $.ajax({
					  type: "GET",
					  url: "update_table.php?action=delete_icons&filename="+filename,
					  success: function(data){	
							$("#images").html("");
							$("#images").html(data);
					  }
				  });
			 }
			 
	   }
	});
});

$("#delete_scenario").live("click",function() {

	$("#dialog_box").dialog({
	open: function(event, ui) {  
			/*jQuery(".ui-dialog-titlebar-close").css("width","85px");
			jQuery(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\"><?php echo get_lang("CloseX"); ?></span>");*/
			$(this).parent().children().children("a.ui-dialog-titlebar-close").hide(); 
		},
	   title: "<?php echo get_lang('DeleteScenario'); ?>",
	  width: 350,
	  height: 185,
	  modal: true,
	  resizable: false,
	  draggable: true,
	   buttons: {
			'<?php echo get_lang("No"); ?>' : function() {
				   $(this).dialog('close');
			 },
			'<?php echo get_lang("Yes"); ?>': function() { 
				 
				 $.ajax({
								  type: "GET",
								  url: "<?php echo api_get_path('WEB_CODE_PATH'); ?>course_home/update_table.php?action=delete_scenario",
								  success: function(data){	
									  window.location.reload();
								  }
							  });
			 }
			 
	   }
	});
});

$("#show_step_border").live("click",function() {

	var step_color = $("#default_step_color").val();
	var hide_border;

	if($("input[type='checkbox'].show_border_class").is(':checked')) {
		$(".icon_block").css("border","none");
		hide_border = 1;
		$("#hid_hide_border").val(1);
	}
	else {
		$(".icon_block").css("border","3px solid "+step_color);
		hide_border = 0;
		$("#hid_hide_border").val(0);
	}

	$.ajax({
		  type: "GET",
		  url: "<?php echo api_get_path('WEB_CODE_PATH'); ?>course_home/update_table.php?action=update_showborder&hide_border="+hide_border,
		  success: function(data){	
			  
		  }
	  });

});

$("#show_step_image").live("click",function() {
	var hide_image;

	if($("input[type='checkbox'].show_image_class").is(':checked')) {
		$(".icon_block_120_80").css("display","none");
		hide_image = 1;
		$("#hid_hide_image").val(1);
	}
	else {	
		$(".icon_block_120_80").css("display","");
		$(".icon_block_120_80 img").css("display","block");
		hide_image = 0;
		$("#hid_hide_image").val(0);
	}
	$.ajax({
		  type: "GET",
		  url: "<?php echo api_get_path('WEB_CODE_PATH'); ?>course_home/update_table.php?action=update_showimage&hide_image="+hide_image,
		  success: function(data){	
			  
		  }
	  });

});

});
