<?php
class Graphics {
	
	function insert_static_progress_bar($percent, $width='300', $height='20'){
		
		
		echo '
			<div class="progress_bar_container" style="border:1px solid; width:'.$width.'px; height:'.$height.'px">
				<div class="progress_bar_indicator" style="height:100%; background-color:#919191; width:'.ceil($percent).'%"></div>
			</div>
		
			';
		
	}
	
	
}