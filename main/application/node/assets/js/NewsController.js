/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){
    
    if ($("#news-form").length > 0) {
        $.validator.addMethod('cb_select_one2', function(value, element) {
            var fields = $("input[name^='visible_']:checked");
            return (fields.length > 0);
        }, 'Select an option');


        $("#news-form").validate({
            ignore: '',
            rules: {
                startDate: 'required',
                endDate: 'required',
                //node_title: 'required',
                news_visibility: {
                        cb_select_one2: true
                }
            }
        });
    }
    
    
    if($(".page-delete-news").length > 0){
        $(".page-delete-news").click(function(e){
            NewsModel.deleteNew($(this), e); 
        });
    }

 $(function() {
    $('#exam-from, #exam-to').blur();
    $( "#from" ).datetimepicker({
        defaultDate: "+1w",
        //changeMonth: true,
        //numberOfMonths: 1,
        dateFormat: "dd-mm-yy",
        currentText: getLang("Today"),
	closeText: getLang("Done"),
        showOn: "button",
        buttonImage: "../main/img/calendar.gif",
        buttonImageOnly: true,
        timeFormat: "hh:mm tt",
        onClose: function( selectedDate ) {
//            var testStartDate = $(this).datetimepicker("getDate");
//            //testStartDate.setHours(testStartDate.getHours()+1);
//            testStartDate.setDate(testStartDate.getDate()+1); 
//            $("#to").datetimepicker("setDate", testStartDate);
            $( "#to" ).datepicker("option", "minDate", selectedDate);
        },onSelect: function (selectedDate){
//            var testStartDate = $(this).datetimepicker("getDate");
//            //testStartDate.setHours(testStartDate.getHours()+1);
//            testStartDate.setDate(testStartDate.getDate()+1); 
            $("#to").datetimepicker("setDate", selectedDate);
        }
    });
    $( "#to" ).datetimepicker({
        defaultDate: "+1w",
        //changeMonth: true,
        //numberOfMonths: 1,
        dateFormat: "dd-mm-yy",
        currentText: getLang("Today"),
	closeText: getLang("Done"),
        showOn: "button",
        buttonImage: "../main/img/calendar.gif",
        buttonImageOnly: true,
        timeFormat: "hh:mm tt",
        onClose: function( selectedDate ) {
            $( "#from" ).datepicker("option", "maxDate", selectedDate);
        },onSelect: function (selectedDate){
            $( "#from" ).datepicker("option", "maxDate", selectedDate);
        }
    });
});
   
    $(".set_visible").click(function(e) {
        NewsModel.setVisible($(this), e);
    });
    
    $(".enabled").click(function(e) {
        NewsModel.enableNode($(this), e);
    });

});

