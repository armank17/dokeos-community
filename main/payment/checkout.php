<?php
$cidReset = true;
$language_file = array('registration', 'admin');

require_once dirname(__FILE__) . '/../inc/global.inc.php';

require_once api_get_path(LIBRARY_PATH) . 'sessionmanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'language.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'usermanager.lib.php';
require_once api_get_path(SYS_PATH) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';

if ($_SESSION['isShoppingCartActive'] != TRUE) {
    header('location : ../../index.php');
}

if (!isset($_SESSION['shopping_cart']['steps'])) {
    $_SESSION['shopping_cart']['steps'] = array();
    $_SESSION['shopping_cart']['steps'][1] = TRUE;
}

if(isset($_GET['prev']) && !empty($_GET['prev']) && $_GET['prev']==1){
    unset($_SESSION['shopping_cart']['steps'][2]);
    unset($_SESSION['shopping_cart']['steps'][3]);
    unset($_SESSION['shopping_cart']['steps'][4]);
}

if(!isset($_GET['prev']) && empty($_GET['prev'])){
    unset($_SESSION['shopping_cart']['steps'][2]);
    unset($_SESSION['shopping_cart']['steps'][3]);
    unset($_SESSION['shopping_cart']['steps'][4]);
}

$objShoppingCartController = new ShoppingCartController();
$stepNumber = 1;
$objShoppingCartController->checkStep($stepNumber, $_SESSION);
if (isset($_GET['op']) && $_GET['op'] == 'delete') {
    $code = Database::escape_string($_GET['code']);
    if (isset($_SESSION['shopping_cart']['items']) && isset($_SESSION['shopping_cart']['items'][$code])) {
        unset($_SESSION['shopping_cart']['items'][$code]);
    }
}
$this_section = SECTION_PLATFORM_ADMIN;
//display the header
Display::display_header(get_lang('TrainingCategory'));
?>
<script type="text/javascript">
    function next_step(netx_url){
        document.location.href=netx_url;
    }
</script>
<div id="content">
    <?php echo $objShoppingCartController->getBreadCrumbs($_SESSION, $_GET); 
    $tax = substr(get_lang('EcommerceTaxPercent'), 0, 3);
    ?>
    <table class="data_table">
        <tr>
            <th><?php echo get_lang(ucfirst($_SESSION['shopping_cart']['item_type'])); ?></th>
            <th width="75px"><?php echo get_lang('CourseAccess'); ?></th>
            <th width="70px"><?php echo get_lang('Price'); ?></th>
            <th width="70px"><?php echo substr(get_lang('Quantity'), 0, 5) . '.'; ?></th>
            <th width="50px"><?php echo $tax; ?></th>
            <th width="70px"><?php echo get_lang('Total'); ?></th>
            <th width="70px"><?php echo get_lang('Total') . ' + ' . $tax; ?></th>
            <th width="55px"><?php echo substr(get_lang('Delete'), 0, 3) . '.'; ?></th>
        </tr>
        <?php
        $index = 1;
        $subTotal = 0;
        $code = $_SESSION['shopping_cart']['currency']['code'];
        $symbol = $_SESSION['shopping_cart']['currency']['symbol'];
        if (empty($_SESSION['shopping_cart']['items'])) {
            echo '<script type="text/javascript">window.location = "' . api_get_path(WEB_PATH) . '";</script>';
        }
        $tax = api_get_setting('e_commerce_catalog_tax');
 
        foreach ($_SESSION['shopping_cart']['items'] as $item){
            
            if (!isset($item['quantity'])) {
                $item['quantity'] = 1;
            }
            
            $price = ($code == 'EUR') ? api_number_format($item['price']) . ' ' . $symbol : $symbol . ' ' . api_number_format($item['price']);
            $price_qty = ($code == 'EUR') ? api_number_format($item['price'] * $item['quantity']) . ' ' . $symbol : $symbol . ' ' . api_number_format($item['price'] * $item['quantity']);
            $price_tax_qty = ($code == 'EUR') ? api_number_format($item['price'] * (1 + ($tax / 100)) * $item['quantity']) . ' ' . $symbol : $symbol . ' ' . api_number_format($item['price'] * (1 + ($tax / 100)) * $item['quantity']);                       
            ?>
            <tr <?php echo (($index % 2) == 0) ? 'class="row_odd"' : 'class="row_even"'; ?>>
                <td><a href="<?php echo $item['url']; ?>"><?php echo $item['type'] == 'course'?api_get_course_visualcode($item['name']):$item['name']; ?></a></td>
                <td style="text-align: center;"><?php echo $item['duration'] . '  ' . ucfirst(($item['duration']<=1)?(get_lang($item['duration_type'])):(get_lang($item['duration_type']))); ?></td>
                <td style="text-align: center;"><?php echo $price; ?></td>
                <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                <td style="text-align: center;"><?php echo $tax; ?> %</td>
                <td style="text-align: center;"><?php echo $price_qty; ?></td>
                <td style="text-align: center;"><?php echo $price_tax_qty; ?></td>
                <td style="text-align: center;"><a href="<?php echo api_get_self() . '?op=delete&code=' . $item['code']; ?>" title="<?php echo get_lang('Remove'); ?>">
                        <?php echo Display::return_icon('pixel.gif', get_lang('Remove'), array('class' => 'actionplaceholdericon actiondelete')); ?></a></td>
            </tr>
            <?php
            $index++;
//            $subTotal += $item['price'];

            $total += $item['price'];
            $total_qty += $item['price'] * $item['quantity'];
            $total_tax += $item['price'] * (1 + ($tax / 100));
            $total_tax_qty += $item['price'] * (1 + ($tax / 100)) * $item['quantity'];
            $tax_amount += $item['price'] * ($tax / 100);
        }
        
        
        
        $_SESSION['shopping_cart']['total_price'] = $total;
        $_SESSION['shopping_cart']['total_price_qty'] = $total_qty;
        $_SESSION['shopping_cart']['total_price_tax'] = $total_tax;
        $_SESSION['shopping_cart']['total_price_tax_qty'] = $total_tax_qty;
        $_SESSION['shopping_cart']['total_tax_amount'] = $tax_amount;
        
        
