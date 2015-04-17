<?php 
echo '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/functionsAlerts.js"></script>';
?>
<div id="wrapper_glossary_content">
    <div id="glossarylist">
        <div style="height:370px;">
                    <table width="100%" align="left">
                    <tbody><tr><td valign="top" align="left">
                    <div align="center" class="quiz_content_actions" style="margin:0px;width:80%;">A - Z</div>
                    </td></tr>
                    <tr><td>
                     <?php

                     if (count($glossaryList) > 0) {
                         $addPageToUrl = "";
                         if (isset($_GET['page'])) {
                            $addPageToUrl = '&page='.Security::remove_XSS($_GET['page']); 
                         }
                         foreach ($glossaryList as $glossaryinfo) {
                             echo '<a href="'.  api_get_self().'?'.  api_get_cidreq().'&amp;id='.$glossaryinfo['id'].'&amp;action=showterm'.$addPageToUrl.'">'.Display::return_icon('pixel.gif',$glossaryinfo['name'], array('class' => 'actionplaceholdericon actionpreview')).' '.$glossaryinfo['name'].'</a><br/><br/>';
                         }
                     } else {
                         echo get_lang('ThereAreNoDefinitionsHere');
                     }

                     ?>
                    </td></tr>
                    </tbody>
                    </table>
                    <div class="pager" align="center"><?php echo $pagerLinks; ?></div>
        </div>
    </div>
    <div id="whiteboard">
        <div id="wrapper_glossary_form">
            <div id="glossary_form">
                <div align="center" class="title-glossary"><?php echo $glossary_tittle; ?></div>
                <?php 
                    if ($glossary_tittle) : ?>
                        <script>
                            if ($(".title-glossary").length > 0) { 
                                $(".title-glossary").css("margin-top","-50px");
                            }
                        </script>
                <?php endif; ?>
                <div align="center" style="overflow:auto;text-align:left;" class="quiz_content_actions glossary_description_height">
                    <?php echo $glossary_comment; ?>
                </div>
                    <?php if (api_is_allowed_to_edit()) { ?>
                <div align="right" style="border-top:1px solid #cccccc; padding-top:5px; " class="quiz_content_actions">
                    <a href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;id='.$glossary_id.'&amp;action=edit' ?>">
                        <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?>&nbsp;&nbsp;
                    </a>
                    <?php
                    $link = api_get_self().'?'.  api_get_cidreq().'&amp;id='.$glossary_id.'&amp;action=delete';
                    $title = get_lang("ConfirmationDialog");
                    $text = get_lang("ConfirmYourChoice");
                    ?>
                    <a <?php echo 'onclick="Alert_Confim_Delete(\''.$link.'\',\''.$title.'\',\''.$text.'\');"'  ?> href="javascript:void(0);">
                        <?php echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')); ?>
                    </a>
                </div>
                     <?php } ?>
            </div>
                    <div id="glossary_image_map">
                        <a href="<?php echo api_get_self().'?'.  api_get_cidreq(); ?>"><img style="margin:30px 30px 0 0; right:0; top:0;" src="../.././../img/imagemap90.png" class="abs"></a>
                    </div>
        </div>
    </div>
    <div id="glossary_image" style="top:135px;">
            <img  src="../.././../img/instructor_analysis.png" class="abs">
    </div>
</div>