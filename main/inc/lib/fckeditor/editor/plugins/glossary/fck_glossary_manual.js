$(document).ready(function() {
    $(window).load(function () {
	  my_protocol = location.protocol;
	  my_pathname=location.pathname;
	  work_path = my_pathname.substr(0,my_pathname.indexOf('/courses/'));
	    $("body .glossary").mouseover(function(){
	        is_glossary_name=$(this).html();
	        div_show_id="div_show_id";
	        div_content_id="div_content_id";
                var text_box = $(this).text();
                $("<div style='display:none;' title='"+ text_box +"' id="+div_content_id+">&nbsp;</div>").insertAfter(this);
	       $.ajax({
	            contentType: "application/x-www-form-urlencoded",
	            type: "POST",
	            url: my_protocol+"//"+location.host+work_path+"/main/glossary/glossary_ajax_request.php",
	            data: "glossary_name="+is_glossary_name,
	            success: function(response) {
                          $("div#"+div_content_id).show();
                          $("div#"+div_content_id).css({
                                'width':'500px', 
                                'border':'6px solid #525252', 
                                'min-height':'120px',
                                'padding':'5px 10px',
                                '-moz-border-radius': '5px',
                                '-webkit-border-radius': '5px',
                                'border-radius': '5px',
                                'z-index':'1000',
                                'background-color':'#FFF',
                                'font-size':'12px',
                                'font-family':'Verdana',
                                'color':'#000',
                                'overflow':'auto'
                          });    
                          $("div#"+div_content_id).center();
                          var btn_close = '<div class="btn-glossary-close" style="text-align:right;background-color: #E8E8E8;height: 15px;margin:-5px -10px;padding:5px;margin-bottom:5px;overflow:hidden;"><table width="100%"><tr><td align="left" valign="top" style="font-weight:bold;">'+text_box+'</td><td align="right" valign="top" width="50px;"><a href="javascript:void(0)" onclick="closeGlossaryPopup(\'div_content_id\');"><img src="'+my_protocol+"//"+location.host+work_path+"/main/img/"+'close.gif" border="0" /></a></td></tr></table></div>';
                          $("div#"+div_content_id).html(btn_close+response); 

	            }
	        });
	    });
    });
});

function closeGlossaryPopup(obj) {
    $("div#"+obj).hide("slow");    
    $("div#"+obj).remove();
}

jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", "20%");
    this.css("left", "20%");
    return this;
}