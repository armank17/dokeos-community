<?php
require_once '../inc/global.inc.php';
// only for admin account
api_protect_admin_script();

$tbl_certificate_template = Database::get_main_table(TABLE_MAIN_CERTIFICATE_TEMPLATE);

$affected_lp_rows = 0;

$Check1 = Database::query("SELECT id  FROM $tbl_certificate_template WHERE id =1");
$Check2 = Database::query("SELECT id  FROM $tbl_certificate_template WHERE id =2");
$Check3 = Database::query("SELECT id  FROM $tbl_certificate_template WHERE id =3");

if (Database::num_rows($Check1) <= 1) {
 Database::query("UPDATE $tbl_certificate_template SET content='<div  style=\"width: 100%;height:90px; float: left; text-align: center\">
                    <span style=\"color:#950101;font-size: 62px;font-style: italic;\">Award for Excellence</span>
            </div>
            <div  style=\"width: 100%;height:40px; margin-top: 75px; float: left; text-align: center\">
                <span style=\"font-size: 28px;font-family:arial,helvetica,sans-serif; font-style: italic;color:#950101;font-weight: bold;\">Presented to</span>
            </div>
            <div  style=\"width: 100%;height:40px; margin-top: 0px; float: left; text-align: center\">
                    <span style=\"font-family:arial,helvetica,sans-serif;font-size:28px;font-style: italic;\">{StudentFullName}</span>
            </div>
            <div  style=\"width: 100%;height:40px; margin-top: 60px; float: left; text-align: center\">
                    <span style=\"font-family:arial,helvetica,sans-serif;color:#950101;font-size: 28px;font-style: italic;font-weight: bold\">For the successful completion of</span>
            </div>
            <div  style=\"width: 100%;height:40px; margin-top: 0px; float: left; text-align: center\">
                    <span style=\"font-size:28px;font-style: italic;\">{ModuleName}</span>
            </div>
            <div  style=\"width: 100%;height:40px; margin-top: 130px; float: left; text-align: center\">
                    <span style=\"font-size:28px;font-style: italic;\">{TrainerFullName}</span>
            </div>
            <div  style=\"width: 100%;height:40px; margin-top: 0px; float: left; text-align: center\">
                    <span style=\"font-family:arial,helvetica,sans-serif;font-size: 28px;font-style: italic;\">Trainer</span>
            </div>
            <div  style=\"width: 100%;height:40px; margin-top: 0px; float: left; text-align: left\">
                    <span style=\"font-family:arial,helvetica,sans-serif;font-size: 28px;font-style: italic; margin-left: 50px;\">{Date}</span>
            </div>' WHERE id =1");    
  $affected_lp_rows++;
}
 if (Database::num_rows($Check2) <= 1) {
 Database::query("UPDATE $tbl_certificate_template SET content='<div class=\"certificate-drag\" style=\"width: 1070px;height:90px;  text-align: center;\">
	<span style=\"color:#0f1910;font-size: 72px; font-style: italic\">Award for Excellence</span>
</div>

<table>
    <tr>
        <td><div style=\"width:340px; margin-top: 45px;height:85px; float: left; text-align: right;\"> 
         <span style=\"color:#3d4d3f;font-size: 28px;\">Presented to</span>
    </div></td>
        <td><div style=\"width: 558px; margin-top: 45px;height:85px; float: left; text-align: left; margin-left: 58%px; margin-left: 74px;\">
        <span style=\"font-size:28px;\">{StudentFullName}</span>
    </div></td>
    </tr>    
</table>


<div class=\"certificate-drag\" style=\"width: 1070px;height:55px;  text-align: center;\">
	<span style=\"font-size:36px;color: rgb(134, 139, 118);font-style: italic; font-weight: bold;\">For</span>
</div>

<div class=\"certificate-drag\" style=\"width: 1070px;height:55px; text-align: center; margin-top: 30px;\">
	<span style=\"font-size:28px;\">{ModuleName}</span>
</div>
<table>
    <tr>
        <td>
            <div style=\"width: 376px; margin-left: 70px; margin-top: 45px;height:85px; float: left; text-align: center;\"> 
                <span style=\"font-size:28px;\">{TrainerFullName}</span><br>
                <span style=\"font-size:28px;\">Trainer</span>
            </div>
        </td>
        <td>
            <div style=\"width: 420px; margin-top: 45px;height:45px;  text-align: right; margin-left: 58%px; margin-left: 74px;\">
                <span style=\"font-size:28px;\">{Date}</span>
            </div>
        </td>
    </tr>
</table>' WHERE id =2");   
  $affected_lp_rows++;
 }
    
if (Database::num_rows($Check3) <= 1) {
Database::query("UPDATE $tbl_certificate_template SET content='<div class=\"certificate-drag\" style=\"width: 1070px;height:90px;  text-align: center;\">
	<span style=\"color:#0f1910;font-size: 72px; font-style: italic\">Award for Excellence</span></div><table><tr><td><div style=\"width:340px; margin-top: 45px;height:85px; float: left; text-align: right;\"> 
       <span style=\"color:#3d4d3f;font-size: 28px;\">Presented to</span>
    </div></td>
        <td><div style=\"width: 558px; margin-top: 45px;height:85px; float: left; text-align: left; margin-left: 58%px; margin-left: 74px;\">
        <span style=\"font-size:28px;\">{StudentFullName}</span>
    </div></td>
    </tr>    
</table>
<div class=\"certificate-drag\" style=\"width: 1070px;height:55px;  text-align: center;\">
<span style=\"font-size:36px;color: rgb(134, 139, 118);font-style: italic; font-weight: bold;\">For</span>
</div>
<div class=\"certificate-drag\" style=\"width: 1070px;height:55px; text-align: center; margin-top: 30px;\">
<span style=\"font-size:28px;\">{ModuleName}</span>
</div>
<table>
    <tr>
        <td>
            <div style=\"width: 376px; margin-left: 70px; margin-top: 45px;height:85px; float: left; text-align: center;\"> 
                <span style=\"font-size:28px;\">{TrainerFullName}</span><br>
                <span style=\"font-size:28px;\">Trainer</span>
            </div>
        </td>
        <td>
            <div style=\"width: 420px; margin-top: 45px;height:45px;  text-align: right; margin-left: 58%px; margin-left: 74px;\">
                <span style=\"font-size:28px;\">{Date}</span>
            </div>
        </td></tr></table>' WHERE id =3");   
  $affected_lp_rows++;
} 
    
     

    
   
    
   

echo '<p>'.$affected_lp_rows.' files updated</p>';

?>

