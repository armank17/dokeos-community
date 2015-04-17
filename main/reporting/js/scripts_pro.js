$(function(){

	$("#tablist1-tab1").live('click', function() {

		var current_tab = $("#current_tab").val();

		if(current_tab == 'courses'){
		$("#select_trainer").val(0);
		$("#select_session").val(0);	
		$("#course_search").val("");
		$("#hid_action_code").val("");
		}
		$("#current_tab").val("courses");
		//$("#course_search").val("");
		var selectVal = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var searchVal = $("#course_search").val();
		
		

		$.ajax({
			url: "get_ajax_data.php?action=get_courses&sessionId=" + selectVal + "&userid=" + trainer + "&search=" + searchVal,
			success: function(data) {
				$(".stacktable").html('');
				$("#courses").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_session&session_id="+selectVal,
			success: function(data) {
				$("#select_session").html(data);
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_pages&sessionId=" + selectVal + "&userid=" + trainer + "&search=" + searchVal,
			success: function(data) {
				$("#course_pages").html(data);
			}
		});
		
	});

	$("#tablist1-tab2").live('click', function() {
		//$('#tablist1-tab3').trigger('click');
		var current_tab = $("#current_tab").val();

		var action_code = $("#hid_action_code").val();
		
		if(current_tab == 'modules'){
		$("#select_courses").val(0);			
		}
		$("#module_search").val("");
		$("#current_tab").val('modules');
		var selectVal = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();		

		var selectCourse = $('#select_courses :selected').val();
		var sample = $('#select_courses :selected').val();
		var module_search = $("#module_search").val();	

		if(trainer === undefined){
			trainer = 0;
		}

		if(module_search === undefined){
			module_search = 0;
		}
		
		if(action_code != '' && action_code !== undefined){
			selectCourse = action_code;
		}

		$.ajax({
			url: "get_ajax_data.php?action=get_course_text&course_search=" + course_search + "&userid=" + trainer + "&sessionId=" + selectVal + "&course_code=" + selectCourse + "&search=" + module_search + "&from=modules",
			success: function(data) {			
				$("#modules_text").html(data);				
			}
		});
		
		if(sample === undefined){
			$.ajax({
				url: "get_ajax_modules.php?action=backto_modules_page&course_search=" + course_search + "&userid=" + trainer + "&sessionId=" + selectVal + "&course_code=" + selectCourse + "&search=" + module_search,
				beforeSend: function(){
                       $("#loaderDiv").show();
					   $("#dataDiv").hide();
                   },	
				success: function(data) {
					$("#loaderDiv").hide();
					$("#dataDiv").show();
					$("#tablist1-panel2").html(" ");
					$(".stacktable").html('');
					$("#tablist1-panel2").html(data);
					$(".responsive").stacktable({myClass: "stacktable small-only"});
				}
			});
		}
		else {
			$.ajax({
				url: "get_ajax_data.php?action=get_modules&course_search=" + course_search + "&userid=" + trainer + "&sessionId=" + selectVal + "&course_code=" + selectCourse + "&search=" + module_search,
				success: function(data) {

					$(".stacktable").html('');
					$("#modules").html(data);
					$('.responsive').stacktable({myClass: 'stacktable small-only'});
				}
			});	
		}

		$.ajax({
			url: "get_ajax_data.php?action=get_module_pages&course_search=" + course_search + "&userid=" + trainer + "&sessionId=" + selectVal + "&course_code=" + selectCourse + "&search=" + module_search,
			success: function(data) {
				$("#module_pages").html(data);
			}
		});
	});

	$("#tablist1-tab3").live('click', function() {
		var current_tab = $("#current_tab").val();

		if(current_tab == 'quizzes'){
		$("#list_courses").val(0);
		$("#list_session").val(0);
		$("#list_quiz").val(0);
		$("#select_type").val(0);
		$("#quiz_search").val("")
		}
		$("#current_tab").val("quizzes");
		var action_code = $("#hid_action_code").val();
		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var module_course = $('#select_courses :selected').val();
		var quiz_search = $("#quiz_search").val();	

		var quiz_course = $('#list_courses :selected').val();	
		var sample = $('#list_courses :selected').val();
		var selQuiz = $('#list_quiz :selected').val();		
		var selQuizType = $('#select_type :selected').val();
		var quiz_session = $('#list_session :selected').val();

		if(quiz_session != '0') {
			selSession = $('#list_session :selected').val();
		}
		
		var course = '';
		
		if(quiz_course === undefined){
			quiz_course = 0;
		}
		if(selQuiz === undefined){
			selQuiz = 0;
		}
		if(selQuizType === undefined){
			selQuizType = 0;
		}
		if(selSession === undefined){
			selSession = 0;
		}
		if(quiz_search === undefined){
			quiz_search = '';
		}	
		if(module_course === undefined){
			if(action_code != ''){
				module_course = action_code;
			}
			else {
			module_course = 0;
			}
		}	

		if(module_course == '0' && quiz_course == '0') {
			course = '0';
		}
		else if(quiz_course == '0') {
			course = module_course;
		}
		else {
			course = quiz_course;
		}

		$.ajax({
			url: "get_ajax_data.php?action=get_course_text&userid="+trainer+"&course_search="+course_search+"&course_code="+course+"&sessionId="+selSession+"&search="+quiz_search+"&from=quiz",
			success: function(data) {			
				$("#quiz_text").html(data);				
			}
		});

		if(sample === undefined){

			$.ajax({
				url: "get_ajax_pages.php?action=backto_quiz_page&user_id="+trainer+"&course_search="+course_search+"&course_code="+course+"&sessionId="+selSession+"&search="+quiz_search,
				beforeSend: function(){
                       $("#loaderDiv").show();
					   $("#dataDiv").hide();
                   },	
				success: function(data) {
					$("#loaderDiv").hide();
					$("#dataDiv").show();
					$("#tablist1-panel3").html(" ");
					$(".stacktable").html('');
					$("#tablist1-panel3").html(data);
					$(".responsive").stacktable({myClass: "stacktable small-only"});
				}
			});
		}
		else {

		$.ajax({
			url: "get_ajax_data.php?action=get_quizzes&user_id="+trainer+"&course_search="+course_search+"&course_code="+course+"&sessionId="+selSession+"&search="+quiz_search,
			success: function(data) {

				$(".stacktable").html('');
				$("#quizzes").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});	
		}

		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_pages&user_id="+trainer+"&course_search="+course_search+"&course_code="+course+"&sessionId="+selSession+"&search="+quiz_search,
			success: function(data) {
				$("#quiz_pages").html(data);
			}
		});
	});

	$("#tablist1-tab4").live('click', function() {

		var current_tab = $("#current_tab").val();
		if(current_tab == 'face2face'){
			$("#list_courses_ff").val(0);
		}
		$("#current_tab").val("learners");
		var action_code = $("#hid_action_code").val();
		var facetofaceCourse = $('#list_courses_ff :selected').val();
		var searchVal = $("#facetoface_search").val();	
		var moduleCourse = $('#select_courses :selected').val();
		var quizCourse = $('#list_courses :selected').val();	
		var trainer = $('#select_trainer :selected').val();

		if(facetofaceCourse === undefined){
			facetofaceCourse = 0;
		}		
		if(searchVal === undefined){
			searchVal = '';
		}

		if(quizCourse === undefined){
			quizCourse = 0;
		}
		if(moduleCourse === undefined){
			if(action_code != ''){
				moduleCourse = action_code;
			}
			else {
			moduleCourse = 0;
			}
		}	

		var course = '';
		if(quizCourse == '0' && moduleCourse == '0'){
			course = 0;
		}
		else if(facetofaceCourse == '0') {
			if(quizCourse != '0'){
			course = quizCourse;
			}
			else if(moduleCourse != '0'){
				course = moduleCourse
			}
		}
		else if(quizCourse == '0') {
			course = moduleCourse;			
		}
		else {
			course = facetofaceCourse;
		}

		$.ajax({
			url: "get_ajax_data.php?action=get_facetoface&course_code="+course+"&search="+searchVal+"&user_id="+trainer,
			success: function(data) {
				$(".stacktable").html('');
				$("#facetoface").html(data);	
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});

	});

	$("#tablist1-tab5").live('click', function() {
		
		var current_tab = $("#current_tab").val();

	//	window.location = "index.php";
		if(current_tab == 'learners'){
		$("#course_list").val(0);
		$("#session_list").val(0);
		$("#learners_filter").val(1);
		$("#quiz_ranking").val(0);
		$("#learner_search").val("")
		}
		$("#current_tab").val("learners");
		var action_code = $("#hid_action_code").val();

		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var moduleCourse = $('#select_courses :selected').val();
		var quizCourse = $('#list_courses :selected').val();		
		var searchVal = $("#learner_search").val();	

		var learnerCourse = $('#course_list :selected').val();
		var sample = $('#course_list :selected').val();		
		var user_session = $('#session_list :selected').val();

		if(user_session != '0') {
			selSession = $('#session_list :selected').val();
		}

		var selStatus = $('#learners_filter :selected').val();
		var selRank = $('#quiz_ranking :selected').val();
		
		if(learnerCourse === undefined){
			learnerCourse = 0;
		}
		if(selStatus === undefined){
			selStatus = 1;
		}
		if(selRank === undefined){
			selRank = 0;
		}
		if(selSession === undefined){
			selSession = 0;
		}
		if(searchVal === undefined){
			searchVal = '';
		}

		if(quizCourse === undefined){
			quizCourse = 0;
		}
		if(moduleCourse === undefined){
			if(action_code != ''){
				moduleCourse = action_code;
			}
			else {
			moduleCourse = 0;
			}
		}	

		var course = '';
		if(quizCourse == '0' && moduleCourse == '0' && learnerCourse == '0'){
			course = 0;
		}
		else if(learnerCourse == '0') {
			if(quizCourse != '0'){
			course = quizCourse;
			}
			else if(moduleCourse != '0'){
				course = moduleCourse
			}
		}
		else if(quizCourse == '0') {
			course = moduleCourse;			
		}
		else {
			course = learnerCourse;
		}

		$.ajax({
			url: "get_ajax_data.php?action=get_course_text&userid="+trainer+"&course_search="+course_search+"&course_code="+course+"&sessionId="+selSession+"&search="+searchVal+"&from=learner",
			success: function(data) {			
				$("#learner_text").html(data);				
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_users_pages&trainer_id="+trainer+"&sessionId="+selSession+"&course_search="+course_search+"&course_code="+course+"&search="+searchVal+"&status="+selStatus+"&rank="+selRank,
			success: function(data) {
				$("#learners_pages").html(data);
			}
		});

		
		if(sample === undefined){
			$.ajax({
				url: "get_ajax_learners.php?action=backto_learners_page&trainer_id="+trainer+"&sessionId="+selSession+"&course_search="+course_search+"&course_code="+course+"&search="+searchVal+"&status="+selStatus+"&rank="+selRank,
				beforeSend: function(){
                       $("#loaderDiv").show();
					   $("#dataDiv").hide();
                   },	
				success: function(data) {
					$("#loaderDiv").hide();
					$("#dataDiv").show();
					$("#tablist1-panel5").html(" ");
					$(".stacktable").html('');
					$("#tablist1-panel5").html(data);
					$(".responsive").stacktable({myClass: "stacktable small-only"});
				}
			});
		}
		else {
			$.ajax({
				url: "get_ajax_data.php?action=get_users&trainer_id="+trainer+"&sessionId="+selSession+"&course_search="+course_search+"&course_code="+course+"&search="+searchVal+"&status="+selStatus+"&rank="+selRank,
				success: function(data) {

					$(".stacktable").html('');
					$("#learners").html(data);	
					$('.responsive').stacktable({myClass: 'stacktable small-only'});
				}
			});
		}
	});	

	$("#courseshead").live('click', function() {
		//$("#select_trainer").val(0);
		//$("#select_session").val(0);

		$("#select_trainer").val(0);
		$("#select_session").val(0);
		//$("#course_search").val("");
		var selectVal = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var searchVal = $("#course_search").val();	
		
		$.ajax({
			url: "get_ajax_data.php?action=get_courses&sessionId=" + selectVal + "&userid=" + trainer + "&search=" + searchVal + "&device=small",
			success: function(data) {
				$("#chartContainer").css("display", "none");
				$(".stacktable").html('');
				$("#courses").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_session&session_id="+selectVal,
			success: function(data) {
				$("#select_session").html(data);
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_pages&sessionId=" + selectVal + "&userid=" + trainer + "&search=" + searchVal,
			success: function(data) {
				$("#course_pages").html(data);
			}
		});
	});

	$("#moduleshead").live('click', function() {
		//$("#select_courses").val(0);	
		
		$("#select_courses").val(0);
		var selectVal = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var selectCourse = $('#select_courses :selected').val();
		var module_search = $("#module_search").val();			

		$.ajax({
			url: "get_ajax_data.php?action=get_course_text&course_search=" + course_search + "&userid=" + trainer + "&sessionId=" + selectVal + "&course_code=" + selectCourse + "&search=" + module_search + "&from=modules",
			success: function(data) {			
				$("#modules_text").html(data);				
			}
		});
		
		$.ajax({
			url: "get_ajax_data.php?action=get_modules&course_search=" + course_search + "&userid=" + trainer + "&sessionId=" + selectVal + "&course_code=" + selectCourse + "&search=" + module_search,
			success: function(data) {
				$(".stacktable").html('');
				$("#modules").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});	

		$.ajax({
			url: "get_ajax_data.php?action=get_module_pages&course_search=" + course_search + "&userid=" + trainer + "&sessionId=" + selectVal + "&course_code=" + selectCourse + "&search=" + module_search,
			success: function(data) {
				$("#module_pages").html(data);
			}
		});
	});

	$("#quizhead").live('click', function() {

		/*$("#list_courses").val(0);
		$("#list_session").val(0);
		$("#list_quiz").val(0);
		$("#select_type").val(0);*/

		$("#list_courses").val(0);
		$("#list_session").val(0);
		$("#list_quiz").val(0);
		$("#select_type").val(0);
		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var module_course = $('#select_courses :selected').val();
		var quiz_search = $("#quiz_search").val();	

		var quiz_course = $('#list_courses :selected').val();	
		var sample = $('#list_courses :selected').val();
		var selQuiz = $('#list_quiz :selected').val();		
		var selQuizType = $('#select_type :selected').val();
		var quiz_session = $('#list_session :selected').val();

		if(quiz_session != '0') {
			selSession = $('#list_session :selected').val();
		}
		
		var course = '';
		
		if(quiz_course === undefined){
			quiz_course = 0;
		}
		if(selQuiz === undefined){
			selQuiz = 0;
		}
		if(selQuizType === undefined){
			selQuizType = 0;
		}
		if(selSession === undefined){
			selSession = 0;
		}
		if(quiz_search === undefined){
			quiz_search = '';
		}		
		
		if(module_course == '0' && quiz_course == '0') {
			course = '0';
		}
		else if(quiz_course == '0') {
			course = module_course;
		}
		else {
			course = quiz_course;
		}

		$.ajax({
			url: "get_ajax_data.php?action=get_course_text&userid="+trainer+"&course_search="+course_search+"&course_code="+course+"&sessionId="+selSession+"&search="+quiz_search+"&from=quiz",
			success: function(data) {			
				$("#quiz_text").html(data);				
			}
		});

		if(sample === undefined){

			$.ajax({
				url: "get_ajax_pages.php?action=backto_quiz_page&user_id="+trainer+"&course_search="+course_search+"&course_code="+course+"&sessionId="+selSession+"&search="+quiz_search,
				beforeSend: function(){
                       $("#loaderDiv").show();
					   $("#dataDiv").hide();
                   },	
				success: function(data) {
					$("#loaderDiv").hide();
					$("#dataDiv").show();
					$("#tablist1-panel3").html(" ");
					$(".stacktable").html('');
					$("#tablist1-panel3").html(data);
					$(".responsive").stacktable({myClass: "stacktable small-only"});
				}
			});
		}
		else {

		$.ajax({
			url: "get_ajax_data.php?action=get_quizzes&user_id="+trainer+"&course_search="+course_search+"&course_code="+course+"&sessionId="+selSession+"&search="+quiz_search,
			success: function(data) {
				$(".stacktable").html('');
				$("#quizzes").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});	
		}

		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_pages&user_id="+trainer+"&course_search="+course_search+"&course_code="+course+"&sessionId="+selSession+"&search="+quiz_search,
			success: function(data) {
				$("#quiz_pages").html(data);
			}
		});
	});

	$("#facetofacehead").live('click', function() {

		var current_tab = $("#current_tab").val();
		if(current_tab == 'face2face'){
			$("#list_courses_ff").val(0);
		}
		$("#current_tab").val("learners");
		var action_code = $("#hid_action_code").val();
		var facetofaceCourse = $('#list_courses_ff :selected').val();
		var searchVal = $("#facetoface_search").val();	
		var moduleCourse = $('#select_courses :selected').val();
		var quizCourse = $('#list_courses :selected').val();	
		var trainer = $('#select_trainer :selected').val();

		if(facetofaceCourse === undefined){
			facetofaceCourse = 0;
		}		
		if(searchVal === undefined){
			searchVal = '';
		}

		if(quizCourse === undefined){
			quizCourse = 0;
		}
		if(moduleCourse === undefined){
			if(action_code != ''){
				moduleCourse = action_code;
			}
			else {
			moduleCourse = 0;
			}
		}	

		var course = '';
		if(quizCourse == '0' && moduleCourse == '0'){
			course = 0;
		}
		else if(facetofaceCourse == '0') {
			if(quizCourse != '0'){
			course = quizCourse;
			}
			else if(moduleCourse != '0'){
				course = moduleCourse
			}
		}
		else if(quizCourse == '0') {
			course = moduleCourse;			
		}
		else {
			course = facetofaceCourse;
		}

		$.ajax({
			url: "get_ajax_data.php?action=get_facetoface&course_code="+course+"&search="+searchVal+"&user_id="+trainer,
			success: function(data) {
				$(".stacktable").html('');
				$("#facetoface").html(data);	
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});
	});

	$("#learnershead").live('click', function() {

		/*$("#course_list").val(0);
		$("#session_list").val(0);
		$("#learners_filter").val(1);
		$("#quiz_ranking").val(0);*/

		$("#course_list").val(0);
		$("#session_list").val(0);
		$("#learners_filter").val(1);
		$("#quiz_ranking").val(0);
		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var moduleCourse = $('#select_courses :selected').val();
		var quizCourse = $('#list_courses :selected').val();		
		var searchVal = $("#learner_search").val();	

		var learnerCourse = $('#course_list :selected').val();
		var sample = $('#course_list :selected').val();		
		var user_session = $('#session_list :selected').val();

		if(user_session != '0') {
			selSession = $('#session_list :selected').val();
		}

		var selStatus = $('#learners_filter :selected').val();
		var selRank = $('#quiz_ranking :selected').val();
		
		if(learnerCourse === undefined){
			learnerCourse = 0;
		}
		if(selStatus === undefined){
			selStatus = 1;
		}
		if(selRank === undefined){
			selRank = 0;
		}
		if(selSession === undefined){
			selSession = 0;
		}
		if(searchVal === undefined){
			searchVal = '';
		}

		if(quizCourse === undefined){
			quizCourse = 0;
		}

		var course = '';
		if(quizCourse == '0' && moduleCourse == '0' && learnerCourse == '0'){
			course = 0;
		}
		else if(learnerCourse == '0') {
			if(quizCourse != '0'){
			course = quizCourse;
			}
			else if(moduleCourse != '0'){
				course = moduleCourse
			}
		}
		else if(quizCourse == '0') {
			course = moduleCourse;			
		}
		else {
			course = learnerCourse;
		}

		$.ajax({
			url: "get_ajax_data.php?action=get_course_text&userid="+trainer+"&course_search="+course_search+"&course_code="+course+"&sessionId="+selSession+"&search="+searchVal+"&from=learner",
			success: function(data) {			
				$("#learner_text").html(data);				
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_users_pages&trainer_id="+trainer+"&sessionId="+selSession+"&course_search="+course_search+"&course_code="+course+"&search="+searchVal+"&status="+selStatus+"&rank="+selRank,
			success: function(data) {
				$("#learners_pages").html(data);
			}
		});

		
		if(sample === undefined){
			$.ajax({
				url: "get_ajax_learners.php?action=backto_learners_page&trainer_id="+trainer+"&sessionId="+selSession+"&course_search="+course_search+"&course_code="+course+"&search="+searchVal+"&status="+selStatus+"&rank="+selRank,
				beforeSend: function(){
                       $("#loaderDiv").show();
					   $("#dataDiv").hide();
                   },	
				success: function(data) {
					$("#loaderDiv").hide();
					$("#dataDiv").show();
					$("#tablist1-panel5").html(" ");
					$(".stacktable").html('');
					$("#tablist1-panel5").html(data);
					$(".responsive").stacktable({myClass: "stacktable small-only"});
				}
			});
		}
		else {
			$.ajax({
				url: "get_ajax_data.php?action=get_users&trainer_id="+trainer+"&sessionId="+selSession+"&course_search="+course_search+"&course_code="+course+"&search="+searchVal+"&status="+selStatus+"&rank="+selRank,
				success: function(data) {
					$(".stacktable").html('');
					$("#learners").html(data);	
					$('.responsive').stacktable({myClass: 'stacktable small-only'});
				}
			});
		}
	});

	$(".action_module").live('click', function() {

		var hidCourseCode = $(this).attr('id');

		var myArray = hidCourseCode.split('_');
		$("#hid_action_code").val(myArray[1]);

		/*var myString = $(this).attr('href');
		var myArray = myString.split('?');

		var hidCode = myArray[1].split('=');
		alert("hid==="+hidCode[1]);
		$("#hid_action_code").val(hidCode[1]);*/

		$('#tablist1-tab2').trigger('click');
		
	});

    $("#select_trainer").bind('change keyup',function() {
		var selectVal = $('#select_trainer :selected').val();
		var searchVal = $("#course_search").val();	

		$.ajax({
			url: "get_ajax_data.php?action=get_session&userid=" + selectVal,
			success: function(data) {
				$("#select_session").html(data);
			}
		});		

		$.ajax({
			url: "get_ajax_data.php?action=get_pages&userid=" + selectVal,
			success: function(data) {
				$("#course_pages").html(data);
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_courses&userid=" + selectVal + "&search=" + searchVal,
			success: function(data) {

				$(".stacktable").html('');				
				$("#courses").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});			
			}
		});

	});

	$("#course_pagination li a").live('click', function(e) {
		
		var selectVal = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var searchVal = $("#course_search").val();	
		
		var myString = $(this).attr('href');
		var myArray = myString.split('&');

		var liRef = myArray[1].split('=');
		var pageno = liRef[1] - 1;

		$.ajax({
			url: "get_ajax_data.php?action=get_courses&sessionId=" + selectVal + "&search=" + searchVal + "&userid=" + trainer + "&" + myArray[1],
			success: function(data) {

				$(".stacktable").html('');
				$("#courses").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});
			$("#course_pagination li").removeClass("active");
			$("#course_pagination li:eq("+pageno+")").addClass("active");

		$.ajax({
			url: "get_ajax_data.php?action=get_pages&userid=" + trainer + "&page=" + liRef[1] + "&sessionId=" + selectVal + "&search=" + searchVal,
			success: function(data) {
				$("#course_pages").html(data);
			}
		});
		e.preventDefault();
	});	

	$("#select_session").bind("change keyup",function() {
		var selectVal = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var searchVal = $("#course_search").val();	

		$.ajax({
			url: "get_ajax_data.php?action=get_courses&sessionId=" + selectVal + "&userid=" + trainer + "&search=" + searchVal,
			success: function(data) {

				$(".stacktable").html('');
				$("#courses").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_pages&sessionId=" + selectVal + "&userid=" + trainer + "&search=" + searchVal,
			success: function(data) {
				$("#course_pages").html(data);
			}
		});
	});

	$("#select_courses").live("change keyup",function() {
		var selectCourse = $('#select_courses :selected').val();
		$("#list_courses").val(selectCourse);
		$("#course_list").val(selectCourse);

		$.ajax({
			url: "get_ajax_data.php?action=get_modules&course_code=" + selectCourse,
			success: function(data) {
				$(".stacktable").html('');
				$("#modules").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});	
		
		$.ajax({
			url: "get_ajax_data.php?action=get_module_pages&course_code=" + selectCourse,
			success: function(data) {
				$("#module_pages").html(data);
			}
		});

	});

	$("#module_pagination li a").live('click', function(e) {

		var selectCourse = $('#select_courses :selected').val();
		var searchVal = $("#module_search").val();	

		var selectVal = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();
				
		var myString = $(this).attr('href');
		var myArray = myString.split('&');

		var liRef = myArray[1].split('=');
		var pageno = liRef[1] - 1;
		$.ajax({
			url: "get_ajax_data.php?action=get_modules&course_code=" + selectCourse + "&" + myArray[1] + "&search=" + searchVal + "&userid=" + trainer + "&course_search=" + course_search + "&sessionId=" + selectVal,
			success: function(data) {
				$(".stacktable").html('');
				$("#modules").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});
			$("#module_pagination li").removeClass("active");
			$("#module_pagination li:eq("+pageno+")").addClass("active");

		$.ajax({
			url: "get_ajax_data.php?action=get_module_pages&course_code=" + selectCourse + "&" + myArray[1] + "&search=" + searchVal + "&userid=" + trainer + "&course_search=" + course_search + "&sessionId=" + selectVal,
			success: function(data) {
				$("#module_pages").html(data);
			}
		});
		e.preventDefault();
	});
	

	$("#list_courses").live("change keyup",function() {
		var selCourse = $('#list_courses :selected').val();		
		$("#course_list").val(selCourse);
		$("#quiz_search").val("");

		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_session&course_code=" + selCourse,
			success: function(data) {
				$("#list_session").html(data);
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_list&course_code=" + selCourse,
			success: function(data) {
				$("#list_quiz").html(data);
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_quizzes&course_code=" + selCourse,
			success: function(data) {
				$(".stacktable").html('');
				$("#quizzes").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});	

		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_pages&course_code=" + selCourse,
			success: function(data) {
				$("#quiz_pages").html(data);
			}
		});		

	});

	$("#list_quiz").live("change keyup",function() {
		var selCourse = $('#list_courses :selected').val();		
		var selQuiz = $('#list_quiz :selected').val();		
		
		$.ajax({
			url: "get_ajax_data.php?action=get_quizzes&course_code=" + selCourse + "&quiz=" + selQuiz,
			success: function(data) {
				$(".stacktable").html('');
				$("#quizzes").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});	

		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_type&course_code=" + selCourse + "&quiz=" + selQuiz,
			success: function(data) {

				$("#select_type").html(data);
			}
		});	

		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_pages&course_code=" + selCourse + "&quiz=" + selQuiz,
			success: function(data) {
				$("#quiz_pages").html(data);
			}
		});	

		/*$.ajax({
			url: "get_ajax_data.php?action=get_quiz_session&course_code=" + selCourse + "&quiz=" + selQuiz,
			success: function(data) {
				alert("data==="+data);
				$("#list_session").html(data);
			}
		});	*/
		
		/*$.ajax({
			url: "get_ajax_data.php?action=get_module_pages&course_code=" + selectCourse,
			success: function(data) {
				$("#module_pages").html(data);
			}
		});*/

	});

	$("#select_type").live("change keyup",function() {
		var selCourse = $('#list_courses :selected').val();		
		var selQuiz = $('#list_quiz :selected').val();		
		var selQuizType = $('#select_type :selected').val();		
		
		$.ajax({
			url: "get_ajax_data.php?action=get_quizzes&course_code=" + selCourse + "&quiz=" + selQuiz + "&quiztype=" + selQuizType,
			success: function(data) {

				$(".stacktable").html('');
				$("#quizzes").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});			
		
		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_pages&course_code=" + selCourse + "&quiz=" + selQuiz + "&quiztype=" + selQuizType,
			success: function(data) {
				$("#quiz_pages").html(data);
			}
		});

	});

	$("#list_session").live("change keyup",function() {
		var selCourse = $('#list_courses :selected').val();		
		var selQuiz = $('#list_quiz :selected').val();		
		var selQuizType = $('#select_type :selected').val();		
		var selSession = $('#list_session :selected').val();
		
		$.ajax({
			url: "get_ajax_data.php?action=get_quizzes&course_code=" + selCourse + "&quiz=" + selQuiz + "&quiztype=" + selQuizType + "&sessionId=" + selSession,
			success: function(data) {

				$(".stacktable").html('');
				$("#quizzes").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});			
		
		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_pages&course_code=" + selCourse + "&quiz=" + selQuiz + "&quiztype=" + selQuizType + "&sessionId=" + selSession,
			success: function(data) {
				$("#quiz_pages").html(data);
			}
		});

	});	
	
	$("#quiz_pagination li a").live('click', function(e) {	

		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();

		var quizCourse = $('#list_courses :selected').val();		
		var selQuiz = $('#list_quiz :selected').val();		
		var selQuizType = $('#select_type :selected').val();		
		selSession = $('#list_session :selected').val();
		var searchVal = $("#quiz_search").val();	

		var moduleCourse = $('#select_courses :selected').val();
		var searchVal = $("#module_search").val();
		var course = 0;
		if(quizCourse == '0' && moduleCourse == '0'){
			course = '0';
		}
		else if(quizCourse == '0'){
			course = moduleCourse;
		}
		else {
			course = quizCourse;
		}

		var myString = $(this).attr('href');
		var myArray = myString.split('&');

		var liRef = myArray[1].split('=');
		var pageno = liRef[1] - 1;
		$.ajax({
			url: "get_ajax_data.php?action=get_quizzes&course_code=" + course + "&quiz=" + selQuiz + "&quiztype=" + selQuizType + "&sessionId=" + selSession + "&" + myArray[1] + "&search=" + searchVal + "&course_search="+course_search + "&user_id="+trainer,
			success: function(data) {
				$(".stacktable").html('');
				$("#quizzes").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});
			$("#quiz_pagination li").removeClass("active");
			$("#quiz_pagination li:eq("+pageno+")").addClass("active");

		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_pages&course_code=" + course + "&quiz=" + selQuiz + "&quiztype=" + selQuizType + "&sessionId=" + selSession + "&page=" + liRef[1] + "&search=" + searchVal + "&course_search="+course_search + "&user_id="+trainer,
			success: function(data) {
				$("#quiz_pages").html(data);
			}
		});
		e.preventDefault();
	});

	$("#course_list").live("change keyup",function() {
		var selectVal = $('#course_list :selected').val();
		$("#course_search").val("");	

		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_session&course_code=" + selectVal,
			success: function(data) {
				$("#session_list").html(data);
			}
		});		

		$.ajax({
			url: "get_ajax_data.php?action=get_users_pages&course_code=" + selectVal,
			success: function(data) {
				$("#learners_pages").html(data);
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_users&course_code=" + selectVal,
			success: function(data) {

				$(".stacktable").html('');
				$("#learners").html(data);	
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});

	});

	$("#session_list").live("change keyup",function() {
		var selectVal = $('#course_list :selected').val();
		var selSession = $('#session_list :selected').val();
		
		$.ajax({
			url: "get_ajax_data.php?action=get_users_pages&course_code=" + selectVal + "&sessionId=" + selSession,
			success: function(data) {
				$("#learners_pages").html(data);
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_users&course_code=" + selectVal + "&sessionId=" + selSession,
			success: function(data) {
				$(".stacktable").html('');
				$("#learners").html(data);	
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});

	});

	$("#learners_filter").live("change keyup",function() {
		var selectVal = $('#course_list :selected').val();
		var selSession = $('#session_list :selected').val();
		var selStatus = $('#learners_filter :selected').val();

		$.ajax({
			url: "get_ajax_data.php?action=get_users&course_code=" + selectVal + "&sessionId=" + selSession + "&status=" + selStatus,
			success: function(data) {
				$(".stacktable").html('');
				$("#learners").html(data);	
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_users_pages&course_code=" + selectVal + "&sessionId=" + selSession + "&status=" + selStatus,
			success: function(data) {
				$("#learners_pages").html(data);
			}
		});
	});

	$("#quiz_ranking").live("change keyup",function() {
		var selectVal = $('#course_list :selected').val();
		var selSession = $('#session_list :selected').val();
		var selStatus = $('#learners_filter :selected').val();
		var selRank = $('#quiz_ranking :selected').val();

		$.ajax({
			url: "get_ajax_data.php?action=get_users&course_code=" + selectVal + "&sessionId=" + selSession + "&status=" + selStatus + "&rank=" + selRank,
			success: function(data) {
				$(".stacktable").html('');
				$("#learners").html(data);	
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
				$("#learners_pages").html("");				
			}
		});

		/*$.ajax({
			url: "get_ajax_data.php?action=get_users_pages&course_code=" + selectVal + "&sessionId=" + selSession + "&status=" + selStatus + "&rank=" + selRank,
			success: function(data) {
				$("#learners_pages").html(data);
			}
		});*/

	});

	$("#learners_pagination li a").live('click', function(e) {	

		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var moduleCourse = $('#select_courses :selected').val();
		var quizCourse = $('#list_courses :selected').val();		
		var searchVal = $("#learner_search").val();	

		var learnerCourse = $('#course_list :selected').val();
		selSession = $('#session_list :selected').val();
		var selStatus = $('#learners_filter :selected').val();
		var selRank = $('#quiz_ranking :selected').val();

		if(learnerCourse === undefined){
			learnerCourse = 0;
		}
		if(selStatus === undefined){
			selStatus = 1;
		}
		if(selRank === undefined){
			selRank = 0;
		}
		if(selSession === undefined){
			selSession = 0;
		}
		if(searchVal === undefined){
			searchVal = '';
		}

		if(quizCourse === undefined){
			quizCourse = 0;
		}
		
		var course = '';
		if(quizCourse == '0' && moduleCourse == '0' && learnerCourse == '0'){
			course = 0;
		}
		else if(learnerCourse == '0') {
			if(quizCourse != '0'){
			course = quizCourse;
			}
			else if(moduleCourse != '0'){
				course = moduleCourse
			}			
		}
		else if(quizCourse == '0') {
			course = moduleCourse;			
		}
		else {
			course = learnerCourse;
		}

		/*var selectVal = $('#course_list :selected').val();
		var selSession = $('#session_list :selected').val();
		var selStatus = $('#learners_filter :selected').val();
		var selRank = $('#quiz_ranking :selected').val();
		var searchVal = $("#learner_search").val();	*/
				
		var myString = $(this).attr('href');
		var myArray = myString.split('&');

		var liRef = myArray[1].split('=');
		var pageno = liRef[1] - 1;
		$.ajax({
			url: "get_ajax_data.php?action=get_users&course_code=" + course + "&sessionId=" + selSession + "&status=" + selStatus + "&rank=" + selRank + "&" + myArray[1] + "&search=" + searchVal + "&trainer_id=" + trainer+"&course_search="+course_search,
			success: function(data) {
				$(".stacktable").html('');
				$("#learners").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});
			$("#learners_pagination li").removeClass("active");
			$("#learners_pagination li:eq("+pageno+")").addClass("active");

		$.ajax({
			url: "get_ajax_data.php?action=get_users_pages&course_code=" + course + "&sessionId=" + selSession + "&status=" + selStatus + "&rank=" + selRank + "&page=" + liRef[1] + "&search=" + searchVal + "&trainer_id=" + trainer+"&course_search="+course_search,
			success: function(data) {
				$("#learners_pages").html(data);
			}
		});
		e.preventDefault();
	});

	$("#inreport").live('click', function(e) {
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "individual_reporting.php?" + myArray[1],
			success: function(data) {
				$("#loaderDiv").hide();
				$("#dataDiv").show();
				$("#tablist1-panel5").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel5").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});
		e.preventDefault();
	});

	$("#show_result").live('click', function(e) {
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "quiz_result.php?" + myArray[1],
			success: function(data) {
				$("#tablist1-panel3").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel3").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});
		e.preventDefault();
	});

	$("#module_quiz_result").live('click', function(e) {
		
		var current_tab = $("#current_tab").val();
		
		if(current_tab === undefined){

			var myString = $(this).attr('href');
			var myArray = myString.split('?');
			var myUrl = myArray[1] + "&page=users";			

			$.ajax({
				url: "quiz_result.php?" + myUrl,
				success: function(data) {
					$(".stacktable").html('');
					$("#dataDiv").html(" ");				
					$("#dataDiv").html(data);					
					$(".responsive").stacktable({myClass: "stacktable small-only"});
				}
			});
		}
		else {
			var myString = $(this).attr('href');
			var myArray = myString.split('?');

			if(current_tab == 'learners'){
				var myUrl = myArray[1];
			}
			else {
				var myUrl = myArray[1] + "&page=module";
			}

			$.ajax({
				url: "quiz_result.php?" + myUrl,
				success: function(data) {
					$(".stacktable").html('');
					if(current_tab == 'learners'){
						$("#tablist1-panel5").html(" ");				
						$("#tablist1-panel5").html(data);
					}
					else {
						$("#tablist1-panel2").html(" ");				
						$("#tablist1-panel2").html(data);
					}
					$(".responsive").stacktable({myClass: "stacktable small-only"});
				}
			});
		}
		e.preventDefault();
	});

	$("#individual_result").live('click', function(e) {
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "quiz_result.php?" + myArray[1],
			success: function(data) {
				$("#tablist1-panel5").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel5").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});
		e.preventDefault();
	});

	$("#user_individual_result").live('click', function(e) {
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "quiz_result.php?" + myArray[1],
			success: function(data) {
				$("#dataDiv").html(" ");
				$(".stacktable").html('');
				$("#dataDiv").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});
		e.preventDefault();
	});

	$("#module_result").live('click', function(e) {
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "module_result.php?" + myArray[1],
			success: function(data) {
				$("#tablist1-panel5").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel5").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});
		e.preventDefault();
	});

	$("#user_module_result").live('click', function(e) {
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "module_result.php?" + myArray[1] + "&page=users",
			success: function(data) {
				$("#dataDiv").html(" ");
				$(".stacktable").html('');
				$("#dataDiv").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});
		e.preventDefault();
	});

	$("#module_list_result").live('click', function(e) {
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "module_result.php?" + myArray[1],
			success: function(data) {
				$("#tablist1-panel2").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel2").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});
		e.preventDefault();
	});

	$("#listlearners").live('click', function(e) {
				
		var myString = $(this).attr('href');
		var myArray = myString.split('?');
		$.ajax({
			url: "list_learners.php?" + myArray[1],
			success: function(data) {
				$("#tablist1-panel3").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel3").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});
		e.preventDefault();
	});

	$("#module_learners").live('click', function(e) {
				
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "module_learners.php?" + myArray[1],
			success: function(data) {

				$("#tablist1-panel2").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel2").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});
		e.preventDefault();
	});

	$("#list_attempt").live('change keyup', function(e) {
		var selAttempt = $('#list_attempt :selected').val();
		var quizid = $('#hid_quizid').val();
		var code = $('#hid_code').val();
		var session_id = $('#hid_session_id').val();
		
		$.ajax({
			url: "list_learners.php?attempt_id=" + selAttempt + "&quiz_id=" + quizid + "&course_code=" + code + "&sessionId=" + session_id,
			success: function(data) {
				$("#tablist1-panel3").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel3").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});
		e.preventDefault();
	});

	$("#listlearnersff").live('click', function(e) {
				
		var myString = $(this).attr('href');
		var myArray = myString.split('?');
		$.ajax({
			url: "list_learners_ff.php?" + myArray[1],
			success: function(data) {
				$("#tablist1-panel4").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel4").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});
		e.preventDefault();
	});

	$("#learners_back").live('click', function(e) {
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "get_ajax_pages.php?action=backto_quiz_page&" + myArray[1],
			success: function(data) {

				$("#tablist1-panel3").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel3").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});

		e.preventDefault();
	});

	$("#facetoface_back").live('click', function(e) {
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "get_ajax_facetoface.php?action=backto_facetoface_page&" + myArray[1],
			success: function(data) {

				$("#tablist1-panel4").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel4").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});

		e.preventDefault();
	});

	$("#modules_back").live('click', function(e) {
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "get_ajax_modules.php?action=backto_modules_page&" + myArray[1],
			success: function(data) {

				$("#tablist1-panel2").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel2").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});

		e.preventDefault();
	});

	$("#report_back").live('click', function(e) {

		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "get_ajax_learners.php?action=backto_learners_page&" + myArray[1],
			success: function(data) {
				$("#tablist1-panel5").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel5").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});

		e.preventDefault();
	});

	$("#quizresult_back").live('click', function(e) {
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "list_learners.php?" + myArray[1],
			success: function(data) {

				$("#tablist1-panel3").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel3").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});

		e.preventDefault();
	});

	$("#quizindividual_back").live('click', function(e) {
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "individual_reporting.php?" + myArray[1],
			success: function(data) {

				$("#tablist1-panel5").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel5").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});

		e.preventDefault();
	});

	$("#moduleindividual_back").live('click', function(e) {
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		$.ajax({
			url: "module_learners.php?" + myArray[1],
			success: function(data) {

				$("#tablist1-panel2").html(" ");
				$(".stacktable").html('');
				$("#tablist1-panel2").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});

		e.preventDefault();
	});

	$("#moduleresult_back").live('click', function(e) {

		var current_tab = $("#current_tab").val();

		var myString = $(this).attr('href');
		var myArray = myString.split('?');
		if(current_tab == 'learners'){
			var myUrl = myArray[1];
		}
		else {
			var myUrl = myArray[1] + "&page=module";
		}

		$.ajax({
			url: "module_result.php?" + myUrl,
			success: function(data) {
				$(".stacktable").html('');
				if(current_tab == 'learners'){
					$("#tablist1-panel5").html(" ");					
					$("#tablist1-panel5").html(data);
				}
				else {
					$("#tablist1-panel2").html(" ");					
					$("#tablist1-panel2").html(data);
				}
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});

		e.preventDefault();
	});

	$("#user_moduleresult_back").live('click', function(e) {

		var myString = $(this).attr('href');
		var myArray = myString.split('?');
		var myUrl = myArray[1] + "&page=users";
		
		$.ajax({
			url: "module_result.php?" + myUrl,
			success: function(data) {
				$(".stacktable").html('');
				$("#dataDiv").html(" ");					
				$("#dataDiv").html(data);
				$(".responsive").stacktable({myClass: "stacktable small-only"});
			}
		});

		e.preventDefault();
	});

	$("#list_courses_ff").live("change keyup",function() {
		var selectCourse = $('#list_courses_ff :selected').val();
		$("#list_courses").val(selectCourse);
		$("#course_list").val(selectCourse);

		$.ajax({
			url: "get_ajax_data.php?action=get_facetoface&course_code=" + selectCourse,
			success: function(data) {
				$(".stacktable").html('');
				$("#facetoface").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});			

	});

	$("#coursebtn_search").live('click', function(e) {

		var searchVal = $("#course_search").val();	
		var selTrainer = $("#select_trainer").val();
		var selSession = $("#select_session").val();

		$.ajax({
			url: "get_ajax_data.php?action=get_courses&search=" + searchVal + "&sessionId=" + selSession + "&userid=" + selTrainer,			
			success: function(data) {

				$(".stacktable").html('');
				$("#courses").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_pages&search=" + searchVal + "&sessionId=" + selSession + "&userid=" + selTrainer,
			success: function(data) {
				$(".stacktable").html('');
				$("#course_pages").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});
		
		e.preventDefault();
	});

	$("#coursebtn_search").keyup(function(event){
       if(event.keyCode == 13){
           var searchVal = $("#course_search").val();	
			var selTrainer = $("#select_trainer").val();
			var selSession = $("#select_session").val();
					
			$.ajax({
				url: "get_ajax_data.php?action=get_courses&search=" + searchVal + "&sessionId=" + selSession + "&userid=" + selTrainer,
				success: function(data) {
					$(".stacktable").html('');
					$("#courses").html(data);
					$('.responsive').stacktable({myClass: 'stacktable small-only'});
				}
			});

			$.ajax({
				url: "get_ajax_data.php?action=get_pages&search=" + searchVal + "&sessionId=" + selSession + "&userid=" + selTrainer,
				success: function(data) {
					$(".stacktable").html('');
					$("#course_pages").html(data);
					$('.responsive').stacktable({myClass: 'stacktable small-only'});
				}
			});
       }
	   event.preventDefault();
    });

	$("#modulebtn_search").live('click', function(e) {
		var selectCourse = $('#select_courses :selected').val();
		var searchVal = $("#module_search").val();	

		var selectVal = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		$.ajax({
			url: "get_ajax_data.php?action=get_modules&course_code=" + selectCourse + "&search=" + searchVal + "&user_id=" + trainer + "&sessionId=" + selectVal + "&course_search=" + course_search,
			success: function(data) {
				$(".stacktable").html('');
				$("#modules").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});	

		$.ajax({
			url: "get_ajax_data.php?action=get_module_pages&course_code=" + selectCourse + "&search=" + searchVal,
			success: function(data) {
				$("#module_pages").html(data);
			}
		});

		e.preventDefault();
	});

	$("#modulebtn_search").keyup(function(event){
       if(event.keyCode == 13){
            var selectCourse = $('#select_courses :selected').val();
			var searchVal = $("#module_search").val();
			
			var selectVal = $('#select_session :selected').val();
			var trainer = $('#select_trainer :selected').val();
			var course_search = $("#course_search").val();

			$.ajax({
				url: "get_ajax_data.php?action=get_modules&course_code=" + selectCourse + "&search=" + searchVal + "&user_id=" + trainer + "&sessionId=" + selectVal + "&course_search=" + course_search,
				success: function(data) {
					$(".stacktable").html('');
					$("#modules").html(data);
					$('.responsive').stacktable({myClass: 'stacktable small-only'});
				}
			});	

			$.ajax({
				url: "get_ajax_data.php?action=get_module_pages&course_code=" + selectCourse + "&search=" + searchVal,
				success: function(data) {
					$("#module_pages").html(data);
				}
			});
       }
	   event.preventDefault();
    });

	$("#quizbtn_search").live('click', function(e) {
		var selCourse = $('#list_courses :selected').val();		
		var selQuiz = $('#list_quiz :selected').val();		
		var selQuizType = $('#select_type :selected').val();		
		var selSession = $('#list_session :selected').val();
		var searchVal = $("#quiz_search").val();	

		$.ajax({
			url: "get_ajax_data.php?action=get_quizzes&course_code=" + selCourse + "&quiz=" + selQuiz + "&quiztype=" + selQuizType + "&sessionId=" + selSession + "&search=" + searchVal,
			success: function(data) {
				$(".stacktable").html('');
				$("#quizzes").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});			
		
		$.ajax({
			url: "get_ajax_data.php?action=get_quiz_pages&course_code=" + selCourse + "&quiz=" + selQuiz + "&quiztype=" + selQuizType + "&sessionId=" + selSession + "&search=" + searchVal,
			success: function(data) {
				$("#quiz_pages").html(data);
			}
		});

		e.preventDefault();
	});

	$("#quizbtn_search").keyup(function(event){
       if(event.keyCode == 13){
            var selCourse = $('#list_courses :selected').val();		
			var selQuiz = $('#list_quiz :selected').val();		
			var selQuizType = $('#select_type :selected').val();		
			var selSession = $('#list_session :selected').val();
			var searchVal = $("#quiz_search").val();	

			$.ajax({
				url: "get_ajax_data.php?action=get_quizzes&course_code=" + selCourse + "&quiz=" + selQuiz + "&quiztype=" + selQuizType + "&sessionId=" + selSession + "&search=" + searchVal,
				success: function(data) {
					$(".stacktable").html('');
					$("#quizzes").html(data);
					$('.responsive').stacktable({myClass: 'stacktable small-only'});
				}
			});			
			
			$.ajax({
				url: "get_ajax_data.php?action=get_quiz_pages&course_code=" + selCourse + "&quiz=" + selQuiz + "&quiztype=" + selQuizType + "&sessionId=" + selSession + "&search=" + searchVal,
				success: function(data) {
					$("#quiz_pages").html(data);
				}
			});
       }
	   event.preventDefault();
    });

	$("#facetofacebtn_search").live('click', function(e) {
		var selectCourse = $('#list_courses_ff :selected').val();
		var searchVal = $("#facetoface_search").val();	
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	
		if(selectCourse === undefined){
			selectCourse = 0;
		}
		$.ajax({
			url: "get_ajax_data.php?action=get_facetoface&course_code=" + selectCourse + "&search=" + searchVal + "&user_id=" + trainer + "&course_search=" + course_search,
			success: function(data) {
				$(".stacktable").html('');
				$("#facetoface").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});	

		
		e.preventDefault();
	});

	$("#facetofacebtn_search").keyup(function(event){
       if(event.keyCode == 13){
            var selectCourse = $('#list_courses_ff :selected').val();
		var searchVal = $("#facetoface_search").val();	
		
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	
		if(selectCourse === undefined){
			selectCourse = 0;
		}
		$.ajax({
			url: "get_ajax_data.php?action=get_facetoface&course_code=" + selectCourse + "&search=" + searchVal + "&user_id=" + trainer + "&course_search=" + course_search,
			success: function(data) {
				$(".stacktable").html('');
				$("#facetoface").html(data);
				$('.responsive').stacktable({myClass: 'stacktable small-only'});
			}
		});	

		
	   }
		e.preventDefault();
    });

	$("#learnerbtn_search").live('click', function(e) {

		var selectVal = $('#course_list :selected').val();
		var selSession = $('#session_list :selected').val();
		var selStatus = $('#learners_filter :selected').val();
		var selRank = $('#quiz_ranking :selected').val();
		var searchVal = $("#learner_search").val();	
		//searchVal = $("#autocomplete11").val();	

		selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();

		$.ajax({
			url: "get_ajax_data.php?action=get_users&course_code=" + selectVal + "&sessionId=" + selSession + "&status=" + selStatus + "&rank=" + selRank + "&search=" + searchVal + "&trainer_id=" + trainer + "&course_search=" + course_search,
			success: function(data) {
				$(".stacktable").html('');
				$("#learners").html(data);	
				$('.responsive').stacktable({myClass: 'stacktable small-only'});						
			}
		});

		$.ajax({
			url: "get_ajax_data.php?action=get_users_pages&course_code=" + selectVal + "&sessionId=" + selSession + "&status=" + selStatus + "&search=" + searchVal,
			success: function(data) {
				$("#learners_pages").html(data);
			}
		});

		e.preventDefault();
	});

	$("#learnerbtn_search").keyup(function(event){
       if(event.keyCode == 13){
            var selectVal = $('#course_list :selected').val();
			var selSession = $('#session_list :selected').val();
			var selStatus = $('#learners_filter :selected').val();
			var selRank = $('#quiz_ranking :selected').val();
			var searchVal = $("#learner_search").val();	

			$.ajax({
				url: "get_ajax_data.php?action=get_users&course_code=" + selectVal + "&sessionId=" + selSession + "&status=" + selStatus + "&rank=" + selRank + "&search=" + searchVal,
				success: function(data) {
					$(".stacktable").html('');
					$("#learners").html(data);	
					$('.responsive').stacktable({myClass: 'stacktable small-only'});						
				}
			});

			$.ajax({
				url: "get_ajax_data.php?action=get_users_pages&course_code=" + selectVal + "&sessionId=" + selSession + "&status=" + selStatus + "&search=" + searchVal,
				success: function(data) {
					$("#learners_pages").html(data);
				}
			});
       }
	   event.preventDefault();
    });

	/*$("#course_search").autocomplete("autocomplete.php?action=search_courses", {
        selectFirst: true
  });*/

  $('#statslink').live("click",function(e){

		var current_tab = $("#current_tab").val();

		if(current_tab === undefined){

			var myString = $(this).attr('href');
			var myArray = myString.split('?');
			var myUrl = myArray[1] + "&page=users";

			$.ajax({
				url: "module_result.php?" + myUrl,
				success: function(data) {
					$(".stacktable").html('');					
					$("#dataDiv").html(" ");					
					$("#dataDiv").html(data);					
					$(".responsive").stacktable({myClass: "stacktable small-only"});
				}
			});
		}
		else {

			var myString = $(this).attr('href');
			var myArray = myString.split('?');
			if(current_tab == 'learners'){
				var myUrl = myArray[1];
			}
			else {
				var myUrl = myArray[1] + "&page=module";
			}

			$.ajax({
				url: "module_result.php?" + myUrl,
				success: function(data) {
					$(".stacktable").html('');
					if(current_tab == 'learners'){
						$("#tablist1-panel5").html(" ");					
						$("#tablist1-panel5").html(data);
					}
					else {
						$("#tablist1-panel2").html(" ");					
						$("#tablist1-panel2").html(data);
					}
					$(".responsive").stacktable({myClass: "stacktable small-only"});
				}
			});
		}
		e.preventDefault();
	});

  $("#course_search").live("focus.autocomplete", function(){ $(this).autocomplete({
	  source: "autocomplete.php?action=search_courses",
	  minLength: 1,//search after two characters
	  select: function(event,ui){
		var selected_text = ui.item.value;				
		$("#course_search").val(selected_text);	
		$('#coursebtn_search').trigger('click');
		return false;
	  //do something, like search for your hotel detail page
	  }
	});
	});

	/*$("#module_search").autocomplete("autocomplete.php?action=search_modules", {
        selectFirst: true
  });*/
	
	$("#module_search").live("focus.autocomplete", function(){ $(this).autocomplete({
	  source: "autocomplete.php?action=search_modules",
	  minLength: 1,//search after two characters
	  select: function(event,ui){
		var selected_text = ui.item.value;	
	//	$("#module_search").val($(selected_text).text());	
		$("#module_search").val(selected_text);	
		$('#modulebtn_search').trigger('click');
		return false;
	  //do something, like search for your hotel detail page
	  }
	});
	});
	
	/*$("#module_search").keyup(function(event){
		
	}).autocomplete("autocomplete.php?action=search_modules&user_id="+$('#select_trainer :selected').val()+"&course_search="+$("#course_search").val(), {
			selectFirst: true
	  });*/

	/*$("#quiz_search").autocomplete("autocomplete.php?action=search_quizzes", {
        selectFirst: true
  });*/

	$("#quiz_search").live("focus.autocomplete", function(){ $(this).autocomplete({
	  source: "autocomplete.php?action=search_quizzes",
	  minLength: 1,//search after two characters
	  select: function(event,ui){
		var selected_text = ui.item.value;		
		/*var str_contains = $(selected_text).text().indexOf('-');
		alert("temp====="+temp);
		if(str_contains > -1){
			var temp = $(selected_text).text().split("-");
		$("#quiz_search").val(temp[1]);	
		}
		else {
		$("#quiz_search").val($(selected_text).text());	
		}*/
		//$("#quiz_search").val($(selected_text).text());	
		$("#quiz_search").val(selected_text);	
		$('#quizbtn_search').trigger('click');
		return false;
	  //do something, like search for your hotel detail page
	  }
	});
	});

	$("#facetoface_search").live("focus.autocomplete", function(){ $(this).autocomplete({
	  source: "autocomplete.php?action=search_facetoface",
	  minLength: 1,//search after two characters
	  select: function(event,ui){
		var selected_text = ui.item.value;	
	//	$("#module_search").val($(selected_text).text());	
		$("#facetoface_search").val(selected_text);	
		$('#facetofacebtn_search').trigger('click');
		return false;
	  //do something, like search for your hotel detail page
	  }
	});
	});
	
	/*$("#learner_search").autocomplete("autocomplete.php?action=search_learners", {
        selectFirst: true
  });*/

	/*$("#quiz_search").live("focus.autocomplete", function(){ $(this).autocomplete("autocomplete.php?action=search_quizzes", {
        selectFirst: true
  });
});*/
  
	/*$("#learner_search").live("focus.autocomplete", function(){ $(this).autocomplete("autocomplete.php?action=search_learners", {
        selectFirst: true,		
			close: function (a, b) {
        alert("Anjan");
		return false;
    }
		});		
  });*/

  /*$("#learner_search").live("focus.autocomplete", function(){ $(this).autocomplete("autocomplete.php?action=search_learners", {
            selectFirst: true,
            select: function(event, ui) {
				alert("anjan");                
            }
        });
  }); */


		$("#learner_search").live("focus.autocomplete", function(){ $(this).autocomplete({
		  source: "autocomplete.php?action=search_learners",
		  minLength: 1,//search after two characters
		  select: function(event,ui){
			var selected_text = ui.item.value;	
			  //$("#learner_search").val($(selected_text).text());	
			  $("#learner_search").val(selected_text);	
			  $('#learnerbtn_search').trigger('click');
			  return false;
		  //do something, like search for your hotel detail page
		  }
		});
		});

	
	

	$('#course_reset,#module_reset,#quiz_reset,#facetoface_reset,#learner_reset').live("click",function(e){		
		window.location.reload(true);
		/*var current_attr = $(this).attr('id');

		$("#select_trainer").val(0);
		$("#select_session").val(0);
		$("#course_search").val("");
		$("#select_courses").val(0);
		$("#list_courses").val(0);
		$("#list_session").val(0);
		$("#list_quiz").val(0);
		$("#select_type").val(0);
		$("#course_list").val(0);
		$("#session_list").val(0);
		$("#learners_filter").val(1);
		$("#quiz_ranking").val(0);
		$("#module_search").val("");
		$("#quiz_search").val("");
		$("#learner_search").val("");

		if(current_attr == 'course_reset') {
			$.ajax({
				url: "get_ajax_data.php?action=get_courses",
				success: function(data) {
					$(".stacktable").html('');
					$("#courses").html(data);
					$('.responsive').stacktable({myClass: 'stacktable small-only'});
				}
			});

			$.ajax({
				url: "get_ajax_data.php?action=get_pages",
				success: function(data) {
					$("#course_pages").html(data);
				}
			});
		}

		if(current_attr == 'module_reset') {
			$.ajax({
				url: "get_ajax_data.php?action=get_modules",
				success: function(data) {
					$(".stacktable").html('');
					$("#modules").html(data);
					$('.responsive').stacktable({myClass: 'stacktable small-only'});
				}
			});	

			$.ajax({
				url: "get_ajax_data.php?action=get_module_pages",
				success: function(data) {
					$("#module_pages").html(data);
				}
			});
		}

		if(current_attr == 'quiz_reset') {
			$.ajax({
				url: "get_ajax_data.php?action=get_quizzes",
				success: function(data) {
					$(".stacktable").html('');
					$("#quizzes").html(data);
					$('.responsive').stacktable({myClass: 'stacktable small-only'});
				}
			});	

			$.ajax({
				url: "get_ajax_data.php?action=get_quiz_pages",
				success: function(data) {
					$("#quiz_pages").html(data);
				}
			});
		}

		if(current_attr == 'learner_reset') {
			$.ajax({
				url: "get_ajax_data.php?action=get_users_pages",
				success: function(data) {
					$("#learners_pages").html(data);
				}
			});

			$.ajax({
				url: "get_ajax_data.php?action=get_users",
				success: function(data) {
					$(".stacktable").html('');
					$("#learners").html(data);	
					$('.responsive').stacktable({myClass: 'stacktable small-only'});
				}
			});
		}

		e.preventDefault();*/
	});

	$('#course_export').live("click",function(e){
		var trainer = $('#select_trainer :selected').val();
		var session = $('#select_session :selected').val();
		var searchVal = $("#course_search").val();
		
		window.location.href = "index.php?c=export&module=courses&sessionId=" + session +"&userid="+trainer+"&search="+searchVal;	
		e.preventDefault();
	});

	$('#course_print').live("click",function(e){
		var trainer = $('#select_trainer :selected').val();
		var session = $('#select_session :selected').val();
		var searchVal = $("#course_search").val();
		
		window.open("index.php?c=print&module=courses&sessionId=" + session +"&userid="+trainer+"&search="+searchVal,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		e.preventDefault();
	});

	$('#module_export').live("click",function(e){
		var action_code = $("#hid_action_code").val();

		var courses = $('#select_courses :selected').val();
		var module_search = $("#module_search").val();	

		var selectVal = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();
		if(action_code != ''){
			courses = action_code;
		}
		
		window.location.href = "index.php?c=export&module=modules&courses=" + courses + "&search=" + module_search + "&userid="+trainer+"&sessionId="+selectVal+"&course_search="+course_search;	
		e.preventDefault();
	});

	$('#module_print').live("click",function(e){
		var action_code = $("#hid_action_code").val();

		var courses = $('#select_courses :selected').val();
		var module_search = $("#module_search").val();	

		var selectVal = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();
		if(action_code != ''){
			courses = action_code;
		}
		
		window.open("index.php?c=print&module=modules&courses=" + courses + "&search=" + module_search + "&userid=" + trainer +"&sessionId="+selectVal+"&course_search="+course_search,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		e.preventDefault();
	});

	$('#quiz_export').live("click",function(e){
		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var module_course = $('#select_courses :selected').val();
		var quiz_search = $("#quiz_search").val();	

		var quiz_course = $('#list_courses :selected').val();		
		var selQuiz = $('#list_quiz :selected').val();				
		var selType = $('#select_type :selected').val();				
		var quiz_session = $('#list_session :selected').val();	
		if(quiz_session != '0') {
			selSession = $('#list_session :selected').val();	
		}	
		
		var course = '';

		if(module_course == '0' && quiz_course == '0') {
			course = '0';
		}
		else if(quiz_course == '0') {
			course = module_course;
		}
		else {
			course = quiz_course;
		}
		
		window.location.href = "index.php?c=export&module=quizzes&courses=" + course+"&quiz="+selQuiz+"&type="+selType+"&session="+selSession+"&search="+quiz_search+"&user_id="+trainer+"&course_search="+course_search;	
		e.preventDefault();
	});

	$('#quiz_print').live("click",function(e){
		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var module_course = $('#select_courses :selected').val();
		var quiz_search = $("#quiz_search").val();	

		var quiz_course = $('#list_courses :selected').val();		
		var selQuiz = $('#list_quiz :selected').val();				
		var selType = $('#select_type :selected').val();				
		var quiz_session = $('#list_session :selected').val();	
		if(quiz_session != '0') {
			selSession = $('#list_session :selected').val();	
		}
		
		var course = '';

		if(module_course == '0' && quiz_course == '0') {
			course = '0';
		}
		else if(quiz_course == '0') {
			course = module_course;
		}
		else {
			course = quiz_course;
		}
		
		window.open("index.php?c=print&module=quizzes&courses=" + course+"&quiz="+selQuiz+"&type="+selType+"&session="+selSession+"&search="+quiz_search+"&user_id="+trainer+"&course_search="+course_search,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		e.preventDefault();
	});

	$('#face2face_print').live("click",function(e){
		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var module_course = $('#select_courses :selected').val();
		var quiz_course = $('#list_courses :selected').val();		
		var ff_search = $("#facetoface_search").val();	
		
		var course = '';

		if(module_course == '0' && quiz_course == '0') {
			course = '0';
		}
		else if(quiz_course == '0') {
			course = module_course;
		}
		else {
			course = quiz_course;
		}
		
		window.open("index.php?c=print&module=face2face&courses=" + course+"&session="+selSession+"&search="+ff_search+"&user_id="+trainer+"&course_search="+course_search,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		e.preventDefault();
	});

	$('#face2face_export').live("click",function(e){
		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var module_course = $('#select_courses :selected').val();
		var quiz_course = $('#list_courses :selected').val();		
		var ff_search = $("#facetoface_search").val();	
		
		var course = '';

		if(module_course == '0' && quiz_course == '0') {
			course = '0';
		}
		else if(quiz_course == '0') {
			course = module_course;
		}
		else {
			course = quiz_course;
		}
		
		window.location.href = "index.php?c=export&module=face2face&courses=" + course+"&session="+selSession+"&search="+ff_search+"&user_id="+trainer+"&course_search="+course_search;	
		e.preventDefault();
	});

	$('#learner_export').live("click",function(e){
		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var moduleCourse = $('#select_courses :selected').val();
		var quizCourse = $('#list_courses :selected').val();		

		var learnerCourse = $('#course_list :selected').val();
		var user_session = $('#session_list :selected').val();	
		if(user_session != '0') {
			selSession = $('#session_list :selected').val();
		}			
		var filterVal = $('#learners_filter :selected').val();				
		var rankVal = $('#quiz_ranking :selected').val();	
		var searchVal = $("#learner_search").val();	

		var course = '';
		if(quizCourse == '0' && moduleCourse == '0' && learnerCourse == '0'){
			course = 0;
		}
		else if(learnerCourse == '0') {
			if(quizCourse != '0'){
			course = quizCourse;
			}
			else if(moduleCourse != '0'){
				course = moduleCourse
			}
		}
		else if(learnerCourse != '0') {
			course = learnerCourse;			
		}
		else if(quizCourse == '0') {
			course = moduleCourse;			
		}
		else {
			course = learnerCourse;
		}
		
		window.location.href = "index.php?c=export&module=learners&courses=" + course+"&sessionId="+selSession+"&filter="+filterVal+"&rank="+rankVal+"&search="+searchVal+"&trainer_id="+trainer+"&course_search="+course_search;	
		e.preventDefault();
	});

	$('#learner_print').live("click",function(e){
		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var moduleCourse = $('#select_courses :selected').val();
		var quizCourse = $('#list_courses :selected').val();		

		var learnerCourse = $('#course_list :selected').val();
		var user_session = $('#session_list :selected').val();	
		if(user_session != '0') {
			selSession = $('#session_list :selected').val();
		}
		var filterVal = $('#learners_filter :selected').val();				
		var rankVal = $('#quiz_ranking :selected').val();	
		var searchVal = $("#learner_search").val();	

		var course = '';
		if(quizCourse == '0' && moduleCourse == '0' && learnerCourse == '0'){
			course = 0;
		}
		else if(learnerCourse == '0') {
			if(quizCourse != '0'){
			course = quizCourse;
			}
			else if(moduleCourse != '0'){
				course = moduleCourse
			}
		}
		else if(learnerCourse != '0') {
			course = learnerCourse;			
		}
		else if(quizCourse == '0') {
			course = moduleCourse;			
		}
		else {
			course = learnerCourse;
		}
		
		window.open("index.php?c=print&module=learners&courses=" + course+"&sessionId="+selSession+"&filter="+filterVal+"&rank="+rankVal+"&search="+searchVal+"&trainer_id="+trainer+"&course_search="+course_search,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		e.preventDefault();
	});

	$('#individual_leaner_export').live("click",function(e){
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		window.location.href = "individual_reporting.php?c=export&module=learnersreport&" + myArray[1];		
		
		e.preventDefault();
	});

	$('#individual_leaner_print').live("click",function(e){
		var myString = $(this).attr('href');
		var myArray = myString.split('?');
		
		window.open("individual_reporting.php?c=print&module=learnersreport&" + myArray[1],"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		e.preventDefault();
	});

	$('#export_learners_list').live("click",function(e){
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		window.location.href = "list_learners.php?c=export&" + myArray[1];		
		
		e.preventDefault();
	});

	$('#export_learners_list_ff').live("click",function(e){
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		window.location.href = "list_learners_ff.php?c=export&" + myArray[1];		
		
		e.preventDefault();
	});

	$('#export_module_learners_list').live("click",function(e){
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		window.location.href = "module_learners.php?c=export&" + myArray[1];		
		
		e.preventDefault();
	});

	$('#export_screen_list').live("click",function(e){
		
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		window.location.href = "module_result.php?export=csv&" + myArray[1];
		
		e.preventDefault();
	});

	$('#print_screen_list').live("click",function(e){		

		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		window.open("module_result.php?export=print&" + myArray[1],"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		
		e.preventDefault();
	});

	$('#print_learners_list').live("click",function(e){
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		window.open("list_learners.php?c=print&" + myArray[1],"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		e.preventDefault();
	});

	$('#print_learners_list_ff').live("click",function(e){
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		window.open("list_learners_ff.php?c=print&" + myArray[1],"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		e.preventDefault();
	});

	$('#print_module_learners_list').live("click",function(e){
		var myString = $(this).attr('href');
		var myArray = myString.split('?');

		window.open("module_learners.php?c=print&" + myArray[1],"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		e.preventDefault();
	});

});

function exportprintdata(mode,module){
	if(module=='courses'){
		var trainer = $('#select_trainer :selected').val();
		var session = $('#select_session :selected').val();
		var searchVal = $("#course_search").val();	
		if(mode=='export'){
			window.location.href = "index.php?c="+mode+"&module="+module+"&sessionId=" + session +"&userid="+trainer+"&search="+searchVal;	
		}
		if(mode=='print'){
			window.open("index.php?c="+mode+"&module="+module+"&sessionId=" + session +"&userid="+trainer+"&search="+searchVal,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		}
	}
	if(module=='modules'){
		var courses = $('#select_courses :selected').val();
		var module_search = $("#module_search").val();	

		var selectVal = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	
		
		if(mode=='export'){
			window.location.href = "index.php?c="+mode+"&module="+module+"&courses=" + courses + "&search=" + module_search + "&userid="+trainer+"&sessionId="+selectVal+"&course_search="+course_search;	
		}
		if(mode=='print'){
			window.open("index.php?c="+mode+"&module="+module+"&courses=" + courses + "&search=" + module_search + "&userid=" + trainer +"&sessionId="+selectVal+"&course_search="+course_search,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		}
	}
	if(module=='quizzes'){
		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var module_course = $('#select_courses :selected').val();
		var quiz_search = $("#quiz_search").val();	

		var quiz_course = $('#list_courses :selected').val();		
		var selQuiz = $('#list_quiz :selected').val();				
		var selType = $('#select_type :selected').val();				
		selSession = $('#list_session :selected').val();	
		
		var course = '';

		if(module_course == '0' && quiz_course == '0') {
			course = '0';
		}
		else if(quiz_course == '0') {
			course = module_course;
		}
		else {
			course = quiz_course;
		}

		if(mode=='export'){
			window.location.href = "index.php?c="+mode+"&module="+module+"&courses=" + course+"&quiz="+selQuiz+"&type="+selType+"&session="+selSession+"&search="+quiz_search+"&user_id="+trainer+"&course_search="+course_search;	
		}
		if(mode=='print'){
			window.open("index.php?c="+mode+"&module="+module+"&courses=" + course+"&quiz="+selQuiz+"&type="+selType+"&session="+selSession+"&search="+quiz_search+"&user_id="+trainer+"&course_search="+course_search,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		}
	}
	if(module=='learners'){

		var selSession = $('#select_session :selected').val();
		var trainer = $('#select_trainer :selected').val();
		var course_search = $("#course_search").val();	

		var moduleCourse = $('#select_courses :selected').val();
		var quizCourse = $('#list_courses :selected').val();		

		var learnerCourse = $('#course_list :selected').val();
		selSession = $('#session_list :selected').val();				
		var filterVal = $('#learners_filter :selected').val();				
		var rankVal = $('#quiz_ranking :selected').val();	
		var searchVal = $("#learner_search").val();	

		var course = '';
		if(quizCourse == '0' && moduleCourse == '0' && learnerCourse == '0'){
			course = 0;
		}
		else if(learnerCourse == '0') {
			if(quizCourse != '0'){
			course = quizCourse;
			}
			else if(moduleCourse != '0'){
				course = moduleCourse
			}
		}
		else if(quizCourse == '0') {
			course = moduleCourse;			
		}
		else {
			course = learnerCourse;
		}

		if(mode=='export'){
			window.location.href = "index.php?c="+mode+"&module="+module+"&courses=" + course+"&session="+selSession+"&filter="+filterVal+"&rank="+rankVal+"&search="+searchVal+"&trainer_id="+trainer+"&course_search="+course_search;	
		}
		if(mode=='print'){
			window.open("index.php?c="+mode+"&module="+module+"&courses=" + course+"&session="+selSession+"&filter="+filterVal+"&rank="+rankVal+"&search="+searchVal+"&trainer_id="+trainer+"&course_search="+course_search,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		}
	}
}

function exportlearnersdata(mode,module){
	if(module=='learnersreport'){
		var modeval = new Array();
		modeval = mode.split("@");
		var userid = modeval[1];
		var course_search = modeval[2];

		if(modeval[0]=='export'){
			window.location.href = "individual_reporting.php?c="+modeval[0]+"&module="+module+"&userid=" + userid + "&course_search=" + course_search;	
		}
		if(modeval[0]=='print'){
			window.open("individual_reporting.php?c="+modeval[0]+"&module="+module+"&userid=" + userid + "&course_search=" + course_search,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
		}
	}
}

function exportlearnerslist(mode,quizid,code){
	if(mode=='export'){
		window.location.href = "list_learners.php?c="+mode+"&quiz_id="+quizid+"&course_code=" + code;	
	}
	if(mode=='print'){
		window.open("list_learners.php?c="+mode+"&quiz_id="+quizid+"&course_code=" + code,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
	}
}