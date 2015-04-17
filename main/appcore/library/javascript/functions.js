function parseSocialURL(url) {
    url.match(/^http:\/\/(?:.*?)\.?(youtube|vimeo)\.com\/(watch\?[^#]*v=(\w+)|(\d+)).*$/);
    return {
        provider : RegExp.$1,
        id : RegExp.$1 == 'vimeo' ? RegExp.$2 : RegExp.$3
    }
}

function getLang(variable) {
    var mypath = typeof myDokeosWebPath !== 'undefined'?myDokeosWebPath:'/';    
    var urlLang = mypath+'main/index.php?module=i18n&cmd=Language&func=lang&variable='+variable;
    var translation = $.ajax({url: urlLang, async: false}).responseText;
    return translation;
}

function GetYoutubeId(url) {
    var YoutubeId = url.toString().slice( url.search( /\?v=/i ) + 3 ) ;
    var end = YoutubeId.indexOf( '%' ) ;
    if (end > 0) {
            YoutubeId = YoutubeId.substring( 0, end ) ;
    }
    return YoutubeId ;
}

function equalHeight(group) {        
    //var rightH = $('#player-view-content').prop('scrollHeight');
    //group.height(rightH);
 }

function cleanCdata(string) {
   return string.replace("<![CDATA[", "").replace("]]>", "");
}

function loadAudioPlayer(audio, action) {    
    var playerId = 'mediaplayeritem-record';    
    if (audio != '') {
        jwplayer(playerId).load({
                'file': audio
        });
        $(".audio-actions").show();
        $(".audio-actions").attr("id", "unmute");
        $(".audio-actions").find('img').attr("id", "speaker_on"); 
    } 
    switch (action) {
        case 'play':
        case 'unmute':
            jwplayer(playerId).play();
            break;
        case 'mute':
            jwplayer(playerId).pause();
            break;
    }
}

function loadFromIframeAudioPlayer(audio, action) {
    var playerId = 'mediaplayeritem-record';    
    if (audio != '') {
        window.parent.jwplayer(playerId).load({
            'file': audio
        });
        window.parent.$("#item-audio").val(audio);
        window.parent.$(".audio-actions").show();
        window.parent.$(".audio-actions").attr("id", "unmute");
        window.parent.$(".audio-actions").find('img').attr("id", "speaker_on"); 
    } 
    switch (action) {
        case 'play':
        case 'unmute':
            window.parent.jwplayer(playerId).play();
            break;
        case 'mute':
            window.parent.jwplayer(playerId).pause();
            break;
    }
}

function reOpenDialog(myurl, mytitle, mywidth, myheight) {
    iframe = $('<iframe id="idialog" src="'+myurl+'" frameborder="0"></iframe>');
    // UI Dialog
    if (myheight === 600)
        myheight = 730;
    else
        myheight = 340;
    iframe.dialog({
        autoOpen: false,
        modal: true,
        title: mytitle,
        resizable: false,
        closeText: getLang('Close'),
        width: (mywidth - 10),
        height: myheight
    });
    
    $(".ui-dialog").find(".ui-dialog-titlebar-close").css("right","1em");
    $(".ui-dialog").find(".ui-icon-closethick").css("padding-right","5px");
    
    iframe.dialog('open');
    iframe.css({"display":"block", "width": (mywidth-25)+'px', "height": (myheight-10)+'px'});
    iframe.contents().find("body").css("font-size", "15px");
}

function saveItemToc(attrHref) {
    $.ajax({
        type: "GET",
        url: attrHref,
        dataType: "xml",
        success: function(xml) {
            $(xml).find('response').each(function(){
                var view_content = $(this).find('view_content').text();
                var view_left = $(this).find('view_left').text(); 
                var view_top = $(this).find('view_top').text();
                $("#player-view-middle-left").html(cleanCdata(view_left));
                $("#player-view-content").html(cleanCdata(view_content));
                $("#player-view-top").html(cleanCdata(view_top));
            });
        }
   });
}

function saveLpQuiz(attrHref) {
    $.ajax({
        type: "GET",
        url: attrHref,
        dataType: "xml",
        success: function(xml) {
            $(xml).find('response').each(function(){
                var view_left = $(this).find('view_left').text(); 
                var view_top = $(this).find('view_top').text();
                $("#player-view-middle-left").html(cleanCdata(view_left));
                $("#player-view-top").html(cleanCdata(view_top));
            });
        }
   });
}

function setIframeHeight() {    
    if ($("#author-iframe").length > 0) {
        $("#author-iframe").iframeAutoHeight({minHeight:580});
    }
}

function showNiceScroll(element, size, color) {
    var mycolor  = typeof color  !== 'undefined'?color:$("#header_background").css("background-color");
    var mysize   = typeof size  !== 'undefined'?size:8;
    if (element.length > 0) {
        element.niceScroll({cursorcolor: mycolor, cursorwidth: mysize});
    }    
}

function updateOogieImageHeight(w) {    
    if ($(".oogie-image").length) {
        var curr_h = document.body.offsetHeight - ($(".toogle-slide-top").height() + 55);    
        if (w > 0 && curr_h > w) {
            curr_h = w;
        }
        $(".oogie-image").attr('height', curr_h);
    }        
}
