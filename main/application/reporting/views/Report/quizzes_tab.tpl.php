<div class="clearfix"></div>
<?php if ($this->printPage != 'print'): ?>
<div class="row-fluid" id="search-filters">
    <!-- Search Form -->
    <?php echo $this->setTemplate('search_form', 'Report'); ?>
</div>                   
<!-- Pagination -->
<div id="pagination">    
    <?php 
        if ($this->paginator->num_pages > 1 && !$this->isEmpty) {
            echo $this->paginator->display_pages();
        }
    ?>
</div>
<span>
    <h4><?php echo $this->get_lang('QuizzesAverageValues'); ?>
    <?php 
        if (!empty($this->txtSearchDefault)) {
            echo ' - '.$this->get_lang('SearchByQuizOrExamName').' : '.$this->encodingCharset($this->txtSearchDefault);
        }
    ?>
    </h4>
</span>
<?php endif; ?>

<!-- Chart -->
<div class="span11 chart_print" id="chartContainer2">    
    <div id="chartQuizContainer"></div>    
</div>
<div class="data-container">
    <?php if (!$this->isEmpty || $this->printPage == 'print'): ?>
        <table name="quizzes" id="quizzes" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang('Quiz')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Type')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ReportInCourse')); ?></th>            
                    <th><?php echo $this->encodingCharset($this->get_lang('AverageScore')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Highest')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Lowest')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Participation')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('AverageTime')); ?></th>
                    <?php if ($this->printPage != 'print'): ?>
                    <th class="print_invisible"><?php echo $this->encodingCharset($this->get_lang('Detail')); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>                            
                <?php                            
                    if (!empty($this->quizData)):
                        foreach ($this->quizData as $data):
                ?>
                        <tr>
                            <td><?php echo cut($this->encodingCharset($data['title']), 40, true); ?></td>
                            <td><?php echo $this->encodingCharset($data['mode']); ?></td>
                            <td align="center"><?php echo cut($this->encodingCharset($data['incourse']), 30, true); ?></td>
                            <td align="center"><?php echo $data['score']; ?></td>
                            <td align="center"><?php echo $data['highest']; ?></td>
                            <td align="center"><?php echo $data['lowest']; ?></td>
                            <td align="center"><?php echo $data['participation']; ?></td>
                            <td align="center"><?php echo $data['time']; ?></td>
                            <?php if ($this->printPage != 'print'): ?>
                            <td align="center">
                                <a class='action_quiz' id='' href='<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayQuizUsers&id='.$data['id'].'&courseCode='.$data['course_code'].'&mode='.$data['mode']; ?>'>
                                    <img src='<?php echo api_get_path(WEB_CSS_PATH).$this->stylesheet; ?>/images/action/list_learners.png'>
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>                                 
                <?php
                        endforeach;
                    else:
                ?>
                      <tr><td colspan="9" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
                <?php        
                    endif;
                ?>
            </tbody>
        </table>
    <?php else: ?>    
        <div class="warning-message"><?php echo $this->encodingCharset($this->get_lang('UseFiltersToSelectReporting')); ?></div>    
    <?php endif; ?>
</div>
<input type="hidden" name="hid_action_code" id="hid_action_code" />
<?php if ($this->printPage != 'print' && !$this->isEmpty): ?>
<span class="pull-right">
    <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=export&type=quizzes'; ?>" id="quiz_export" >
        <img src="<?php echo api_get_path(WEB_IMG_PATH).'csv.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
        <?php echo $this->encodingCharset($this->get_lang("Export")); ?>
    </a>&nbsp;|&nbsp;
    <a title="<?php echo $this->encodingCharset($this->get_lang("Print")); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportPrint&func=quizzes&searchText='.urlencode($this->txtSearchDefault); ?>" id="course_print" class="reporting-print">
        <img src="<?php echo api_get_path(WEB_IMG_PATH).'print_22.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
        <?php echo $this->encodingCharset($this->get_lang("Print")); ?>
    </a>
</span>
<?php endif; ?>
<div class="clearfix"></div>
<?php 
    if (count($this->chartValues) > 1 && count($this->chartValues) < 15 && !$this->isEmpty): 
        if (count($this->chartValues) > 10) {$h = '300px';}
        else if (count($this->chartValues) > 5) { $h = '260px'; }
        else { $h = '170px'; }
?>
    <script>
        if ($("#chartQuizContainer").length) {
            $("#chartQuizContainer").html("");
            $("#chartQuizContainer").css({'display':'block','height':'<?php echo $h; ?>','width':'80%','padding-left':'50px'});           
            var chart = new CanvasJS.Chart("chartQuizContainer", {
                axisX: {
                        interval: 1,
                        gridThickness: 0,
                        labelFontSize: 11,
                        labelFontStyle: "normal",
                        labelFontWeight: "normal",
                        labelFontFamily: "Verdana"				
                },
                axisY2:{
                        interlacedColor: "#F9F9F9",
                        gridColor: "#F9F9F9",				
                        titleFontColor: "#424242",
                        titleFontWeight: "bold",
                        indexOrientation: "vertical",
                        maximum: 100,
                        labelFontSize: 12
                },
                toolTip:{
                        enabled: false   //enable here
                },
                data: [{   				
                        type: "bar",
                        name: "chart",
                        axisYType: "secondary",
                        color: "<?php echo $this->themeColor; ?>",
                        indexLabel: "{y}  ",
                        indexLabelPlacement: "outside",  
                        indexLabelOrientation: "horizontal",
                        indexLabelFontFamily: "Verdana",
                        indexLabelFontColor: "#CCC",
                        indexLabelFontSize: 14,
                        indexLabelFontWeight: "bold",
                        dataPoints: [<?php echo $this->encodingCharset(implode(',', $this->chartValues)); ?>]                        
                }]
            });
            chart.render();
        }
    </script>
<?php endif; ?>
<script>
    if ($(".action_quiz").length) {
        $(".action_quiz").click(function(e){
            ReportingModel.displayQuizUsers(e, $(this));
        });
    }    
    if ($(".reporting-print").length) {
        $(".reporting-print").click(function(e){            
            ReportingModel.printPage(e, $(this));
        });
    } 
    if ($(".cut-tooltip").length > 0) {
        ReportingModel.cutTooltip();
    }
</script>
<div id="closeText" style="display: none;" class="<?php echo get_lang('Close') ?>"></div>