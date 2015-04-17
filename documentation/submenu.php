<div id="header2">
    <div class="headerinner">
        <ul id="dokeostabs">
            
            <li class="guide" id="current"><a href="installation_guide.php">Installation Guide</a></li>
        </ul>
        <div style="clear: both;" class="clear"> </div>
    </div>
</div>

<div id="header4">
    <div class="headerinner">
        <div id="help-content">
            <strong>General help</strong><br />
            <a href="../../documentation/installation_guide.php" target="_blank">read the installation guide (the document you are currently looking at)</a><br />
            <a href="http://dokeos.com/en/manual/installation" target="_blank">Read a more up to date installation manual on http://www.dokeos.com</a><br /><br />
        </div>
        <div id="help">
            <a href="#" style="" id="help-link">Help</a>
        </div>
    </div>
</div>
<script type="text/javascript" language="JavaScript">
		jQuery(document).ready( function($) {
			// Expand or collapse the help
			$('#help-link').click(function () {
				$('#help-content').slideToggle('fast', function() {
					if ( $(this).hasClass('help-open') ) {
						$('#help a').css({'backgroundImage':'url("../main/img/screen-options-right.gif")'});
						$(this).removeClass('contextual-help-open');
					} else {
						$('#help a').css({'backgroundImage':'url("../main/img/screen-options-right-up.gif")'});
						$(this).addClass('help-open');
					}
				});
				return false;
			});
		});
	</script>