<div class="content" id="resDoc">
    
    <?php 
        if (!empty($this->quizzes)):              
            foreach ($this->quizzes as $quiz) :
    ?>
                <div id="resExercise-<?php echo $quiz['id']; ?>" class="lp_resource_elements">
                    <div class="lp_resource_element">
                        <img title="" style="margin-right:5px;" src="<?php echo api_get_path(WEB_IMG_PATH); ?>quiz_middle.png" alt="">
                        <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=index&'.api_get_cidreq().'&itemType=quiz&itemPath='.$quiz['id'].'&ressource=quiz&lpId='.$this->lpId.$this->extraParams; ?>" class="embed-quiz" title="<?php echo $this->get_lang('ConfirmationEmbedQuiz'); ?>"><?php echo $quiz['title']; ?></a>
                    </div>
                </div>         
    <?php
            endforeach;
        else:
            echo stripcslashes($this->get_lang('NoQuizYet'));
        endif;
    ?>
        
</div>