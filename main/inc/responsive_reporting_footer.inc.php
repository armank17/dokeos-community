</div>
</div>
<div class="pushx"></div>
<footer>
    <div class="container">
        <div class="row">
          <?php
              $menuLinks = (function_exists(get_menu_links) ? get_menu_links('footer') : array());
              if(count($menuLinks) > 0):
          ?>
          <div class="sticky-footer">    
                <div id="footer-link">
                    <div class="footer-link-wrapper">
                    <ul>
                    <?php             
                        /* footer menu links */
                        foreach($menuLinks as $menuLink):
                    ?>
                            <li class="tab_link"><a href="<?php echo $menuLink['link_path']; ?>" target="<?php echo $menuLink['target']; ?>"><?php echo $menuLink['title']; ?></a></li>
                    <?php
                        endforeach;
                    ?>
                    </ul>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
          </div>
        <?php endif; ?>
        </div><!-- end of #row -->        
    </div>
</footer>
<?php
$include_library_path = api_get_path(WEB_LIBRARY_PATH) . 'javascript/responsive/';
$normal_include_library_path = api_get_path(WEB_LIBRARY_PATH) . 'javascript/';
?>

<script src="<?php echo $include_library_path; ?>js/bootbox.min.js"></script>
<script src="<?php echo $include_library_path; ?>js/respond.min.js"></script>
<script src="<?php echo $include_library_path; ?>js/prefixfree.min.js"></script>        
<script src="<?php echo $include_library_path; ?>js/modernizr.js"></script>
<script src="<?php echo $normal_include_library_path; ?>customInput.jquery.js"></script>
<script type="text/javascript">
    $(function() {
        try {
            $("input").customInput();
        } catch (e) {
        }
    });
</script>
<?php
global $htmlFooterXtra;
// Display all $htmlFooterXtra
if (isset($htmlFooterXtra) && $htmlFooterXtra) {
    foreach ($htmlFooterXtra as $this_html_footer) {
        echo($this_html_footer);
    }
}
?>
</body>
</html>