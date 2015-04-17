var ReportingModel = function() {        
    return {
        displayTabContent: function(panel, func) {
            var webPath = decodeURIComponent($("#webPath").val());           
            var currentTab = $("#current-tab").val();
            var myurl = webPath+"main/index.php?module=reporting&cmd=ReportAjax&func="+func+'&currentTab='+currentTab+'&tns='+Math.random();
            $(".responsive-tabs__panel").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            if ($("#trainer").length) {
                myurl += '&selectedTrainer='+$("#trainer").val();
            }
            $.ajax({
                url: myurl,
                success: function(data) {
                    panel.html(data);
                }
            });
        },
        displayModuleUsers: function(e, select) {
            e.preventDefault();            
            var webPath = decodeURIComponent($("#webPath").val());
            var myurl = select.attr("href");
            $(".responsive-tabs__panel").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            $.ajax({
                url: myurl,
                success: function(data) {
                    $("#tablist1-panel3").html(data);
                }
            });
        },        
        displayModuleUserDetail: function(e, select) {
            var webPath = decodeURIComponent($("#webPath").val());
            var currentTab = $("#current-tab").val();
            e.preventDefault();            
            var myurl = select.attr("href");
            var panel;
            if (currentTab == 'user_detail' || currentTab == 'learner_reporting') {
                panel = $("#tablist1-panel6");
            }
            else {
                panel = $("#tablist1-panel3");
            }
            $(".responsive-tabs__panel").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            $.ajax({
                url: myurl,
                success: function(data) {                    
                    panel.html(data);
                }
            });
        },
        displayUserQuizResult: function(e, select) {
            var webPath = decodeURIComponent($("#webPath").val());
            var currentTab = $("#current-tab").val();
            e.preventDefault();            
            var myurl = select.attr("href");
            var panel;
            if (currentTab == 'user_detail' || currentTab == 'learner_reporting') {
                panel = $("#tablist1-panel6");
            }
            else if (currentTab == 'module_users') {
                panel = $("#tablist1-panel3");
            }
            else {
                panel = $("#tablist1-panel4");
            }
            $(".responsive-tabs__panel").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            $.ajax({
                url: myurl,
                success: function(data) {                    
                    panel.html(data);
                }
            });
        },     
        displayUserAccessDetails: function(e, select) {
            var webPath = decodeURIComponent($("#webPath").val());
            e.preventDefault();         
            var myurl = select.attr("href");
            var panel;
            panel = $("#tablist1-panel6");            
            $(".responsive-tabs__panel").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            $.ajax({
                url: myurl,
                success: function(data) {                    
                    panel.html(data);
                }
            });
        },        
        displayLearnerDetail: function(e, select) {
            e.preventDefault(); 
            var webPath = decodeURIComponent($("#webPath").val());
            var myurl = select.attr("href");
            $(".responsive-tabs__panel").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            $.ajax({
                url: myurl,
                success: function(data) {
                    $("#tablist1-panel6").html(data);
                }
            });
        },
        displayCourseModules: function(e, select) {
            e.preventDefault();
            var webPath = decodeURIComponent($("#webPath").val());
            var myurl = select.attr("href");
            $(".responsive-tabs__panel").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            $.ajax({
                url: myurl,
                success: function(data) {
                    $("#tablist1-tab3").click();
                    $("#tablist1-panel3").html(data);
                }
            });
        },
        displaySessionCourses: function(e, select) {
            e.preventDefault();
            var webPath = decodeURIComponent($("#webPath").val());
            var myurl = select.attr("href");
            $(".responsive-tabs__panel").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            $.ajax({
                url: myurl,
                success: function(data) {
                    $("#tablist1-tab2").click();
                    $("#tablist1-panel2").html(data);
                }
            });
        },        
        displayQuizUsers: function(e, select) {
            var webPath = decodeURIComponent($("#webPath").val());
            e.preventDefault();
            var myurl = select.attr("href");
            $(".responsive-tabs__panel").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            $.ajax({
                url: myurl,
                success: function(data) {
                    $("#tablist1-panel4").html(data);
                }
            });
        },
        paginateItems: function(e, select, myform) {
            e.preventDefault();
            var webPath = decodeURIComponent($("#webPath").val());            
            var myurl = select.attr("href");
            var currentTab = $("#current-tab").val();
            var formValues = myform.serialize();
            var panel;
            if (currentTab == 'sessions') {
                panel = $("#tablist1-panel1");
            }
            else if (currentTab == 'courses') {
                panel = $("#tablist1-panel2");
            }
            else if (currentTab == 'modules') {
                panel = $("#tablist1-panel3");
            }
            else if (currentTab == 'quizzes') {
                panel = $("#tablist1-panel4");
            }
            else if (currentTab == 'learners') {
                panel = $("#tablist1-panel6");
            }
            if ($(".data-container").length) {
                $(".data-container").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            }          
            $.post(myurl, formValues, function(data){
                panel.html(data);
            });
        },
        paginateModuleUsers: function(e, select) {
            e.preventDefault();            
            var webPath = decodeURIComponent($("#webPath").val());               
            var myurl = select.attr("href");
            if ($(".data-container").length) {
                $(".data-container").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            }
            $.ajax({
                url: myurl,
                success: function(data) {
                    $("#tablist1-panel3").html(data);
                }
            });
        },
        paginateQuizUsers: function(e, select) {
            e.preventDefault();            
            var webPath = decodeURIComponent($("#webPath").val());             
            var myurl = select.attr("href");
            if ($(".data-container").length) {
                $(".data-container").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            }
            $.ajax({
                url: myurl,
                success: function(data) {
                    $("#tablist1-panel4").html(data);
                }
            });
        },
        paginateFace2FaceUsers: function(e, select) {
            e.preventDefault();            
            var webPath = decodeURIComponent($("#webPath").val());             
            var myurl = select.attr("href");
            if ($(".data-container").length) {
                $(".data-container").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            }
            $.ajax({
                url: myurl,
                success: function(data) {
                    $("#tablist1-panel5").html(data);
                }
            });
        },
        displayFace2faceUsers: function(e, select) {
            e.preventDefault();
            var webPath = decodeURIComponent($("#webPath").val());
            var myurl = select.attr("href");
            $(".responsive-tabs__panel").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            $.ajax({
                url: myurl,
                success: function(data) {
                    $("#tablist1-panel5").html(data);
                }
            });
        },
        changeCboFilters: function(select) {
            var webPath = decodeURIComponent($("#webPath").val());
            var currentTab = $("#current-tab").val();
            var type = select.attr("id");
            var myurl  = webPath + 'main/index.php?module=reporting&cmd=ReportAjax&func=changeCboFilters&type='+type+'&currentTab='+currentTab;
            if ($("#category").length) {
                myurl += '&selectedCategory='+$("#category").val();
            }
            if ($("#session").length) {
                myurl += '&selectedSession='+$("#session").val();
            }
            if ($("#course").length) {
                myurl += '&selectedCourse='+$("#course").val();
            }
            if ($("#trainer").length) {
                myurl += '&selectedTrainer='+$("#trainer").val();
            }
            if ($("#quiz").length) {
                myurl += '&selectedQuiz='+$("#quiz").val();
            }
            if ($("#quiz-type").length) {
                myurl += '&selectedQuizType='+$("#quiz-type").val();
            }
            if ($("#active-learner").length) {
                myurl += '&selectedActiveLearner='+$("#active-learner").val();
            }
            if ($("#quiz-ranking").length) {
                myurl += '&selectedQuizRanking='+$("#quiz-ranking").val();
            }            
            $.ajax({
                url: myurl,
                success: function(data) {
                    $("#search-filters").html(data);
                }
            });
        },
        submitSearch: function(myform) {
            var webPath = decodeURIComponent($("#webPath").val());
            var type = $("#current-tab").val();
            var formValues = myform.serialize();
            var panel;
            if (type == 'sessions') {
                panel = $("#tablist1-panel1");
            }
            else if (type == 'courses') {
                panel = $("#tablist1-panel2");
            }
            else if (type == 'modules' || type == 'module_users') {
                panel = $("#tablist1-panel3");
            }
            else if (type == 'quizzes' || type == 'quiz_users') {
                panel = $("#tablist1-panel4");
            }
            else if (type == 'learners') {
                panel = $("#tablist1-panel6");
            }
            else if (type == 'facetoface' || type == 'facetoface_users') {
                panel = $("#tablist1-panel5");
            }            
            if ($(".data-container").length) {
                $(".data-container").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            }
            $.post(webPath+'main/index.php?module=reporting&cmd=ReportAjax&func=submitSearch', formValues, function(data){
                panel.html(data);
            });
        },
        submitFilter: function(myform) {
            var webPath = decodeURIComponent($("#webPath").val());
            var type = $("#current-tab").val();
            var formValues = myform.serialize();
            var panel;
            if (type == 'sessions') {
                panel = $("#tablist1-panel1");
            }
            else if (type == 'courses') {
                panel = $("#tablist1-panel2");
            }
            else if (type == 'modules') {
                panel = $("#tablist1-panel3");
            }
            else if (type == 'quizzes') {
                panel = $("#tablist1-panel4");
            }
            else if (type == 'learners') {
                panel = $("#tablist1-panel6");
            }
            else if (type == 'facetoface') {
                panel = $("#tablist1-panel5");
            }
            if ($(".data-container").length) {
                $(".data-container").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            }
            $.post(webPath+'main/index.php?module=reporting&cmd=ReportAjax&func=submitFilters', formValues, function(data){
                panel.html(data);
            });
        },
        resetFilter: function() {
            var webPath = decodeURIComponent($("#webPath").val());
            var type = $("#current-tab").val();
            var panel;
            if (type == 'sessions') {
                panel = $("#tablist1-panel1");
            }
            else if (type == 'courses') {
                panel = $("#tablist1-panel2");
            }
            else if (type == 'modules') {
                panel = $("#tablist1-panel3");
            }
            else if (type == 'quizzes') {
                panel = $("#tablist1-panel4");
            }
            else if (type == 'learners') {
                panel = $("#tablist1-panel6");
            }
            else if (type == 'facetoface') {
                panel = $("#tablist1-panel5");
            }
            if ($(".data-container").length) {
                $(".data-container").html('<div id="loaderTab"><img src="'+webPath+'main/img/ajaxloader.gif" /></div>');
            }
            $.get(webPath+'main/index.php?module=reporting&cmd=ReportAjax&func=resetFilters&currentTab='+type, function(data){
                panel.html(data);
            });
        },
        printPage: function(e, select) {
            e.preventDefault();      
            var mywidth = 950;
            var myheight = 600;
            var myurl = select.attr("href");       
            var mytitle = select.attr("title");
            var myiframe = $("<iframe src='"+myurl+" 'frameborder='0'></iframe>");        
            var closeText = $("#closeText").attr("class");             
            // UI Dialog
            myiframe.dialog({
                autoOpen: false,
                modal: true,
                title: mytitle,
                resizable: false,
                width: mywidth,
                height: myheight,
                closeText:closeText,
                dialogClass: 'open-ui-page-dialog'
            });        
            myiframe.dialog('open');
            myiframe.css({"display":"block", "width": (mywidth - 15)+'px', "height": (myheight - 15)+'px'});
        },
        cutTooltip: function() {
            $('.cut-tooltip[title]').qtip({
                style: { 
                    padding: 5,
                    background: '#A2D959',
                    color: 'black',
                    textAlign: 'center',
                    border: {
                       width: 7,
                       radius: 5,
                       color: '#A2D959'
                    },
                    name: 'dark'
                 }
            });
        }
    };   
}();