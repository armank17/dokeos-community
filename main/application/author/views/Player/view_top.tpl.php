<style type="text/css">
    .cut-tooltip {
        float: none;
        display: inline;
    }
</style>
<div style="position: relative;" id="generic_tool_header">
    <div id="header_background"></div>
    <div id="view-top-first">
        <a target="_self" style="margin-left:8px;" id="back2home" href="<?php echo api_get_path(WEB_COURSE_PATH).$this->courseInfo['path'].'/index.php'; ?>" class="title-button back2home-from-author">
            <img width="42" height="37" alt="" src="<?php echo api_get_path(WEB_IMG_PATH).'spacer.gif'; ?>" style="margin-left:-5px; margin-top:-5px; ">
            <div class="course_session_name"><?php echo cut($this->encodingCharset($this->courseInfo['name']), 20, true) ?></div>
        </a>               
    </div>
    <div id="view-top-second">
        <div id="view-top-second-title" style="width:37%"><?php echo cut($this->encodingCharset($this->currentItem['title']), 30, true) ?></div>
    </div>
    <div id="view-top-third">
        <div class="cols" id="arrows">   
            
            <?php echo $this->getProgressBar(); ?>
            
            <!-- Audio button -->
            <a href="#" id="unmute" class="<?php echo $this->audioActions(); ?>" style="<?php echo !empty($this->currentItem['audio'])?'display:block;':'display:none;'; ?>float:left;"><img id="speaker_on" src="<?php echo api_get_path(WEB_IMG_PATH).'spacer.gif'; ?>" /></a>                       
            <!-- Previous button -->
            <?php if ($this->prevItemId > 0): ?>
                <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=PlayerAjax&func=switchItem&'.api_get_cidreq().'&lpId='.$this->lpId.'&lpItemId='.$this->prevItemId.'&currentItemId='.$this->currentItem['id']; ?>" id="lp-nav-<?php echo $this->prevItemId; ?>" class="lp-nav">
                    <img src="<?php echo api_get_path(WEB_IMG_PATH).'spacer.gif'; ?>" id="coursepreviousbutton" />
                </a>
            <?php else: ?>
                <img src="<?php echo api_get_path(WEB_IMG_PATH).'spacer.gif'; ?>" id="coursepreviousbutton_na" />
            <?php endif; ?>            
            <!-- Next button -->
            <?php if ($this->nextItemId > 0): ?>
                <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=PlayerAjax&func=switchItem&'.api_get_cidreq().'&lpId='.$this->lpId.'&lpItemId='.$this->nextItemId.'&currentItemId='.$this->currentItem['id']; ?>" id="lp-nav-<?php echo $this->nextItemId; ?>" class="lp-nav">
                    <img src="<?php echo api_get_path(WEB_IMG_PATH).'spacer.gif'; ?>" id="coursenextbuttongreen" />
                </a>
            <?php else: ?>
                <img src="<?php echo api_get_path(WEB_IMG_PATH).'spacer.gif'; ?>" id="coursenextbuttongreen_na" />
            <?php endif; ?>               
        </div>         
    </div>
</div>
<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>application/author/assets/js/functions.js" type="text/javascript" language="javascript"></script>
<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>application/author/assets/js/view_top.js" type="text/javascript" language="javascript"></script>
