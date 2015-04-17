<div id="dataDiv">    
    <h4><?php echo $this->encodingCharset($this->courseInfo['name']); ?> - <?php echo $this->encodingCharset($this->moduleInfo['name']); ?> - <?php echo $this->encodingCharset($this->learnerInfo['firstname'].' '.$this->learnerInfo['lastname']); ?></h4>
    <div class="data-container">
        <table id="module-user" class="responsive large-only table-striped" width="100%">
            <thead>
                <tr>
                    <th width="40%"><?php echo $this->encodingCharset($this->get_lang('ScormLessonTitle')); ?></th>
                    <th width="15%"><?php echo $this->encodingCharset($this->get_lang('ScormStatus')); ?></th>
                    <th width="10%"><?php echo $this->encodingCharset($this->get_lang('ScormScore')); ?></th>
                    <th width="15%"><?php echo $this->encodingCharset($this->get_lang('ScormTime')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($this->moduleUserDetailData)): 
                        foreach ($this->moduleUserDetailData as $data):                    
                        if (count($data['views']) > 1): // Details with multiple views                            
                ?>
                            <tr>
                                <td width="40%"><strong><?php echo $this->encodingCharset($data['name']); ?></strong></td>
                                <td width="15%"></td>
                                <td width="10%"></td>
                                <td width="15%"></td>
                                <td width="10%"></td>
                            </tr>
                            <?php 
                                if (!empty($data['views'])): 
                                    $i = 1;
                                    foreach ($data['views'] as $view):
                                        $styleColor = in_array($view['status'], array('completed', 'passed'))?'color:green':'color:black;';                                                 
                            ?>  
                                        <tr>
                                            <td width="40%" class="attempts-label"><?php echo $this->encodingCharset($this->get_lang('Attempt')).' '.(!empty($view['session_id'])?'('.$this->encodingCharset($this->get_lang('Session').': '.$view['session_name']).')':'('.$this->encodingCharset($this->get_lang('NoSessions')).')'); ?></td>
                                            <td width="15%" align="center" style="<?php echo $styleColor; ?>"><?php echo $this->encodingCharset($this->get_lang($this->moduleStatusLangVariables[$view['status']])); ?></td>
                                            <td width="10%" align="center"><?php echo in_array($view['item_type'], array('quiz', 'sco'))?($view['max_score'] > 0?round($view['score']).'/'.round($view['max_score']):'n.a.'):'n.a'; ?></td>
                                            <td width="15%" align="center"><?php echo api_format_time($view['time']); ?></td>                                            
                                        </tr>
                                <?php 
                                        $i++;
                                    endforeach; 
                                endif;
                           
                           else:
                               $styleColor = in_array($data['last_view']['status'], array('completed', 'passed'))?'color:green':'color:black;';                  
                ?>
                                <tr>                                    
                                    <td width="40%"><strong><?php echo $this->encodingCharset($data['name']); ?></strong></td>
                                    <td width="15%" align="center" style="<?php echo $styleColor; ?>"><?php echo $this->encodingCharset($this->get_lang($this->moduleStatusLangVariables[$data['last_view']['status']])); ?></td>
                                    <td width="10%" align="center"><?php echo in_array($data['last_view']['item_type'], array('quiz', 'sco'))?($data['last_view']['max_score'] > 0?round($data['last_view']['score']).'/'.round($data['last_view']['max_score']):'n.a.'):'n.a'; ?></td>
                                    <td width="15%" align="center"><?php echo api_format_time($data['last_view']['time']); ?></td>                                    
                                </tr>
                <?php
                           endif;                                                                
                        endforeach;
                    else:
                ?>
                    <tr><td colspan="5" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
               <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    window.onload=function() {
        window.print();
    };
</script>