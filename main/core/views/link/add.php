<?php
	if(!empty($errormessage)) {
		echo '<div class="confirmation-message rounded">'.$errormessage.'</div>';
	}
	$addCategoryForm->display();
?>