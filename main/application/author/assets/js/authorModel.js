var AuthorModel = function() {
    var courseCode, webPath;

    function updateCKValue() {
        for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
    }

    function getCKInstance(editorname) {
        var myinstances = [];
        if (window.parent.$("#cke_authoring_editor").length > 0) {
            for (var i in window.parent.CKEDITOR.instances) {
                window.parent.CKEDITOR.instances[i];
                window.parent.CKEDITOR.instances[i].name;
                window.parent.CKEDITOR.instances[i].value;
                window.parent.CKEDITOR.instances[i].updateElement();
                myinstances[window.parent.CKEDITOR.instances[i].name] = window.parent.CKEDITOR.instances[i];
            }
            return myinstances[editorname];
        }
        return false;
    }

    function openDialog(myurl, mytitle, mywidth, myheight, refresh, func) {
        courseCode = decodeURIComponent($("#courseCode").val());
        webPath = decodeURIComponent($("#webPath").val());
        iframe = $('<iframe id="idialog" src="' + myurl + '" frameborder="0"></iframe>');

        // UI Dialog
        iframe.dialog({
            autoOpen: false,
            closeText: getLang('Close'),
            modal: true,
            title: mytitle,
            resizable: false,
            width: mywidth,
            height: myheight,
            dialogClass: 'author-dialog',
            open: function(event, ui) {
                if (func == 'sorter') {
                    $("input#item-func").val("sorter");
                    $("#authoring-form").submit();
                }
            },
            close: function(event, ui) {
                if (func == 'sorter') {
                    var courseCode = decodeURIComponent(window.parent.$("#courseCode").val());
                    var webPath = decodeURIComponent(window.parent.$("#webPath").val());
                    var itemType = window.parent.$("input#item-type").val();
                    var itemId = window.parent.$("input#item-id").val();
                    var lpId = window.parent.$("input#item-lp-id").val();
                    var itemQuery = itemId != '' ? '&lpItemId=' + itemId : '';
                    var href = webPath + 'main/index.php?module=author&cmd=Authoring&func=index&cidReq=' + courseCode + '&itemType=' + itemType + '&lpId=' + lpId + itemQuery;
                    window.parent.location.href = href;
                    return false;
                }
                else {
                    if (refresh == 'true') {
                        window.parent.location.reload();
                        return false;
                    }
                }
            }


        });

        $(".ui-dialog").find(".ui-dialog-titlebar-close").css("right", "1em");
        $(".ui-dialog").find(".ui-icon-closethick").css("padding-right", "5px");

        iframe.dialog('open');
        iframe.css({"display": "block", "width": (mywidth - 15) + 'px', "height": (myheight - 15) + 'px'});
        iframe.contents().find("body").css("font-size", "15px");
        if (func == 'sorter') {
            iframe.dialog({
                buttons: [{
                        id: "btn-close-sort",
                        text: getLang('Validate'),
                        click: function() {
                            $(this).dialog("close");
                        }
                    }]
            });
            //iframe.attr("width", mywidth+'px');
            iframe.css("width", (mywidth - 15) + 'px');
        }
    }

    function reOpenUiDialog(myurl, mytitle, mywidth, myheight) {
        window.parent.iframe.dialog("close");
        window.parent.reOpenDialog(myurl, mytitle, mywidth, myheight);

    }

    function getQueryParams(url) {
        var qparams = {},
                parts = (url || '').split('?'),
                qparts, qpart,
                i = 0;
        if (parts.length <= 1) {
            return qparams;
        } else {
            qparts = parts[1].split('&');
            for (i in qparts) {
                if (typeof(qparts[i]) == 'string')
                {
                    qpart = qparts[i].split('=');
                    qparams[decodeURIComponent(qpart[0])] =
                            decodeURIComponent(qpart[1] || '');
                }
            }
        }
        return qparams;
    }
    ;

    function setCKImage(imagePath, imageTitle) {
        var editor = getCKInstance('authoring_editor');
        var imageElement = editor.document.createElement('img');
        imageElement.setAttribute('src', imagePath);
        imageElement.setAttribute('alt', imageTitle);

        imageElement.setAttribute('align', "right");
        imageElement.setAttribute('style', "margin: 30px;");

        editor.insertElement(imageElement);
    }

    function setCKThumbVideo(imagePath) {
        var editor = getCKInstance('authoring_editor');

        var imageElement = editor.document.createElement('img');
         imageElement.setAttribute('id', 'thumb_image_streaming');
        imageElement.setAttribute('src', imagePath);
        imageElement.setAttribute('align', "left");
        imageElement.setAttribute('style', "margin-right:10px; width:330px;");

        editor.insertElement(imageElement);
    }



    function setCKVideo(videoPath, videotype, thumbnail) {
        var editor = getCKInstance('authoring_editor');
        if (videotype == 'social') {
            var s = getVideoInnerHTML(randomnumber, videoPath, 400, 300, videotype, thumbnail);
            editor.insertHtml(s);
        }
        else if (videotype == 'streaming') {
            var randomnumber = generateId('video');
            var videoNode = window.parent.CKEDITOR.dom.element.createFromHtml('<cke:streaming></cke:streaming>', editor.document);
            videoNode.setAttributes(
                    {
                        id: 'player' + randomnumber + '-parent',
                        width: 400,
                        height: 300,
                        poster: ''
                    });
            var innerHtml = getVideoInnerHTML(randomnumber, videoPath, 400, 300, videotype, thumbnail);
            videoNode.setHtml(innerHtml);
            var newFakeImage = editor.createFakeElement(videoNode, 'cke_streaming', 'streaming', false);
            editor.insertElement(newFakeImage);
        }
        else {
            var randomnumber = generateId('video');
            var videoNode = window.parent.CKEDITOR.dom.element.createFromHtml('<cke:jwvideo></cke:jwvideo>', editor.document);
            videoNode.setAttributes(
                    {
                        id: 'player' + randomnumber + '-parent',
                        width: 400,
                        height: 300,
                        poster: ''
                    });
            var innerHtml = getVideoInnerHTML(randomnumber, videoPath, 400, 300, videotype, thumbnail);
            videoNode.setHtml(innerHtml);
            var newFakeImage = editor.createFakeElement(videoNode, 'cke_jwvideo', 'video', false);
            editor.insertElement(newFakeImage);
        }
    }

    function generateId(salt) {
        var now = new Date();
        return salt + now.getFullYear() + now.getMonth() + now.getDate() + now.getHours() + now.getMinutes() + now.getSeconds();
    }

    function getVideoInnerHTML(playerid, path, mywidth, myheight, videotype, thumbnail) {
        var s = '';
        var fileUrl = path;
        var fileUrlHtml = path;
        var previewUrl = thumbnail;
        var cmbAlign = 'left';
        var cmbBuffer = 1;
        var autoplay = false;
        var loop = false;
        var fullscreen = true;

        if (videotype == 'streaming') {
            var s = '';
            var sExt = fileUrl.match(/\.(flv|mpg|mpeg|mp4|avi|wmv|mov|asf)$/i);
            if (sExt.length && sExt.length > 0) {
                sExt = sExt[0].replace(".", "");
            } else {
                sExt = '';
            }
            var hdnVideoParams = 'fileUrl=' + fileUrl + ' previewUrl=' + previewUrl + ' width=' + mywidth + ' height=' + myheight + ' cmbAlign=' + cmbAlign + ' cmbBuffer=' + cmbBuffer + ' autoplay=' + autoplay + ' loop=' + loop + ' fullscreen=' + fullscreen;
            s += '<div id="player' + playerid + '-config" style="display: none; visibility: hidden; width: 0px; height: 0px; overflow: auto;">' + window.parent.$.base64.encode(hdnVideoParams) + '</div>';
            s += '<div id="test-' + playerid + '">';
            s += '<div id="player' + playerid + '" class="thePlayerStreaming">';

            var exploded = fileUrl.split('/');
            var theFile = exploded[exploded.length - 1];
            var client = exploded[exploded.length - 2];
            var stream_server = 'dokeos.net';
            var video_url_base = stream_server + ':1935/vod/_definst_/' + sExt + ':' + client + '/' + theFile;

            var ua = navigator.userAgent.toLowerCase();
            is_android = ua.indexOf("android") > -1;
            if (is_android) {
                android = ua.substring(ua.indexOf("android"));
                android_version = android.substring(8, android.indexOf("."));
            }

            if (is_android && android_version >= 4) {
                s += '<video ';
                if (autoplay == true) {
                    s += ' autoplay="autoplay"';
                }
                if (fullscreen == false) {
                    s += ' controls';
                }
                s += ' autobuffer';
                s += ' src="http://' + video_url_base + '/playlist.m3u8"';
                s += ' width="' + mywidth + '"';
                s += ' height="' + myheight + '"';
                if (previewUrl != "") {
                    s += ' poster="' + previewUrl + '"';
                }
                s += '></video>'
            }
            else {
                s += '<div id="player' + playerid + '-parent2"></div>';
                s += '<script type="text/javascript">';
                s += 'jwplayer("player' + playerid + '-parent2").setup({';
                s += '   playlist: [{';
                if (previewUrl != "") {
                    s += ' image: "' + previewUrl + '",';
                }
                s += ' sources: [';
                s += ' { file: "http://' + video_url_base + '/playlist.m3u8" },';
                s += ' { file: "rtmp://' + video_url_base + '" }';
                s += ' ],';
                s += ' title : "' + theFile + '"';
                s += ' }],';
                s += ' bufferlength: "' + cmbBuffer + '",';
                s += ' startparam: "starttime",';
                if (autoplay == true) {
                    s += '   autostart: true,';
                }
                s += '   height: ' + myheight + ',';
                s += '   primary: "html5",';
                s += '   width: ' + mywidth;
                s += '});';
                s += '</script>';
            }
            s += '</div>';
            s += '</div>';
        }
        else if (videotype == 'social') {
            var YoutubeSite = 'http://www.youtube.com/v/';
            var HighQualityString = '%26hl=en%26fs=1%26rel=0%26ap=%2526fmt=18';
            var video = parseSocialURL(path);
            if (video.provider == 'vimeo') {
                src = 'http://vimeo.com/moogaloop.swf?clip_id=' + video.id;
            }
            else {
                var YoutubeId = GetYoutubeId(path);
                src = YoutubeSite + YoutubeId + HighQualityString;
            }
            s += '<iframe width="' + mywidth + '" align="left" height="' + myheight + '" src="' + src + '" frameborder="0" allowfullscreen></iframe>';
        }
        else {

            // A hidden div containing setting, added width, height, overflow for MSIE7
            s += '<div id="player' + playerid + '-config" style="display: none; visibility: hidden; width: 0px; height: 0px; overflow: auto;">';
            s += 'fileUrl=' + fileUrl + ' fileUrlHtml=' + fileUrlHtml + ' previewUrl=' + previewUrl + ' width=' + mywidth + ' height=' + myheight + ' cmbAlign=' + cmbAlign + ' cmbBuffer=' + cmbBuffer + ' autoplay=' + autoplay + ' loop=' + loop + ' fullscreen=' + fullscreen;
            s += '</div>';
            s += '<div id="test-' + playerid + '">';
            s += '<div id="player' + playerid + '" class="thePlayer">';

            var sExt = path.match(/\.(flv|mpg|mpeg|mp4|avi|wmv|mov|asf)$/i);
            if (sExt.length && sExt.length > 0) {
                sExt = sExt[0];
            } else {
                sExt = '';
            }

            if (sExt == '.flv' || sExt == '.mp4' || sExt == '.mov') {
                s += '<div id="player' + playerid + '-parent2">Loading the player ...</div>';
                s += '<script type="text/javascript">';
                s += 'jwplayer("player' + playerid + '-parent2").setup({';
                s += 'file: "' + fileUrl + '",';
                if (previewUrl != "") {
                    s += 'image: "' + previewUrl + '",'
                }
                s += 'height: ' + myheight + ',';
                s += 'autostart: ' + (autoplay == true ? 'true' : 'false') + ',';
                s += 'repeat: ' + (loop == true ? 'true' : 'false') + ',';
                s += 'bufferlength: ' + cmbBuffer + ',';
                s += 'width: ' + mywidth + ',';
                s += 'primary: "flash"';
                s += '});';
                var controls = fullscreen;
                s += 'jwplayer("player' + playerid + '-parent2").setControls(' + controls + ');';
                s += '</script>';
            }
            else {
                // only embed for other video types
                pluginspace = 'http://www.microsoft.com/Windows/MediaPlayer/';
                codebase = 'http://www.microsoft.com/Windows/MediaPlayer/';
                classid = 'classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95"';
                sType = (sExt == '.mpg' || sExt == '.mpeg') ? 'video/mpeg' :
                        (sExt == '.avi' || sExt == '.wmv' || sExt == '.asf') ? 'video/x-msvideo' :
                        (sExt == '.mov') ? 'video/quicktime' :
                        (sExt == '.mp4') ? 'video/mpeg4-generic' :
                        'video/x-msvideo';
                s += '<embed type="' + sType + '" src="' + fileUrl + '" ' +
                        'autosize="false" ' +
                        'fullscreen="true" ' +
                        'autostart="' + (autoplay == true ? 'true' : 'false') + '" ' +
                        'loop="' + (loop == true ? 'true' : 'false') + '" ' +
                        'showcontrols="' + (fullscreen == true ? 'true' : 'false') + '"' +
                        'showpositioncontrols="' + (fullscreen == true ? 'true' : 'false') + '" ' +
                        'showtracker="true"' +
                        'showaudiocontrols="' + (fullscreen == true ? 'true' : 'false') + '" ' +
                        'showgotobar="true" ' +
                        'showstatusbar="true" ' +
                        'pluginspace="' + pluginspace + '" ' +
                        'codebase="' + codebase + '"';
                s += 'width="' + mywidth + '" height="' + myheight + '"';
                s += '></embed>';
            }
            s += '</div>';
            s += '</div>';
        }
        return s;
    }

    function loadAudioPlayer(audio, action) {
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

    function stopPlayers() {
        var players = $(".thePlayer");
        if (players.length > 0) {
            players.each(function() {
                playerId = $(this).attr("id");
                jwplayer("mediaplayer" + playerId).stop();
            });
        }
    }

    return {
        hideLeftBlock: function(select) {
            var layout = $("#layout");
            $(".toogle-slide-left").animate({width: "0px"}, 0, "linear");
            switch (layout.val()) {
                case "0":
                    if (device_is_mobile === true) {
                        $("#player-view-middle-center").animate({width: "860px", marginLeft: "20px"}, 500, "linear");
                        $("#player-view-middle-left").hide("blind", {direction: "left"}, "500");
                        select.animate(500, "linear");
                        $(".player-actions-left").css("overflow", "hidden");
                        select.removeClass("arrow_left").addClass("arrow_right");
                    } else {
                        $("#player-view-middle-center").animate({width: "865px", marginLeft: "115px"}, 500, "linear");
                        $("#player-view-middle-left").show("blind", {direction: "left"}, "500");
                        select.animate(500, "linear");
                        $(".player-actions-left").css("overflow", "hidden");
                        select.removeClass("arrow_right").addClass("arrow_left");
                        $(".toogle-slide-left").animate({width: "10.2%"}, 500, "linear");
                    }
                    break;
                case "1":
                    if (device_is_mobile === true) {
                        $("#player-view-middle-center").animate({width: "96%", marginLeft: "20px"}, 500, "linear");
                        $("#player-view-middle-left").hide("blind", {direction: "left"}, "500");
                        select.animate(500, "linear");
                        $(".player-actions-left").css("overflow", "hidden");
                        select.removeClass("arrow_left").addClass("arrow_right");
                    } else {
                        $("#player-view-middle-center").animate({width: "768px", marginLeft: "115px"}, 500, "linear");
                        $("#player-view-middle-left").show("blind", {direction: "left"}, "500");
                        select.animate(500, "linear");
                        $(".player-actions-left").css("overflow", "hidden");
                        select.removeClass("arrow_right").addClass("arrow_left");
                        $(".toogle-slide-left").animate({width: "10.2%"}, 500, "linear");
                    }
                    break;
            }
            layout.val(layout.val() * 1 + 1);
        },
        showLeftBlock: function(select) {
            var layout = $("#layout");
            $(".toogle-slide-left").animate({width: "0px"}, 500, "linear");
            switch (layout.val()) {
                case "1":
                    if (device_is_mobile === true) {
                        $("#player-view-middle-center").animate({width: "765px", marginLeft: "115px"}, 500, "linear");
                        $("#player-view-middle-left").show("blind", {direction: "left"}, "500");
                        select.animate(500, "linear");
                        $(".player-actions-left").css("overflow", "hidden");
                        select.removeClass("arrow_right").addClass("arrow_left");
                        $(".toogle-slide-left").animate({width: "11.5%"}, 500, "linear");
                    } else {
                        $("#player-view-middle-center").animate({width: "960px", marginLeft: "20px"}, 500, "linear");
                        $("#player-view-middle-left").hide("blind", {direction: "left"}, "500");
                        select.animate(500, "linear");
                        $(".player-actions-left").css("overflow", "hidden");
                        select.removeClass("arrow_left").addClass("arrow_right");
                    }
                    break;
                case "2":
                    if (device_is_mobile === true) {
                        $("#player-view-middle-center").animate({width: "865px", marginLeft: "115px"}, 500, "linear");
                        $("#player-view-middle-left").show("blind", {direction: "left"}, "500");
                        select.animate(500, "linear");
                        $(".player-actions-left").css("overflow", "hidden");
                        select.removeClass("arrow_right").addClass("arrow_left");
                        $(".toogle-slide-left").animate({width: "11.5%"}, 500, "linear");
                    } else {
                        $("#player-view-middle-center").animate({width: "865px", marginLeft: "20px"}, 500, "linear");
                        $("#player-view-middle-left").hide("blind", {direction: "left"}, "500");
                        select.animate(500, "linear");
                        $(".player-actions-left").css("overflow", "hidden");
                        select.removeClass("arrow_left").addClass("arrow_right");
                    }
                    break;
            }
            layout.val(layout.val() * 1 - 1);
        },
        hideRightBlock: function(select) {
            var layout = $("#layout");
            switch (layout.val()) {
                case "0":
                    if (device_is_mobile === true) {
                        $("#player-view-middle-center").animate({width: "86%", marginright: "0%"}, 500, "linear");
                        $("#player-view-middle-right").hide("blind", {direction: "right"}, "500");
                        select.animate(500, "linear");
                        $(".player-actions-right").css("overflow", "hidden");
                        select.removeClass("arrow_right").addClass("arrow_left");
                        $(".player-actions-right").css('position', 'absolute');
                    } else {
                        $("#player-view-middle-center").animate({width: "860px", marginright: "13%"}, 500, "linear");
                        $("#player-view-middle-right").show("blind", {direction: "right"}, "500");
                        select.animate(500, "linear");
                        $(".toogle-slide-right").animate({width: "10.2%"}, 500, "linear");
                        $(".player-actions-right").css("overflow", "hidden");
                        select.removeClass("arrow_left").addClass("arrow_right");
                    }
                    break;
                case "1":
                    if (device_is_mobile === true) {
                        $("#player-view-middle-center").animate({width: "96%"}, 500, "linear");
                        $("#player-view-middle-right").hide("blind", {direction: "right"}, "500");
                        select.animate(500, "linear");
                        $(".player-actions-right").css("overflow", "hidden");
                        select.removeClass("arrow_right").addClass("arrow_left");
                        $(".player-actions-right").css('position', 'absolute');
                    } else {
                        $("#player-view-middle-center").animate({width: "765px", marginright: "13%"}, 500, "linear");
                        $("#player-view-middle-right").show("blind", {direction: "right"}, "500");
                        select.animate(500, "linear");
                        $(".toogle-slide-right").animate({width: "10.2%"}, 500, "linear");
                        $(".player-actions-right").css("overflow", "hidden");
                        select.removeClass("arrow_left").addClass("arrow_right");
                    }
                    break;
            }
            layout.val(layout.val() * 1 + 1);
        },
        showRightBlock: function(select) {
            var layout = $("#layout");
            $("#player_right_view").css('margin-left', '0');
            switch (layout.val()) {
                case "1":
                    if (device_is_mobile === true) {
                        $("#player-view-middle-center").animate({width: "765px"}, 500, "linear");
                        $("#player-view-middle-right").show("blind", {direction: "right"}, "500");
                        select.animate(500, "linear");
                        $(".toogle-slide-right").animate({width: "11%"}, 500, "linear");
                        $(".player-actions-right").css("overflow", "hidden");
                        select.removeClass("arrow_left").addClass("arrow_right");
                    } else {
                        $("#player-view-middle-center").animate({width: "960px", marginright: "20px"}, 500, "linear");
                        $("#player-view-middle-right").hide("blind", {direction: "right"}, "500");
                        select.animate(500, "linear");
                        $(".toogle-slide-right").animate({width: "0", right: '21px'}, 500, "linear");
                        $(".player-actions-right").css("overflow", "hidden");
                        select.removeClass("arrow_right").addClass("arrow_left");
                        $(".player-actions-right").css('position', 'absolute');
                    }
                    break;
                case "2":
                    if (device_is_mobile === true) {
                        $("#player-view-middle-center").animate({width: "86%"}, 500, "linear");
                        $("#player-view-middle-right").show("blind", {direction: "right"}, "500");
                        select.animate(500, "linear");
                        $(".toogle-slide-right").animate({width: "11%"}, 500, "linear");
                        $(".player-actions-right").css("overflow", "hidden");
                        select.removeClass("arrow_left").addClass("arrow_right");
                    } else {
                        $("#player-view-middle-center").animate({width: "86%", marginright: "0%"}, 500, "linear");
                        $("#player-view-middle-right").hide("blind", {direction: "right"}, "500");
                        select.animate(500, "linear");
                        $(".toogle-slide-right").animate({width: "0", right: '21px'}, 500, "linear");
                        $(".player-actions-right").css("overflow", "hidden");
                        select.removeClass("arrow_right").addClass("arrow_left");
                        $(".player-actions-right").css('position', 'absolute');
                    }
                    break;
            }
            layout.val(layout.val() * 1 - 1);
        },
        confirmExit: function(e, select) {
            e.preventDefault();
            var url = select.attr("href");
            if (typeof(CKEDITOR) != "undefined") {
                var body = CKEDITOR.instances["authoring_editor"].document.getBody();
                var currentContent = body.getOuterHtml();
                var oldContent = $.data(window.document.body, "content");
                if (!(oldContent == currentContent))
                {
                    $.confirm(getLang('AreYouSureToExit'), getLang('ConfirmationDialog'), function() {
                        location.href = url;
                    });
                }
                else {
                    location.href = url;
                }
            }
            else {
                location.href = url;
            }
        },
        loadQuiz: function(e, select) {
            e.preventDefault();
            var url = select.attr("href");
            var confirmMsn = select.attr("title");
            $.confirm(confirmMsn, getLang('ConfirmationDialog'), function() {
                window.parent.location.href = url;
            }, false);
        },
        deleteAudio: function(e, select) {
            e.preventDefault();
            var url = select.attr("href");
            var confirmMsn = select.attr("title");
            window.parent.$.confirm(confirmMsn, getLang('ConfirmationDialog'), function() {
                $.get(url, function(data) {
                    $("#audios").html(data);
                });
            }, false);
        },
        deleteImage: function(e, select) {
            e.preventDefault();
            var url = select.attr("href");
            var confirmMsn = select.attr("title");
            window.parent.$.confirm(confirmMsn, getLang('ConfirmationDialog'), function() {
                $.get(url, function(data) {
                    $("#images").html(data);
                });
            }, false);
        },
        changeExportSelect: function(select) {
            var export_type = select.val();
            if (export_type == "default_export") {
                $("#default_scorm_message").show();
                $("#select_navigation").hide();
            } else {
                $("#default_scorm_message").hide();
                $("#select_navigation").show();
            }
            $("#current_export_type").attr("value", export_type);
        },
        goTo: function(select, e) {
            e.preventDefault();
            var url = select.attr("href");
            window.parent.location.href = url;
            return false;
        },
        exportScorm: function(select, e) {
            e.preventDefault();
            $("#export-dialog").dialog({
                height: 200,
                width: 380,
                modal: true,
                buttons: {
                    "Cancel": function() {
                        $(this).dialog("close");
                    },
                    "Export": function() {
                        var export_type = $("#current_export_type").val();
                        var href = select.attr("href");
                        if (export_type == "layout_export") {
                            var navigation = $("input[type=\'radio\'].export-nav");
                            if (navigation.is(":checked")) {
                                var nav = $("input[type=\'radio\'].export-nav:checked").val();
                                location.href = href + "&navigation=" + nav + "&export_mode=layout_export";
                            }
                        } else {
                            location.href = href + "&export_mode=default_export";
                        }
                        $(this).dialog("close");
                    }
                },
                close: function() {
                }
            });
            $("#export-dialog").siblings('div.ui-dialog-titlebar').remove();
        },
        sortMenuItems: function() {
            $(".item-sort").sortable({
                opacity: 0.6,
                cursor: "move",
                cancel: ".nodrag"
            });
        },
        sortModules: function() {
            courseCode = decodeURIComponent($("#courseCode").val());
            webPath = decodeURIComponent($("#webPath").val());
            $(".sort").sortable({
                opacity: 0.6,
                cursor: "move",
                cancel: ".nodrag",
                update: function(event, ui) {
                    var current_lp_id = ui.item.attr("id");
                    var current_lp_data = current_lp_id.split("lp_row_");
                    var Lp_id = current_lp_data[1];
                    var sorted_list = $(this).sortable("serialize");
                    sorted_list = sorted_list.replace(/&/g, "");
                    var sorted_data = sorted_list.split("lp_row[]=");
                    // get new order of this lp
                    var newOrder = 0;
                    for (var i = 0; i < sorted_data.length; i++) {
                        if (sorted_data[i] == Lp_id) {
                            newOrder = i;
                        }
                    }
                    // call ajax to save new position
                    $.ajax({
                        type: "GET",
                        url: webPath + "main/index.php?module=author&cmd=HomeAjax&func=changeLpPosition&cidReq=" + courseCode + "&lp_id=" + Lp_id + "&new_order=" + newOrder,
                        success: function(response) {
                        }
                    });
                }
            });
        },
        sortItems: function() {
            if ($("#GalleryContainer").length > 0) {
                $("#GalleryContainer").sortable({
                    connectWith: "#GalleryContainer",
                    cursor: "move",
                    stop: function(event) {
                        courseCode = decodeURIComponent($("#courseCode").val());
                        webPath = decodeURIComponent($("#webPath").val());
                        var query = $("#items-sort").serialize();
                        $.ajax({
                            type: "POST",
                            url: webPath + "main/index.php?module=author&cmd=AuthoringAjax&func=updateItems&cidReq=" + courseCode,
                            data: query,
                            success: function(msg) {
                            }
                        });
                    }
                });
            }
        },
        loadSorterActions: function(e, select) {
            e.preventDefault();
            courseCode = decodeURIComponent($("#courseCode").val());
            webPath = decodeURIComponent($("#webPath").val());
            var attrId = select.attr("id");
            var exploded = attrId.split("-");
            var action = exploded[0];
            var itemId = parseInt(exploded[1]);
            var lpId = $("#lpId").val();
            if (action == 'edit') {
                var url = select.attr("href");
                window.parent.location.href = url;
                return false;
            }
            else if (action == 'delete') {
                var confirmMsn = select.attr("title");
                $.confirm(confirmMsn, getLang('ConfirmationDialog'), function() {
                    $.ajax({
                        type: "GET",
                        url: webPath + "main/index.php?module=author&cmd=AuthoringAjax&func=deleteItem&itemId=" + itemId + "&cidReq=" + courseCode,
                        success: function(msg) {
                            parentItemId = window.parent.$("input#item-id").val();
                            if (itemId == parentItemId) {
                                window.parent.$("input#item-id").val("");
                            }
                            location.href = webPath + "main/index.php?module=author&cmd=Authoring&func=sorter&cidReq=" + courseCode + "&lpId=" + lpId;
                            return false;
                        }
                    });
                }, false);
            }
            else if (action == 'deleteaudio') {
                var confirmAudioMsn = select.attr("title");
                $.confirm(confirmAudioMsn, getLang('ConfirmationDialog'), function(r) {
                    $.ajax({
                        type: "GET",
                        url: webPath + "main/index.php?module=author&cmd=AuthoringAjax&func=deleteAudioItem&itemId=" + itemId + "&cidReq=" + courseCode,
                        success: function(msg) {
                            $("#deleteaudio-" + itemId).remove();
                        }
                    });
                }, false);
            }
        },
        loadImages: function(e, select) {
            e.preventDefault();
            var href = select.attr("href");
            var title = select.attr("title");
            setCKImage(href, title);
            window.parent.iframe.dialog("close");
        },
        loadSocialVideos: function(e, select) {
            e.preventDefault();
            var href = select.val();
            if (href.length == 0) {
                $.alert(getLang('UrlRequired'), getLang('Error'), 'error', false);
                return false;
            }
            if (!(/\.youtube\.com/i.test(href)) && !(/vimeo\.com/i.test(href))) {
                $.alert(getLang('InvalidProvider'), getLang('Error'), 'error', false);
                return false;
            }
            setCKVideo(href, 'social', '');
            window.parent.iframe.dialog("close");
        },
        loadVideos: function(e, select) {
            e.preventDefault();
            var href = select.attr("href");
            var thumbnail = select.attr("id");
            //var thumbnail = select.attr("title");
            //setCKVideo(href, 'streaming', thumbnail);
            setCKThumbVideo(thumbnail);
            setCKVideo(href, 'streaming', thumbnail);
            window.parent.iframe.dialog("close");
        },
        loadAudios: function(e, select) {
            e.preventDefault();
            courseCode = decodeURIComponent($("#courseCode").val());
            webPath = decodeURIComponent($("#webPath").val());
            var path = select.attr("href");
            path = decodeURIComponent(path);
            loadAudioPlayer(path, 'play');
            window.parent.iframe.dialog("close");
        },
        tagId: function() {
            if ($(".tag-it").length > 0) {
                $(".tag-it").tagit({
                    removeConfirmation: true,
                    allowSpaces: true
                });
            }
        },
        uploadScoPpt: function() {
            if ($('.form-upload').length > 0) {
                var bar = $('.bar');
                var percent = $('.percent');
                var status = $('#status');
                $('.form-upload').ajaxForm({
                    beforeSend: function() {
                        status.empty();
                        var percentVal = '0%';
                        bar.width(percentVal)
                        percent.html(percentVal);
                    },
                    uploadProgress: function(event, position, total, percentComplete) {
                        var percentVal = percentComplete + '%';
                        bar.width(percentVal)
                        percent.html(percentVal);
                    },
                    success: function() {
                        var percentVal = '100%';
                        bar.width(percentVal)
                        percent.html(percentVal);
                    },
                    complete: function(xhr) {
                        status.html(xhr.responseText);
                    }
                });
            }
        },
        submitSettingForm: function() {
            if ($('#lp-settings').length > 0) {
                $('#lp-settings').ajaxForm({
                    beforeSubmit: function(formData, jqForm, options) {
                        var form = jqForm[0];
                        if (!form.lp_name.value) {
                            $("input[name='lp_name']").addClass("ui-state-error");
                            $("input[name='lp_name']").focus().blur(function(e) {
                                $("input[name='lp_name']").removeClass("ui-state-error");
                            });
                            return false;
                        }
                    },
                    complete: function(xhr) {
                        var lpId = xhr.responseText;
                        var oldUrl = window.parent.location.toString();
                        var url = oldUrl;

                        if (oldUrl.indexOf("lpId") == -1) {
                            url = oldUrl + "&lpId=" + lpId;

                        }

                        window.parent.location = url;
                    }
                });
            }
        },
        submitSettingForm2: function() {
            if ($('#lp-settings2').length > 0) {
                $('#lp-settings2').ajaxForm({
                    beforeSubmit: function(formData, jqForm, options) {
                        var form = jqForm[0];
                        if (!form.lp_name.value) {
                            $("input[name='lp_name']").addClass("ui-state-error");
                            $("input[name='lp_name']").focus().blur(function(e) {
                                $("input[name='lp_name']").removeClass("ui-state-error");
                            });
                            return false;
                        }
                    },
                    complete: function(xhr) {
                        var lpId = xhr.responseText;
                        //var oldUrl = window.parent.location.toString();
                        var oldUrl = $("#webPathNotEncoded").val() + "main/index.php?module=author&cmd=Authoring&func=index";
                        var url = oldUrl;



                        if (oldUrl.indexOf("lpId") == -1) {
                            url = oldUrl + "&lpId=" + lpId;

                        }

                        window.parent.location = url;
                    }
                });
            }
        },
        uploadVideo: function() {
            if ($("#upload-video").length > 0) {
                var bar = $('.bar');
                var percent = $('.percent');
                var status = $('#status');
                $('#upload-video').ajaxForm({
                    dataType: 'json',
                    beforeSend: function() {
                        $(".progress").css("visibility", "visible");
                        status.empty();
                        var percentVal = '0%';
                        bar.width(percentVal)
                        percent.html(percentVal);
                    },
                    uploadProgress: function(event, position, total, percentComplete) {
                        var percentVal = percentComplete + '%';
                        bar.width(percentVal)
                        percent.html(percentVal);
                    },
                    success: function(data) {
                        var percentVal = '100%';
                        bar.width(percentVal)
                        percent.html(percentVal);
                        if (data.success == false) {
                            jAlert(data.message, 'Confirmation');
                        }
                        else {
                            setCKVideo(data.href, 'video', '');
                            window.parent.iframe.dialog("close");
                        }
                    }
                });
            }
        },
        updateTemplateEditor: function(e, select) {
            e.preventDefault();
            courseCode = decodeURIComponent($("#courseCode").val());
            webPath = decodeURIComponent($("#webPath").val());
            var url = select.attr("href");
            var params = getQueryParams(url);
            var editor = getCKInstance('authoring_editor');
            if (editor) {
                editor.updateElement();
                $.ajax({
                    type: "POST",
                    data: window.parent.$("#authoring-form").serialize(),
                    url: webPath + "main/index.php?module=author&cmd=AuthoringAjax&func=updateTplEditor&cidReq=" + courseCode + "&tplId=" + params['tplId'],
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            editor.setData(data.content);
                            window.parent.$("#txt-item-title").val(data.title);
                            window.parent.$("input#item-title").val(data.title);
                            window.parent.$("input#item-type").val(data.item_type);
                            window.parent.iframe.dialog("close");
                        }
                    }
                });
            }
            else {
                window.parent.location.href = url;
            }
        },
        changeCertificateThumb: function(select) {
            var tpl_id = select.val();
            courseCode = decodeURIComponent($("#courseCode").val());
            webPath = decodeURIComponent($("#webPath").val());
            $("#quiz-certificate-score").hide();
            if (tpl_id == 0) {
                $("#quiz-certificate-score input[name='certificate_min_score']").val('');
                $("#quiz-certificate-score").hide();
            } else {
                $("#quiz-certificate-score").show();
            }
            $.ajax({
                type: "GET",
                url: webPath + "main/index.php?module=author&cmd=AuthoringAjax&func=displayCertPicture&cidReq=" + courseCode + "&certifId=" + tpl_id,
                success: function(data) {
                    $("#quiz-certificate-thumb").show();
                    $("#quiz-certificate-thumb").html(data);
                }
            });
        },
        reOpenDialog: function(e, select) {
            e.preventDefault();
            var w = 800;
            var h = 500;
            var url = select.attr("href");
            var title = select.attr("title");
            var params = getQueryParams(url);
            if (params['width']) {
                w = parseInt(params['width']);
            }
            if (params['height']) {
                h = parseInt(params['height']);
            }
            reOpenUiDialog(url, title, w, h);
        },
        deleteItem: function(selector) {
            var attrId = selector.attr("id");
            var itemId = parseInt(attrId.replace("delitem-", ""));
            window.parent.$("input#item-id").val("");
            $("#toggle_menu_item_" + itemId).remove();
        },
        deleteItemAudio: function(selector) {
            var attrId = selector.attr("id");
            var itemId = parseInt(attrId.replace("delaudio-", ""));
            if ($("#inp-delaudio-" + itemId).length == 0) {
                var inpAudio = '<input type="hidden" name="delAudios[]" value="' + itemId + '" id="inp-delaudio-' + itemId + '" />';
                $(inpAudio).appendTo('#form-items');
                selector.remove();
            }
        },
        hideTitleBlockError: function(selector) {
            selector.removeClass("ui-state-error");
        },
        showActionDialog: function(e, selector) {
            e.preventDefault();
            var w = 800;
            var h = 600;
            var refresh = true;
            var url = selector.attr("href");
            var title = selector.attr("title");
            var params = getQueryParams(url);
            if (params['width']) {
                w = parseInt(params['width']);
            }
            if (params['height']) {
                h = parseInt(params['height']);
            }
            if (params['refresh']) {
                refresh = params['refresh'];
            }
            // Validations
            switch (params['func']) {
                case 'sorter':
                    var itemTitle = $("input#txt-item-title").val();
                    if ($.trim(itemTitle) == '') {
                        $.alert(getLang('TitleRequiredField'), getLang('Error'), 'error');
                        $("input#txt-item-title").addClass("ui-state-error");
                        return false;
                    }
                    break;
                case 'image':
                case 'video':
                    if ($("#cke_authoring_editor").length == 0) {
                        $.alert(getLang('ChooseLayoutToInsertRessource'), getLang('Error'), 'error');
                        return false;
                    }
                    break;
            }
            openDialog(url, title, w, h, refresh, params['func']);
        },
        setHeightAuthoringiframe: function() {
            if ($("#authoring-iframe").length > 0) {
                $("#authoring-iframe").iframeAutoHeight({minHeight: 700});
            }
        },
        submitItemsForm: function(e, select) {
            e.preventDefault();
            var src = select.attr("action");
            var queryString = select.formSerialize();
            $.post(src, queryString, function(data) {
                if (data.success) {
                    $.messageBox(data.message, 'Confirmation', 'confirmation');
                    location.href = decodeURIComponent(data.redirect);
                }
            }, 'json');
        },
        showItemsForm: function() {
            $(".menu-item-view").hide();
            $(".menu-item-edit").show();
        },
        showHideCourseMenuBtn: function() {
            if ($(".menu-item-edit").css("display") == 'block') {
                $(".menu-item-edit").hide();
                $("#courseToggleMenu").hide();
                $("#menu-item-view").hide();
            }
        },
        showHideItemsCancelBtn: function() {
            $(".menu-item-view").show();
            $(".menu-item-edit").hide();
        },
        submitAuthoringForm: function() {
            if ($("#authoring-form").length > 0) {
                $('#authoring-form').ajaxForm({
                    dataType: 'json',
                    beforeSerialize: function($Form, options) {
                        if ($("#cke_authoring_editor").length > 0) {
                            for (instance in CKEDITOR.instances) {
                                CKEDITOR.instances[instance].updateElement();
                            }
                        }
                    },
                    success: function(data) {
                        if (data.itemId) {
                            if (data.message) {
                                $.messageBox(data.message, 'Confirmation', 'confirmation');
                            }
                            if (data.url) {
                                location.href = decodeURIComponent(data.url);
                            }
                            else {
                                $("input#item-id").val(data.itemId);
                            }
                        }
                        else {
                            $.alert(data.message, getLang('Error'), 'error');
                            $("input#txt-item-title").addClass("ui-state-error");
                            return false;
                        }
                    }
                });
            }
        },
        saveItemTitle: function(selector) {
            var topTitle = selector.val();
            $("input#item-title").attr("value", topTitle);
        },
        toogleItemsMenu: function(e) {
            e.preventDefault();



            if ($("#courseToggleMenu").length > 0) {
                if ($("#courseToggleMenu").css("display") == 'block' && $("#courseToggleMenu").attr('completed') === '1') {
                    $("#courseToggleMenu").hide(400, function() {
                        $("#courseToggleMenu").attr('completed', '0')
                    });
                }
                else {
                    $("#courseToggleMenu").show(400, function() {
                        $("#courseToggleMenu").attr('completed', '1')
                    });
                }
            }

        },
        submitAndNextItem: function(e) {
            e.preventDefault();
            $("#btn-action").val("next");
            $("input#item-func").val("next");
            $("#authoring-form").submit();
        },
        submitCurrentItem: function(e) {
            e.preventDefault();
            var itemTitle = $("input#txt-item-title").val();
            if ($.trim(itemTitle) == '') {
                $.alert(getLang('TitleRequiredField'), getLang('Error'), 'error');
                $("input#txt-item-title").addClass("ui-state-error");
                return false;
            }
            $("input#item-func").val("submitItem");
            $("#authoring-form").submit();

        },
        switchAudioPlayer: function(e, select) {
            e.preventDefault();
            var action = select.attr("id");
            if (action == 'unmute') {
                select.attr("id", "mute");
                $(".audio-actions").find('img').attr("id", "speaker_mute");
            }
            else if (action == 'mute') {
                select.attr("id", "unmute");
                $(".audio-actions").find('img').attr("id", "speaker_on");
            }
            loadAudioPlayer('', action);
        },
        hideToogleMenu: function(select) {
        },
        showToogleMenu: function(select) {
        }
    };
}();
