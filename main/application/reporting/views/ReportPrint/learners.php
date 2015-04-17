<span>
    <h4><?php echo $this->get_lang('LearnersAverageValues'); ?>
    <?php 
        if (!empty($this->txtSearchDefault)) {
            echo ' - '.$this->get_lang('SearchByLearnerFirtnameOrLastname').' : '.$this->encodingCharset($this->txtSearchDefault);
        }
    ?>
    </h4>
</span>
<div class="data-container">
        <table name="learners" id="courses" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang('LastName')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('FirstName')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('LatestConnection')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ModulesTime')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ModulesProgress')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ModulesScore')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('QuizzesScore')); ?></th>                    
                </tr>
            </thead>
            <tbody>                            
                <?php                            
                    if (!empty($this->learnersData)):
                        foreach ($this->learnersData as $data):
                ?>
                        <tr>
                            <td align="center"><?php echo $this->encodingCharset($data['lastname']); ?></td>
                            <td align="center"><?php echo $this->encodingCharset($data['firstname']); ?></td>
                            <td align="center"><?php echo $this->encodingCharset($data['lastest_connection']); ?></td>
                            <td align="center"><?php echo $data['modules_time']; ?></td>
                            <td align="center"><?php echo $data['modules_progress']; ?></td>
                            <td align="center"><?php echo $data['modules_score']; ?></td>
                            <td align="center"><?php echo $data['quizzes_score']; ?></td>                            
                        </tr>                                 
                <?php
                        endforeach;
                    else:
                ?>
                      <tr><td colspan="8" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
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