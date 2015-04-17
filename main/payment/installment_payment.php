<?php

// resetting the course id
$cidReset = true;

// name of the language file that needs to be included
$language_file = array ('registration','admin');

// setting the help
$help_content = 'platformadministrationeditsessioncategory';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// update dates
if (isset($_POST['action']) && $_POST['action'] == 'update') {    
    $updated = SessionManager::update_payment_dates($_POST);        
    header('Location: '.api_get_path(WEB_CODE_PATH).'payment/installment_payment.php?user_id='.intval($_POST['user_id']));
    exit;    
}

// display the header
Display::display_header(get_lang('EditSessionCategory'));

if (isset($_GET['user_id'])) {
    $manage = true;    
    $user_id = intval($_GET['user_id']);
} else {
    $user_id = api_get_user_id();
}

$back = '#';
if (api_is_platform_admin() && $manage) {
    $back = api_get_path(WEB_CODE_PATH).'admin/user_list.php';
} else {
    $back = api_get_path(WEB_CODE_PATH).'payment/session_category_payments.php';
}

// clear pre proccess order
SessionManager::clear_catalogue_order_process();

?>
<div class="actions">
    <a href="<?php echo api_get_path(WEB_CODE_PATH).'payment/session_category_payments.php'; ?>"><?php echo Display::return_icon('pixel.gif', get_lang("Catalogue"), array('class' => 'toolactionplaceholdericon toolactioncatalogue')).get_lang('Catalogue'); ?></a>
    <?php print Display::return_icon('pixel.gif', get_lang("InstallmentPaymentInfo"), array('class' => 'toolactionplaceholdericon toolactionother')).get_lang('InstallmentPaymentInfo'); ?>
</div>

<div id="content">

    <?php 
    
    echo api_display_tool_title(get_lang('InstallmentPayment'));
    
    if (isset($_GET['action']) && $_GET['action'] == 'edit') {         
        $cid = intval($_GET['sess_id']);
        $uid = intval($_GET['user_id']);        
        $cat_sess = SessionManager::get_session_category($cid);
        $uinfo = api_get_user_info($uid);
        $partial = SessionManager::get_user_sess_partials_payment_atos($uid, $cid);
        $times = unserialize($partial['dates']);  
    ?>
        
    <form method="post" name="form" action="<?php echo api_get_self(); ?>?user_id=<?php echo $uid; ?>" style="margin:0px;">
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="sess_id" value="<?php echo $cid; ?>" />
        <input type="hidden" name="user_id" value="<?php echo $uid; ?>" />
        
        <h3><?php print api_get_person_name($uinfo['firstname'], $uinfo['lastname']).': '.$cat_sess['name']; ?></h3>
        
        <table border="0" cellpadding="5" cellspacing="0" width="550">
        <tr>
          <td width="40%" valign="top"><?php echo get_lang('PaymentDate1').':' ?>&nbsp;&nbsp;</td>
          <td width="70%">
            <?php echo api_draw_date_picker('sess_pay_date1', date('Y-m-d', $times[1])); ?>
          </td>
        </tr>
        <tr>
          <td width="40%" valign="top"><?php echo get_lang('PaymentDate2').':' ?>&nbsp;&nbsp;</td>
          <td width="70%">
            <?php echo api_draw_date_picker('sess_pay_date2', date('Y-m-d', $times[2])); ?>
          </td>
        </tr>
        <tr>
          <td width="40%" valign="top"><?php echo get_lang('PaymentDate3').':' ?>&nbsp;&nbsp;</td>
          <td width="70%">
            <?php echo api_draw_date_picker('sess_pay_date3', date('Y-m-d', $times[3])); ?>
          </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
          <td colspan="2">
            <button class="save" type="submit" value="<?php echo get_lang('UpdatePaymentDates') ?>"><?php echo get_lang('UpdatePaymentDates') ?></button>
          </td>
        </tr>
        </table>
        
    </form>    
        
    
    <?php } else {
        
        $rs = Database::query("SELECT * FROM payment_part_user_atos WHERE user_id={$user_id}");
        if (Database::num_rows($rs)) {
            echo '<table border="0" width="100%"  cellspacing="6" cellpadding="6" >';
            echo '<tr><th>'.get_lang('SessionCategory').'</th><th>'.get_lang('MethodOfPayment').'</th><th>'.get_lang('PaymentDate').'</th><th>'.get_lang('Actions').'</th></tr>';
            while ($row = Database::fetch_object($rs)) {                        
                $cat_sess = SessionManager::get_session_category($row->sess_id);
                $l_cost = round(($cat_sess['cost']/3), 2);            
                echo '<tr><td align="center">'.$cat_sess['name'].'</td><td align="center">'.get_lang('Week').'</td>';
                // payment dates
                $dates = unserialize($row->dates);
                if (!empty($dates)) {
                    $rs_u_pay_info = SessionManager::get_user_sess_payment_atos($user_id, $row->sess_id);
                    $quota = $rs_u_pay_info['curr_quota'];
                    echo '<td><table width="100%" border="1" style="border-collapse: collapse;"><tr>';
                    foreach ($dates as $q => $date) {                    
                        if ($q > $quota) {
                            echo '<td style="color:red" align="center">'.api_format_date(DATE_FORMAT_SHORT, $date).'</td>';
                        } else {
                            echo '<td align="center">'.api_format_date(DATE_FORMAT_SHORT, $date).'</td>';
                        }
                    }
                    echo '</tr></table></td>';
                } else {
                    echo '<td>&nbsp;</td>';
                }

                if ($manage) {
                    echo '<td align="center"><a href="'.api_get_path(WEB_CODE_PATH).'payment/installment_payment?action=edit&user_id='.$user_id.'&sess_id='.$row->sess_id.'">'.get_lang('EditPaymentDates').'</a></td>';
                } else {
                    if (empty($row->status)) {
                        echo '<td align="center"><a href="'.api_get_path(WEB_CODE_PATH).'payment/atos-sips/call_request.php?cat_id='.$row->sess_id.'&user_id='.$user_id.'&pay_type=3&from=installment"><font color="red">'.get_lang('PayNextQuota').'</font></a></td>';
                    } else {
                        echo '<td align="center">'.get_lang('AllQuotasPayed').'</td>';
                    }
                }

            }
            echo '</table>';
        } else {             
            echo get_lang('YouDoNotHaveInstallmentPayments');
        }
    }
    
    
            
?>

</div>
<?php 
// display the footer
Display::display_footer();
?>
