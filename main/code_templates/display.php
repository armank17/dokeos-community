<?php

$cidReset = true;
// including the global Dokeos file
require_once '../inc/global.inc.php';

Display::display_header();
echo '<div id="content">';
Display::display_confirmation_message(get_lang('ConfirmationMessage'), false,true);
Display::display_warning_message(get_lang('WarningMessage'), false,true);
Display::display_error_message(get_lang('ErrorMessage'), false,true);
Display::display_normal_message(get_lang('Message'), false,true);
echo '</div>';