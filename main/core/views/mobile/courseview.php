<?php
echo $this->data['head'];
/*echo "<pre>";
print_r($this->data['tool']);
echo"</pre>";*/
?>


<div data-role="content" data-inset="true">
    
<div class="ui-grid-b">
	<div class="ui-block-a">
            <div id="content"> 
                <div id="icons">
                    <img src="<?php echo $this->data['tool'][0]->image; ?>">
                </div>
                <div id="text" class="text_icon">
                <?php echo $this->data['tool'][0]->name_published; ?>
                </div>
            </div>
        </div>
	<div class="ui-block-b">
            <div id="content"> 
                <div id="icons">
                    <img src="<?php echo $this->data['tool'][1]->image; ?>">
                </div>
                <div id="text" class="text_icon">
                <?php echo $this->data['tool'][1]->name_published; ?>
                </div>
            </div>            
        </div>
	<div class="ui-block-c">
            <div id="content"> 
                <div id="icons">
                    <img src="<?php echo $this->data['tool'][2]->image; ?>">
                </div>
                <div id="text" class="text_icon">
                <?php echo $this->data['tool'][2]->name_published; ?>
                </div>
            </div>            
        </div>
</div>   
 <div class="ui-grid-b">
	<div class="ui-block-a">
            <div id="content"> 
                <div id="icons">
                    <img src="<?php echo $this->data['tool'][3]->image; ?>">
                </div>
                <div id="text" class="text_icon">
                <?php echo $this->data['tool'][3]->name_published; ?>
                </div>
            </div>            
        </div>
	<div class="ui-block-b">
            <div id="content"> 
                <div id="icons">
                    <img src="<?php echo $this->data['tool'][4]->image; ?>">
                </div>
                <div id="text" class="text_icon">
                <?php echo $this->data['tool'][4]->name_published; ?>
                </div>
            </div>            
        </div>
	<div class="ui-block-c">
            <div id="content"> 
                <div id="icons">
                    <img src="<?php echo $this->data['tool'][5]->image; ?>">
                </div>
                <div id="text" class="text_icon">
                <?php echo $this->data['tool'][5]->name_published; ?>
                </div>
            </div>            
        </div>
</div> 
 <div class="ui-grid-b">
	<div class="ui-block-a">
            <div id="content"> 
                <div id="icons">
                    <img src="<?php echo $this->data['tool'][6]->image; ?>">
                </div>
                <div id="text" class="text_icon">
                <?php echo $this->data['tool'][6]->name_published; ?>
                </div>
            </div>            
        </div>
	<div class="ui-block-b">
            <div id="content"> 
                <div id="icons">
                    <img src="<?php echo $this->data['tool'][7]->image; ?>">
                </div>
                <div id="text" class="text_icon">
                <?php echo $this->data['tool'][7]->name_published; ?>
                </div>
            </div>            
        </div>
	<div class="ui-block-c"></div>
</div>    
 <!--<div class="ui-grid-b">
	<div class="ui-block-a"><img src="<?php echo $this->data['tool'][0]->image; ?>"><?php echo $this->data['tool'][0]->name_published; ?></div>
	<div class="ui-block-b"><img src="<?php echo $this->data['tool'][1]->image; ?>"><?php echo $this->data['tool'][1]->name_published; ?></div>
	<div class="ui-block-c"><img src="<?php echo $this->data['tool'][2]->image; ?>"><?php echo $this->data['tool'][2]->name_published; ?></div>
</div>   
 <div class="ui-grid-b">
	<div class="ui-block-a"><img src="<?php echo $this->data['tool'][3]->image; ?>"><?php echo $this->data['tool'][3]->name_published; ?></div>
	<div class="ui-block-b"><img src="<?php echo $this->data['tool'][4]->image; ?>"><?php echo $this->data['tool'][4]->name_published; ?></div>
	<div class="ui-block-c"><img src="<?php echo $this->data['tool'][5]->image; ?>"><?php echo $this->data['tool'][5]->name_published; ?></div>
</div> 
 <div class="ui-grid-b">
	<div class="ui-block-a"><img src="<?php echo $this->data['tool'][6]->image; ?>"><?php echo $this->data['tool'][6]->name_published; ?></div>
	<div class="ui-block-b"><img src="<?php echo $this->data['tool'][7]->image; ?>"><?php echo $this->data['tool'][7]->name_published; ?></div>
	<div class="ui-block-c"></div>
</div> -->    

</div> 
<?php
echo $this->data['foot'];
?>