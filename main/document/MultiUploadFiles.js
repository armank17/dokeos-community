/**
 * This plugin has been developed to implement drag and drop functionality to preview the files to upload.
 * @param MAX_FILES  : Indicates the maximum number of files to upload
 * @param FILES_TYPE : Indicates the allowed file formats up : {'IMAGES', 'DOCUMENTS', 'ARCHIVES', 'AUDIO', 'VIDEO', '*'}
 * @param PATH_FILE  : Indicates the path in which the files will get uploaded
 * @author Elmer Charre Salazar <elmer.nyd@gmail.com>
 * @version November 2013
 */

jQuery.fn.extend({
    DinamicUpload: function(FILES_TYPE, style) {
        $('.dragandrophandler').remove();
        FILES_TYPE || (FILES_TYPE = '*');
        style || (style = 2);
        Files_Type = FILES_TYPE.toUpperCase();
        allowed_Formats('');
        if (Files_Type === '*') {
            PermitedFormat = getLang('DisableFormat');
            $('.msg-error-formats').text(PermitedFormat + " " + (extArray.join(" ")) + "");
        }
        else {
            PermitedFormat = getLang('AllowedFormat');
            $('.msg-error-formats').text(PermitedFormat + " " + (extArray.join(" ")) + "");
        }
        $('.msg-error-formats').show('slow');

        obj_input_add = $(this);
        var add_file = document.createElement('div');
        add_file.innerHTML = '<a class="toolactionplaceholdericon toolactionadd" \n\
                              style="float:right; width:40px; height:40px; margin-top:-75px;" href="javascript:void(0)" \n\
                              onclick="return addInputFile('+style+');"></a>';
        obj_input_add.append(add_file);
    },
    DragandDrop: function(FILES_TYPE, MAX_FILES, PATH_FILE) {
        return this.each(function() {
            obj = $(this);
            if (support_api === 1) {
                obj.addClass("dragandrophandler");
                MAX_FILES || (MAX_FILES = 100);
            } else {
                obj.hide();
                MAX_FILES = 1;
            }
            FILES_TYPE || (FILES_TYPE = '*');
            Max_Files = MAX_FILES;
            Files_Type = FILES_TYPE.toUpperCase();
            Path_File = PATH_FILE;
            Dropbox = Path_File.substring(Path_File.lastIndexOf("/"));

            allowed_Formats('');
            if (Files_Type === '*') {
                PermitedFormat = getLang('DisableFormat');
                $('.msg-error-formats').text(PermitedFormat + " " + (extArray.join(" ")) + "");
            }
            else {
                PermitedFormat = getLang('AllowedFormat');
                $('.msg-error-formats').text(PermitedFormat + " " + (extArray.join(" ")) + "");
            }
            $('.msg-error-formats').show('slow');

            obj.on("dragenter", function(e) {
                e.stopPropagation();
                e.preventDefault();
                obj.css("border", "1px solid #B3B3B3");
            });

            obj.on("dragover", function(e) {
                e.stopPropagation();
                e.preventDefault();
            });

            obj.on("dragleave", function(e) {
                e.stopPropagation();
                e.preventDefault();
                obj.css("border", "1px dashed #B3B3B3");
            });

            obj.on("drop", function(e) {
                e.stopPropagation();
                e.preventDefault();
                obj.css("border", "1px dashed #B3B3B3");
                handleFileUpload(e.originalEvent.dataTransfer.files, obj);
            });

            $(document).on('dragenter', function(e) {
                e.stopPropagation();
                e.preventDefault();
            });

            $(document).on('dragover', function(e) {
                e.stopPropagation();
                e.preventDefault();
            });

            $(document).on('drop', function(e) {
                e.stopPropagation();
                e.preventDefault();
            });

            $(":file").change(function(e) {
                e.stopPropagation();
                e.preventDefault();

                if (support_api === 0) { //browser not support API
                    var name_file = e.target.value.replace("C:\\fakepath\\", "");
                    if (rowCount === 1) { //IE8 - 9
                        if (allowed_Formats(name_file) === true)
                            $('.filename').text(name_file);
                    } else
                        handleFileUpload([{name: name_file}], obj);
                } else {
                    handleFileUpload(e.target.files, obj);
                }
            });
        });
    }
});

