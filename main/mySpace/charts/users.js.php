<?php
if (!function_exists('api_get_path')) {
    die('Warning, global.inc.php wasn\'t included. Security issue.');
}
?>
<div id="tabs">
    <ul>
        <li><a href="#tabs-1"><?php echo get_lang('Progress') ?></a></li>
        <li><a href="#tabs-2"><?php echo get_lang('Score') ?></a></li>
        <li><a href="#tabs-3"><?php echo get_lang('Time') ?></a></li>
    </ul>
    <div style="width:90%; margin:0 auto;">
        <div id="tabs-1"></div>
        <div id="tabs-2"></div>
        <div id="tabs-3"></div>
    </div>
</div>
<script type="text/javascript">


    function getMedianSerie(values){

        serie = [];
        medianValue = 0;

        // sort ascendent of the values
        values.sort(function(a,b){return a-b;});
        if(values.length == 1)
            medianValue = values[0];
        else if(values.length % 2 == 0)
            medianValue = values[values.length / 2 - 1] + values[values.length / 2] / 2;
        else
            medianValue = values[parseInt(values.length / 2)];
        medianValue = Math.round(medianValue);
        return [[medianValue,0.7, medianValue+'%'],[medianValue, values.length+0.3, medianValue+'%']];

    }

    function getAverageSerie(total, length){

        averageValue = Math.round(total / length);
        return [[averageValue,0.7, averageValue+'%'],[averageValue, length+0.3, averageValue+'%']];

    }

    function getTimeMedianSerie(values){

        serie = [];
        medianValue = 0;

        // sort ascendent of the values
        values.sort(function(a,b){return a-b;});

        if(values.length % 2 == 0)
            medianValue = values[values.length / 2 - 1] + values[values.length / 2] / 2;
        else
            medianValue = values[parseInt(values.length / 2)];

        medianValue = Math.round(medianValue);

        return [[medianValue,0.7, time_to_hms(medianValue)],[medianValue, values.length+0.3, time_to_hms(medianValue)]];

    }

    function getTimeAverageSerie(total, length){

        averageValue = total / length;
        averageValue = Math.round(averageValue);
        return [[averageValue,0.7, time_to_hms(averageValue)],[averageValue, length+0.3, time_to_hms(averageValue)]];

    }

    function time_to_hms(time){

        hours = Math.floor(time / 3600);
        time = time % 3600;
        minutes = Math.floor(time / 60);
        seconds = time % 60;

        if(minutes < 10)
            minutes = '0'+minutes;
        if(seconds < 10)
            seconds = '0'+seconds;

        return hours+':'+minutes+':'+seconds;

    }



    $(document).ready(
    function(){

        // click listener to forward to the course tracking
        $.jqplot.eventListenerHooks.push(['jqplotClick', clickOnPlot]);

        // init vars required by every plots
        users = [];
        users_id = [];
        // init vars for progress plot
        progressSerie = [];
        totalProgress = 0;
        medianProgressArray = [];

        // init vars for score plot
        scoreSerie = [];
        totalScore = 0;
        medianScoreArray = [];

        // init vars for time plot
        timeSerie = [];
        totalTime = 0;
        medianTimeArray = [];

        // collect datas from users
        index = 0;
        maxTime = 0;
<?php foreach ($chart_data as $user_id => $user_data): ?>
                        index++;
                        users.push('<?php echo addslashes($user_data['firstname']) . ' ' . addslashes($user_data['lastname']); ?>');
                        users_id.push("<?php echo $user_id ?>");
                        progressSerie.push([<?php echo intval($user_data['progress']) ?>, index, "<?php echo $user_id ?>", '']);
                        totalProgress += <?php echo intval($user_data['progress']); ?>;
                        medianProgressArray.push(<?php echo intval($user_data['progress']) ?>);

                        scoreSerie.push([<?php echo intval($user_data['score']) ?>, index, "<?php echo $user_id ?>", '']);
                        totalScore += <?php echo intval($user_data['score']); ?>;
                        medianScoreArray.push(<?php echo intval($user_data['score']) ?>);

                        timeSerie.push([<?php echo intval($user_data['time']) ?>, index, "<?php echo $user_id ?>", '']);
                        totalTime += <?php echo intval($user_data['time']); ?>;
                        medianTimeArray.push(<?php echo $user_data['time'] ?>);

                        if(maxTime < <?php echo intval($user_data['time']) ?>)
                        maxTime = <?php echo intval($user_data['time']) ?>;

<?php endforeach; ?>


                    //let's prepare the ticks for time plot
                    maxTime = maxTime - maxTime%4 + 4;
                    timeTicks = [[0,'0:00:00']];
                    for(i=1 ; i<5 ; i++)
                        timeTicks.push([i*maxTime/4, time_to_hms(i*maxTime/4)]);

                    // and now draw the plots
                    progressPlot = $.jqplot('tabs-1',[progressSerie, getAverageSerie(totalProgress, progressSerie.length), getMedianSerie(medianProgressArray)],{
                        legend:{
                            show:true,
                            location:'ne'
                        },
                        height: (110 + users.length*15),
                        title: '<?php echo get_lang('Progress') ?>',
                        axes: {
                            xaxis:{min:0, max:100, tickOptions:{formatString:'%.1i%', mark:'outside'}, showTicks: true, showTickMarks: true, ticks:[0,25,50,75,100]},
                            yaxis:{renderer:$.jqplot.CategoryAxisRenderer, tickOptions:{mark:'outside'}, showTicks: true, showTickMarks: true, ticks:users}
                        },
                        series: [
                            {
                                renderer:$.jqplot.BarRenderer,
                                rendererOptions: {
                                    barDirection: 'horizontal',
                                    barWidth: 15
                                },
                                pointLabels:{location:'w'},
                                label:'<?php echo get_lang('Trainings') ?>'
                            },
                            {
                                showMarker: false,
                                pointLabels:{location:'e'},
                                label:'<?php echo get_lang('Average') ?>'
                            },
                            {
                                showMarker: false,
                                pointLabels:{location:'e'},
                                label:'<?php echo get_lang('Median') ?>'
                            }
                        ],
                        grid: {
                            drawGridlines: true
                        },
                        cursor: {
                            showTooltip: false,
                            style: 'pointer'
                        }
                    });

                    scorePlot = $.jqplot('tabs-2',[scoreSerie, getAverageSerie(totalScore, scoreSerie.length), getMedianSerie(medianScoreArray)],{
                        legend:{
                            show:true,
                            location:'ne'
                        },
                        height: (110 + users.length*15),
                        title: '<?php echo get_lang('Score') ?>',
                        axes: {
                            xaxis:{min:0, max:100, tickOptions:{formatString:'%.1i%', mark:'outside'}, showTicks: true, showTickMarks: true, ticks:[0,25,50,75,100]},
                            yaxis:{renderer:$.jqplot.CategoryAxisRenderer, tickOptions:{mark:'outside'}, showTicks: true, showTickMarks: true, ticks:users}
                        },
                        series: [
                            {
                                renderer:$.jqplot.BarRenderer,
                                rendererOptions: {
                                    barDirection: 'horizontal',
                                    barWidth: 15
                                },
                                pointLabels:{location:'w'},
                                label:'<?php echo get_lang('Trainings') ?>'
                            },
                            {
                                showMarker: false,
                                pointLabels:{location:'e'},
                                label:'<?php echo get_lang('Average') ?>'
                            },
                            {
                                showMarker: false,
                                pointLabels:{location:'e'},
                                label:'<?php echo get_lang('Median') ?>'
                            }
                        ],
                        grid: {
                            drawGridlines: true
                        },
                        cursor: {
                            showTooltip: false,
                            style: 'pointer'
                        }
                    });

                    timePlot = $.jqplot('tabs-3',[timeSerie, getTimeAverageSerie(totalTime, timeSerie.length), getTimeMedianSerie(medianTimeArray)],{
                        legend:{
                            show:true,
                            location:'ne'
                        },
                        height: (110 + users.length*15),
                        title: '<?php echo get_lang('Time') ?>',
                        axes: {
                            xaxis:{ticks:timeTicks, tickOptions:{mark:'outside'}, showTicks: true, showTickMarks: true, min:0},
                            yaxis:{renderer:$.jqplot.CategoryAxisRenderer, tickOptions:{mark:'outside'}, showTicks: true, showTickMarks: true, ticks:users}
                        },
                        series: [
                            {
                                renderer:$.jqplot.BarRenderer,
                                rendererOptions: {
                                    barDirection: 'horizontal',
                                    barWidth: 15
                                },
                                pointLabels:{location:'w'},
                                label:'<?php echo get_lang('Trainings') ?>'
                            },
                            {
                                showMarker: false,
                                pointLabels:{location:'e'},
                                label:'<?php echo get_lang('Average') ?>'
                            },
                            {
                                showMarker: false,
                                pointLabels:{location:'e'},
                                label:'<?php echo get_lang('Median') ?>'
                            }
                        ],
                        grid: {
                            drawGridlines: true
                        },
                        cursor: {
                            showTooltip: false,
                            style: 'pointer'
                        }
                    });

                    function clickOnPlot(ev, gridpos, datapos, neighbor, plot){
                        if(neighbor)
                        {
                            document.location = '<?php echo api_get_path(WEB_CODE_PATH) ?>mySpace/myStudents.php?student='+neighbor.data[2]+'&details=true&course=<?php echo $_course['id'] ?>&origin=tracking_course';
                        }
                    }
                    $("#tabs").tabs();

                    $(".jqplot-yaxis-tick").css({cursor: "pointer", zIndex: "1"}).click(function(){
                        var curr_uid = users_id[$(this).index()];
                        document.location = '<?php echo api_get_path(WEB_CODE_PATH) ?>mySpace/myStudents.php?student='+curr_uid+'&details=true&course=<?php echo $_course['id'] ?>&origin=tracking_course';
                    });

                });


</script>
