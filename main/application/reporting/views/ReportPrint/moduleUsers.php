<div id="dataDiv">    
    <h4><?php echo $this->encodingCharset($this->get_lang("Module")); ?> : <?php echo $this->encodingCharset($this->moduleInfo['name']); ?></h4>    
    <div class="data-container">
        <table id="list_module_learners" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang("LastName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("FirstName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("ScormTime")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("Progress")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("ReportScore")); ?></th>					
                </tr>
            </thead>
            <tbody>
                <?php                            
                    if (!empty($this->moduleUsersData)):
                        foreach ($this->moduleUsersData as $userId => $data):
                ?>
                        <tr>
                            <td><?php echo $this->encodingCharset($data['lastname']); ?></td>
                            <td><?php echo $this->encodingCharset($data['firstname']); ?></td>
                            <td align="center"><?php echo $data['time']; ?></td>
                            <td align="center"><?php echo $data['progress']; ?></td>
                            <td align="center"><?php echo $data['score']; ?></td>                           
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