var obj = "";
var rowCount = 0;
var Max_Files = 0;
var Files_Type = "";
var Path_File = "";
var Dropbox = '';
var array_files_name = new Array();
var array_files = new Array();
var support_api = 0;
var is_image = 0;
var count_input = 0;
var obj_input_add = "";
var txtUpload = "";
var txtUploading = "";
var Nofile = "";
var PermitedFormat = "";
var DiskSpace = 0;
var QuotaFiles = 0;
var SpaceAvailable = "";

function handleFileUpload(files, obj) {
    for (var i = 0; i < files.length; i++) {
        if (allowed_Formats(files[i].name) === true) {
            var index = searchFile(files[i].name);
            if (index === -1) {
                var status = new classFile(obj, i);
                status.setFileNameSize(files[i]);
                abortFile(status);
            }
        }
    }
    $(":file").val('');
}

function hide_msg_error() {
    if (Files_Type === '*')
        $('.msg-error-formats').text(PermitedFormat + " " + (extArray.join(" ")) + "");
    else
        $('.msg-error-formats').text(PermitedFormat + " " + (extArray.join(" ")) + "");
}

function classFile(obj, i) {
    if (rowCount < Max_Files) {
        rowCount++;
        var row = "odd";
        if (rowCount % 2 === 0)
            row = "even";

        this.setFileNameSize = function(file) {
            this.statusbar = $("<div id=" + (rowCount - 1) + " class=statusbar " + row + "></div>");
            if (file.type.match('image.*')) {
                if (support_api === 1) {
                    if (is_image === 1) {
                        this.image = $("<img id=img_preview" + i + " style='width:140px;height:auto;margin-left:5px;' src=''>").appendTo(this.statusbar);
                        this.filename = $("<div class=filename style='font-size:13px;margin-left:10px;width:58%;'></div>").appendTo(this.statusbar);
                    }
                    else {
                        this.image = $("<img id=img_preview" + i + " style='width:80px;height:auto;margin-left:5px;' src=''>").appendTo(this.statusbar);
                        this.filename = $("<div class=filename style='font-size:13px;margin-left:10px; width:67%;'></div>").appendTo(this.statusbar);
                    }
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#img_preview' + i).attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                    reader = null;
                }
            } else {
                this.image = $("<img id=img_preview" + i + " style='width:80px; height:80px;margin-left:5px;' src='/main/img/icon_document_tool/" + get_icon_document(file.name) + "'>").appendTo(this.statusbar);
                this.filename = $("<div class=filename style='font-size:13px;margin-left:10px; width:67%;'></div>").appendTo(this.statusbar);
            }
            this.size = $("<div class=filesize></div>").appendTo(this.statusbar);
            this.sizereal = $("<div id=sizereal" + rowCount + " style='display:none;'></div>").appendTo(this.statusbar);

            if (support_api === 1)
                this.abort = $("<img class=delete_upload src='/main/img/delete_na.gif' style='cursor:pointer;'>").appendTo(this.statusbar);
            obj.after(this.statusbar);

            if (support_api === 1) { //browser support API
                var length = file.size;
                QuotaFiles = QuotaFiles + length;
                this.sizereal.html(length);
                length = parseFloat(length / 1024);
                if (length > 1024) {
                    length = length / 1024;
                    length = length.toFixed(2) + " MB";
                }
                else
                    length = length.toFixed(2) + " KB";
                this.size.html(length);
            }
            array_files_name[rowCount - 1] = file.name;
            array_files[rowCount - 1] = file;
            this.filename.html(file.name);
        };

        this.setAbort = function(jqxhr) {
            var sb = this.statusbar;
            this.abort.click(function() {
                QuotaFiles = QuotaFiles - parseFloat($('#sizereal' + (parseInt(sb.attr("id")) + 1)).text());
                array_files_name[sb.attr("id")] = "";
                array_files[sb.attr("id")] = "";
                jqxhr.abort();
                sb.remove();
            });
        };
    } else {
        $('.msg-error-formats').text(getLang('MaxFile') + " " + Max_Files);
        setTimeout('hide_msg_error()', 2000);
    }
}

function searchFile(file) {
    var index = -1;
    for (var i = 0; i < array_files_name.length; i++) {
        if (file === array_files_name[i]) {
            index = i;
            break;
        }
    }
    return index;
}

function abortFile(status) {
    status.setAbort($.ajax({contentType: false, processData: false, cache: false, data: null}));
}

