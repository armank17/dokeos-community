<?php

$output_dir = "elmer/";
if (isset($_FILES["myfile"])) {
    $ret = array();

    $error = $_FILES["myfile"]["error"];
    //You need to handle  both cases
    //If Any browser does not support serializing of multiple files using FormData() 
    if (!is_array($_FILES["myfile"]["name"])) { //single file
        //$fileName = $_FILES["myfile"]["name"];
        $fileName = $_POST["name1"] . $_POST["name2"];
        move_uploaded_file($_FILES["myfile"]["tmp_name"], $output_dir . $fileName);
        $ret[] = $fileName;
    } else {  //Multiple files, file[]
        $fileCount = count($_FILES["myfile"]["name"]);
        for ($i = 0; $i < $fileCount; $i++) {
            //$fileName = $_FILES["myfile"]["name"][$i];
            $fileName = $_POST["name1"] . $_POST["name2"];
            move_uploaded_file($_FILES["myfile"]["tmp_name"][$i], $output_dir . $fileName);
            $ret[] = $fileName;
        }
    }
    echo json_encode($ret);
}
?>