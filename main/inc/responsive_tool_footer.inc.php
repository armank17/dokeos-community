</div>
<footer>
</footer>
<?php
$include_library_path = api_get_path(WEB_LIBRARY_PATH) . 'javascript/responsive/';
$normal_include_library_path = api_get_path(WEB_LIBRARY_PATH).'javascript/';
?>
<script src="<?php echo $include_library_path;  ?>js/bootstrap.min.js"></script>
<script src="<?php echo $include_library_path;  ?>js/bootbox.min.js"></script>
<script src="<?php echo $include_library_path;  ?>js/respond.min.js"></script>
<script src="<?php echo $include_library_path;  ?>js/prefixfree.min.js"></script>        
<script src="<?php echo $include_library_path;  ?>js/modernizr.js"></script>
<script src="<?php echo $normal_include_library_path;  ?>customInput.jquery.js"></script>
<script type="text/javascript">            
$(function(){
        try {
            $("input").customInput();
        } catch(e){}
});
</script>
<?php
    if (!empty($htmlFootXtra))
    {
        foreach($htmlFootXtra as $this_html_foot)
        {
                echo($this_html_foot);
        }
    }
?>
    </body>
</html>