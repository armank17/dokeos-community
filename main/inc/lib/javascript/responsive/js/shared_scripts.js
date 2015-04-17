$(document).on("ready", function(){ 
    var client = $('span#base_url').data('client');
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
    var player = $("#video-player");
    ua = navigator.userAgent.toLowerCase();
    is_android = ua.indexOf("android") > -1;
    if (is_android) {
        android = ua.substring(ua.indexOf("android"));
        android_version = android.substring(8, android.indexOf("."));
    }
    stream_server = 'dokeos.net';//dokeos.net
    src_file = player.data('video');
    file = end(src_file.split('/'));
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
    video_title = player.prev().text();
    video_url_base = stream_server + ':1935/vod/_definst_/' + file_type + ':' + client + '/' + file;
    if (is_android && android_version >= 4) {
        $('div#' + player).html(
        '<video autoplay="autoplay" controls autobuffer src="http://' + video_url_base + '/playlist.m3u8"  width="' + video_width + '" height="' + video_height + '"  poster="../../main/upload/webtv/thumbs/' + client + '/' + file + '.png">\\n\
         </video>');
    } else {
        sources = [
            { file: 'http://' + video_url_base + '/playlist.m3u8' },
            { file: 'rtmp://' + video_url_base }
        ];
        if ($.browser.mobile) {
            sources.push({ file: 'rtsp://' + video_url_base });
        }
        jwplayer("video-player").setup({
            playlist: [{
                image: "../../main/upload/webtv/thumbs/" + client + '/' + file + ".png",
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
    }
});