function allowed_Formats(file) {
    var allowSubmit = false;
    is_image = 0;
    switch (Files_Type) {
        case 'ANIMATION':
            extArray = new Array(".swf");
            $(":file").attr('accept', "application/x-shockwave-flash");
            break;
        case 'IMAGES':
            is_image = 1;
            extArray = new Array(".jpg", ".png", ".jpeg", ".gif");
            $(":file").attr('accept', "image/jpg, image/png, image/jpeg, image/gif");
            break;
        case 'MINDMAP':
            is_image = 1;
            extArray = new Array(".jpg", ".png", ".jpeg", ".gif", ".xmind");
            $(":file").attr('accept', "image/jpg, image/png, image/jpeg, image/gif, image/xmind");
            break;
        case 'DOCUMENTS':
            extArray = new Array(".csv", ".doc", ".docx", ".odp", ".ods", ".odt", ".pdf", ".ppt", ".pptx", ".rtf", ".xls", ".xlsx", ".xps", ".txt", ".eps", ".wps");
            break;
        case 'AUDIO':
            extArray = new Array(".mp3");
            $(":file").attr('accept', "audio/mp3");
            break;
        case 'VIDEO':
            extArray = new Array(".mp4");
            $(":file").attr('accept', "video/mp4");
            break;
        case 'ARCHIVES':
            extArray = new Array(".zip", ".rar", ".jar", ".tar", ".tar.gz", ".cab");
            break;
        case '*':
            extArray = new Array(".exe", ".bash", ".mp4", ".flv", ".avi", ".3gp", ".m4v", ".mkv", ".mov", ".mpeg", ".mpg", ".ovg", ".wmv", ".webm", ".divx", ".wma");
            allowSubmit = true;
            break;
    }

    var ext = (file.substring(file.lastIndexOf("."))).toLowerCase();
    for (var i = 0; i < extArray.length; i++) {
        if (Files_Type === '*') {
            if (extArray[i] === ext) {
                allowSubmit = false;
                break;
            }
        } else {
            if (extArray[i] === ext) {
                allowSubmit = true;
                break;
            }
        }
    }

    return allowSubmit;
}

$(function() {
    var mypath = typeof myDokeosWebPath !== 'undefined' ? myDokeosWebPath : '/';
    $.ajax({
        url: mypath + 'main/document/quota.php?action=diskspace',
        success: function(value_quota) {
            DiskSpace = value_quota;
        }
    });
    SpaceAvailable = getLang('ShowCourseQuotaUse');
    txtUpload = getLang('Upload');
    txtUploading = getLang('Uploading') + ". ";
    Nofile = getLang('NoFileSelect');
    if (typeof FormData !== "undefined") { //browser support API
        support_api = 1;
        $(":file").nicefileUpload();
    }
    else {
        support_api = 0;
        $(":file").nicefileinput();
        $(":file").attr('name', 'files[]');
        $(":file").attr('id', 'type_file0');
    }

    var element = document.querySelector('input[type=file]');
    element.setAttribute("multiple", "multiple");
    //Submit Form Data
    $(":submit").click(function(e) {
        e.preventDefault();
        if (support_api === 0) { //browser not support API
            saveFiles();
        } else {
            if ($(".statusbar").length === 0) {
                $('.msg-error-formats').text(Nofile);
                setTimeout('hide_msg_error()', 2000);
            } else {
                try {
                    Path_File = Path_File + "&search_terms=" + $('.tag-it').val();
                } catch (err) {
                }
                if ($("#space").is(':checked')) //EscapeSpaces is checked
                    Path_File = Path_File + "&space=no";
                if ($("#rename").is(':checked')) //RenameFiles is checked
                    Path_File = Path_File + "&rename=yes";

                if (Dropbox === '/dropbox' || Dropbox === '/mindmaps') {
                    var array_users = new Array();
                    var user_id = 0;
                    try {
                        var select = document.getElementById('users') || document.getElementById('users_maps');
                        if ($("#db_overwrite").is(':checked')) //Dropbox file overwrite
                            Path_File = Path_File + "&dbox=yes";
                        for (var i = 0; i < select.length; i++) {
                            if (select.options[i].selected === true) {
                                array_users[user_id] = select[i].value;
                                user_id++;
                            }
                        }
                    } catch (err) {
                        array_users.length = 0;
                    }
                    Path_File = Path_File + "&users=" + array_users;
                    if (array_users.length === 0 && Dropbox === '/dropbox') {
                        $('.msg-error-formats').text(getLang('SelectUser'));
                        select.focus();
                        setTimeout('hide_msg_error()', 2500);
                    }
                    else
                        saveFiles();
                } else {
                    if ($("#unzip").is(':checked')) //Unzip is checked
                        Path_File = Path_File + "&unzip=yes";
                    saveFiles();
                }
            }
        }
    });
});

