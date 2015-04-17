<?php

// name of the language file that needs to be included
$language_file = 'exercice';

// including the global library
require_once ('../../inc/global.inc.php');
$interbreadcrumb[] = array("url" => "../exercice.php","name" => get_lang('Exercices'));

// setting the tabs
$this_section = SECTION_COURSES;

// access control
api_protect_course_script(true);

// read the exercise id
$exerciseId = intval($_GET['exerciceId']);