/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

(function() {
    CKEDITOR.dialog.add('imgmap', function(editor) {
        return {
            title: editor.lang.imgmap.modal_title,
            contents: [{
                id: 'iframe',
                expand: true,
                elements: [{
                    type: 'iframe',
                    src: editor.config.fileImgMapUrl,
                    width: '100%',
                    height: '100%'
                }]
            }],
            onOk: function() {
                $(".cke_dialog_ui_iframe")[0].contentWindow.Ok();
                return true;
            }
        };
    });
})();