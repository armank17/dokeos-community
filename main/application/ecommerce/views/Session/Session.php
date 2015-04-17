<?php
$this->ajax->printJavascript("appcore/library/xajax/");
?>
<link rel="stylesheet" media="all" type="text/css" href="<?php echo $this->css; ?>" />
<script type="text/javascript">
    $(document).ready(function() {
        var id_orden = 1;

        $(".data_table tbody tr:not(:first)").each(function() {
            $(this).attr("id", "recordsArray_c" + id_orden);
            id_orden++;
        });

        var id_orden = 1;

        $(".data_table tbody tr:not(:first)").each(function() {
            course_code = $("#recordsArray_c" + id_orden).find("input").attr('name', 'id[]').val();
            $(this).attr("id", "recordsArray_" + course_code);
            id_orden++;
        });

        // Make sortable the table by tr element
        $(".data_table tbody").sortable({
            opacity: 0.6,
            placeholder: "effectDragDrop",
            items: "tr:not(:first)", // Not make sortable the first tr which is the header
            cursor: "crosshair",
            handle: "#ddrag",
            //cancel: ".nodrag",
            update: function() {
                var order = $(this).sortable("serialize");
                var record = order.split("&");
                var recordlen = record.length;
                var disparr = new Array();
                for (var i = 0; i < (recordlen); i++) {
                    var recordval = record[i].split("=");
                    disparr[i] = recordval[1];
                }

                $.ajax({
                    type: "GET",
                    url: "<?php echo api_get_path(WEB_AJAX_PATH) ?>ecommerce_courses.ajax.php?action=updatePositionSessions&disporder=" + disparr,
                    success: function(msg) {
                    }
                })
            }
        }).sortable("serialize");
        
    });
</script>
<?php
if (!isset($_GET['session_ecommerce_column']) && empty($_GET['session_ecommerce_column'])) {
    unset($_SESSION['session_ecommerce_column']);
}
$table = new SortableTable('session_ecommerce', array($this->model, 'getCountSessions'), array($this->model, 'getSessionData'), 12);
$parameters = array();
$table->set_additional_parameters($parameters);
$table->set_header(0, '', false, 'width="10px"');
$table->set_header(1, get_lang('Move'), true, '', 'width="15px" id=ddrag');
$table->set_header(2, get_lang('Id'), true, 'width="15px"');
$table->set_header(3, get_lang('Title'), true, 'width="100px"');
$table->set_header(4, get_lang('Category'), true);
$table->set_header(5, get_lang('Price'), true, 'width="55px"');
$table->set_header(6, get_lang('Duration'), true, 'width="70px"');
$table->set_header(7, get_lang('Catalog'), false, 'width="70px"');
$table->set_header(8, get_lang('Actions'), false, 'width="65px"');
//$table->set_column_filter(8, array($this, 'getSessionCatalogButtonList'));
$table->set_column_filter(8, array($this, 'getSessionCatalogButtonList'));
$table->set_form_actions(array('delete' => get_lang('langDelete')));
$table->display();
?>
<div id="divFormCategory"></div>