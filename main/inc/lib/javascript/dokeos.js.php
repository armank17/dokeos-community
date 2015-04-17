<?php
// $cidReset is mandatory
//$cidReset= TRUE;
require_once dirname(__FILE__).'/../../global.inc.php';

require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpath.class.php';
require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpathItem.class.php';
$is_sco = false;
if (isset($_SESSION['lpobject'])) {
    $mylp = unserialize($_SESSION['lpobject']);
    if (intval($mylp->type) == 1) {
        $is_sco = false;
    }
    else {
        $is_sco = true;
    }
}
?>
var myDokeosWebPath = "<?php echo api_get_path(WEB_PATH); ?>";
var device_is_mobile = false;
// this script contains all the Dokeos specific javascript
// is is a php function so that we can use php code also
jQuery(document).ready( function($) {
        device_is_mobile = api_device_is_mobile();
        // focus first element in a form content
        //$('#content form:first *:input[type!=hidden]:first:not(button)').focus();
        
        //$('#content form input:text:first').focus();
        
        if ($(".pull-bottom").length > 0) {
            $("#content").css("padding-bottom", "90px");
            $("#content").css("min-height", "410px");
        }
        
        if ($(".custom-dashed").length > 0) {
            $("#courseintroduction").css("margin-top", "0px");
        }
        
        if ($("#tool_1").length > 0) {
            $(".scroll").css("margin-top", "10%");
        }
        
        if ($(".row").length > 0) {
            $(".label1").attr("style","padding-top:12px !important")
        }
        
        // general customization form
        if ($(".formw").length > 0) { 
            <!--$(".label").attr("style","margin-top:2px; margin-bottom:2px");-->
            $(".label").css({"margin-top":"2px", "margin-bottom":"2px"});
            <!--$(".formw").attr("style","margin-top:2px; margin-bottom:2px");-->
            $(".formw").css({"margin-top":"2px", "margin-bottom":"2px"});
            <!--$(".formw1 .cusformw-content").attr("style","margin-top:2px; margin-bottom:2px");-->
            $(".formw1 .cusformw-content").css({"margin-top":"2px", "margin-bottom":"2px"});
            <!--$(".formw1 input[type='radio'], .formw input[type='checkbox'], .formw input.NFI-current[type='file']").attr("style", "margin-top:-12px !important");-->
            $(".formw1 input[type='radio'], .formw input[type='checkbox'], .formw input.NFI-current[type='file']").css({"margin-top":"-12px !important"});
            <!--$(".formw .formw1 input[type='text']").attr("style", "margin-top:-10px ; margin-bottom:8px");-->
            $(".formw .formw1 input[type='text']").css({"margin-top":"-10px","margin-bottom":"8px"});
            <!--$(".formw .formw1 select").attr("style", "margin-top:-8px !important");-->
            <!--$("#new_myagenda_item .formw .formw1 select").attr("style", "margin-top:0px !important");-->
            $(".formw .formw1 select").css({"margin-top":"-8px !important"});
            <!--$(".cusformw-content").attr ("style","padding-top:15px;")-->
            $(".cusformw-content").css ({"padding-top":"12px"})
        }
        
        // other customization form
         if ($(".formw").length > 0) { 
            $(".formw1 .cusformw-content").css({"margin-top":"2px", "margin-bottom":"2px"});
        }
        
	// Expand or collapse the help
	$('#help-link').click(function () {
		$('#help-content').slideToggle('fast', function() {
			if ( $(this).hasClass('help-open') ) {
				$('#help a').css({'backgroundImage':'url("<?php echo api_get_path(WEB_PATH); ?>main/img/screen-options-right.gif")'});
				$(this).removeClass('contextual-help-open');
			} else {
				$('#help a').css({'backgroundImage':'url("<?php echo api_get_path(WEB_PATH); ?>main/img/screen-options-right-up.gif")'});
				$(this).addClass('help-open');
			}
		});
		return false;
	});

	$(window).load(function () {
		$(".focus").focus();
	});

	// Expand or collapse the who is online
	$('#online-link').click(function () {
		$('#online-content').slideToggle('fast', function() {
			if ( $(this).hasClass('help-open') ) {
				$('#online a').css({'backgroundImage':'url("<?php echo api_get_path(WEB_PATH); ?>main/img/screen-options-right.gif")'});
				$(this).removeClass('help-open');
				var action = 'closing';
			} else {
				$('#online a').css({'backgroundImage':'url("<?php echo api_get_path(WEB_PATH); ?>main/img/screen-options-right-up.gif")'});
				$(this).addClass('help-open');
				var action = 'opening';
			}
			
			if ( action == 'opening' ){
				$.ajax({
					beforeSend: function(object) {
						$("#online-content").html('<?php Display::display_icon('loadingAnimation.gif'); ?>');
					},
					contentType: "application/x-www-form-urlencoded",
					type: "GET",
					url: "<?php echo api_get_path(WEB_CODE_PATH);?>ajax.php",
					data: "action=whoisonline&display=thumbnails",
					success: function(data) {
						$("#online-content").html(data);
					}
				});
			}
		});
		return false;
	});

	// change the url of the links with class make_visible_and_invisible so that the link is not followed when clicked
	// we use this to make it backwards compatible when javascript is disabled
  	$("a.make_visible_and_invisible").attr("href","javascript:void(0);");

	// when we click a link with class make_visible_and_invisible we change the visibility of the tool
	$("a.make_visible_and_invisible >img").click(function () {
		

                // This code is added in order for support the tablet style
                try {
                  image = ""; 
                  image_css_info = $(this).attr("class");
                  current_css = image_css_info.replace("actionplaceholderminiicon","");
                  current_css = jQuery.trim(current_css);
                } catch(e){
                  current_css = "";
		// the visibility image is a full url. We want to know if its invisible.gif or visible.gif
		  image_url = $(this).attr("src");
		  image = image_url.replace("<?php echo api_get_path(WEB_IMG_PATH); ?>","");
                }
		// are we making the tool visible or invisible? This all depend on the current icon
		if (image=="closedeye_tr.png" || current_css=="toolactionhide"){
			action = "make_visible";
                        current_css = "toolactionhide";
		} else {
			action = "make_invisible";
                        current_css = "toolactionview";
		}
                 
		// the id of the tool that we are changing
		tool_id = $(this).attr("id").replace("linktool_","");

		$.ajax({
			contentType: "application/x-www-form-urlencoded",
			beforeSend: function(object) {
				$('.confirmation-message').hide();
				$(".normal-message-lib").show();
			},
			type: "GET",
                        url: "<?php echo api_get_path(WEB_CODE_PATH);?>course_home/ajax.php?<?php echo api_get_cidreq(); ?>",
			data: "id="+tool_id+"&action="+action+"&current_css="+current_css,
			success: function(data) {
				// make the tool visible
				if (action == 'make_visible'){
					// change the visibility icon, its alt text and its title
                                        if (current_css=='toolactionhide') {
					  $("#linktool_"+tool_id).attr("src", "<?php echo api_get_path(WEB_IMG_PATH); ?>pixel.gif");
					  $("#linktool_"+tool_id).attr("class", "actionplaceholderminiicon toolactionview");
                                        } else {
					  $("#linktool_"+tool_id).attr("src", "<?php echo api_get_path(WEB_IMG_PATH); ?>visible_link.png");
                                        }
					$("#linktool_"+tool_id).attr("alt", "<?php echo get_lang('VisibleClickToMakeInvisible'); ?>");
					$("#linktool_"+tool_id).attr("title", "<?php echo get_lang('VisibleClickToMakeInvisible'); ?>");

					// the feedback message that should be displayed
					message ="<?php echo get_lang('ToolIsNowVisible', '').' ';?>" + '('+ data +')';

					// change the visible style
					$("#tool_"+tool_id).toggleClass('invisible');
				}

				// make the tool invisible
				if (action == 'make_invisible'){
                                        if (current_css=='toolactionview') {
					  $("#linktool_"+tool_id).attr("src", "<?php echo api_get_path(WEB_IMG_PATH); ?>pixel.gif");
					  $("#linktool_"+tool_id).attr("class", "actionplaceholderminiicon toolactionhide");
                                        } else {
					  $("#linktool_"+tool_id).attr("src", "<?php echo api_get_path(WEB_IMG_PATH); ?>closedeye_tr.png");
                                        }
					$("#linktool_"+tool_id).attr("alt", "<?php echo get_lang('InvisibleClickToMakeVisible'); ?>");
					$("#linktool_"+tool_id).attr("title", "<?php echo get_lang('InvisibleClickToMakeVisible'); ?>");
					
					// the feedback message that should be displayed
					message = "<?php echo get_lang('ToolIsNowHidden', '').' '; ?>" + '('+ data +')';

					// change the tool icon
					tool_image = $("#toolimage_"+tool_id).attr("src");
					
					// change the visible style
					$("#tool_"+tool_id).toggleClass('invisible');
				}

				// add or remove the invisible class to the tool link
				$("#istooldesc_"+tool_id).toggleClass("invisible");

				// hide the "processing" feedback message			
				$(".normal-message-lib").hide();

				// display the confirmation message (with the correct feedback message)
				$(".confirmation_message_content").html(message);
                                $('.confirmation-message').show();
			}
		});
	}); 
        
       var map = { 
        'confirmation-message': '.confirmation-message', 
        'warning-message-lib': '.warning-message-lib', 
        'error-message-lib': '.error-message-lib', 
        'normal-message-lib': '.normal-message-lib' 
      }; 
      
      $.each(map, function(key, value) { 
             $(value+' .close_message_box').click(function() {
                    $(this).parent().parent().parent().remove();
                    $(".course-image-upload").css("top", "3%"); // this css modifies: "upload image" in "course_info"
            });
      });

      if ($(".cut-tooltip").length > 0) {
        $('.cut-tooltip[title]').qtip({
            style: { 
                width: 800,
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
      
      if ($(".sas-attribute-blocked").length) {
        $(".sas-attribute-blocked").click(function(e) {
            e.preventDefault();
            var message = "<?php echo get_lang('ThisOptionIsBlockedDueYourCurrentVersionIsLimited'); ?><br /><?php echo get_lang('GoToYourAccountPageToUpgrade').' <br /><button class=\"save\" onclick=\" location.href=\''.api_get_path(WEB_CODE_PATH).'index.php?module=suiteManager&cmd=Pricing&func=index\';\" style=\"float:none;margin:auto;\" >Upgrade</button>'; ?>";
            $.messageBox(message, "<?php echo get_lang('Warning'); ?>", "warning", false, 500, 200);
            return false;
        });
      }
    
      /**
     * Checks if the current device is mobile or tablets
     * 
     * @author Elmer Charre Salazar <elmer.nyd@gmail.com>
     */
    function api_device_is_mobile() {
        if($.browser.device = (/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase()))) {
            return true;
        } else {
            return false;
        }
    }
      
    /**
     * Checks if the current page uses jwplayer 5 and replaces the code to version 6 
     * to load correctly the old contents.
     * 
     * @author Ricardo Garcia Rodriguez <master.ojitos@gmail.com>
     */
    function replace_jwplayer_to_v6() {
        var run, iframe_script_text, current_player, config, current_script, editor, i, config_temp, config_split, file, play, buffer, setup, iframe_script, players, iframe, is_chrome;
        run = function(players, iframe){
            if (iframe) {
                iframe_script_text = '';
            }
            players.each(function() {
                try {
                    current_player = $(this);
                    config = current_player.parent().parent().find("div[id$=-config]").text();
                    current_script = current_player.find("script:last");
                    is_chrome = false;
                    if(/chrom(e|ium)/.test(navigator.userAgent.toLowerCase())){
                        is_chrome = true;
                    }
                    // added to load the player in chrome too
                    if (current_script.html().indexOf("skin") < 0 && !is_chrome) {
                        return;
                    }
                    editor = config.indexOf("fileUrl") >= 0 ? "ckeditor" : "fckeditor";
                    config = config.split(" ");
                    config_temp = new Array();
                    for (i = 0; i < config.length; i++) {
                        config_split = config[i].split("=");
                        config_temp[config_split[0]] = config_split[1];
                    }
                    config = config_temp;
                    if (editor == "ckeditor") {
                        file = config["fileUrl"];
                        play = config["autoplay"];
                        buffer = config["cmbBuffer"];
                    } else {
                        file = config["url"];
                        play = config["play"];
                        buffer = config["buffer"];
                    }
                    setup = {
                        file: file, 
                        width: config["width"], 
                        height: config["height"], 
                        autostart: play, 
                        repeat: config["loop"], 
                        bufferlength: buffer,
                        primary: "flash"
                    };
                    if (iframe) {
                        setup = $.toJSON(setup);
                        iframe_script_text += 'window.frames[0].jwplayer("' + current_player.attr("id") + '-parent2").setup(' + setup + ');';
                    } else {
                        jwplayer(current_player.attr("id") + "-parent2").setup(setup);                
                    }
                } catch(e) {}
            });
            if (iframe) {
                iframe_script = window.frames[0].document.createElement("script");
                iframe_script.text = iframe_script_text;
                iframe.append(iframe_script);
            }
        };
        players = $(".thePlayer");
        if (players.length > 0) {
            run(players);
        }

        iframe = $("iframe#content_id");
        if (iframe.length > 0) {
            iframe.load(function() {
                iframe = $(this).contents();
                players = iframe.find(".thePlayer");
                if (players.length > 0) {
                    run(players, iframe);
                }
            });
        }
    }
    <?php if(!$is_sco): ?>
        $.getScript('<?php echo api_get_path(WEB_LIBRARY_PATH); ?>javascript/jquery.json.min.js');
        replace_jwplayer_to_v6();
    <?php endif; ?>
});

/**
* Custom ui apis
*/
/*
* Message box style ui without buttons
* @params string   message, The message will be shown inside the bod
* @params string   title, The title will be shown in the title bar of the box
* @params string   type, message type (confirmation | warning | error)
* @example 
*       $.messageBox('Message', 'Title', 'confirmation');
* @author Christian Flores <aflores609@yahoo.com>
*/
$.extend({messageBox: function (message, title, type, modal, width, height) 
  {
    var icon, html;
    var type = typeof type !== 'undefined'?type:'confirmation';
    var mymodal = typeof modal !== 'undefined'?modal:true;
    var mywidth  = typeof width  !== 'undefined'?width:350;
    var myheight = typeof height !== 'undefined'?height:185;
    if (type == 'confirmation') { icon = '<img src="<?php echo api_get_path(WEB_IMG_PATH).'message_normal.png'; ?>">';  }
    else if (type == 'warning') { icon = '<img src="<?php echo api_get_path(WEB_IMG_PATH).'message_warning.png'; ?>">'; }
    else if (type == 'error')   { icon = '<img src="<?php echo api_get_path(WEB_IMG_PATH).'messagebox_warning.gif'; ?>">'; }    
    if (type == 'none') {
        html = '<div class="custom-message-box">'+message+'</div>';
    }
    else {
        html = '<table><tr><td width="35px">'+icon+'</td><td>'+message+'</td></tr></table>';
    }
    $("<div></div>").dialog( {
      create: function(event, ui) {
        $(event.target).parent().css('position', 'fixed');
      },
      resizeStop: function(event, ui) {
        var position = [(Math.floor(ui.position.left) - $(window).scrollLeft()),
                         (Math.floor(ui.position.top) - $(window).scrollTop())];
        $(event.target).parent().css('position', 'fixed');
        $(this).dialog('option','position',position);
      },
      resizable: false,
      title: title,
      closeText: getLang('Close'),
      width: mywidth,
      height: myheight,
      modal: mymodal
    }).html(html);
    $('.messagebox.ui-dialog').css({position:"fixed"});
  }
});

/*
* alert popup style ui 
* @params string   message, The message will be shown inside the bod
* @params string   title, The title will be shown in the title bar of the box
* @params string   type, message type (confirmation | warning | error)
* @example 
*       $.alert('Message', 'Title', 'error');
* @author Christian Flores <aflores609@yahoo.com>
*/
$.extend({ alert: function (message, title, type, modal)
  {
    var type = typeof type !== 'undefined'?type:'confirmation';
    var mymodal = typeof modal !== 'undefined'?modal:true;
    var icon, html;
    if (type == 'confirmation') { icon = '<img src="<?php echo api_get_path(WEB_IMG_PATH).'message_normal.png'; ?>">';  }
    else if (type == 'warning') { icon = '<img src="<?php echo api_get_path(WEB_IMG_PATH).'message_warning.png'; ?>">'; }
    else if (type == 'error')   { icon = '<img src="<?php echo api_get_path(WEB_IMG_PATH).'messagebox_warning.gif'; ?>">'; }
    html = '<table><tr><td width="35px">'+icon+'</td><td>'+message+'</td></tr></table>';   
    $("<div></div>").dialog( {
      create: function(event, ui) {
        $(event.target).parent().css('position', 'fixed');
      },
      resizeStop: function(event, ui) {
        var position = [(Math.floor(ui.position.left) - $(window).scrollLeft()),
                         (Math.floor(ui.position.top) - $(window).scrollTop())];
        $(event.target).parent().css('position', 'fixed');
        $(this).dialog('option','position',position);
      },
      buttons: {
              "<?php echo get_lang('Accept'); ?>": function() {
                  $(this).dialog("close");                  
              }
      },
      resizable: false,
      title: title,
      closeText: getLang('Close'),
      modal: mymodal      
    }).html(html);
    // The closeText alignment // 
    $(".ui-dialog").find(".ui-dialog-titlebar-close").css("right","1em");
    $(".ui-dialog").find(".ui-icon-closethick").css("padding-right","5px");    
  }
});

/*
* Confirmation popup style ui 
* @params string     message, The message will be shown inside the bod
* @params string     title, The title will be shown in the title bar of the box
* @params callback   okAction, fuction callback will be run when you do clic in ok button
* @params bool       modal, It is optional, you could show the popup like a modal (default: true)
* @params int        width, It is optional
* @params int        height, It is optional
* @example 
*       $.confirm('Message', 'Title', function() {
*           // code block will run when you accept the message;
*       });
* @author Christian Flores <aflores609@yahoo.com>
*/
$.extend({
    confirm: function(message, title, okAction, modal, width, height) {
        var mymodal  = typeof modal  !== 'undefined'?modal:true;
        var mywidth  = typeof width  !== 'undefined'?width:350;
        var myheight = typeof height !== 'undefined'?height:185;
        var icon, html;
        icon = '<img src="<?php echo api_get_path(WEB_IMG_PATH).'dokeos_question.png'; ?>">';    
        html = '<table><tr><td width="35px">'+icon+'</td><td>'+message+'</td></tr></table>';
        $("<div></div>").dialog({
            // Remove the closing 'X' from the dialog
            open: function(event, ui) { $(this).parent().children().children("a.ui-dialog-titlebar-close").hide(); }, 
            buttons: {
              "<?php echo get_lang('Yes'); ?>": function() {
                  $(this).dialog("close");
                  if (typeof (okAction) == 'function') {
                    setTimeout(okAction, 50);
                  }
              },
              "<?php echo get_lang('No'); ?>": function() {
                  $(this).dialog("close");
              }
            },
            create:function () {
                $(this).closest(".ui-dialog").find(".ui-button:first").addClass("ui-first-button");
            },
            resizable: false,
            title: title,
            closeText: getLang('Close'),
            width: mywidth,
            height: myheight,
            modal: mymodal
        }).html(html);
    }
});
/*
*
*
*/