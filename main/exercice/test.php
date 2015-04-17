<!DOCTYPE HTML>
<html lang="en-US">
    <?php
    include("../inc/global.inc.php");
    ?>
<head>
	<meta charset="UTF-8">
	<title></title>
        <script type="text/javascript"	src="<?php echo api_get_path(WEB_PATH) ?>main/inc/lib/ckeditor_1/ckeditor.js" ></script>
	<script type="text/javascript" src="<?php echo api_get_path(WEB_PATH) ?>main/inc/lib/javascript/new/jquery.js"></script>
	<script type="text/javascript">
		function createEditor(){
            var class_row = 'rowe';
            var rowCount = $("#data_table >tbody >tr").length;
            var nameEditor = 'editor_'+rowCount;
            var htmlinput = 'answer: '+rowCount;
            $("#data_table > tbody:last").append("<tr class='"+class_row+"'><td align='center'><br />"+htmlinput+"</td><td align='center'><textarea name='"+nameEditor+"' id='"+nameEditor+"'></textarea></td></tr>");
            CKEDITOR.replace(nameEditor,{
                toolbar:"Introduction",
                language : "en"
            });  
		}
                
                function removeEditor(){
                    var rowCount = $("#data_table >tbody >tr").length;
                    var nameEditor = 'editor_'+rowCount;
                    CKEDITOR.instances.editor_1.destroy();
                    //CKEDITOR.instances.nameEditor.destroy();
                    $("#data_table > tbody > tr:last").remove();
                    //var num = $(\'input[name="nb_answers"]\').val();
                }
	</script>
</head>
<body>
	<p>
		<input onclick="createEditor();" type="button" value="Create Editor">
		<input onclick="removeEditor();" type="button" value="Remove Editor">
	</p>
	<div>
		<table id="data_table">
			<tbody>
				<tr>
					<td>Answers</td>
				</tr>
			</tbody>
		</table>
	</div>
</body>
</html>