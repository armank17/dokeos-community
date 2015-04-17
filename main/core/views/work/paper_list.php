<div>
	<table width="98%" cellpadding="3" cellspacing="3">
		<tr>
			<td>
				<h3 class="orange"><?php echo $title; ?></h3>
			</td>
		</tr>
		<tr>
			<td>
				<span><?php echo $description; ?></span>
			</td>
		</tr>
	</table>
  <?php
	echo $papersList->display();
	?>
</div>