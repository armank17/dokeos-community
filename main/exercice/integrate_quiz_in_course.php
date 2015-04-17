<?php
$language_file = array('exercice');

require_once '../inc/global.inc.php';
require_once '../newscorm/learnpathList.class.php';

if(!api_is_allowed_to_edit()) {
  api_not_allowed(true);
}

$list = new LearnpathList(api_get_user_id());
$lp_list = $list->get_flat_list();

$quiz_id = intval($_GET['quizId']);
?>
<?php Display::display_reduced_header(); ?>

<form method="post" action="<?php api_get_self() ?>?choice=integrate_quiz_in_lp" id="integrate_quiz_form">
<input type="hidden" name="exerciseId" value="<?php echo $quiz_id ?>" />
<input type="hidden" name="sec_token" value="<?php echo Security::get_token() ?>" />
<div>
	<div class="label"><?php echo get_lang('ChooseWhereTheQuizIsIntegrated') ?></div>
	<div>
		<select name="integrate_lp_id">
		<?php foreach($lp_list as $lp_id=>$lp): ?>
		<option value="<?php echo $lp_id?>"><?php echo $lp['lp_name']?></option>
		<?php endforeach; ?>
		</select>
	</div>
</div>
<div class="row">
	<div class="submit"><button type="submit" class="add"><?php echo get_lang('Validate') ?></button></div>
</div>
</form>