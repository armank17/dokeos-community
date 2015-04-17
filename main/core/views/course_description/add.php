<?php
	if ($show_peda_suggest) {
            if (isset ($question_info)) {
                    $message = '<strong>'.get_lang('QuestionPlan').'</strong><br />';
                    $message .= $question_info;
                    Display::display_normal_message($message, false, true);
            }
    }
	if (api_get_setting('wcag_anysurfer_public_pages')=='true') {
            echo (WCAG_Rendering::editor_header());
    }

	echo $courseDescriptionForm->display(); 

	if (api_get_setting('wcag_anysurfer_public_pages')=='true') {
            echo (WCAG_Rendering::editor_footer());
    }
?>