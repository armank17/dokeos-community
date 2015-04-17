<div id="dataDiv">   
    <h4><?php echo $this->encodingCharset($this->courseInfo['name']); ?> <?php echo !empty($this->selectedSessionId)?' - '.$this->get_lang('Session').' : '.$this->sessionInfo['name']:''; ?> - <?php echo $this->encodingCharset($this->quizInfo['title']); ?> - <?php echo $this->encodingCharset($this->learnerInfo['firstname'].' '.$this->learnerInfo['lastname']); ?></h4>    
    <div class="data-container">
        <?php echo $this->encodingCharset($this->quizResultContent); ?>        
        <div id="question_score">
            <?php echo $this->encodingCharset($this->get_lang('YourTotalScore')).' : '.round($this->totalScore).' / '.$this->totalWeighting; ?>
        </div>                
    </div>   
</div>
<script>
    window.onload=function() {
        window.print();
    };
</script>