function FormSubmit() {
    var send_form = false;
    for (var i = 0; i < count_input + 1; i++) {
        if ($('#type_file' + i).val() !== '') {
            send_form = true;
            break;
        }
    }
    if (send_form === true)
        $('form').submit();
    else {
        $('.msg-error-formats').text(Nofile);
        setTimeout('hide_msg_error()', 2500);
    }
}

function saveFiles() {
    $(document).scrollTop(0);
    if (support_api === 0) { //browser not support API
        try {
            var select = document.getElementById('users');
            if (select === null) { //Tool Documents
                FormSubmit();
            }
            else { //Tool Dropbox
                if (select.selectedIndex > -1)
                    FormSubmit();
                else {
                    $('.msg-error-formats').text(getLang('SelectUser'));
                    select.focus();
                    setTimeout('hide_msg_error()', 2500);
                }
            }
        } catch (err) {
        }
    } else {
        if (QuotaFiles > DiskSpace) {
            $('.msg-error-formats').text(SpaceAvailable + ' ' + ((DiskSpace / 1024) / 1024).toFixed(2) + ' MB');
            setTimeout('hide_msg_error()', 5000);
        } else {
            var data_files = new FormData();
            for (var i = 0; i < array_files.length; i++) {
                if (array_files[i] !== '') {
                    data_files.append("files[]", array_files[i]);
                }
            }
            //send files by ajax
            $('.dragandrophandler').remove();
            $('img.delete_upload').hide(500);
            $(":submit").attr("disabled", true);
            $(":file").attr("disabled", true);
            $.ajax({
                xhr: function() {
                    var xhrobj = $.ajaxSettings.xhr();
                    if (xhrobj.upload) {
                        xhrobj.upload.addEventListener('progress', function(event) {
                            var percent = 0;
                            var position = event.loaded || event.position;
                            var total = event.total;
                            if (event.lengthComputable)
                                percent = Math.ceil(position / total * 100);
                            progress(percent);
                        }, false);
                    }
                    return xhrobj;
                },
                url: Path_File,
                type: "POST",
                contentType: false,
                processData: false,
                cache: false,
                data: data_files,
                success: function(msg) {
                    window.location.href = Path_File;
                }
            });
        }
    }
}

function progress(progress) {
    $('.msg-error-formats').text(txtUploading + progress + "% ");
}

!function(a) {
    a.fn.nicefileUpload = function() {
        var c = {label: txtUpload};
        return this.each(function() {
            var b = this;
            var g = a('<div class="msg-error-formats">').css({display: "block"}),
            h = a("<div>").css({overflow: "hidden", position: "relative", display: "block", "width": "120px", "float": "left", "text-align": "center"}).addClass("NFI_button").html(c.label);
            a(b).after(g), a(b).wrap(h), a(".NFI").wrapAll('<div class="NFI-wrapper" id="NFI-wrapper">'), a(".NFI-wrapper").css({overflow: "auto", display: "inline-block"}),
            //a("#NFI-wrapper").addClass(a(b).attr("class")), a(b).css({opacity: 0, position: "absolute", border: "none", margin: 0, padding: 0, top: 0, right: "0", cursor: "pointer", height: "60px"}).addClass("NFI-current"),
            a(b).attr("title", txtUpload);
        });
    };
}(jQuery);

