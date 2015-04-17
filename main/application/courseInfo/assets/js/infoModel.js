var InfoModel = function() {
    
    var txt_close = getLang('Close');
    var txt_crop = getLang('Crop').toUpperCase();
    var courseCode, webPath;

    function openDialog(myurl, mytitle, mywidth, myheight, refresh, func, deleteFn) {
        courseCode = decodeURIComponent($("#courseCode").val());
        webPath = decodeURIComponent($("#webPath").val());
        iframe = $('<iframe id="idialog" src="' + myurl + '" frameborder="0"></iframe>');

        // UI Dialog
        iframe.dialog({
            autoOpen: false,
            closeText: txt_close,
            modal: true,
            title: mytitle,
            resizable: false,
            width: mywidth,
            height: myheight,
            open: function(event, ui) {
                if (func === 'sorter') {
                    $("input#item-func").val("sorter");
                    $("#authoring-form").submit();
                }
            },
            close: function(event, ui) {
                if (deleteFn !== "") {
                    $.ajax({
                        type: "POST",
                        url: webPath + "main/index.php?module=courseInfo&cmd=InfoAjax&func=" + deleteFn,
                        success: function(data) {
                            window.parent.location.reload();
                        }
                    });
                }
                return false;
            }
        });

        $(".ui-dialog").find(".ui-dialog-titlebar-close").css("right", "1em");
        $(".ui-dialog").find(".ui-icon-closethick").css("padding-right", "5px");

        iframe.dialog('open');
        iframe.css({"display": "block", "width": (mywidth - 15) + 'px', "height": (myheight - 15) + 'px'});
        iframe.contents().find("body").css("font-size", "15px");

        if (func === 'sorter') {
            iframe.dialog({
                buttons: [{
                        id: "btn-close-sort",
                        text: getLang('Validate'),
                        click: function() {
                            $(this).dialog("close");
                        }
                    }]
            });
            iframe.css("width", (mywidth - 15) + 'px');
        }
    }
    
    function cropDialog(url) {
        iframe = $('<iframe id="idialog" src="' + url + '" frameborder="0"></iframe>');
        iframe.dialog({
            autoOpen: false,
            modal: true,
            title: txt_crop,
            closeText: txt_close,
            resizable: false
        });
        $(".ui-dialog").find(".ui-dialog-titlebar-close").css("right", "1em");
        $(".ui-dialog").find(".ui-icon-closethick").css("padding-right", "5px");
    }

    function getQueryParams(url) {
        var qparams = {}, parts = (url || '').split('?'), qparts, qpart, i = 0;
        if (parts.length <= 1) {
            return qparams;
        } else {
            qparts = parts[1].split('&');
            for (i in qparts) {
                if (typeof(qparts[i]) === 'string') {
                    qpart = qparts[i].split('=');
                    qparams[decodeURIComponent(qpart[0])] = decodeURIComponent(qpart[1] || '');
                }
            }
        }
        return qparams;
    };

    return {
        showActionDialog: function(e, selector) {
            e.preventDefault();
            var w = 525;
            var h = 440;
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
                    if ($.trim(itemTitle) === '') {
                        $.alert(getLang('TitleRequiredField'), getLang('Error'), 'error');
                        $("input#txt-item-title").addClass("ui-state-error");
                        return false;
                    }
                    break;
                case 'image':
                case 'video':
                    if ($("#cke_authoring_editor").length === 0) {
                        $.alert(getLang('ChooseLayoutToInsertRessource'), getLang('Error'), 'error');
                        return false;
                    }
                    break;
            }
            openDialog(url, title, w, h, refresh, params['func']);
        },

        showActionDialogCrop: function(path_img, width, height, reload, resize, temp_name, dest_name, ext, folder) {
            folder || (folder = '');
            var url = '/main/index.php?module=courseInfo&cmd=Info&func=uploadLogo&path_img='+path_img+'&width='+width+'&height='+height+
                      '&reload='+reload+'&resize='+resize+'&temp_img='+temp_name+'&dest_img='+dest_name+'&folder='+folder+'&ext='+ext;
                  
            cropDialog(url);
        },
        
        showActionDialogCropQuiz: function(path_img, width, height, reload, resize, temp_name, dest_name, ext, folder, post_id, side) {  
            
            folder || (folder = '');
            var url = '/main/index.php?module=courseInfo&cmd=Info&func=uploadImageQuiz&path_img='+path_img+'&width='+width+'&height='+height+
                      '&reload='+reload+'&resize='+resize+'&temp_img='+temp_name+'&dest_img='+dest_name+'&folder='+folder+'&post_id='+post_id+'&side='+side+'&ext='+ext;                              
            cropDialog(url);
           
        }
    }
}();
