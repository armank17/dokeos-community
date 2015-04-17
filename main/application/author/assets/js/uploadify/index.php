<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<link href="uploadfile.css" rel="stylesheet">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="jquery.uploadfile.min.js"></script>
</head>
<body>
<div id="mulitplefileuploader">Upload</div>
<div id="status"></div>
<script>
$(document).ready(function() {
    var settings = {
        url: "upload.php",
        dragDrop: true,
        fileName: "myfile",
        allowedTypes: "jpg, png, gif, jpeg",	
        returnType: "json",
        showDone: false,
        showProgress: true,
        showDelete: false,
        showAbort: true,
        formData: {
            "name1": "elmer",
            "name2": "charre.jpg"
        },
        onSuccess: function(files, data, xhr) {
            //alert((data));
        }
    };
    $("#mulitplefileuploader").uploadFile(settings);
});
</script>
</body>

