<?php
require_once '../global.inc.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
$user_id = intval($_GET['user_id']);
$action = $_GET['action'];
$user_info = array();
$user_info = UserManager::get_all_user_info($user_id);
$image_path = UserManager::get_user_picture_path_by_id($user_id, 'web', false, true);
$image_dir = $image_path['dir'];
$image = $image_path['file'];
$image_file = $image_dir.$image;
$img_attributes = 'src="'.$image_file.'?rand='.time().'" '
.'alt="'.api_get_person_name($user_data['firstname'], $user_data['lastname']).'" ';

$get_user_info = $user_info[0];
$user_table = '
<table class="data_table" width="645" height="271" border="0">
    <tbody>';
if ($image_file != '') { 
$user_table .= '
        <tr class="row_odd">
            <td style="width: 250px;">' . get_lang('Photo') . '<br />
            </td>
            <td align="center">:<br />
            </td>
            <td><center><img ' . $img_attributes . '/></center></td>
        </tr>';
}
if ($get_user_info['firstname'] != '') {
$user_table .= '
        <tr class="row_even">
            <td>' . get_lang('FirstName') . '<br />
            </td>
            <td align="center">:</td>
            <td>' . $get_user_info['firstname'] . '</td>
        </tr>';
}
if ($get_user_info['lastname'] != '') {
$user_table .= '
        <tr class="row_odd">
            <td>' . get_lang('LastName') . '<br />
            </td>
            <td align="center">:</td>
            <td>' . $get_user_info['lastname'] . '</td>
        </tr>';
}
if ($get_user_info['competences'] != '') {
$user_table .= '
        <tr class="row_even">
            <td>' . get_lang('MyCompetences') . '</td>
            <td align="center">:</td>
            <td>' . $get_user_info['competences'] . '</td>
        </tr>';
}
if ($get_user_info['diplomas'] != '') {
$user_table .= '
        <tr class="row_odd">
            <td style="width: 250px;"> ' . get_lang('MyDiplomas') . ' </td>
            <td align="center">:</td>
            <td>' . $get_user_info['diplomas'] . '</td>
        </tr>';
}
if ($get_user_info['teach'] != '') {
$user_table .= '
        <tr class="row_even">
            <td style="width: 250px;"> ' . get_lang('MyTeach') . ' </td>
            <td align="center" style="margin-left: -88px;">:</td>
            <td>' . $get_user_info['teach'] . '</td>
        </tr>';
}
if ($get_user_info['openarea']) {
$user_table .= '
        <tr class="row_odd">
            <td>' . get_lang('MyPersonalOpenArea') . ' </td>
            <td align="center">:</td>
            <td>' . $get_user_info['openarea'] . '</td>
        </tr>';
}
        '
    </tbody>
</table>
';
echo utf8_encode($user_table);