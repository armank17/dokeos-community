<div id="dataDiv">    
    <h4><?php echo $this->quizMode == 'exam'?$this->encodingCharset($this->get_lang("Exam")):$this->encodingCharset($this->get_lang("Quiz")); ?> : <?php echo $this->encodingCharset($this->quizInfo['title']); ?></h4>
    <div class="data-container">
        <table name="list_quiz_learners" id="list_quiz_learners" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang("LastName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("FirstName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("ReportScore")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("ScormTime")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("Attempts")); ?></th>				
                </tr>
            </thead>
            <tbody>
                <?php                            
                    if (!empty($this->quizUsersData)):
                        foreach ($this->quizUsersData as $userId => $data):
                ?>
                        <tr>
                            <td><?php echo $this->encodingCharset($data['lastname']); ?></td>
                            <td><?php echo $this->encodingCharset($data['firstname']); ?></td>
                            <td align="center"><?php echo $data['score']; ?></td>
                            <td align="center"><?php echo $data['time']; ?></td>
                            <td align="center"><?php echo $data['attempts']; ?></td>                           
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