//        $_SESSION['shopping_cart']['subTotal'] = $total;
//        $_SESSION['shopping_cart']['taxAmount'] = $tax_amount;
//        $_SESSION['shopping_cart']['total'] = $total_tax_qty;
        
        
        if ($code == 'EUR') {
            $total_qty = api_number_format($total_qty) . ' ' . $symbol;
            $total_tax = api_number_format($total_tax) . ' ' . $symbol;
            $total_tax_qty = api_number_format($total_tax_qty) . ' ' . $symbol;
            $tax_amount = api_number_format($tax_amount) . ' ' . $symbol;
        } else {
            $total_qty = $symbol . ' ' . api_number_format($total_qty);
            $total_tax = $symbol . ' ' . api_number_format($total_tax);
            $total_tax_qty = $symbol . ' ' . api_number_format($total_tax_qty);
            $tax_amount = $symbol . ' ' . api_number_format($tax_amount);
        }
        ?>
        <tr style="height: 30px" class="row_odd">
            <td colspan="5">&nbsp;</td>
            <td style="text-align: right;"><b><?php echo get_lang('SubTotal'); ?></b></td>
            <td style="text-align: center;"><?php echo $total_qty; ?></td>
            <td >&nbsp;</td>
        </tr>

        <tr style="height: 30px" class="row_odd">
            <td colspan="5">&nbsp;</td>
            <td style="text-align: right;"><b><?php echo get_lang('Tax'); ?></b></td>
            <td style="text-align: center;"><?php echo $tax_amount; ?></td>
            <td >&nbsp;</td>
        </tr>     
        <tr style="height: 30px" class="row_odd">
            <td colspan="5">&nbsp;</td>
            <td style="text-align: right;"><b><?php echo get_lang('Total'); ?></b></td>
            <td style="text-align: center;"><?php echo $total_tax_qty; ?></td>
            <td >&nbsp;</td>
        </tr>
    </table>

    <div align="right" style="margin: 10px 5px;">
        <!--<a href="<?php // echo api_get_path(WEB_PATH); ?>main/payment/checkout_2_registration.php?id=<?php //echo $_SESSION['cat_id']; ?>&prev=2" class="button add"><span><?php //echo ( (api_get_user_id() != 0) ? get_lang('NextStep') : get_lang('NextStep')) ?></span></a>-->
        <div class="pull-bottom">
        <button onclick="next_step('<?php echo api_get_path(WEB_PATH); ?>main/payment/checkout_2_registration.php?id=<?php echo $_SESSION['cat_id']; ?>&prev=2');return false;" class="button add"><span><?php echo ( (api_get_user_id() != 0) ? get_lang('NextStep') : get_lang('NextStep')) ?></span></button>
        </div>
    </div>

</div>

<?php
echo Display::display_footer();
