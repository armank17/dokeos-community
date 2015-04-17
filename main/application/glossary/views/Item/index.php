<link rel="stylesheet" href="/main/application/glossary/assets/css/styles.css" />
<script src="/main/application/glossary/assets/scripts/script.js"></script>
<div id="wrapper_glossary_content">
    <div id="glossarylist">
        <div style="height:370px;">
                <table width="100%" align="left">
                    <tbody><tr><td valign="top" align="left">
                        <div align="center" class="quiz_content_actions" style="margin:0px;width:80%;">A - Z</div>
                    </td></tr>
                    <div id="list-terms">
                        <?php
                            echo $this->list
                        ?>
                    </div>
                    </tbody>
                </table>
            <div class="pager" align="center"><?php echo $this->pagerLinks; ?></div>
        </div>
    </div>
    <div id="whiteboard">
        <div id="wrapper_glossary_form">
            <div id="glossary_form">
                <div align="center" class="quiz_content_actions"><?php echo $this->item->name; ?></div>
                <div align="center" style="overflow:auto;text-align:left;" class="quiz_content_actions glossary_description_height">
                    <?php echo $this->item->description;; ?>
                </div>
                <div id="item-edit">
                    <?php if (api_is_allowed_to_edit()) { ?>
                        <div align="right" style="border:none;" class="quiz_content_actions">
                            <a href="index.php?&module=glossary&cmd=Edit&func=index&<?php echo api_get_cidreq();?>&id=<?php echo $this->item->glossary_id;?>">
                                <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?>&nbsp;&nbsp;
                            </a>
                            <a href="javascript:void(0)" onclick="javascript:deleteItemGlossary('<?php echo $this->item->glossary_id;?>','<?php echo $this->course;?>')">
                                <?php echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')); ?>
                            </a>
                        </div>
                     <?php } ?>
                </div>
            </div>
            <div style="float: left">
                <div class="fila">
                    <a href="index.php?&module=glossary&<?php echo api_get_cidreq(); ?>">
                        <div  class="glossary-map-90"></div>
                    </a>
                </div>
                <div class="fila">
                    <div class="glossary-instructor-item"></div>
                </div>
                
            </div>
        </div>
    </div>
</div>