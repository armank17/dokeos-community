<?php
/* For licensing terms, see /dokeos_license.txt */
// including the global Dokeos file
require_once  '../global.inc.php';
global $_course;




if(isset($_POST)){

$id = $_GET['id'];
$language = $_GET['language'];
getIdLang($id, $language);

}              
         

function getIdLang($id,$lang){
   $table_emailtemplate 	= Database::get_main_table(TABLE_MAIN_EMAILTEMPLATES);
    $sql            = "SELECT description from $table_emailtemplate WHERE id =$id";

    $result         = Database::query($sql,__FILE__,__LINE__);
    $row            = Database::fetch_array($result);
    $description    = $row['description'];
    
    $sqlLang        = "select id from $table_emailtemplate where description = '$description' AND language = '$lang'";
    $resultLang     = Database::query($sqlLang,__FILE__,__LINE__);
    
    if(Database::num_rows($resultLang)>0){
        $rowLang        = Database::fetch_array($resultLang);
        $IdLang     = $rowLang['id'];   
        echo $IdLang;
    }
    
      
}
?>
