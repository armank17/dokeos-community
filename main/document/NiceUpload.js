/**
 * This plugin has been developed to implement Picture Preview functionality to preview the files to upload.
 * @param MAX_WIDTH  : Max Width Input File
 * @param MAX_SIZE : Max Width & Height Preview Picture
 * @author Elmer Charre Salazar <elmer.nyd@gmail.com>
 * @version December 2013
 */

jQuery.fn.extend({
    NiceInputUpload: function(MAX_WIDTH, MAX_SIZE, float, MAX_WIDTH_BAR, Preview, id_progress) {
        return this.each(function() {
            MAX_WIDTH_BAR || (MAX_WIDTH_BAR = '100%');
            id_progress || (id_progress = 'progress_upload');
            $(this).nicefileinput(MAX_WIDTH, MAX_SIZE, float, MAX_WIDTH_BAR, Preview, id_progress);
        });
    }
});

!function(a) {
    a.fn.nicefileinput = function(MAX_WIDTH, MAX_SIZE, float, MAX_WIDTH_BAR, Preview, id_progress) {
        var c = {label: getLang('Browse')};
        return this.each(function() {
            var b = this;
            var g = a('<input type="text" name="imageFileName" id="imageFileName" disabled>').css({display: "inline", opacity: "0", "width": "50%", "background-color": "white"});
            var progress = a('<div id="'+id_progress+'">').css({display: "none", "max-width": MAX_WIDTH_BAR, "height": "30px", "border-radius": "4px", "line-height": "30px", "text-align": "center", "font-weight": "bold", "font-size": "14px"});
            if (Preview === true) {
                var img = a('<img id="imgPreviewNice" src="">').css({display: "none", "max-width": MAX_SIZE, "max-height": MAX_SIZE, "border-radius": "4px"});
                h = a("<div>").css({position: "relative", display: "block", "float": float, "text-align": "center", "width": MAX_WIDTH, "height": "28px", "line-height": "27px", "border-radius": "5px"}).addClass("NFI_button").html(c.label);
                a(b).after(img), a(b).after(progress), a(b).after(g), a(b).wrap(h), a(".NFI").wrapAll('<div class="NFI-wrapper" id="NFI-wrapper">'), a(".NFI-wrapper").css({overflow: "auto", display: "inline-block"}), a("#NFI-wrapper").addClass(a(b).attr("class")), a(b).css({opacity: 0, position: "absolute", border: "none", margin: 0, padding: 0, top: "-4px", left: "-4px", cursor: "pointer", height: "40px", width: (MAX_WIDTH + 10)}).addClass("NFI-current"), a(b).attr("title", getLang('Browse'));
            } else {
                h = a("<div>").css({position: "relative", display: "block", "float": float, "text-align": "center", "width": MAX_WIDTH, "height": "28px", "line-height": "27px", "border-radius": "5px"}).addClass("NFI_button").html(c.label);
                a(b).after(progress), a(b).after(g), a(b).wrap(h), a(".NFI").wrapAll('<div class="NFI-wrapper" id="NFI-wrapper">'), a(".NFI-wrapper").css({overflow: "auto", display: "inline-block"}), a("#NFI-wrapper").addClass(a(b).attr("class")), a(b).css({opacity: 0, position: "absolute", border: "none", margin: 0, padding: 0, top: "-4px", left: "-4px", cursor: "pointer", height: "40px", width: (MAX_WIDTH + 10)}).addClass("NFI-current"), a(b).attr("title", getLang('Browse'));
            }
        });
    };
}(jQuery);
