<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author
* @package dokeos.admin
*/

// resetting the course id
$cidReset = true;

// name of the language file that needs to be included
$language_file = array ('registration','admin');

// setting the help
$help_content = 'platformadministrationsessionadd';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once(api_get_path(LIBRARY_PATH).'sessionmanager.lib.php');
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';

// Cancel order
if (isset($_GET['action']) && $_GET['action'] == 'cancel_order') {
    SessionManager::clear_catalogue_order_process();
    header('Location: '.api_get_path(WEB_PATH));
    exit;
}

/*$htmlHeadXtra [] = '
<!-- stats -->
    <script type="text/javascript">
    <!--
    xtnv = document; //affiliation frameset : document, parent.document ou top.document
    xtsd = "http://logi3";
    xtsite = "257797";
    xtn2 = "3"; //utiliser le numero du niveau 2 dans lequel vous souhaitez ranger la page
    xtpage = "formations-DF::inscription"; //placer un libell� de page pour les rapports Xiti
    roimt = ""; //valeur du panier pour ROI (uniquement pour les pages d�finies en transformation)
    roitest = false; //� true uniquement si vous souhaitez effectuer des tests avant mise en ligne
    visiteciblee = false; //� true pour les pages qui caract�risent une visite cibl�e
    xtprm = ""; //Param�tres suppl�mentaires (optionnel)
    //-->
    </script>
    <script type="text/javascript" src="http://www.formation-publique.fr/xtroi.js"></script>
    <noscript>
    <img width="1" alt="" height="1" src="http://logi3.xiti.com/hit.xiti?s=257797&s2=3&p=formations-DF::matiere-30065778648&roimt=&roivc=&" >
    </noscript>
<!-- fin stats -->

';*/

$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function() {
    $("#cancell_button_id").click(function() {
        $( "#cancell_message_id" ).dialog({
                resizable: false,
                height:140,
                modal: true,
                buttons: {
                        "'.get_lang('Accept').'": function() {
                                window.location.href = "'.api_get_path(WEB_PATH).api_get_self().'?action=cancel_order";
                        },
                        "'.get_lang('Cancel').'": function() {
                                $(this).hide();
                                $(this).dialog( "destroy" );
                        }
                }
         });
    });

 });
</script>';

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

if (!isset($_SESSION['steps'][5])) {
    $_SESSION['steps'][5] = true;
}

// Database Table Definitions
if (isset($_REQUEST['iden'])) {
    $iden = $_REQUEST['iden'];
    $_SESSION['iden'] =  $iden;
}
if (isset($_REQUEST['wish'])) {
    $wish = $_REQUEST['wish'];
    if($wish == 0){
    $user_id = $_user['user_id'];
    }
    $_SESSION['wish'] =  $wish;
}

//display the header
Display::display_header(get_lang('TrainingCategory'));

if (!isset($_SESSION['user_info']) && !isset($_SESSION['selected_courses'])) {
    $lback = api_get_path(WEB_PATH);
} else {
    $lback = api_get_path(WEB_CODE_PATH).'admin/feedback.php?iden='.$_SESSION['iden'].'&amp;wish='.$_SESSION['wish'].'&amp;id='.$_SESSION['cat_id'].'&amp;prev=4';
}

