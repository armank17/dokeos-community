<span>
    <h4><?php echo $this->encodingCharset($this->get_lang('SessionsAverageValues')); ?>
    <?php 
        if (!empty($this->txtSearchDefault)) {
            echo ' - '.$this->get_lang('SearchBySessionName').' : '.$this->encodingCharset($this->txtSearchDefault);
        }
    ?>
    </h4>
</span>
<div class="data-container">   
        <table id="courses" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang('SessionName')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Learners')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Courses')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ModulesTime')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ModulesProgress')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ModulesScore')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('QuizzesScore')); ?></th>
                </tr>
            </thead>
            <tbody>                            
                <?php                            
                    if (!empty($this->sessionsData)):
                        foreach ($this->sessionsData as $data):
                ?>
                        <tr>
                            <td><?php echo $this->encodingCharset($data['name']); ?></td>
                            <td align="center"><?php echo $data['total_learners']; ?></td>
                            <td align="center"><?php echo $data['total_courses']; ?></td>
                            <td align="center"><?php echo $data['modules_time']; ?></td>
                            <td align="center"><?php echo $data['modules_progress']; ?></td>
                            <td align="center"><?php echo $data['modules_score']; ?></td>
                            <td align="center"><?php echo $data['quizzes_score']; ?></td>
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