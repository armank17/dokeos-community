<!--div id="upgrade-title"><?php echo $this->get_lang('ContactUsToUpgrade'); ?></div-->
<h4><span id="upgrade-title"><?php echo $this->get_lang('ContactUsToUpgrade'); ?></span></h4>
<form action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=suiteManager&cmd=PricingAjax&func=updateAccount'; ?>" method="POST" id="pricing-account">
    <input type="hidden" name="userId" value="<?php echo $this->currentUserInfo['user_id']; ?>" />    
    <table width="100%" id="tbl_profile" border="0"> 
        <tr>
            <td width="80px" align="right"><strong><?php echo $this->get_lang('FullName'); ?>:</strong></td>
            <td><input type="text" name="fullname" value="<?php echo $this->currentUserInfo['extra']['fullname']; ?>" class="required" /></td>
            <td width="80px" align="right"><strong><?php echo $this->get_lang('Phone'); ?>:</strong></td>
            <td><input type="text" name="phone" value="<?php echo $this->currentUserInfo['phone']; ?>" class="required" /></td>
            <td width="80px">&nbsp;</td>
        </tr>
        <tr>
            <td align="right"><strong><?php echo $this->get_lang('Email'); ?>:</strong></td>
            <td><input type="text" name="email" value="<?php echo $this->currentUserInfo['mail']; ?>" class="required email" /></td>
            <td align="right"><strong><?php echo $this->get_lang('Address'); ?>:</strong> </td>
            <td><input type="text" name="address" value="<?php echo $this->currentUserInfo['extra']['address']; ?>" class="required" /></td>
            <td width="80px">&nbsp;</td>
        </tr>
        <tr>
            <td align="right"><strong><?php echo $this->get_lang('Company'); ?>:</strong></td>
            <td><input type="text" name="company" value="<?php echo $this->currentUserInfo['extra']['company']; ?>" class="required" /></td>
            <td align="right"><strong><?php echo $this->get_lang('Country'); ?>:</strong></td>
            <td><input type="text" name="country" value="<?php echo $this->currentUserInfo['extra']['country']; ?>"  class="required" /></td>            
            <td width="80px">&nbsp;</td>
        </tr>
        <tr>
            <td align="right"><strong><?php echo $this->get_lang('Subject'); ?>:</strong></td>
            <td colspan="3"><input type="text" name="subject" class="required" style="width: 94%;"/></td>
            <td width="80px">&nbsp;</td>
        </tr>
        <tr>
            <td align="right" valign="top"><strong><?php echo $this->get_lang('YourMessage'); ?>:</strong></td>
            <td colspan="3"><textarea style="width: 94%; height:120px;" name="message" class="required"></textarea></td>
            <td width="80px">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3"><div id="install-loading"></div></td>
            <td align="right"><button type="submit" class="button-blue"><?php echo $this->get_lang('Send'); ?></button></td>
            <td width="80px">&nbsp;</td>
        </tr>
    </table>    
</form>
<input type="hidden" name="webPath" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
<!--
<h4><?php echo $this->get_lang('YourDemand'); ?></h4>
<table width="100%" id="tbl_products_header"> 
    <tbody>
            <tr>
                <th width="50%">
                    <span class="pricing-table-title"><?php echo $this->get_lang('ChooseAProduct'); ?></span>
                    <span class="pricing-table-subtitle">(<?php echo $this->get_lang('ClickOnTheCellToSelectProduct'); ?>)</span>
                </th>
                <th width="30%">
                    <span class="pricing-table-title"><?php echo $this->get_lang('ChooseAPlan'); ?></span>            
                </th>
                <th>
                    <span class="pricing-table-title"><?php echo $this->get_lang('YourBestMonthlyPrice'); ?></span>            
                </th>
            </tr>
    </tbody>
</table>
<?php if (!empty($this->dokeosSuite)): ?>
    <form id="pricing-form" method="POST">
    <ul id="pricing_product_list" class="ui-selectable">
        <?php 
            $i = 0;
            foreach ($this->dokeosSuite as $variable => $suite):         
        ?>
            <li id="product-<?php echo $variable; ?>" class="<?php echo $i % 2 == 0?'li-odd':'li-even'; ?> pricing-items  ui-selectee">
                <input type="hidden" class="hdn-attributes" name="pricing[<?php echo $variable; ?>][attribute]" value="<?php echo $suite['default_attribute']; ?>" id="hdn-attribute-<?php echo $variable; ?>" disabled="true" />
                <input type="hidden" class="hdn-suites" name="pricing[<?php echo $variable; ?>][suite]" value="<?php echo $variable; ?>" disabled="true" />
                <input type="hidden" class="hdn-prices" name="pricing[<?php echo $variable; ?>][price]" value="<?php echo $suite['default_price']; ?>" id="hdn-price-<?php echo $variable; ?>" disabled="true" />
                <table width="100%">
                    <tbody>
                        <tr class=" product_list_1 first_item num-<?php echo $suite['id']; ?>">
                            <td width="50%">            
                                <div class="product_img_link">
                                   <img src="<?php echo $suite['image_path']; ?>" />
                                </div>  
                                <div class="product_short">
                                    <h3><?php echo strtoupper($suite['name']); ?></h3>
                                    <p class="product_desc"><?php echo $suite['short_description']; ?></p>                                
                                </div>
                                <div class="product_description">
                                    <?php echo $suite['large_description']; ?>                              
                                </div>
                                <div class="product-row">                              
                                    <a href="<?php echo $suite['more_info_link']; ?>"><?php echo $this->get_lang('MoreInfo'); ?></a>
                                </div>                            
                            </td>
                            <td width="30%">                                          
                                <?php if (!empty($suite['attributes'])): ?>
                                    <div class="attributes">                                    
                                        <label class="attribute_label"><?php echo $suite['attributes']['name']; ?></label>
                                        <div class="attribute_list">
                                            <select class="attributes_select" id="cbo-<?php echo $variable; ?>">
                                                <?php foreach ($suite['attributes']['values'] as $opt => $value): ?>
                                                    <option value="<?php echo $value; ?>"><?php echo $opt; ?></option>
                                                <?php endforeach; ?>                                                
                                            </select>
                                            <br>
                                            <p class="attribute_label"><?php echo $this->get_lang('ContactUs'); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td align="center">
                                <div class="right_block">
                                    <span id="price_<?php echo $variable; ?>" class="price"><?php echo $suite['default_price']; ?> &euro;</span><br> 
                                    <span class="quota">/month</span>                                                                	 
                                    <div class="clear noneclass"></div>                                    
                                </div>            
                            </td>
                        </tr>                                     
                    </tbody>
                </table>
            </li>
        <?php 
            $i++;
            endforeach; 
        ?>
    </ul>    
    <table width="100%" id="tbl_products_footer">
        <tr>
            <th width="50%">&nbsp;</th>
            <th width="30%" align="right" valign="middle"><?php echo $this->get_lang('Total'); ?></th>
            <th align="center" valign="middle"><span id="total-price">0</span> &euro;</th>
        </tr>
        <tr>
            <td width="50%">&nbsp;</td>
            <td width="30%" align="right" valign="middle">&nbsp;</td>
            <td align="center" valign="middle"><button class="save" id="pricing-button" type="button" style="float:none;"><?php echo $this->get_lang('Send'); ?></button></td>
        </tr>
    </table>
    </form>

<?php else: ?>
    <div id="no-suite"><?php echo $this->get_lang('NoProductsAvailable'); ?></div>
<?php endif; ?>
-->
