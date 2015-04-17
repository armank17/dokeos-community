<?php
/* For licensing terms, see /dokeos_license.txt */

/*****************
TODO: This is a draft for a demo. This code has to be improved before the release
******************/

$language_file = array('document','admin');
// resetting the course id
$cidReset=true;

require_once ('../inc/global.inc.php');
$this_section =  SECTION_COURSES;

// Access restrictions
if (api_is_anonymous()) {
	api_not_allowed(true);
}

Display::display_header(get_lang('Search'));
	
?>
<script type="text/javascript" language="javascript">
	function search(){
		var input = $('#input').val();
		var loader = $('#loader');
		var contentChild = $('#content_with_secondary_actions div');
		var result = $('#result'); 
		$('#result').html("");
		loader.show();
		loader.html('<br/><br/>' + '<?php echo get_lang('Searching').'&nbsp;'; ?>'+'<strong>'+input+'</strong>' +'...');
		
		contentChild.removeClass('bg_search_stewart');

		// ajax request to show results
		$.ajax({
			  url: '<?php echo api_get_path(WEB_CODE_PATH).'search/get_results.ajax.php' ?>',
			  cache: false,
			  data: 'input='+input,
			  success: function(html){
			    showResults();
			    $('#result').html(html);
			  }
			});
		return false;
		
	}
	
	function showResults(){
		var loader = $('#loader');
		var result = $('#result'); 
		
		loader.hide();
		result.fadeIn();
	}

	function hideResults(){
		var result = $('#result');
		var form = $('#search_form'); 
		result.hide();
		form.fadeIn();
		$('#input').focus();
	}
	
	// intercept "enter" to submit form
	$(document).ready(function(){
		$('#input').focus();
	});
	
</script>

<style type="text/css">

.loader {
	background:url('../img/navigation/ajax-loader.gif') no-repeat center 0;
	color:#F09A43;
	margin:0 auto;
	padding:20px 0;
	text-align:center;
	width:150px;
}

.button_text	{
	height:15px;
	padding:70px 10px 30px;
	width:180px;
}
.button_notext	{
	height:115px !important;
	padding:0 !important;
	position: relative;

}

.bg_black:hover	{ background-color:#000 !important; }

#result { padding: 20px 0; }
</style>
<?php

if(!extension_loaded('xapian'))
{
	echo '<div id="content"><div class="confirmation-message">'.(get_lang('SearchXapianModuleNotInstaled')).'</div></div>';
}
else {
		
	
	//--- actions ---
	echo '<div class="actions">';
			//echo '<a class="" href="index.php?'.api_get_cidreq().'" onclick="hideResults()">'.Display::return_icon('navigation/renault/loupe.png').'NEW SEARCH</a>';
			echo '<div class="float_l">';
			echo '<form  method="post" onsubmit="return search();">';
			    echo '<span>';
				echo '<input style="width:400px;" type="text" id="input" name="input">';
				echo '</span>';
				
				echo '<span>';
				echo '<button id="submitBt" class="search">'.get_lang('Search').'</button>';
				echo '</span>';
			echo '</form>';
			echo '</div>';
			/*echo '<a class="" href="#">'.Display::return_icon('navigation/renault/films.png').'VIDEO ONLY</a>';
			echo '<a class="" href="#">'.Display::return_icon('navigation/renault/plus.png'). 'MORE CRITERIA</a>';*/
			echo '<div class="float_r">';
			//echo '<a class="uploadbtn" href="upload.php?'.api_get_cidreq().'">'.Display::return_icon('navigation/renault/upload.png').get_lang('NewUpload'). '</a>';
		    echo '</div>';
		    echo '<div style="clear: both; font-size: 0;"></div>';
	echo '</div>';
		
	
	
	
	// --- div#content ---
	echo '<div id="content_with_secondary_actions" class="rel">';
	
		   // Start search content
		   echo '<div class="bg_search_stewart view_search_min_height">';
			
			echo '<div id="loader" class="rel loader" style="display:none; top:150px;">';
			echo '</div>';
			
			echo '<div id="result" style="display:none;">';
			
			echo '</div>';
	
	
	
	
		echo '<div style="clear: both; font-size: 0;"></div></div>';
	echo '</div>';	//end div#content
	
//	echo '<div class="actions">';
//		echo '&nbsp;';
//	echo '</div>';
}

Display::display_footer();
exit;

?>