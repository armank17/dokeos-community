$(document).on("ready", function(){
    var $this, array;
    var base_url = $('span#base_url').data('base-url');
    var client = $('span#base_url').data('client');
    
    $("section.channels").sortable({
        placeholder: 'ui-state-highlight',
        start: function(e, ui) {
            $(ui.item).css("opacity", 0.5);
            ui.item.addClass("noclick");
        },
        stop: function(e, ui) {
            $(ui.item).css("opacity", 1);
        },
        update: function() {
            $.post($("section.channels").data("sort-url"), {sort: $("section.channels").sortable("toArray", {attribute: "data-id"})}).done(function(){
                original_content = $('section.channels').html();
            });
        }
    }).disableSelection();
    
    var moFile = function(e){
        e.preventDefault();
        $(this).parent().prev().trigger("click");
    };
    $('.mo_file').on("click", moFile);
    $('.mo_file').next().on("click", moFile);
    $('.mo_file').parent().prev().on("change", function(){
        $(this).next().find('.mo_file').val(this.value);
    });
    
    $(".mo-slider").each(function(){
        $this = $(this);
        $("<div />").appendTo($this).slider({
            min: $this.data("min"),
            max: $this.data("max"),
            value: $this.data("value"),
            slide: function(event, ui) {
                $(ui.handle).parents(".catalogue-duration").find("input").val(ui.value);
            }
        });        
    });
    
    var group;
    $('div.btn-group[data-toggle=buttons-radio]').each(function(){
        group = $(this);
        $('button[value=' + group.next().val() + ']', group).addClass('active');
        $('button', group).on('click', function(){
            $(this).parent().next().val(this.value);
        });
    });

    var icon, class_name, icon_class;
    $("body").on("click", ".change-status", function(e){
        e.preventDefault();
        $this = $(this);
        icon_class = $this.data("icons").split("/");
        icon = $this.find("i");
        class_name = icon.attr("class");
        icon.hide().removeClass(class_name);
        $.get($this.attr("href"));
        icon.addClass(class_name == icon_class[0] ? icon_class[1] : icon_class[0]).fadeIn();
    });

    var action_delete = function($this, redirect){
        bootbox.confirm($('span#base_url').data('are-you-sure'), function(result) {
            if (result) {
                $.ajax($this.attr('href')).done(function(text) {
                    if (!redirect) {
                        $this.parent().parent().fadeOut('slow', function(){
                            $(this).remove();
                        });
                    } else {
                        document.location = $('span#base_url').data('base-url');
                    }
                }).fail(function(){
                    alert('Page not found.');
                });
            }
        });
    };
    $("body").on("click", ".action-delete", function(e){
        e.preventDefault();
        action_delete($(this), $(this).data("redirect"));
    });
    
    $("body").on("click", ".copy-clipboard", function(e){
        e.preventDefault();
        window.prompt($('span#base_url').data('copy-clipboard'), $(this).attr("href"));
    });
    
    $("a.facebook").on("click", function(e){
        e.preventDefault();
        window.open($(this).attr("href"), 'Facebook', 'width=600, height=300, toolbar=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no');
    });
    
    var end = function(array) {
        var last_elm,key;
        if (array.constructor==Array) {
            last_elm=array[(array.length-1)];
        } else {
            for (key in array) {
                last_elm=array[key];
            }
        }
        return last_elm;
    };

    var ua, is_android, android, android_version, stream_server, src_file, prefile, minus, file, file_type, video_height, video_width, video_title, video_url_base, sources;
    var video_popup = function(player, e) {
        if (typeof player === "object") {
            $this = $(this).parent().parent();
            if ($this.hasClass("noclick")) {
                $this.removeClass("noclick");
                return;
            }
            player = "stream-player";
            bootbox.alert('<div id="' + player + '">' + $('span#base_url').data('loading-the-player') + ' ...</div>');
            e = $(this);
        } else if (!$('div[id^=' + player + ']').length) {
            return;
        }
        ua = navigator.userAgent.toLowerCase();
        is_android = ua.indexOf("android") > -1;
        if (is_android) {
            android = ua.substring(ua.indexOf("android"));
            android_version = android.substring(8, android.indexOf("."));
        }
        stream_server = 'dokeos.net';//dokeos.net
        src_file = e.attr('src');
        prefile = src_file.substring(src_file.indexOf('=') + 1);
        minus = prefile.length - 4;
        file = prefile.substr(0, minus);
        file_type = end(file.split('.'));
        var big = { width : 640, height : 480 };
        var medium = { width : 320, height : 240 };
        var small = { width : 160, height : 120 };
        var tiny = { width : 80, height : 60 };
        var $size = '';
        var height = $(window).height();
        var width = $(window).width();
        if (width > height) {
            if (height >= 638) {
                $size = big;
            } else if (height > 405 && height <= 637) {
                $size = medium;
            } else if(height > 388 && height <= 405) {
                $size = small;
            } else if (height <= 388) {
                $size = tiny;
            }
        } else if (height > width) {
            if (width >= 719) {
                $size = big;
            } else if (width > 410 && width <= 718) {
                $size = medium;
            } else if(width > 252 && width <= 410) {
                $size = small;
            } else if (width <= 252) {
                $size = tiny;
            }
        }
        video_height = $size.height;
        video_width = $size.width;
        if (player == "stream-player") {
            video_title = $(this).parent().attr('title');
        } else {
            video_title = e.attr('title');
        }
        video_url_base = stream_server + ':1935/vod/_definst_/' + file_type + ':' + client + '/' + file;
        if (is_android && android_version >= 4) {
            $('div#' + player).html(
            '<video autoplay="autoplay" controls autobuffer src="http://' + video_url_base + '/playlist.m3u8"  width="' + video_width + '" height="' + video_height + '"  poster="thumbnail.php?file=' + file + '.png&size=720x540">\\n\
             </video>');
        } else {
            sources = [
                { file: 'http://' + video_url_base + '/playlist.m3u8' },
                { file: 'rtmp://' + video_url_base }
            ];
            if ($.browser.mobile) {
                sources.push({ file: 'rtsp://' + video_url_base });
            }
            jwplayer(player).setup({
                playlist: [{
                    image: "thumbnail.php?file=/" + file + ".png&size=720x540",
                    sources: sources,
                    title : video_title
                }],
                bufferlength: '2',
                startparam:'starttime',
                autostart: true,
                height: video_height,
                primary: "html5",
                width: video_width
            });
            if (player == "stream-player") {
                $('div.modal-footer a.btn').text('Close');                
            }
        }
    };
    
    $("section.edit-video .cover-image img").on("click", video_popup);
    
    var target;
    $("section.channel-videos ul li").on("click", function(e){
        target = $(e.target);
        if (target.is(".no_clickable") || target.is("i")) {
            return;
        }
        $this = $(this);
        video_popup("video-player", $this.find("figure img"));
        $("section.channel-videos ul li").removeClass("active");
        $this.addClass("active");
    });
    
    var figure_active = $("section.channel-videos ul li.active").index();
    if (figure_active < 0) figure_active = 0;
    $("section.channel-videos ul li:eq(" + figure_active + ")").trigger("click");

    var timestamp = 0;
    var progress_token;
    var comet = function(){
        $.ajax('comet.php?progress_token=' + progress_token + '&timestamp=' + timestamp)
        .done(function(response){
            timestamp = response.timestamp;
            if (response.progress < 100) {
                $('#all_progress .progress-label', $this).text('Processing Form: ' + response.progress + '%').next().width(response.progress + '%');
                comet();
            }
        }).fail(function(){
            return false;
        });
    }
    var upload_progress, mo_alert;
    $("form").on("submit", function(e) {
        e.preventDefault();
        $this = $(this);
        percentage = 50;
        upload_progress = false;
        $("input:file", $this).each(function(){
            if (this.value != "") {
                upload_progress = true;
                return false;
            }
        });
        upload_progress = upload_progress && $('#upload_progress', $this).length;
        $this.ajaxSubmit({
            beforeSubmit: function(formData, form, options) {
                $("html").animate({scrollTop : 0}, 500);
                progress_token = $("#progress_token", $this).val();
                if (upload_progress) {
                    $('#upload_progress', $this).fadeIn().find('.progress-label').text('Uploading File: 0%').next().width('0%');                    
                }
                $('#all_progress', $this).fadeIn().find('.progress-label').text('Processing Form: 10%').next().width('10%');
                comet();
            },
            uploadProgress: function(event, position, total, percentage) {
                if (upload_progress) {
                    $('#upload_progress .progress-label', $this).text('Uploading File: ' + percentage + '%').next().width(percentage + '%');
                }
            },
            success: function(data) {
                if (upload_progress) {
                    $('#upload_progress .progress-label', $this).text('Uploading File: 100%').next().width('100%');
                }
                $('#all_progress .progress-label', $this).text('Processing Form: ' + '100%').next().width('100%');
                mo_alert = $('<p />').addClass('hide alert');
                if (data.code == 200) {
                    mo_alert.addClass('alert-success');
                } else {
                    mo_alert.addClass('alert-error');
                }
                $this.find(".control-group:eq(0)").before(mo_alert);
                mo_alert.html(data.message || "Unexpected error.").slideDown(500, function() {
                    setTimeout(function() {
                        if (upload_progress) {
                            $('#upload_progress .progress-label', $this).text('0%').next().width('0%').parent().fadeOut();
                        }
                        $('#all_progress .progress-label', $this).text('0%').next().width('0%').parent().fadeOut();
                        mo_alert.slideUp(1000, function() {
                            if (data.code == 200) {
                                document.location = base_url;
                            }else if (data.code != 200) {
                                mo_alert.remove();
                            }
                        });
                    }, 3000);
                });
            }
        });
    });
    
    var original_content = $('section.channels').html();
    var term, new_content, video_folder, video_ext, status, link, controls;
    $('#search_video input').keyup(function() {
        term = $.trim(this.value);
        if (term != "") {
            $.getJSON('index.php?action=search_video&term=' + term, function(videos) {
                new_content = '';
                if (videos != '') {
                    $.each(videos, function(index, video) {
                        if (video.type == 'channel') {
                            video_folder = 'channels';
                            video_ext =  '';
                        } else {
                            video_folder = 'thumbs';
                            video_ext = '.png';
                        }
                        status = video.status == 1 ? 'visible' : 'invisible';
                        if (video.id != '') {
                            link = video.channel_id != 0 ? 
                                '<a href="' + base_url + '&action=view_channel&id=' + video.channel_id + '&video_id=' + video.id + '">' : 
                                '<a href="' + base_url + '&action=view_video&id=' + video.id + '">';
                            controls = $('span#base_url').data('allowed-to-edit') === 1 ?
                               '<div class="video-actions">\n\
                                    <a href="' + base_url + '&action=change_status_' + video.type + '&id=' + video.id + '"><i class="dk-icon-action-' + status + '-white"></i></a>\n\
                                    <a href="' + base_url + '&action=edit_' + video.type + '&id=' + video.id + '"><i class="dk-icon-action-edit-white"></i></a>\n\
                                    <a class="action-delete" href="' + base_url + '&action=delete_' + video.type + '&id=' + video .id + '"><i class="dk-icon-action-delete-white"></i></a>\n\
                                </div>' : '';
                            new_content +=
                            '<article>\n\
                                <div class="video" title="' + video.title + '">\n\
                                    ' + link + '\n\
                                    <img src="../upload/webtv/' + video_folder + '/' + client + '/' + video.video_src + video_ext + '">\n\
                                    </a>\n\
                                </div>\n\
                                ' + controls + '\n\
                            </article>';
                        }
                    });
                } else {
                    new_content = '<span class="label">' + $('span#base_url').data('no-results') + ' "' + term + '"</span>';
                }
                $('section.channels').hide().html(new_content).fadeIn().sortable("disable");
            });
        } else if (original_content != $('section.channels').html()) {
            $('section.channels').hide().html(original_content).fadeIn().sortable("enable");
        }
    });
    
    $(".videos-scroll").mCustomScrollbar({
        scrollButtons: { enable: true }
    });
    
    $('.tag-it').tagit({
        removeConfirmation: true,
        allowSpaces: true
    });
});