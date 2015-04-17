<?php 
require_once dirname( __FILE__ ) . '/../../../global.inc.php';

?>
<html>
<link href="sdk.css" rel="stylesheet" type="text/css"/>
  <table class="api" width=400>
	        	<?php 
    		foreach($resArray as $key => $value) {
    			
    			echo "<tr><td> $key:</td><td>$value</td>";
    			}	
       			?>
  </table>
</html>