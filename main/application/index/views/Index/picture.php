<?php
include("../../../../appcore/library/thumbnail/Thumbnail.php");
header("Content-type: image/jpg");
        $mythumb = new appcore_library_thumbnail_Thumbnail();
        $mythumb->loadImage('http://img43.imageshack.us/img43/3022/finalfantasyn.jpg');
        $mythumb->resize(100, 'width');
        $mythumb->show(); 
?>
