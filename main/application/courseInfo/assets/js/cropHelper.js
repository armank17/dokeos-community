jQuery.fn.extend({
    CropHelper: function(params) {

        params = $.extend({
            forceOffset: undefined,
            browserTitle: "Browser",
            isCanvasSupported: undefined,
            imageFormId: "imageForm",
            imageFormName: "imageForm",
            imageFormMethod: "POST",
            imageFormEnctype: "multipart/form-data",
            runParams: undefined,
            windowAction: undefined,
            windowTitle: "Crop",
            windowPostAction: "",
            useForm: false,
            windowUrl: undefined,
            windowUploadUrl: undefined,
            zoomPreview: "100%"

        }, params);


        var fileInput = $(this);

        if (params.useForm.length > 0) {

            var parentForm = fileInput.parents('form:first');
            var newInput1 = $('<input></input>');
            newInput1.attr("type", "hidden");
            newInput1.attr("name", "to");
            newInput1.attr("value", params.windowAction);
            parentForm.append(newInput1);
            var newInput2 = $('<input></input>');
            newInput2.attr("type", "hidden");
            newInput2.attr("name", "runparams");
            newInput2.attr("value", JSON.stringify(params.runParams));
            parentForm.append(newInput2);
        }
        else {

            var parent = fileInput.parent();
            var buttonBrowser = $('<button></button>');
            buttonBrowser.text(params.browserTitle);
            buttonBrowser.css({'border-radius': '0', 'height': '34px', 'border-top-left-radius': '5px', 'border-bottom-left-radius': '5px', 'border': 'none'});
            buttonBrowser.click(function(e) {
                e.preventDefault();
                fileInput.click();
            });
            parent.append(buttonBrowser);
            var inputFileName = $('<input></input>');
            inputFileName.attr("name", "imageFileName");
            inputFileName.attr("id", "imageFileName");
            inputFileName.css({'border-radius': '5px', 'height': '19px', 'width': '50%', 'margin-left': '-5px', 'padding-bottom': '8px'});
            inputFileName.click(function(e) {
                e.preventDefault();
                fileInput.click();
            });
            parent.append(inputFileName);

            var divPreview = $('<div></div>');
            divPreview.attr("id", "divImgPreview");
            var imgPreview = $('<img></img>');
            imgPreview.attr("id", "divImgPreviewed");
            imgPreview.attr("src", "");
            //imgPreview.css("zoom", params.zoomPreview);
            
            imgPreview.css("transform", "Scale(1.0)");
            imgPreview.css("transform-origin", "0");
            
            divPreview.append(imgPreview);


            if ($('#divImgPreview').length) {
                divPreview.attr("class", $('#divImgPreview').attr("class"));
                imgPreview.attr("src", $('#divImgPreviewed').attr("src"));
                divPreview.empty();
                divPreview.append(imgPreview);
                $('#divImgPreview').replaceWith(divPreview);
            }
            else {
                parent.append(divPreview);
            }


            var offset;
            if (params.forceOffset == undefined)
                offset = fileInput.offset();
            else
                offset = params.forceOffset;
            fileInput.remove();
            fileInput.css("opacity", "0");
            fileInput.css("position", "absolute");
            //fileInput.css("left",offset.left);
            //fileInput.css("top",offset.top);
            //fileInput.css("z-index","10");
            fileInput.css("margin", "0px");
            fileInput.css("padding", "0px");
            fileInput.css("height", "30px");
            var newForm = $('<form"></form>');
            newForm.attr("name", params.imageFormName);
            newForm.attr("id", params.imageFormId);
            newForm.attr("method", params.imageFormMethod);
            newForm.attr("enctype", params.imageFormEnctype);
            newForm.append(fileInput);
            var newInput = $('<input></input>');
            newInput.attr("type", "hidden");
            newInput.attr("name", "to");
            newInput.attr("value", params.windowAction);
            newForm.append(newInput);
            $('body').prepend(newForm);
        }

        $('body').prepend('<input type="hidden" id="imageBufferWidth" name="imageBufferWidth"></input>');
        $('body').prepend('<input type="hidden" id="imageBufferHeight" name="imageBufferHeight"></input>');
        $('body').prepend('<input type="hidden" id="imageBuffer" name="imageBuffer"></input>');
        var newA = $('<a></a>');
        newA.attr("to", params.windowAction);
        newA.attr("title", params.windowTitle);
        var rnd = Math.floor((Math.random() * 100) + 1);
        newA.attr("id", "a" + rnd);
        newA.attr("href", params.windowUrl);
        newA.attr("deleteFn", params.windowPostAction);

        $('body').prepend(newA);

        var newInputA = $('<input></input>');
        newInputA.attr("type", "hidden");
        newInputA.attr("id", "runparams");
        newInputA.attr("value", JSON.stringify(params.runParams));

        $('body').prepend(newInputA);

        fileInput.change(function(e) {


            if (params.isCanvasSupported) {
                if (e.target.files[0].type.match('image.*')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = new Image();
                        img.src = e.target.result;
                        img.onload = function() {
                            $("#imageBufferWidth").val(this.width);
                            $("#imageBufferHeight").val(this.height);
                        };
                        $('#imageBuffer').attr('value', e.target.result);
                        InfoModel.showActionDialogX($("#a" + rnd));
                    };
                    reader.readAsDataURL(e.target.files[0]);
                    reader = null;
                } else {
                    //alert(getLang('OnlyJPG'));
                }
            }
            else {
                var formToUse = (params.useForm.length > 0 ? params.useForm : params.imageFormId)


                $("#" + formToUse).ajaxForm({
                    type: "POST",
                    url: params.windowUploadUrl,
                    beforeSend: function() {
                        $("#preview").html('<img src="' + params.uploadingImage + '" alt="Uploading...."/>');
                    },
                    success: function(data) {
                        InfoModel.showActionDialogX($("#a" + rnd));
                    }

                }).submit();

            }


        });

    }

});


