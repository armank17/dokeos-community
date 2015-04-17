<div id="dataDiv">      
    <p class="individual-user-fullname"><?php echo $this->encodingCharset($this->learnerInfo['firstname'].' '.strtoupper($this->learnerInfo['lastname'])); ?></p>
    
    <!-- User information block -->        
    <div id="user-info-block">        
        <table class="responsive large-only table-striped" border="0" id="individual-user-info-table">
            <tbody>
                <tr>
                    <td valign="top" width="5%">
                        <div class="indivual-user-picture">
                            <?php if (file_exists($this->learnerInfo['picture_info']['syspath'])): ?>
                                <img src="<?php echo $this->learnerInfo['picture_info']['webpath']; ?>" <?php echo $this->learnerInfo['picture_info']['attributes']['height'] > 200?' width="200px"':''; ?> />
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="background-color:#FFF;">			
                        <table class="responsive large-only table-striped" border="0">                            
                            <tr><td align="right"><?php echo $this->encodingCharset($this->get_lang("Email")); ?> :</td><td><?php echo $this->learnerInfo['mail']; ?></td></tr>
                            <tr><td align="right"><?php echo $this->encodingCharset($this->get_lang("FirstConnection")); ?> :</td><td><?php echo $this->encodingCharset($this->learnerGlobalData['first_connection']); ?></td></tr>
                            <tr><td align="right"><?php echo $this->encodingCharset($this->get_lang("LatestConnection")); ?> :</td><td><?php echo $this->encodingCharset($this->learnerGlobalData['last_connection']); ?></td></tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>    
    </div>        
    <!-- End user information block -->
    
    <!-- Courses and sessions details block -->        
    <?php if (!empty($this->learnerSessionCoursesDetailData)): ?>
        <div id="individual-reporting-accordion">
    <?php
            foreach ($this->learnerSessionCoursesDetailData as $sid => $sessionCourseDetail):
                foreach ($sessionCourseDetail as  $sessionCourse):               
    ?>
                    <?php if ($sid == 0): ?>
                        <h3 class="ui-widget-header">
                            <p>
                                <span class="reporting-course-title"><?php echo $this->encodingCharset($sessionCourse['course_name']); ?></span><br />
                                <span class="reporting-course-subtitle"><?php echo $this->encodingCharset($this->get_lang('Teachers')).' : '.$this->encodingCharset($sessionCourse['trainer_name']);?></span>
                            </p>
                        </h3>                            
                    <?php endif; ?>

                    <?php if (!empty($sessionCourse['session_name'])): ?>
                        <h3 class="ui-widget-header">
                            <p>
                                <span class="reporting-course-title"><?php echo $this->encodingCharset($this->get_lang('Session')).' : '. $this->encodingCharset($sessionCourse['session_name']); ?> -                                     
                                <?php echo $this->encodingCharset($sessionCourse['course_name']); ?></span><br />
                                <span class="reporting-course-subtitle"><?php echo $this->encodingCharset($this->get_lang('Tutors')).' : '.$this->encodingCharset($sessionCourse['tutor_name']);?></span>                            </p>
                        </h3>
                    <?php endif; ?>
                    <div>
                        <!-- Scenario block -->
                        <h4><?php echo $this->encodingCharset($this->get_lang('ScenarioOverview')); ?></h4>
                        <div class="learner-detail-content-block learner-detail-content-scenario">                            
                            <?php 
                                $scenario = $sessionCourse['scenario'];
                                if (!empty($scenario['data'])): ?>
                                    <table border="1" class="table_scenario" cellpadding="3">                                                                                 
                                        <?php for ($x = 0; $x < $scenario['rows']; $x++): ?>                                            
                                            <tr>
                                                <?php 
                                                    for ($y = 0; $y < $scenario['colums']; $y++): 
                                                        $data = $scenario['data'][$y][$x];                                                   
                                                ?>                                                
                                                    <?php if ($x == 0): ?>
                                                        <th>
                                                            <?php echo cut($this->encodingCharset($data['step_name']), 20, true); ?>
                                                        </th>
                                                    <?php else: ?>
                                                        <td align="center" class="<?php echo $data['td_class']; ?>">
                                                            <p><?php echo cut($this->encodingCharset($data['activity_name']), 20, true); ?></p>
                                                            
                                                            <?php if ($data['activity_type'] == 'module'): ?>
                                                                <p><?php echo $this->encodingCharset($this->get_lang('Progress')); ?> : <?php echo $data['progress']; ?></p>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($data['activity_type'] == 'face2face'): ?>
                                                                <p><?php echo $this->encodingCharset($this->get_lang('Status')); ?> : <?php echo $data['ff_passed']; ?></p>
                                                            <?php endif; ?>    
                                                                
                                                            <?php if (isset($data['score']) && in_array($data['activity_type'], array('quiz', 'exam', 'module', 'face2face', 'assignment')) && !isset($data['comment'])): ?>
                                                                <p><?php echo $this->encodingCharset($this->get_lang('ReportScore')); ?> : <?php echo $data['score']; ?></p>
                                                            <?php endif; ?>
                                                            
                                                            <?php if (isset($data['comment']) && $data['activity_type'] == 'face2face'): ?>
                                                                <p><?php echo $this->encodingCharset($this->get_lang('Comment')); ?> : <?php echo cut($data['comment'], 15, true); ?></p>
                                                            <?php endif; ?>        
                                                                
                                                        </td>
                                                    <?php endif; ?>                                                                                                    
                                                <?php endfor; ?>
                                            </tr>    
                                        <?php endfor; ?>                                        
                                    </table>
                            <?php else: ?>
                               <center><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></center>
                            <?php endif; ?>                                                      
                        </div>
                        <!-- end scenario block -->
                        
                        <!-- Modules block -->
                        <h4><?php echo $this->encodingCharset($this->get_lang('ModulesOverview')); ?></h4>
                        <div class="learner-detail-content-block">
                             <table id="module-user" class="responsive large-only table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th width="45%"><?php echo $this->encodingCharset($this->get_lang('ScormLessonTitle')); ?></th>
                                        <th><?php echo $this->encodingCharset($this->get_lang('ScormTime')); ?></th>
                                        <th><?php echo $this->encodingCharset($this->get_lang('ScormProgress')); ?></th>
                                        <th><?php echo $this->encodingCharset($this->get_lang('ReportScore')); ?></th>                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        if (!empty($sessionCourse['modules'])): 
                                            foreach ($sessionCourse['modules'] as $module):
                                    ?>
                                                <tr>
                                                    <td><?php echo cut($this->encodingCharset($module['name']), 30, true); ?></td>
                                                    <td align="center"><?php echo $module['time']; ?></td>
                                                    <td align="center"><?php echo $module['progress']; ?></td>
                                                    <td align="center"><?php echo $module['score']; ?></td>                                                    
                                                </tr> 
                                    <?php
                                            endforeach;
                                        else: 
                                    ?>
                                        <tr><td colspan="5" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- end modules block -->
                        
                        <!-- Quizzes block -->
                        <h4><?php echo $this->encodingCharset($this->get_lang('QuizzesOverview')); ?></h4>
                        <div class="learner-detail-content-block">
                            <table id="quiz-user" class="responsive large-only table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th width="45%"><?php echo $this->encodingCharset($this->get_lang('StandaloneQuiz')); ?></th>
                                        <th><?php echo $this->encodingCharset($this->get_lang('Type')); ?></th>
                                        <th><?php echo $this->encodingCharset($this->get_lang('Attempts')); ?></th>
                                        <th><?php echo $this->encodingCharset($this->get_lang('ReportScore')); ?></th>
                                        <th><?php echo $this->encodingCharset($this->get_lang('ScormTime')); ?></th>                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        if (!empty($sessionCourse['quizzes'])): 
                                            foreach ($sessionCourse['quizzes'] as $quiz):
                                    ?>
                                                <tr>
                                                    <td><?php echo cut($this->encodingCharset($quiz['name']), 30, true); ?></td>
                                                    <td align="center"><?php echo $quiz['mode'] == 'quiz'?'quiz':'exam'; ?></td>
                                                    <td align="center"><?php echo $quiz['attempts']; ?></td> 
                                                    <td align="center"><?php echo $quiz['score']; ?></td>
                                                    <td align="center"><?php echo $quiz['time']; ?></td>                                                    
                                                </tr> 
                                    <?php
                                            endforeach;
                                        else: 
                                    ?>
                                        <tr><td colspan="6" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- end quizzes block -->
                        
                        <!-- Face2face block -->
                        <h4><?php echo $this->encodingCharset($this->get_lang('Face2faceOverview')); ?></h4>
                        <div class="learner-detail-content-block">
                            <table id="face2face-user" class="responsive large-only table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th width="45%"><?php echo $this->encodingCharset($this->get_lang('ReportActivityName')); ?></th>
                                        <th><?php echo $this->encodingCharset($this->get_lang('Type')); ?></th>
                                        <th><?php echo $this->encodingCharset($this->get_lang('Status')); ?></th>
                                        <th><?php echo $this->encodingCharset($this->get_lang('Comments')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        if (!empty($sessionCourse['face2face'])): 
                                            foreach ($sessionCourse['face2face'] as $x => $face2face):
                                                if ($x == 0) { continue; }
                                    ?>
                                                <tr>
                                                    <td><?php echo cut($this->encodingCharset($face2face['name']), 45, true); ?></td>
                                                    <td align="center"><?php echo $this->encodingCharset($this->get_lang($face2face['type'])); ?></td>
                                                    <td align="center">
                                                        <?php if (isset($face2face['passed'])): ?>                                
                                                            <?php if ($face2face['passed'] === true): ?>
                                                                <img src="<?php echo api_get_path(WEB_CODE_PATH).'application/reporting/assets/img/checkbox_selected.gif'; ?>" />
                                                            <?php else: ?>
                                                                <img src="<?php echo api_get_path(WEB_CODE_PATH).'application/reporting/assets/img/checkbox_unchecked.gif'; ?>" />
                                                            <?php endif; ?>                                
                                                        <?php else: ?>                                   
                                                            <img src="<?php echo api_get_path(WEB_CODE_PATH).'application/reporting/assets/img/checkbox_normal.gif'; ?>" />                                       
                                                        <?php endif; ?>      
                                                    </td> 
                                                    <td align="center">
                                                        <?php 
                                                            if ($face2face['comment'] !== FALSE) {
                                                                echo cut($this->encodingCharset($face2face['comment']), 45, true); 
                                                            }
                                                            else {
                                                                echo '/';
                                                            }                                                            
                                                        ?>
                                                    </td>                                                    
                                                </tr>
                                    <?php
                                            endforeach;
                                    ?>
                                                <tr>
                                                    <th><?php echo $this->encodingCharset($this->get_lang('Total')); ?></th>
                                                    <th>&nbsp;</th>
                                                    <th width="72px"><?php echo isset($sessionCourse['face2face'][0]['total_passed'])?$sessionCourse['face2face'][0]['total_passed'].' %':'n.a.'; ?></th>
                                                    <th>&nbsp;</th>
                                                </tr>
                                    <?php                                            
                                        else: 
                                    ?>
                                        <tr><td colspan="4" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- end face2face block -->  
                        
                    </div>
    <?php
                endforeach;
            endforeach;
            ?>
        </div>  <!-- End accordion -->    
        
  <?php endif; ?>        
    <!-- End courses and sessions details --> 
</div>   
<script>
    window.onload=function() {
        window.print();
    };
</script>