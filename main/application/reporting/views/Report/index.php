<div class="row-fluid">
    <div class="span12">      
        <div class="row-fluid">
            <div class="responsive-tabs">
                <!-- Session Tab -->
                <h3 id="sessionhead"><?php echo $this->get_lang('Sessions'); ?></h3>
                <div>	
                    <?php echo $this->setTemplate('sessions_tab', 'Report'); ?>
                </div>
                
                <!-- Courses Tab -->
                <h3 id="courseshead"><?php echo $this->get_lang('Courses'); ?></h3>
                <div>	
                    <?php echo $this->setTemplate('courses_tab', 'Report'); ?>
                </div>
                
                <!-- Modules Tab -->
                <h3 id="moduleshead"><?php echo $this->get_lang('Modules'); ?></h3>
                <div></div>
                
                <!-- Quizzes Tab -->
                <h3 id="quizhead"><?php echo $this->get_lang('Quizzes'); ?></h3>
                <div></div>
                
                <!-- Face to face tab -->                
                <h3 id="facetofacehead"><?php echo get_lang('Facetoface'); ?></h3>
                <div></div>         
                
                <!-- Learners Tab -->
                <h3 id="learnershead"><?php echo get_lang('Learners'); ?></h3>
                <div></div>
            </div>
        </div>        
    </div>
</div>
<input type="hidden" name="webPath" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />