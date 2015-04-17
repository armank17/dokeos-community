$(document).ready(function() {
    if ($(".createlink").is(':checked')) {
        $("#linkcreation").show();
    }
    if ($("#node-form").length > 0) {
        $("#node-form").validate({
            rules: {
                menu_link_title: {
                    required: {
                        depends: function(element) {
                            return ($('#createlink').is(':checked'));
                        }
                    }
                }
//                ,menu_link_description: {
//                    required: {
//                        depends: function(element) {
//                            return ($('#createlink').is(':checked'));
//                        }
//                    }
//                }
            }
        });
    }

    $("a.link-homepage-template").click(function(e) {
        pageHomeModel.getTemplate($(this), e);
    });

    $(".page-delete-home").click(function(e) {
        pageHomeModel.delete($(this), e);
    });

    $(".createlink").change(function(e) {
        pageHomeModel.createlink($(this), e);
    });
    $(".enabled").click(function(e) {
        pageHomeModel.enableNode($(this), e);
    });
});
