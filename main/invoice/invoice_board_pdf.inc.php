<style type="text/Css">
<!--
.product_list
{
    width: 100%;
}
.product_list th
{
    background-color:#dedede; 
    border-left:1px solid #333;
    border-top:1px solid #333;
    padding: 5px;
}
.product_list td
{ 
    border-left:1px solid #333;
    border-top:1px solid #333;
    padding: 5px;
}
.list_lr
{
    border-right: 1px solid #333;
}
.list_lb
{
    border-bottom: 1px solid #333;
}
-->
</style>
<page style="font-size: 14px">
    <table style="width:100%" border="0" cellspdding="0" cellspacing="0">
        <tr>
            <td style="width:100%" colspan="3">
                <img src="<?php echo $image;?>" border=0>
            </td>
        </tr>
        <tr>
            <td style="width:50%; vertical-align: top;" valign="top">
                <h3><?php echo get_lang('Facture','DLTT','french');?></h3>
                <p class="p_text">
                    <?php echo $company_address[0]['value'];?>
                    <br><br>
                    <?php echo $bank[0]['value']?>
                </p>
            </td>
            <td style="width:10%;">
                &nbsp;
            </td>
            <td style="width:40%;">
                <p class="p_text">
                    <b><?php echo 'Num&eacute;ro';?></b> : <?php echo $number; ?></p>
                <p class="p_text">
                    <b><?php echo get_lang('Payment','DLTT','french') . '</b> : ' . get_lang('Comptant','DLTT','french');?><br>
                    <b><?php echo get_lang('Date','DLTT','french') . '</b> : ' . $p_invoice['payment_date'];?><br>
                </p>
                <p class="p_text">
                    <?php echo htmlentities($user_info_extra['organization'], ENT_QUOTES, 'ISO-8859-15', false);?><br>
                    <?php echo $user_info['civility'].' '.htmlentities($user_info['lastname'] . ' ' . $user_info['firstname'], ENT_QUOTES, 'ISO-8859-15', false);?><br>                    
                    <?php 
						if (!empty($user_info_extra['street'])) {
							echo htmlentities($user_info_extra['street'], ENT_QUOTES, 'ISO-8859-15', false).'<br />';
						}
                    ?>
                    <?php 
						if (!empty($user_info_extra['addressline2'])) {
							echo htmlentities($user_info_extra['addressline2'], ENT_QUOTES, 'ISO-8859-15', false).'<br />';
						}
                    ?>
                    <?php echo $p_invoice['address_zip'].' '.$p_invoice['address_city'];?><br />
                    <?php echo $p_invoice['address_country'] . ' ' . $p_invoice['address_country_code'];?><br>
                    <?php echo (!empty($user_info_extra['tva_id'])?get_lang('T.V.A.','DLTT','french') . ' : ' . $user_info_extra['tva_id']:'');?><br><br>
                </p>
            </td>
        </tr>
    </table><br>
    <table bordercolor="#aaa" border="0" class="product_list" cellspdding="0" cellspacing="0">
        <tr>
            <th style="width: 50%;"><?php echo get_lang('Description','DLTT','french');?></th>
            <th style="width: 10%;"><?php echo 'Dur&eacute;e';?></th>
         <?php /*<th style="width: 10%;"><?php echo get_lang('PriceHT','DLTT','french');?></th> */?>
            <th style="width: 10%;"><?php echo get_lang('Quantity','DLTT','french');?></th>
            <th style="width: 10%;"><?php echo get_lang('T.V.A.','DLTT','french');?></th>
            <th style="width: 10%;"><?php echo get_lang('TotalHT','DLTT','french');?></th>
            <th style="width: 10%;padding-top:20px;" class="list_lr" valign="middle"><?php echo get_lang('TotalTTC','DLTT','french');?></th>
        </tr>
        <?php echo $items;?>
        <tr>
            <td colspan="5" align="right"><?php echo get_lang('SubTotal','DLTT','french');?></td>
            <td class="list_lr" align="right"><?php echo api_number_format($total_invoice_qty);?></td>
        </tr>
        <tr>
            <td colspan="5" align="right"><?php echo get_lang('Tax','DLTT','french');?></td>
            <td class="list_lr" align="right"><?php echo api_number_format($p_invoice['tax']);?></td>
        </tr>
        <tr>
            <td colspan="5" align="right"><?php echo get_lang('TOTAL','DLTT','french');?></td>
            <td class="list_lr" align="right"><?php echo api_number_format($total_invoice_tax_qty);?></td>
        </tr>
        <tr>
            <td class="list_lb" colspan="5" align="right"><?php echo ucfirst(strtolower(get_lang('Facture','DLTT','french')));?></td>
            <td class="list_lb list_lr" align="right">Acquitt&eacute;e</td>
        </tr>
    </table><br>
    <table style="width:100%" border="0" cellspdding="0" cellspacing="0">
        <tr>
            <td style="width: 100%"><?php echo api_utf8_encode($aditionalInfo[0]['value']); ?></td>
        </tr>
    </table>
</page>
