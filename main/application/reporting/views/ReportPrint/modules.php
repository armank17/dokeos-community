<span>
    <h4><?php echo $this->get_lang('ModuleAverageValues'); ?>
    <?php 
        if (!empty($this->txtSearchDefault)) {
            echo ' - '.$this->get_lang('SearchByModuleName').' : '.$this->encodingCharset($this->txtSearchDefault);
        }
    ?>
    </h4>
</span>
<div class="data-container">
        <table id="courses" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang('Modules')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ReportInCourse')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ScormTime')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Progress')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ReportScore')); ?></th>
                </tr>
            </thead>
            <tbody>                            
                <?php                            
                    if (!empty($this->modulesData)):
                        foreach ($this->modulesData as $data):
                ?>
                        <tr>
                            <td width="25%"><?php echo $this->encodingCharset($data['name']); ?></td>
                            <td width="20%" align="center"><?php echo $this->encodingCharset($data['incourse']); ?></td>
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
<script>
    window.onload=function() {
        window.print();
    };
</script>