$(document).ready(function() {    
    var pages = {DOKEOS_ITEMS};
    var titles = {DOKEOS_ITEMS_TITLES};
    var nb_pages = pages.length;
    
    $('iframe').attr('src',pages[0]);
    $("#nav-item-title").html(titles[0]);    
    $("#pagination").html("1 / " + nb_pages);
    
    //iframeLoadPage();
    $(".nav").click(function(e) {        
        e.preventDefault();
        var page = $("#current_page").val();
        var new_page = page;
        var step = $(this).attr("id");

        if (step == "next") {
            if (page < nb_pages) {
                new_page = parseInt(page) + 1;
            }
        }
        else {
            if (page > 1) {
                new_page = parseInt(page) - 1;
            }
        }
        $("#prev").removeClass("first");
        $("#next").removeClass("last");

        if (new_page == 1) {
            $("#prev").addClass("first");
        }

        if (new_page == nb_pages) {
            doScormQuit("completed");
            $("#next").addClass("last");
        }
        $("#pagination").html(new_page + " / " + nb_pages);
        $("#current_page").val(new_page);

        $('iframe').attr('src',pages[parseInt(new_page) - 1]);
        $("#nav-item-title").html(titles[parseInt(new_page) - 1]);
    });
 
});

addEvent(window,'load',addListeners,false);

function doScormQuit(status) {
   computeTime();
   exitPageStatus = true;
   var result;
   result = doLMSCommit();
   result = doLMSSetValue("cmi.core.lesson_status", status);
   result = doLMSFinish();
}

function addListeners(e) {
    loadPage();
}


