<?php
echo $this->data['head'];
?>
<div data-role="content">
    <!-- end section header -->        
    <!-- List view 1 -->
    <ul data-role="listview" data-split-icon="gear" data-split-theme="d" data-filter="true" data-inset="true">
        <li data-role="list-divider" role="heading"><?php echo get_lang('DisplayTrainingList'); ?></li>  
        <?php
        foreach ($course_list as $my_course) {
        ?>
        <li data-ajax="false" data-theme="c"> 
            <img src="<?php echo api_get_path(WEB_PATH).'main/img/miscellaneous.png'; ?>">                           
            <?php
            echo $my_course;
            ?>                                 
        </li>  
        <?php
        }
        ?>
       
    </ul>  
</div> 
<?php
echo $this->data['foot'];
?>