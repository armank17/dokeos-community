<span>
    <h4><?php echo $this->encodingCharset($this->get_lang('FaceToFaceAverageValues')); ?>
    <?php 
        if (!empty($this->txtSearchDefault)) {
            echo ' - '.$this->get_lang('SearchByTitle').' : '.$this->encodingCharset($this->txtSearchDefault);
        }
    ?>
    </h4>
</span>
<div class="data-container">
        <table id="face2faces" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang('ReportInCourse')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('SessionName')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ReportActivityName')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Type')); ?></th>
                    <th><?php echo $this->encodingCharset(ucfirst($this->get_lang('Participants'))); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Status')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ReportScore')); ?></th>
                </tr>
            </thead>
            <tbody>                            
                <?php                            
                    if (!empty($this->face2faceData)):
                        foreach ($this->face2faceData as $data):
                ?>
                        <tr>
                            <td width="22%"><?php echo $this->encodingCharset($data['incourse']); ?></td>
                            <td width="22%" align="center"><?php echo $this->encodingCharset($data['session']); ?></td>
                            <td width="22%"><?php echo $this->encodingCharset($data['name']); ?></td>
                            <td align="center"><?php echo $this->encodingCharset($this->get_lang($data['type'])); ?></td>
                            <td align="center"><?php echo $data['nb_learners']; ?></td>
                            <td align="center"><?php echo $data['pass']; ?></td>
                            <td align="center"><?php echo $data['score']; ?></td>
                        </tr>                                 
                <?php
                        endforeach;
                    else:
                ?>
                      <tr><td colspan="7" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
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