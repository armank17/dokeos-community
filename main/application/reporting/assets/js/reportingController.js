$(function() {
    
    RESPONSIVEUI.responsiveTabs();
    
    if ($('.responsive').length) {
        $('.responsive').stacktable({myClass: 'stacktable small-only'});    
    }
    
    $("#tablist1-tab1").click(function() {
        ReportingModel.displayTabContent($("#tablist1-panel1"), 'displaySessionsTab');
    });
    
    $("#tablist1-tab2").click(function() {
        ReportingModel.displayTabContent($("#tablist1-panel2"), 'displayCoursesTab');
    });
    
    $("#tablist1-tab3").click(function() {
        ReportingModel.displayTabContent($("#tablist1-panel3"), 'displayModulesTab');
    });
    
    $("#tablist1-tab4").click(function() {
        ReportingModel.displayTabContent($("#tablist1-panel4"), 'displayQuizzesTab');
    });
    
    $("#tablist1-tab5").click(function() {
        ReportingModel.displayTabContent($("#tablist1-panel5"), 'displayFace2FaceTab');
    });
    
    $("#tablist1-tab6").click(function() {
        ReportingModel.displayTabContent($("#tablist1-panel6"), 'displayLearnersTab');
    });
    
    if ($(".cbo-filters").length > 0) {
        $(".cbo-filters").change(function() {            
            ReportingModel.changeCboFilters($(this));                        
        });
    }
    
    if ($("#filter_reset").length) {
        $("#filter_reset").click(function(){
            ReportingModel.resetFilter();
        });
    }
  
    if ($("#search-filter-form").length) {
        $("#search-filter-form").submit(function(e){
           e.preventDefault();
           return false;
        });        
        $("#filter-search").keypress(function(e) {
            if(e.which == 13) {
               $("#btn-search").click();
            }
        });
        $("#btn-search").click(function(e) {
            e.preventDefault();
            ReportingModel.submitSearch($("#search-filter-form"));
        });                          
        $("#filter_submit").click(function() {
            ReportingModel.submitFilter($("#search-filter-form"));
        });        
    }
           
    if ($(".action_module_detail").length) {
        $(".action_module_detail").click(function(e){
            ReportingModel.displayCourseModules(e, $(this));
        });
    }
    
    if ($(".paginate").length) {
        $(".paginate").click(function(e){
            ReportingModel.paginateItems(e, $(this), $("#search-filter-form"));
        });
    }
    
    if ($(".reporting-print").length) {
        $(".reporting-print").click(function(e){            
            e.preventDefault();
            var myurl = $(this).attr("href");
            var myopen = window.open(myurl, "toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, width=600, height=400");
            myopen.print();
        });
    }
    
    if ($(".action_module_users").length) {
        $(".action_module_users").click(function(e){
            ReportingModel.displayModuleUserDetail(e, $(this));
        });
    }
    if ($(".action_quiz_users").length) {
        $(".action_quiz_users").click(function(e){
            ReportingModel.displayUserQuizResult(e, $(this));
        });
    }
    //conflict $(".logoutClick").tooltip({});
    //$(document).tooltip();
    
});

