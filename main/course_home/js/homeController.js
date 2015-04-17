$(document).ready(function() {
    $(".action-dialog").click(function(e) {
        HomeModel.showActionDialog(e, $(this));
    });

});
