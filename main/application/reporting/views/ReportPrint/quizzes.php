<div class="clearfix"></div>
<span>
    <h4><?php echo $this->encodingCharset($this->get_lang('QuizzesAverageValues')); ?>
    <?php 
        if (!empty($this->txtSearchDefault)) {
            echo ' - '.$this->get_lang('SearchByQuizOrExamName').' : '.$this->encodingCharset($this->txtSearchDefault);
        }
    ?>
    </h4>
</span>
<!-- Chart -->
<div class="span11 chart_print" id="chartContainer2">    
    <div id="chartQuizContainer"></div>    
</div>
<div class="data-container">
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
                </tr>
            </thead>
            <tbody>                            
                <?php                            
                    if (!empty($this->quizData)):
                        foreach ($this->quizData as $data):
                ?>
                        <tr>
                            <td><?php echo $this->encodingCharset($data['title']); ?></td>
                            <td><?php echo $this->encodingCharset($data['mode']); ?></td>
                            <td align="center"><?php echo $this->encodingCharset($data['incourse']); ?></td>
                            <td align="center"><?php echo $data['score']; ?></td>
                            <td align="center"><?php echo $data['highest']; ?></td>
                            <td align="center"><?php echo $data['lowest']; ?></td>
                            <td align="center"><?php echo $data['participation']; ?></td>
                            <td align="center"><?php echo $data['time']; ?></td>
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
</div>
<div class="clearfix"></div>
<?php 
    if (count($this->chartValues) > 1 && count($this->chartValues) < 15): 
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
    window.onload=function() {
        window.print();
    };
</script>