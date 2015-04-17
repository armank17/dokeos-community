<div id="dataDiv">
    <h4><?php echo $this->encodingCharset($this->get_lang("Activity")); ?> : <?php echo $this->encodingCharset($this->face2faceInfo['name']); ?></h4>    
    <div class="data-container">
        <table id="list_face2face_learners" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang("LastName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("FirstName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("Status")); ?></th>
                    <?php if ($this->face2faceInfo['ff_type'] == 2): ?>
                        <th><?php echo $this->encodingCharset($this->get_lang("ReportScore")); ?></th>
                    <?php else: ?>
                        <th width="35%"><?php echo $this->encodingCharset($this->get_lang("Comments")); ?></th>
                    <?php endif; ?>                    						
                </tr>
            </thead>
            <tbody>
                <?php                            
                    if (!empty($this->face2faceUsersData)):
                        foreach ($this->face2faceUsersData as $data):
                ?>
                        <tr>
                            <td><?php echo $this->encodingCharset($data['lastname']); ?></td>
                            <td><?php echo $this->encodingCharset($data['firstname']); ?></td>
                            <td align="center">                                
                                <?php if (isset($data['passed'])): ?>                                
                                    <?php if ($data['passed'] === true): ?>
                                        <img src="<?php echo api_get_path(WEB_CODE_PATH).'application/reporting/assets/img/checkbox_selected.gif'; ?>" />
                                    <?php else: ?>
                                        <img src="<?php echo api_get_path(WEB_CODE_PATH).'application/reporting/assets/img/checkbox_unchecked.gif'; ?>" />
                                    <?php endif; ?>                                
                                <?php else: ?>                                   
                                    <img src="<?php echo api_get_path(WEB_CODE_PATH).'application/reporting/assets/img/checkbox_normal.gif'; ?>" />                                       
                                <?php endif; ?>                                                                
                            </td>
                            <td align="center">
                                <?php if ($this->face2faceInfo['ff_type'] == 2): ?>
                                    <?php echo $data['score']; ?>
                                <?php else: ?>
                                    <?php echo $this->encodingCharset($data['comment']); ?>
                                <?php endif; ?>
                            </td>
                        </tr>                                 
                <?php
                        endforeach;
                    else:
                ?>
                      <tr><td colspan="6" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
                <?php        
                    endif;
                ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    window.onload=function() {
        window.print();
    };
</script>