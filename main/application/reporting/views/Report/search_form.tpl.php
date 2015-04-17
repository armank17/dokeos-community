<?php if (!in_array($this->currentTab, array('quiz_users', 'module_users', 'facetoface_users'))): ?>
<div id="query-breadcrumb">
    <em>
    <?php 
        if (!empty($this->queryFilters)) 
        {
            echo $this->encodingCharset($this->get_lang('YourQuery').' : '.implode(' > ', $this->queryFilters));
        }  
    ?>
    </em>
</div>
<?php endif; ?>
<form id="search-filter-form" name="search_filter_form">
    <input type="hidden" name="currentTab" id="current-tab" value="<?php echo $this->currentTab; ?>" />    
    <?php if (!in_array($this->currentTab, array('quiz_users', 'module_users', 'facetoface_users'))): ?>
        <div class="span12 pull-right text-align-right reporting-search-filters">
            <input id="filter-search" name="filter_search" type="text" class="input-medium search-query" placeholder="<?php echo $this->encodingCharset($this->searchPlaceHolder); ?>" value="<?php echo $this->encodingCharset($this->txtSearchDefault); ?>">
            <?php if(api_is_allowed_to_edit()): ?>
                 <select id="trainer" name="cbo_trainer" class="cbo-filters">
                    <option value="0"><?php echo $this->encodingCharset($this->get_lang('SelectTrainer')); ?></option>
                    <?php foreach ($this->trainersCbo as $trainer): ?>
                        <option value="<?php echo $trainer['user_id']; ?>" <?php echo $this->selectedTrainerFilter == $trainer['user_id']?' selected="selected"':''; ?> >
                            <?php echo $this->encodingCharset($trainer['lastname'] . ' ' . $trainer['firstname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <?php if (!empty($this->categoriesCbo)): ?>
                <select id="category" name="cbo_category" class="cbo-filters">
                    <option value="0"><?php echo $this->encodingCharset($this->get_lang('SelectSessionsCategory')); ?></option>
                    <?php foreach ($this->categoriesCbo as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $this->selectedCategoryFilter == $category['id']?' selected="selected"':''; ?> >
                            <?php echo $this->encodingCharset($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <select class="cbo-filters-disabled" disabled><option><?php echo $this->encodingCharset($this->get_lang('NoCategories')); ?></option></select>
            <?php endif; ?>
            
            <?php if (!empty($this->sessionsCbo)): ?>
                <select id="session" name="cbo_session" class="cbo-filters">
                    <option value="0"><?php echo $this->encodingCharset($this->get_lang('SelectSession')); ?></option>
                    <?php foreach ($this->sessionsCbo as $session): ?>
                        <option value="<?php echo $session['id']; ?>" <?php echo $this->selectedSessionFilter == $session['id']?' selected="selected"':''; ?> >
                            <?php echo $this->encodingCharset($session['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <select class="cbo-filters-disabled" disabled><option><?php echo $this->encodingCharset($this->get_lang('NoSessions')); ?></option></select>
            <?php endif; ?>

            <?php if (!in_array($this->currentTab, array('sessions'))): ?>
                <?php if (!empty($this->coursesCbo)): ?>
                    <select id="course" name="cbo_course" class="cbo-filters">
                        <option value="0"><?php echo $this->encodingCharset($this->get_lang('SelectCourse')); ?></option>
                        <?php foreach ($this->coursesCbo as $course): ?>
                            <option value="<?php echo $course['code']; ?>" <?php echo $this->selectedCourseFilter == $course['code']?' selected="selected"':''; ?> >
                                <?php echo $this->encodingCharset($course['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <select class="cbo-filters-disabled" disabled><option><?php echo $this->encodingCharset($this->get_lang('NoCourses')); ?></option></select>
                <?php endif; ?>
            <?php endif; ?>
                
            <!-- Extra comboBoxes for quizzes tab -->
            <?php if ($this->currentTab == 'quizzes'): ?>
                <?php if (!empty($this->quizzesCbo)): ?>
                    <select id="quiz" name="cbo_quiz" class="cbo-filters">
                        <option value="0"><?php echo $this->encodingCharset($this->get_lang('SelectQuiz')); ?></option>
                        <?php var_Dump($this->selectedQuizFilter); foreach ($this->quizzesCbo as $quiz): ?>
                            <option value="<?php echo $quiz['course_code']; ?>-<?php echo $quiz['mode']; ?>-<?php echo $quiz['id']; ?>" <?php echo $this->selectedQuizFilter == ($quiz['course_code'].'-'.$quiz['mode'].'-'.$quiz['id'])?' selected="selected"':''; ?> >
                                <?php echo $this->encodingCharset($quiz['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="quiz-type" name="cbo_quiz_type" class="cbo-filters">
                        <option value="0"><?php echo $this->encodingCharset($this->get_lang('SelectType')); ?></option>
                        <option value="1" <?php echo $this->selectedQuizTypeFilter == 1?' selected="selected"':''; ?> >
                            <?php echo $this->encodingCharset($this->get_lang('SelfLearning')); ?>
                        </option>
                        <option value="2" <?php echo $this->selectedQuizTypeFilter == 2?' selected="selected"':''; ?> >
                            <?php echo $this->encodingCharset($this->get_lang('Evaluation').' ('.strtolower($this->get_lang('Exams').')')); ?>
                        </option>
                    </select>
                <?php else: ?>
                    <select class="cbo-filters-disabled" disabled><option><?php echo $this->encodingCharset($this->get_lang('NoQuizzes')); ?></option></select>
                    <select class="cbo-filters-disabled" disabled><option><?php echo $this->encodingCharset($this->get_lang('NoQuizzesType')); ?></option></select>
                <?php endif; ?>                
            <?php endif; ?>
            <!-- End quizzes comboboxes -->

            <!-- Extra comboBoxes for learners tab -->
            <?php if ($this->currentTab == 'learners'): ?>
                <select id="active-learner" name="cbo_active_learner" class="cbo-filters">
                    <option value="0"><?php echo api_utf8_encode($this->get_lang('SelectStatus')); ?></option>
                    <option value="1" <?php echo $this->selectedActiveLearnerFilter == 1?' selected="selected"':''; ?> >
                        <?php echo $this->encodingCharset($this->get_lang('ActiveLearners')); ?>
                    </option>
                    <option value="2" <?php echo $this->selectedActiveLearnerFilter == 2?' selected="selected"':''; ?> >
                        <?php echo $this->encodingCharset($this->get_lang('InActiveLearners')); ?>
                    </option>
                </select>
                <select id="quiz-ranking" name="cbo_quiz_ranking" class="cbo-filters">
                    <option value="0"><?php echo $this->get_lang('QuizzesRanking'); ?></option>
                    <?php foreach ($this->rankingValues as $ranking): ?>
                        <option value="<?php echo $ranking; ?>" <?php echo $this->selectedQuizRankingFilter == $ranking?' selected="selected"':''; ?>><?php echo $ranking.'%'; ?></option>
                    <?php endforeach; ?>                
                </select>
            <?php endif; ?>
            <!-- End learners comboboxes -->

            <!--button id="filter_submit" type="button" name="filter" class="btn"><?php echo $this->encodingCharset($this->get_lang("Filter")); ?></button-->
            <button id="btn-search" type="button" class="btn save"><?php echo $this->encodingCharset($this->get_lang('Search')); ?></button>
            <?php if (!empty($this->queryFilters) || !empty($this->txtSearchDefault)): ?>
                <a href="#" id="filter_reset"><?php echo $this->encodingCharset($this->get_lang("Reset")); ?></a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="span12 pull-right text-align-right reporting-search-form"> 
            <input id="filter-search" style="width: 250px;" name="filter_search" type="text" class="input-medium search-query" placeholder="<?php echo $this->encodingCharset($this->searchPlaceHolder); ?>" value="<?php echo $this->encodingCharset($this->txtSearchDefault); ?>">
            <button id="btn-search" type="button" class="btn save"><?php echo $this->encodingCharset($this->get_lang('Search')); ?></button>
            <?php if ($this->currentTab == 'quiz_users'): ?>
                <input type="hidden" name="courseCode" value="<?php echo $this->selectedQuizCourse; ?>" />
                <input type="hidden" name="mode" value="<?php echo $this->selectedQuizMode; ?>" />
                <input type="hidden" name="id" value="<?php echo $this->selectedQuizId; ?>" />
            <?php elseif ($this->currentTab == 'facetoface_users'): ?>
                <input type="hidden" name="courseCode" value="<?php echo $this->selectedFace2FaceCourse; ?>" />
                <input type="hidden" name="type" value="<?php echo $this->selectedFace2FaceType; ?>" />
                <input type="hidden" name="face2faceId" value="<?php echo $this->selectedFace2FaceId; ?>" />
            <?php elseif ($this->currentTab == 'module_users'): ?>
                <input type="hidden" name="courseCode" value="<?php echo $this->selectedModuleCourse; ?>" />                
                <input type="hidden" name="lpId" value="<?php echo $this->selectedModuleId; ?>" />
            <?php endif; ?>
        </div>
    <?php endif; ?>
</form>
<input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
<script>
if ($(".cbo-filters").length > 0) {
    $(".cbo-filters").change(function() {
        ReportingModel.changeCboFilters($(this));
    });
}
if ($("#filter_reset").length) {
    $("#filter_reset").click(function(e) {
        e.preventDefault();
        ReportingModel.resetFilter();
    });
}
if ($("#search-filter-form").length) {
    $("#search-filter-form").submit(function(e) {
       e.preventDefault();
       return false;
    });        
    $("#filter-search").keypress(function(e) {
        if(e.which == 13) {
           $("#btn-search").click();
        }
    });
    $("#btn-search").click(function(e) {
        e.preventDefault();
        ReportingModel.submitSearch($("#search-filter-form"));
    });                          
    $("#filter_submit").click(function() {
        ReportingModel.submitFilter($("#search-filter-form"));
    });
    if ($(".paginate").length) {
        $(".paginate").click(function(e){
            ReportingModel.paginateItems(e, $(this), $("#search-filter-form"));
        });
    }
}
</script>
<div id="closeText" style="display: none;" class="<?php echo get_lang('Close') ?>"></div>