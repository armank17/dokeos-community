<?php // $Id: $
/* For licensing terms, see /dokeos_license.txt */
$language_file = 'gradebook';
//$cidReset = true;
require_once ('../inc/global.inc.php');
require_once ('lib/be.inc.php');
require_once ('lib/gradebook_functions.inc.php');
require_once ('lib/fe/evalform.class.php');
require_once ('lib/fe/displaygradebook.php');
require_once ('lib/scoredisplay.class.php');
api_block_anonymous_users();
block_students();

$resultadd = new Result();
$resultadd->set_evaluation_id($_GET['selecteval']);
$evaluation = Evaluation :: load($_GET['selecteval']);
$add_result_form = new EvalForm(EvalForm :: TYPE_RESULT_ADD, $evaluation[0], $resultadd, 'add_result_form', null, api_get_self() . '?selectcat=' . Security::remove_XSS($_GET['selectcat']) . '&selecteval=' . Security::remove_XSS($_GET['selecteval']));
if ($add_result_form->validate()) {
	$values = $add_result_form->exportValues();
	$nr_users = $values['nr_users'];
	if ($nr_users == '0') {
		header('Location: gradebook_view_result.php?addresultnostudents=&selecteval=' . Security::remove_XSS($_GET['selecteval']));
		exit;
	}
	// if the evaluation is of the type presence then we have to first get all the users and set the presence of the users 
	// who do not appear in the form submit to 0 (because we use checkboxes => form submit for unchecked checkboxes == empty)
	if ($evaluation[0]->get_type() == 'presence') {
		$tblusers= get_users_in_course($evaluation[0]->get_course_code());
		if(is_array($tblusers) && count($tblusers)>0) {
			foreach ($tblusers as $user) {
				if ( (is_array($values['score']) && !key_exists($user[0],$values['score'])) || !isset($values['score']))
				{
					$values['score'][$user[0]] = 0;
				}
			}
		}
	}	
	$scores = ($values['score']);
	if (is_array($scores)) {
		foreach ($scores as $row) {
			$res = new Result();
			$res->set_evaluation_id($values['evaluation_id']);
			$res->set_user_id(key($scores));
			//if no scores are given, don't set the score
			if ((!empty ($row)) || ($row == '0')) $res->set_score($row);
			$res->add();
			next($scores);
		}
	}
	header('Location: gradebook_view_result.php?addresult=&selecteval=' . Security::remove_XSS($_GET['selecteval']));
	exit;
}
$interbreadcrumb[] = array (
	'url' => $_SESSION['gradebook_dest'],
	'name' => get_lang('Gradebook'
));


Display::display_tool_header(get_lang('AddResult'));

DisplayGradebook :: display_header_result ($evaluation[0], Security::remove_XSS($_GET['selectcat']), 0);

// Start main content
echo '<div id="content">';
	echo '<div class="main">';
	echo $add_result_form->toHtml();
	echo '</div>';
// End main content
echo '</div>';

// Actions bar
echo '<div class="actions">';
echo '</div>';
Display :: display_footer();