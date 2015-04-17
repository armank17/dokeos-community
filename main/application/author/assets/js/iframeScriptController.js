$(document).ready(function() {

    AuthorModel.sortItems();
    AuthorModel.tagId();
    AuthorModel.uploadScoPpt();
    AuthorModel.submitSettingForm();
    AuthorModel.submitSettingForm2();
    AuthorModel.uploadVideo();        

    $(".embed-quiz").click(function(e){
       AuthorModel.loadQuiz(e, $(this)); 
    });
    
    $('.sorter-action').click(function(e) {
        AuthorModel.loadSorterActions(e, $(this));
    });    
    
    $('.load-video').click(function(e) {
        AuthorModel.loadVideos(e, $(this));
    });
    
    $("#btn-upload-social").click(function(e){
        AuthorModel.loadSocialVideos(e, $("#myurl"));
    });        
    
    $(".embed-tpl").click(function(e) {
        AuthorModel.updateTemplateEditor(e, $(this));
    });
    
    $("#quiz-certificate").change(function(e) {
        AuthorModel.changeCertificateThumb($(this));
    });
 
    $(".reopen-dialog").click(function(e){
        AuthorModel.reOpenDialog(e, $(this));        
    });
    
    $("#scorm_export").change(function() {
        AuthorModel.changeExportSelect($(this));        
    });
    
    $("#export-link").click(function(e) {
        AuthorModel.exportScorm($(this), e);
    }); 
    
    $("#go-to").click(function(e){
		AuthorModel.goTo($(this),e);
	});
    
	$("input[name|='enable_behavior_holder']").change(function(e){
		var name = e.srcElement.name;
		var value = e.srcElement.getAttribute("value");
		$("#enable_behavior").val(value);
	});
	
	$("input[name|='enable_behavior_holder'][value|='"+$("#enable_behavior").val()+"']").attr('checked','checked');   
    
});