!function(a) {
    a.fn.nicefileinput = function() {
        var c = {label: txtUpload};
        return this.each(function() {
            var b = this;
            var msg = a('<div class="msg-error-formats">').css({display: "block", 'margin-top': "5px"});
            var g = a('<input type="text" id="txtInputFile0" disabled>').css({display: "inline", "width": "40%", "height": "20px", "margin-top": "5px", "margin-left": "-1px", "background-color": "white"});
            var h = a("<div>").css({overflow: "hidden", position: "relative", display: "block", "float": "left", "text-align": "center", "width": "100px", "border-radius": "0", "border-top-left-radius": "5px", "border-bottom-left-radius": "5px"}).addClass("NFI_button").html(c.label);
            a(b).after(msg), a(b).after(g), a(b).wrap(h), a(".NFI").wrapAll('<div class="NFI-wrapper" id="NFI-wrapper">'), a(".NFI-wrapper").css({overflow: "auto", display: "inline-block"}),
            //a("#NFI-wrapper").addClass(a(b).attr("class")), a(b).css({opacity: 0, position: "absolute", border: "none", margin: 0, padding: 0, top: 0, right: 0, cursor: "pointer", height: "60px"}).addClass("NFI-current"), 
            a(b).on("change", function() {
                if (allowed_Formats($('#type_file0').val()) === true) {
                    $('#txtInputFile0').val($('#type_file0').val().replace("C:\\fakepath\\", ""));
                } else {
                    $('#txtInputFile0').val('');
                    $('#type_file0').replaceWith($('#type_file0').clone(true));
                }
            }), a(b).attr("title", txtUpload);
        });
    };
}(jQuery);

function get_icon_document(file) {
    file = (file.substring(file.lastIndexOf("."))).toLowerCase();
    switch (file) {
        case '.doc': case '.dot': case '.rtf': case '.mcw': case '.wps': case '.psw': case '.docm': case '.docx': case '.dotm':
        case '.dotx': case '.odt': 
            file = 'word-icon.png';
            break;
        case '.pdf':
            file = 'pdf-icon.png';
            break;
        case '.xls': case '.xlt': case '.pxl': case '.xlsx': case '.xlsm': case '.xlam': case '.xlsb': case '.xltm': case '.xltx':
            file = 'excel-icon.png';
            break;
        case '.zip': case '.tar': case '.rar': case '.gz': case '.jar': case '.cab':
            file = 'compress-icon.png';
            break;
        case '.htm': case '.html': case '.htx': case '.xml': case '.xsl': case '.xhtml': case '.dxhtml':
            file = 'html-icon.png';
            break;
        case '.js': case '.css': case '.java': case '.php': case '.phps': case '.asp': case '.cpp':
            file = 'web-icon.png';
            break;
        case '.mp3': case '.wav': case '.wma': case '.m4a': case '.aac': case '.ac3': case '.mka': case '.mpc': case '.ogg':
            file = 'audio-icon.png';
            break;
        case '.txt': case '.log':
            file = 'txt-icon.png';
            break;
        case '.swf': case '.fla':
            file = 'flash-icon.png';
            break;
        case '.ppt': case '.pps': case '.pptm': case '.pptx': case '.potm': case '.potx': case '.ppam': case '.ppsm': case '.ppsx':
            file = 'ppt-icon.png';
            break;
        default:
            file = 'def-icon.png';
            break;
    }
    return file;
}

function addInputFile(style) {
    count_input++;
    switch(style) {
        case 1: style= 'margin-left:95px; width:80%;'; break;
        case 2: style= 'margin-left:24.5%; width:110%;'; break;
        case 3: style= 'margin-left:0; width:110%;'; break;
    }
    var newInput = document.createElement('div');
    newInput.innerHTML = '<div class="row"><div style="'+style+' margin-top:8px;">\n\
                        <div class="NFI_button" style="overflow:hidden; position:relative; display:block; float:left; text-align:center; width: 100px; border-top-left-radius: 5px; border-top-right-radius: 0px; border-bottom-right-radius: 0px; border-bottom-left-radius: 5px;">' + txtUpload + '\n\
                        <input style="width:150px; height:30px !important; opacity:0; filter:alpha(opacity=0); position:absolute; border: none; margin: 0px; padding: 0px; top: 0px; right: 0px; cursor: pointer;" type="file" name="files[]" id="type_file' + count_input + '" title="' + txtUpload + '" onchange="setFileName(this, ' + count_input + ');"></div>\n\
                        <input type="text" id="txtInputFile' + count_input + '" disabled style="display:inline; width:40%; height:20px; margin-top: 5px; margin-left: -1px; background-color: white;"></div></div>';
    obj_input_add.append(newInput);
}

function setFileName(obj, item) {
    if (allowed_Formats(obj.value) === true) {
        $('#txtInputFile' + item).val(obj.value.replace("C:\\fakepath\\", ""));
    } else {
        $('#txtInputFile' + item).val('');
        $('#type_file' + item).replaceWith($('#type_file' + item).clone(true));
    }
}