if (api_get_user_id()) {
    echo '<div class="actions">';
    echo '<a href="'.$lback.'">'.Display::return_icon('pixel.gif', get_lang("Previous"), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Previous').'</a>';
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'payment/session_category_payments"">'.Display::return_icon('pixel.gif', get_lang("Catalogue"), array('class' => 'toolactionplaceholdericon toolactioncatalogue')).get_lang('Catalogue').'</a>';
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'payment/installment_payment.php">'.Display::return_icon('pixel.gif', get_lang("InstallmentPaymentInfo"), array('class' => 'toolactionplaceholdericon toolactionother')).get_lang('InstallmentPaymentInfo').'</a>&nbsp;';
    echo '</div>';
} else {
    echo '<div class="actions">';
    echo '<a href="'.$lback.'">'.Display::return_icon('pixel.gif', get_lang("Previous"), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Previous').'</a>';
    echo '</div>';
}

// start the content div
echo '<div id="content">';
// Steps breadcrumbs
SessionManager::display_steps_breadcrumbs();
echo '<div style="display:none;" id="cancell_message_id">'.get_lang('ConfirmYourChoice').'</div>';
$product = SessionManager::get_session_category($_SESSION['cat_id']);
echo '<div class="row"><div class="form_header register-payment-steps-name"><h2>'.$product['name'].'</h2></div></div>';


echo '<div class="method-payments-icons">';

    if ((isset($_SESSION['user_info']) && isset($_SESSION['selected_courses'])) || api_get_user_id()) {
        echo '<script type="text/javascript">
            function callPayment(pay_type) {
                if (pay_type != 4) {
                   if (pay_type == 2) {
                           window.location.href = "'.api_get_path(WEB_CODE_PATH).'admin/cheque_payment.php?iden='.$_SESSION['iden'].'&wish='.$_SESSION['wish'].'&id='.$_SESSION['cat_id'].'&next=6";
                   } else {
                       //window.location.href = "'.api_get_path(WEB_CODE_PATH).'payment/call_request.php?pay_type="+pay_type+"&id='.$_REQUEST['id'].'";
                       window.location.href = "'.api_get_path(WEB_CODE_PATH).'payment/process_payment_cc.php?pay_type="+pay_type+"&id='.$_REQUEST['id'].'";
                   }

                } else {
                    if(confirm(\''.get_lang('ConfirmYourChoice').'\')) {
                        window.location.href = "'.api_get_path(WEB_PATH).api_get_self().'?action=cancel_order";
                    }
                }
            }
        </script>';

        echo '<div>';
        if (!empty($product['topic'])) {
            $topic = SessionManager::get_topic_info($product['topic']);
            $catalogue = SessionManager::get_catalogue_info($topic['catalogue_id']);
            if (!empty($catalogue['payment'])) {
                $methods = explode(',', $catalogue['payment']);
                if (in_array('Online', $methods)) {
                    echo '<div class="payment-methods">'.Display::return_icon('credit_card.png','', array('onclick'=>'callPayment(1)', 'style'=>'cursor:pointer;')).'<br />'.get_lang('Online').'</div>';//<button name="online" value="Online" onclick="callPayment(1);">'.get_lang('Online').'</button></a>&nbsp;';
                }
                if (!api_get_user_id()) {
                    if (in_array('Cheque', $methods)) {
                        echo '<div class="payment-methods">'.Display::return_icon('cheque.png','', array('onclick'=>'callPayment(2)', 'style'=>'cursor:pointer;')).'<br />'.get_lang('Cheque').'</div>';//'<button name="online" value="Cheque" onclick="callPayment(2);">'.get_lang('Cheque').'</button></a>&nbsp;';
                    }
                }
                if (in_array('Installment', $methods)) {
                    echo '<div class="payment-methods">'.Display::return_icon('installments.png','', array('onclick'=>'callPayment(3)', 'style'=>'cursor:pointer;')).'<br />'.get_lang('TransferIn3Installments').'</div>';//'<button name="cheque" value="Installments" onclick="callPayment(3);">'.get_lang('TransferIn3Installments').'</button>&nbsp;';
                }
            }
        }
        echo '<div class="payment-methods">'.Display::return_icon('cancel.png','', array('id'=>'cancell_button_id', 'style'=>'cursor:pointer;')).'<br />'.get_lang('CancelOrder').'</div>';//'<button name="cheque" value="Cancel" onclick="callPayment(4);">'.get_lang('CancelOrder').'</button>';
        echo '</div>';







    } else {
       echo get_lang('YourSessionOrderIsOver').'&nbsp;<a href="'.api_get_path(WEB_PATH).'">'.get_lang('GoToCatalogue').'</a>';
    }

echo '</div>';

// close the content div
echo '</div>';

// display the footer
Display::display_footer();
?>
