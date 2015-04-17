
<h2><?php echo $this->get_lang('ConvertToUtf8Databases'); ?></h2>
<em><?php echo $this->get_lang('MakeDatabaseBackupBeforeDoingAConvertion'); ?></em>
<table class="data_table">
    <tr>
        <th><?php echo $this->get_lang('DatabaseName'); ?></th>
        <th><?php echo $this->get_lang('DatabasePrefix'); ?></th>
        <th><?php echo $this->get_lang('DatabaseCharset'); ?></th>
        <th width="25%"><?php echo $this->get_lang('Actions'); ?></th>
    </tr>
    
        <?php if (!empty($this->databases)): ?>
            <?php foreach ($this->databases as $database): 
                    $dbName = $database['dbName'];
            ?>
                <tr>
                    <td><?php echo $dbName; ?></td>
                    <td align="center"><?php echo $database['dbPrefix']; ?></td>
                    <td align="center"><?php echo $database['dbCharset']; ?></td>
                    <td align="center">
                        <?php if (stripos($database['dbCharset'], 'utf8') !== FALSE): ?>
                            <span style="font-size: 14px; font-weight: bold;"><em><?php echo $this->get_lang('Updated'); ?></em></span>
                        <?php else: ?>
                            <div class="update-btn">
                                <form name="upd-<?php echo strtoupper($dbName); ?>" id="form-<?php echo strtoupper($dbName); ?>" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=upgrade&cmd=UpdateDb&func=convert'; ?>" method="POST">
                                    <input type="hidden" name="database" value="<?php echo $dbName; ?>" />
                                    <button class="save btn-update" id="btn-<?php echo strtoupper($dbName); ?>" style="float: none;"><?php echo $this->get_lang('Contert'); ?></button>
                                    <div id="submit-progress-<?php echo strtoupper($dbName); ?>">
                                        <div class="progress progress-<?php echo strtoupper($dbName); ?>">
                                            <div class="bar bar-<?php echo strtoupper($dbName); ?>"></div>
                                            <div class="percent percent-<?php echo strtoupper($dbName); ?>">0%</div>
                                        </div>
                                        <div id="status-<?php echo strtoupper($dbName); ?>"></div>
                                    </div>
                                </form>
                            </div>                                
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colpan="4"><?php echo $this->get_lang('Empty'); ?></td></tr>
        <?php endif; ?>    
</table>
<script type="text/javascript" src="<?php echo api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js'; ?>" language="javascript"></script>
<script type="text/javascript" src="<?php echo api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js'; ?>" language="javascript"></script>
<script src="<?php echo api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.form.js'; ?>" language="javascript"></script>
<script>
    if ($(".btn-update").length) {        
        $(".btn-update").click(function(e){
           var btnId = $(this).attr("id");
           var db = btnId.replace("btn-", "");
           var myform = $("#form-"+db);              
           var bar = $('.bar-'+db);
           var percent = $('.percent-'+db);
           var status = $('#status-'+db);           
           if ($(myform).length) {                
                $(myform).ajaxForm({
                     dataType:  'json',                 
                     beforeSend: function() {
                        $(".progress-"+db).css("visibility", "visible");
                        status.empty();
                        var percentVal = 'Processing ... <img src="/main/img/mozilla_blu.gif" />';
                        bar.width(percentVal)
                        percent.html(percentVal);
                     },
                     uploadProgress: function(event, position, total, percentComplete) {
                        var percentVal = percentComplete + '%';
                        bar.width(percentVal)
                        percent.html(percentVal);
                     },
                     success: function(data) {                         
                         var percentVal = '100%';
                         bar.width(percentVal);
                         percent.html(percentVal);                         
                         if (data.success) {
                             location.href = decodeURIComponent(data.redirect);
                         }                         
                     }
                 });                
           }                      
        });       
    }
</script>