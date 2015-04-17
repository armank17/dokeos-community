<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 *	This is the certificate library for Dokeos.
 *	Include/require it in your code to use its functionality.
 */

class CertificateManager {

      protected $user_id;
      protected $course_code;
      protected $certif_tool_type;
      protected $certif_tool_id;
      protected $session_id;
      protected $certif_min_score;
      protected $user_score;
      protected $exe_attempt_id;

      public static function create() {
        return new CertificateManager();
      }

      public function __contruct() {}

      /**
       * Get formulary to add or edit a certificate
       * @param     string      Action (add, edit)
       * @param     int         Optional, template id
       * @return    void
       */
      public function getForm($action, $tpl_id = null) {

            require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';

            if (!in_array($action, array('add', 'edit'))) { return false; }

            // initiate the object
            $form = new FormValidator('certificatetemplate', 'post', api_get_self().'?action='.Security::remove_XSS($action).'&amp;id='.intval($tpl_id));

            $form->add_textfield('title', get_lang('Title'), false, array('id' => 'idTitle','size' => '50'));
            $form->addRule('title', '<div class="required">'.get_lang('ThisFieldIsRequired'), 'required');

            // settting the form elements: the form to upload an image to be used with the template
            $form->addElement('file','template_image',get_lang('Image'),'');
            //$form->addRule('template_image', get_lang('ThisFieldIsRequired'), 'uploadedfile');
            $allowed_picture_types = array ('jpg', 'jpeg', 'png', 'gif');
            $form->addRule('template_image', get_lang('OnlyImagesAllowed').' ('.implode(',', $allowed_picture_types).')', 'filetype', $allowed_picture_types);

            // settting the form elements: a little bit information about the template image
            $form->addElement('static', 'file_comment', '', get_lang('TemplateImageComment100x70'));

            // set the languages
            $form->addElement('select', 'language', get_lang('Language'),$this->getCboLanguagesValues());
            $defaults['language'] = api_get_interface_language();

            // settting the form elements: the content of the template (wysiwyg editor)
            $form->addElement('html_editor', 'template_text', '', null, array('ToolbarSet' => 'CertificateTemplates', 'Width' => '100%', 'Height' => '350'));
            $form->addRule('template_text', '<div class="required">'.get_lang('ThisFieldIsRequired'), 'required');

            $patterns = $this->getCertificatePatterns();
            $table_patterns = '';
            if (!empty($patterns)) {
                $table_patterns .= '<table class="data_table toggle">';
                $table_patterns .= '<thead><tr><th>'.get_lang('Name').'</th><th>'.get_lang('Token').'</th><th>'.get_lang('Description').'</th></tr></thead><tbody>';
                foreach ($patterns as $pattern) {
                    $table_patterns .= '<tr><td>'.$pattern['name'].'</td><td>'.$pattern['token'].'</td><td>'.$pattern['description'].'</td></tr>';
                }
                $table_patterns .= '</tbody></table>';
            }
            $form->addElement('static', '', get_lang('ReplacementPatterns'), $table_patterns);

            // settting the form elements: the submit button
            $form->addElement('style_submit_button' , 'submit', get_lang('Ok') ,'class="save"');

            if ($action == 'edit') {
                $form->addElement('hidden', 'tpl_id', $tpl_id);
                $certificate = $this->getCertificateInfo($tpl_id);
                $defaults['title'] = $certificate['title'];
                $defaults['template_text'] = $certificate['content'];
                $defaults['language'] = $certificate['language'];
            }

            // setting the information of the template that we are adding
            $form->setDefaults($defaults);

            if( $form->validate() ) {
                        // exporting the values
                        $values = $form->exportValues();
                        if ($_FILES['template_image']['error'] == 0) {
                            $values['thumbnail'] = $this->uploadThumbTemplate($_FILES['template_image']);
                        }
                        $saved = $this->saveCertificateTemplate($values);
                        if ($saved) {
                            $this->redirectPage('list', $values['language']);
                        }
            } else {
                // display the form
                $form->display();
            }
            //exit;
      }

      /**
       * Get language form
       * @param  string  Optional, selected language
       * @return void
       */
      public function getLanguageForm($selected = null) {
          require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
          $form = new FormValidator('certificatelang', 'post', api_get_self().'?action=list');
          $languages = $this->getCboLanguagesValues();
          $form->addElement('select', 'language', get_lang('Language'), $languages, 'onchange=change_language(this.value)');
          if (isset($selected)) {
              $defaults['language'] = $selected;
              $form->setDefaults($defaults);
          }


          //add button for to create templates
          $selected_lang = isset($_REQUEST['lang'])?$_REQUEST['lang']:api_get_interface_language();
          if(count($this->getCertificatesList($selected_lang))==0)
            $form->addElement('button', 'generate', 'Generate from English','onclick=generateCertificate(document.certificatelang.language.value) class="save"');

          $form->display();
      }
      /**
       * generation of certifications from English templates
       * @param string $lang
       */
      public function generateCertificationFromEnglish($lang)
      {
        $tbl_cert_template = Database::get_main_table(TABLE_MAIN_CERTIFICATE_TEMPLATE);
        //look if registry exist in the database
        if(count($this->getCertificatesList($lang))== 0)
        {
            $certificates = $this->getCertificatesList('english');

            foreach($certificates as $index=>$certificate)
            {
                $certificate['language'] = $lang;
                // insert
                Database::query("INSERT INTO $tbl_cert_template SET
                                title = '".(isset($certificate['title'])?Database::escape_string($certificate['title']):get_lang('Empty'))."',
                                content = '".(isset($certificate['content'])?Database::escape_string($certificate['content']):'')."',
                                language = '".(isset($certificate['language'])?Database::escape_string($certificate['language']):$lang)."',
                                creation_date = '".date('Y-m-d H:i:s')."'
                                ");
                $lastId = Database::insert_id();
                if ($lastId && !empty($certificate['thumbnail'])) {
                    Database::query("UPDATE $tbl_cert_template SET thumbnail='".Database::escape_string($certificate['thumbnail'])."' WHERE id='".$lastId."'");
                }
            }

        }

      }

      /**
       * Delete a certificate template
       * @param     int     Certificate id
       * @return    int     Affected rows
       */
      public function deleteCertificate($id) {
          $tbl_cert_template = Database::get_main_table(TABLE_MAIN_CERTIFICATE_TEMPLATE);
          Database::query("DELETE FROM $tbl_cert_template WHERE id = '".intval($id)."'");
          return Database::affected_rows();
      }

      /**
       * Save certificate template
       * @params   array    input values
       * @return   int      last insert id
       */
      public function saveCertificateTemplate($values) {
          $tbl_cert_template = Database::get_main_table(TABLE_MAIN_CERTIFICATE_TEMPLATE);
          $lastId = 0;
          if (isset($values['tpl_id'])) {
              // update
              Database::query("UPDATE $tbl_cert_template SET
                                title = '".(isset($values['title'])?Database::escape_string($values['title']):get_lang('Empty'))."',
                                content = '".(isset($values['template_text'])?Database::escape_string($values['template_text']):'')."',
                                language = '".(isset($values['language'])?Database::escape_string($values['language']):'english')."'
                               WHERE id = '".intval($values['tpl_id'])."'
                              ");
              $lastId = intval($values['tpl_id']);
              if ($lastId && !empty($values['thumbnail'])) {
                  Database::query("UPDATE $tbl_cert_template SET thumbnail='".Database::escape_string($values['thumbnail'])."' WHERE id='".$lastId."'");
              }
          }
          else {
              // insert
              Database::query("INSERT INTO $tbl_cert_template SET
                                title = '".(isset($values['title'])?Database::escape_string($values['title']):get_lang('Empty'))."',
                                content = '".(isset($values['template_text'])?Database::escape_string($values['template_text']):'')."',
                                language = '".(isset($values['language'])?Database::escape_string($values['language']):'english')."',
                                creation_date = '".date('Y-m-d H:i:s')."'
                              ");
              $lastId = Database::insert_id();
              if ($lastId && !empty($values['thumbnail'])) {
                  Database::query("UPDATE $tbl_cert_template SET thumbnail='".Database::escape_string($values['thumbnail'])."' WHERE id='".$lastId."'");
              }
          }
          return $lastId;
      }


      /**
       * Upload template thumbnail
       * @param  array  values in $_FILES['template_image']
       * @return string  new thumb path
       */
      public function uploadThumbTemplate($aFile) {

        require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';
        // Try to add an extension to the file if it hasn't one
        $new_file_name = add_ext_on_mime(stripslashes($aFile['name']), $aFile['type']);

        // upload dir
        $upload_dir = api_get_path(SYS_PATH).'home/default_platform_document/template_thumb/';

        // create dir if not exists
        if (!is_dir($upload_dir)) {
            $perm = api_get_setting('permissions_for_new_directories');
            $perm = octdec(!empty($perm)?$perm:'0770');
            $res = @mkdir($upload_dir,$perm);
        }

        // resize image to max default and upload
        require_once (api_get_path(LIBRARY_PATH).'image.lib.php');
        $temp = new image($_FILES['template_image']['tmp_name']);
        $picture_infos=@getimagesize($aFile['tmp_name']);

        $max_width_for_picture = 128;

        if ($picture_infos[0] > $max_width_for_picture) {
            $thumbwidth = $max_width_for_picture;
            if (empty($thumbwidth) or $thumbwidth==0) {
              $thumbwidth=$max_width_for_picture;
            }
            $new_height = round(($thumbwidth/$picture_infos[0])*$picture_infos[1]);
            $temp->resize($thumbwidth,$new_height,0);
        }

        $type=$picture_infos[2];

        switch (!empty($type)) {
                case 2 : $temp->send_image('JPG', $upload_dir.$new_file_name);
                         break;
                case 3 : $temp->send_image('PNG', $upload_dir.$new_file_name);
                         break;
                case 1 : $temp->send_image('GIF', $upload_dir.$new_file_name);
                         break;
        }
        return file_exists($upload_dir.$new_file_name)?$new_file_name:false;
      }

      /**
       * Get certificates list
       */
      public function getCertificatesList($lang = null) {
          $tbl_certificate_template = Database::get_main_table(TABLE_MAIN_CERTIFICATE_TEMPLATE);
          $list = array();
          $filter = ' WHERE 1=1';
          
          if (!isset($lang)) {
              $lang = 'english';
          }
          $filter .= ' AND language= "'.Database::escape_string($lang).'"';
          
          $rs = Database::query("SELECT id, title, thumbnail, content FROM $tbl_certificate_template $filter");
          if (Database::num_rows($rs) > 0) {
              while ($row = Database::fetch_array($rs, 'ASSOC')) {
                  $list[$row['id']] = $row;
              }
          }
          return $list;
      }

      /**
       * Get certificate information by id
       * @param     int     Certificate id
       * @return    array   Certificate information
       */
      public function getCertificateInfo($id) {
          $tbl_certificate_template = Database::get_main_table(TABLE_MAIN_CERTIFICATE_TEMPLATE);
          $info = array();
          $rs = Database::query("SELECT id, title, thumbnail, content, language FROM $tbl_certificate_template WHERE id='".intval($id)."'");
          if (Database::num_rows($rs) > 0) {
              $info = Database::fetch_array($rs, 'ASSOC');
          }

          return $info;
      }

      /**
       * Display certificates list
       * @param     array   Cerificate list
       * @return    void
       */
      public function displayCertificatesList($certificates) {
          $html = '';
          if (!empty($certificates)) {
              $html .= '<table class="gallery">';
              $i=0;
              $j=1;
              foreach ($certificates as $tpl_id => $certificate) {
                  if (!$i % 4) { $html .= '<tr>'; }
                  $html .= '<td>';
                  $html .= '	<div class="section">';
		  $html .= '        <div class="sectiontitle">'.$certificate['title'].'</div>
                                    <div class="sectioncontent">'.$this->returnCertificateThumbnailImg($certificate['id']).'</div>
                                    <div align="center">
                                        <a href="'.api_get_self().'?action=edit&tpl_id='.$certificate['id'].'">'.Display::return_icon('pixel.gif', get_lang("Edit"), array('class' => 'actionplaceholdericon actionedit')).'</a>&nbsp;&nbsp;
                                        <a href="'.api_get_self().'?action=delete&tpl_id='.$certificate['id'].'" onclick="javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;">'.Display::return_icon('pixel.gif', get_lang("Delete"), array('class' => 'actionplaceholdericon actiondelete')).'</a></div>
                                </div>';
                  $html .= '</td>';

                  if ($j == 4) {
			$html .= '</tr>';
			$j=0;
                  }

                  $i++;
		  $j++;
              }
              $html .= '</table>';
          }
          echo $html;
      }


      /**
       * Get languages values for a select input
       * @return    array
       */
      public function getCboLanguagesValues() {
          $cbo_languages = array();
          //$cbo_languages['none'] = get_lang('ChooseLanguage');
          $languages = api_get_languages();
          if (!empty($languages)) {
              foreach ($languages['params'] as $langforlder => $langname) {
                  $cbo_languages[$langforlder] = $langname;
              }
          }
          return $cbo_languages;
      }

      /**
       * Redirect to a certificate page by action
       */
      public function redirectPage($action, $lang = '') {

          echo '<script type="text/javascript">location.href="'.api_get_path(WEB_CODE_PATH).'admin/certificate_templates.php?action='.$action.(!empty($lang)?'&lang='.$lang:'').'"</script>';
          return false;
      }

      /**
       * Display certificate thumbnail
       * @param     int     Certificate id
       * @return    void
       */
      public function returnCertificateThumbnailImg($tpl_id, $scale = true) {
          $image = $attr = '';
          if ($scale) {
              $attr = 'width="128px" height="128px"';
          }
          if (!empty($tpl_id)) {
              $certificate = $this->getCertificateInfo($tpl_id);
              $image = Display::return_icon('emailtemplate.png');
              if (!empty($certificate['thumbnail'])) {
                  $image_syspath = api_get_path(SYS_PATH).'home/default_platform_document/template_thumb/'.$certificate['thumbnail'];
                  if (file_exists($image_syspath)) {                      
                      $image_webpath = api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/'.$certificate['thumbnail'];
                      $image = '<img src="'.$image_webpath.'" '.$attr.' />';
                  }
              }
          }
          return $image;
      }

      /**
       * Check if user is allowed to get a certificate
       * @param     int     User id
       * @param     string  Certificate tool type (module, quiz, session)
       * @param     int     Certificate tool id (if tool type is quiz, the id is of exercise last attempt id)
       * @param     string  Course code
       * @param     int     Session id
       * @return    bool    true if it is allowed
       */
      public function isUserAllowedGetCertificate($user_id, $certif_tool_type, $certif_tool_id, $course_code = null, $session_id = null) {

          // only when certificate is enabled
          if (api_get_setting('enable_certificate') !== 'true') {
              return false;
          }

          // Set current information
          $this->user = $user_id;
          $this->certif_tool_type = $certif_tool_type;
          $this->certif_tool_id = $certif_tool_id;
          $this->course_code = $course_code;
          $this->session_id = $session_id;


          $tools = array('quiz', 'module', 'session');
          $allowed = false;
          if (!in_array($certif_tool_type, $tools)) { return false; }

          switch ($certif_tool_type) {
              case 'quiz':
                  // @todo check if attempt quiz is the last one

                  $tbl_track_e_exe = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
                  $exe_attempt_id = intval($certif_tool_id);
                  $this->exe_attempt_id = $exe_attempt_id;
                  $score = 0;
                  // get attempt
                  $rs = Database::query("SELECT exe_result, exe_weighting, exe_exo_id FROM $tbl_track_e_exe WHERE exe_id = '".intval($exe_attempt_id)."'");
                  if (Database::num_rows($rs) > 0) {
                      $row = Database::fetch_array($rs, 'ASSOC');
                      $score = ($row['exe_result'] * 100)/$row['exe_weighting'];
                      // get certificate min score
                      $certif_tool_info = $this->getCertificateInfoByTool($certif_tool_type, $row['exe_exo_id'], $course_code);
                      $certif_min_score = !empty($certif_tool_info['certif_min_score'])?$certif_tool_info['certif_min_score']:0;
                      // Compare scores
                      if (!empty($certif_tool_info['certif_template']) && $score >= $certif_min_score) {
                          $allowed = true;
                      }
                      $this->certif_min_score = $certif_min_score;
                      $this->user_score = $score;
                      $this->certif_tool_id = $row['exe_exo_id'];
                  }
                  break;
              case 'module':
                  require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpath.class.php';
                  require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
                  $certif_tool_info = $this->getCertificateInfoByTool($certif_tool_type, $certif_tool_id, $course_code);
                  $certif_evalua_type = '';
                  $certif_evaluation  = 0;
                  if (!empty($certif_tool_info['certif_template'])) {
                      if (!empty($certif_tool_info['certif_min_score']) && $certif_tool_info['certif_min_score'] != '0.00') {
                          $certif_evalua_type = 'score';
                          $certif_evaluation  = $certif_tool_info['certif_min_score'];
                      } elseif (!empty($certif_tool_info['certif_min_progress']) && $certif_tool_info['certif_min_progress'] != '0.00') {
                          $certif_evalua_type = 'progress';
                          $certif_evaluation  = $certif_tool_info['certif_min_progress'];
                      }

                      $course_info = api_get_course_info($course_code);
                      if (!empty($certif_evalua_type)) {
                          if ($certif_evalua_type == 'progress') {
                              // get user progress in module
                              $mylp = new learnpath($course_code, $certif_tool_id, $user_id, $course_info['dbName']);
                              $progress = $mylp->get_progress_bar_text('%');
                              // Compare scores
                              if (!empty($certif_tool_info['certif_template']) && $progress[0] >= $certif_evaluation) {
                                  $allowed = true;
                              }
                              $this->certif_min_score = $certif_evaluation;
                              $this->user_score = $progress[0];
                          } else {
                              // get user score in module
                              $score = Tracking::get_avg_student_score($user_id, $course_code, array($certif_tool_id));
                              // Compare scores
                              if (!empty($certif_tool_info['certif_template']) && $score >= $certif_evaluation) {
                                  $allowed = true;
                              }
                              $this->certif_min_score = $certif_evaluation;
                              $this->user_score = $score;
                          }
                      }
                  }
                  break;
              case 'session':
                  require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
                  require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpath.class.php';
                  require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';

                  $certif_tool_info = $this->getCertificateInfoByTool($certif_tool_type, $certif_tool_id, null, $session_id);

                  // get all courses in selected session
                  $session_courses = SessionManager::get_course_list_by_session_id($certif_tool_id);
                  if (!empty($certif_tool_info['certif_template'])) {
                      if (!empty($certif_tool_info['certif_tool'])) {
                          if ($certif_tool_info['certif_tool'] == 'quiz') {

                              // check scores in quizzes by session
                              $total_result = 0;
                              $total_weighting = 0;
                              if (!empty($session_courses)) {
                                  foreach ($session_courses as $session_course) {
                                      // get last quiz score by course and session
                                      $score_info = $this->getLastScoreInfoQuiz($user_id, $session_course, $certif_tool_id);
                                      $total_result += $score_info['exe_result'];
                                      $total_weighting += $score_info['exe_weighting'];
                                  }
                              }
                              $total_score = $total_weighting > 0?($total_result*100)/$total_weighting:0;
                              // Compare scores
                              if (!empty($certif_tool_info['certif_template']) && $total_score >= $certif_tool_info['certif_min_score']) {
                                  $allowed = true;
                              }
                              $this->certif_min_score = $certif_tool_info['certif_min_score'];
                              $this->user_score = $total_score;
                          } else {
                              // check scores or progress in modules by session
                              $certif_evalua_type = '';
                              $certif_evaluation  = 0;
                              if (!empty($certif_tool_info['certif_min_score']) && $certif_tool_info['certif_min_score'] != 0.00) {
                                  $certif_evalua_type = 'score';
                                  $certif_evaluation  = $certif_tool_info['certif_min_score'];
                              } elseif (!empty($certif_tool_info['certif_min_progress']) && $certif_tool_info['certif_min_progress'] != 0.00) {
                                  $certif_evalua_type = 'progress';
                                  $certif_evaluation  = $certif_tool_info['certif_min_progress'];
                              }

                              if (!empty($certif_evalua_type)) {
                                  if ($certif_evalua_type == 'progress') {
                                      $total_progress = 0;
                                      if (!empty($session_courses)) {
                                            foreach ($session_courses as $session_course) {
                                                $course_info = api_get_course_info($session_course);
                                                // get lps by course
                                                $tbl_lp = Database::get_course_table(TABLE_LP_MAIN, $course_info['dbName']);
                                                $rs_lp = Database::query("SELECT id FROM $tbl_lp WHERE session_id='".intval($certif_tool_id)."'");
                                                if (Database::num_rows($rs_lp) > 0) {
                                                    while ($row_lp = Database::fetch_array($rs_lp, 'ASSOC')) {
                                                        $lp_id = $row_lp['id'];
                                                        // get user progress in module
                                                        $mylp = new learnpath($session_course, $lp_id, $user_id, $course_info['dbName']);
                                                        $progress = $mylp->get_progress_bar_text('%');
                                                        $total_progress += $progress[0];
                                                    }
                                                }
                                            }
                                      }
                                      // Compare scores
                                      if (!empty($certif_tool_info['certif_template']) && $total_progress >= $certif_evaluation) {
                                          $allowed = true;
                                      }
                                      $this->certif_min_score = $certif_evaluation;
                                      $this->user_score = $total_progress;
                                  } else {
                                      $total_score = 0;
                                      if (!empty($session_courses)) {
                                            foreach ($session_courses as $session_course) {
                                                $course_info = api_get_course_info($session_course);
                                                // get lps by course
                                                $tbl_lp = Database::get_course_table(TABLE_LP_MAIN, $course_info['dbName']);
                                                $rs_lp = Database::query("SELECT id FROM $tbl_lp WHERE session_id='".intval($certif_tool_id)."'");
                                                if (Database::num_rows($rs_lp) > 0) {
                                                    while ($row_lp = Database::fetch_array($rs_lp, 'ASSOC')) {
                                                        $lp_id = $row_lp['id'];
                                                        // get user score in module
                                                        $total_score += Tracking::get_avg_student_score($user_id, $course_code, array ($lp_id));

                                                    }
                                                }
                                            }
                                      }
                                      // Compare scores
                                      if (!empty($certif_tool_info['certif_template']) && $total_score >= $certif_evaluation) {
                                          $allowed = true;
                                      }
                                      $this->certif_min_score = $certif_evaluation;
                                      $this->user_score = $total_score;
                                  }
                              }
                          }
                      }
                  }
                  break;
          }
          return $allowed;
      }

      /**
       * Get last quiz score by course and session
       */
      public function getLastScoreInfoQuiz($user_id, $course_code, $session_id = 0, $lp_id = 0, $lp_item_id = 0, $exe_exo_id = null) {
          $aScore = array();
          $tbl_stats_exercices = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
          $filter_exe_exo_id = '';
          if (isset($exe_exo_id)) {
              $filter_exe_exo_id .= ' AND exe_exo_id='.intval($exe_exo_id);
          }
          $rs = Database::query('SELECT exe_result, exe_weighting, exe_exo_id, exe_id
                                    FROM ' . $tbl_stats_exercices . '
                                    WHERE exe_user_id="' . intval($user_id) . '"
                                          AND exe_cours_id="'.Database::escape_string($course_code).'"
                                          AND session_id = '.intval($session_id).'
                                          AND orig_lp_id = '.intval($lp_id).'
                                          AND orig_lp_item_id = '.intval($lp_item_id).'
                                          AND status <> "incomplete"
                                          '.$filter_exe_exo_id.'
                                    ORDER BY exe_id DESC LIMIT 1');
          if (Database::num_rows($rs) >  0) {
              $aScore = Database::fetch_array($rs, 'ASSOC');
          }
          return $aScore;
      }

      /**
       * Get certificate info by tool
       * @param     string  Certificate tool type
       * @param     int     Certificate tool id
       * @param     string  Course code
       * @param     int     Session id
       * @return    array   Certificate information by tool
       */
      public function getCertificateInfoByTool($certif_tool_type, $certif_tool_id, $course_code = null, $session_id = null) {
          $tools = array('quiz', 'module', 'session');
          $allowed = false;
          if (!in_array($certif_tool_type, $tools)) { return false; }
          $info = array();
          switch ($certif_tool_type) {
              case 'quiz':
                  // get certificate min score
                  $course_info = api_get_course_info($course_code);
                  $tbl_quiz = Database::get_course_table(TABLE_QUIZ_TEST, $course_info['dbName']);
                  $rs = Database::query("SELECT certif_min_score, certif_template FROM $tbl_quiz WHERE id='".intval($certif_tool_id)."'");
                  if (Database::num_rows($rs) > 0) {
                      $info = Database::fetch_array($rs, 'ASSOC');
                  }
                  break;
              case 'module':
                  // get certificate min score
                  $course_info = api_get_course_info($course_code);
                  $tbl_lp = Database::get_course_table(TABLE_LP_MAIN, $course_info['dbName']);
                  $rs = Database::query("SELECT certif_min_score, certif_min_progress, certif_template FROM $tbl_lp WHERE id='".intval($certif_tool_id)."'");
                  if (Database::num_rows($rs) > 0) {
                      $info = Database::fetch_array($rs, 'ASSOC');
                  }
                  break;
               case 'session':
                   // get certificate min score for session
                   $tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
                   $rs = Database::query("SELECT certif_template, certif_tool, certif_min_score, certif_min_progress FROM $tbl_session WHERE id='".intval($certif_tool_id)."'");
                   if (Database::num_rows($rs) > 0) {
                       $info = Database::fetch_array($rs, 'ASSOC');
                   }
                   break;
          }
          return $info;
      }

      /**
       * Display certificate content
       * @param     string  Mode (html, pdf)
       * @param     string  Certificate tool type
       * @param     int     Certificate tool id
       * @param     string  Course code
       * @param     int     Session id
       */
      public function displayCertificate($mode, $certif_tool_type, $certif_tool_id, $course_code = null, $session_id = null, $dialog = false) {
          $modes = array('html', 'pdf');
          $certif_tool_info = $this->getCertificateInfoByTool($certif_tool_type, $certif_tool_id, $course_code, $session_id);
          if (!in_array($mode, $modes)) { return false; }
          if (empty($certif_tool_info)) { return false; }
          switch ($mode) {
              case 'html':
                  echo $this->displayCertificateHtml($certif_tool_info['certif_template'], $certif_tool_type, $course_code, $dialog, $certif_tool_id);
                  return;
              case 'pdf':

                  break;
          }
      }

      /**
       * Display certificate in html
       * @param     int     Certificate id
       * @return    void
       */
      public function displayCertificateHtml($certif_id, $certif_tool_type, $course_code = null, $dialog = false, $certif_tool_id = null) {
          $certificate_info = $this->getCertificateInfo($certif_id);

          $html = '';
          $link_export = '';
          if ($certif_tool_type == 'quiz' || $certif_tool_type == 'module') {
              $cert_tool_id = isset($this->exe_attempt_id)?intval($this->exe_attempt_id):$certif_tool_id;
              $link_export = api_get_path(WEB_CODE_PATH).'exercice/exercise_certificate_export.php?cidReq='.$course_code.'&export=pdf&tpl_id='.$certif_id.'&certif_tool_id='.$cert_tool_id.'&certif_tool_type='.$certif_tool_type;
          } else if ($certif_tool_type == 'session') {
              $link_export = api_get_path(WEB_PATH).'user_portal.php?export=pdf&tpl_id='.$certif_id.'&certif_tool_id='.$certif_tool_id.'&certif_tool_type='.$certif_tool_type;
          }

          // get values of the patterns in the content
          $certificate_info['content'] = $this->replacePatternCertificateContent($certificate_info['content']);

            $browser = $_SERVER['HTTP_USER_AGENT'];
            $browser = substr($browser, 25, 8);
            if ($browser == "MSIE 6.0"){
                $checkie6 = '830px';
            }
            else{
                $checkie6 = '820px';
            }

          // content in html
          if (!empty($certificate_info['content'])) {
                if ($dialog) {

                    $html .= '<script type="text/javascript">
                                $(document).ready(function() {
                                    
                                    $(".certificate-'.$certif_tool_id.'-link").click(function(e){
                                        $("#certificate-'.$certif_tool_id.'-content").dialog({
                                             height:650,
                                             width: 1110,
                                             modal: true,
                                            open: function(event, ui) {
                                                $(event.target).dialog("widget")
                                                    .css({ position: "fixed" })
                                                    .position({ my: "center", at: "center", of: window });
                                            }                                             
                                        });
                                    });
                                });
                              </script>';
                }



                $html .= '<script type="text/javascript">
                                $(document).ready(function() {
                                if ($("#content_with_secondary_actions").length > 0) {
                                    $("#content_with_secondary_actions").css("height", "860px");
                                }
                                if ($("iframe#content_id").length > 0) {
                                    $("iframe#content_id").css("height", "800px");
                                }
                                });
                        </script>';

                $html .= '<div style="'.($dialog?'display:none':'display:block').';height:756px;width:1070px;">';
               
                $html .= '<div id="certificate-'.$certif_tool_id.'-content">';
                if (!empty($link_export)) {
                   $html .= '<div style="width: 100%;height:50px;position:absolute; top:40px; left: 50px;"><a href="'.$link_export.'" style="margin-left: '.$ml.'px; line-height: 50px;text-decoration:none;"">'.Display::return_icon('48x48file_pdf.png', get_lang('Export'), array('style'=>'vertical-align:middle;')).' '.get_lang('Export').'</a></div>';
               }
               $html .= '';
               $html .= ($dialog) ? html_entity_decode($certificate_info['content']) : utf8_encode($certificate_info['content']);
               $html .= '</div>';
               $html .= '</div>';
          }

          echo $html;

      }

      /**
       * Display certificate in pdf
       * @param     int     Certificate id
       * @return    void
       */
       public function displayCertificatePdf($certif_id) {
          global $language_interface;

          require_once api_get_path(LIBRARY_PATH).'html2pdf/html2pdf.class.php';
          $certificate_info = $this->getCertificateInfo($certif_id);
          // get values of the patterns in the content
          $certificate_info['content'] = $this->replacePatternCertificateContent($certificate_info['content']);
          $path_certif_image = '/main/default_course_document/';

          // replace url
          if (strpos($certificate_info['content'], $path_certif_image) !== FALSE) {
              $certificate_info['content'] = str_replace($path_certif_image, api_get_path(WEB_CODE_PATH).'default_course_document/', $certificate_info['content']);
          }

          if (!empty($certificate_info['content'])) {
                ob_start();
                ?>
                <page backtop="0mm" backbottom="0mm" backleft="3mm" backright="3mm">
                    <?php echo  '<div class="bg-certificate01" style="background-image:url('.api_get_path(WEB_CODE_PATH).'default_course_document/images/templates/certificates/'.$certificate_info['title'].'.jpg);background-repeat:no-repeat;height:756px;width:1070px;margin:0px;">'; ?>
                    <?php echo $certificate_info['content']; ?>
                    <?php echo '</div>'; ?>
                </page>
                <?php
                $content = ob_get_contents();
                ob_end_clean();

                // convert in PDF
                try {
                    @$langhtml2pdf = api_get_language_isocode($language_interface);
                    // Some code translations are needed.
                    $langhtml2pdf = strtolower(str_replace('_', '-', $langhtml2pdf));
                    if (empty ($langhtml2pdf)) {
                            $langhtml2pdf = 'en';
                    }
                    switch ($langhtml2pdf) {
                        case 'uk':
                                $langhtml2pdf = 'ukr';
                                break;
                        case 'pt':
                                $langhtml2pdf = 'pt_pt';
                                break;
                        case 'pt-br':
                                $langhtml2pdf = 'pt_br';
                                break;
                    }

                    // Checking for availability of a corresponding language file.
                    if (!file_exists(api_get_path(SYS_PATH).'main/inc/lib/html2pdf/langues/'.$langhtml2pdf.'.txt')) {
                            // If there was no language file, use the english one.
                            $langhtml2pdf = 'en';
                    }
                    $html2pdf = new HTML2PDF('L', 'A4', $langhtml2pdf, true, 'UTF-8', array(3,0, 3, 0));
                    $content = api_convert_encoding($content, 'UTF-8', api_get_system_encoding());
                    $html2pdf->writeHTML($content);
                    $html2pdf->Output('certificate-'.$certif_id.'.pdf');
                    exit;
                } catch(HTML2PDF_exception $e) {
                    echo $e;
                    exit;
                }
          }
      }

      /**
       * Get certificate patterns to use it in the editor
       * @return    array   The patterns
       */
      public function getCertificatePatterns() {
          $patters = array(
              array('name'=> get_lang('StudentFirstNamePatternTitle'), 'token'=>'{StudentFirstName}', 'description'=>get_lang('StudentFirstNamePatternDescription')),
              array('name'=> get_lang('StudentLastNamePatternTitle'), 'token'=>'{StudentLastName}', 'description'=>get_lang('StudentLastNamePatternDescription')),
              array('name'=> get_lang('StudentFullNamePatternTitle'), 'token'=>'{StudentFullName}', 'description'=>get_lang('StudentFullNamePatternDescription')),
              array('name'=> get_lang('TrainerFirstNamePatternTitle'), 'token'=>'{TrainerFirstName}', 'description'=>get_lang('TrainerFirstNamePatternDescription')),
              array('name'=> get_lang('TrainerLastNamePatternTitle'), 'token'=>'{TrainerLastName}', 'description'=>get_lang('TrainerLastNamePatternDescription')),
              array('name'=> get_lang('TrainerFullNamePatternTitle'), 'token'=>'{TrainerFullName}', 'description'=>get_lang('TrainerFullNamePatternDescription')),
              array('name'=> get_lang('DatePatternTitle'), 'token'=>'{Date}', 'description'=>get_lang('DatePatternDescription')),
              array('name'=> get_lang('ModuleNamePatternTitle'), 'token'=>'{ModuleName}', 'description'=>get_lang('ModuleNamePatternDescription')),
              array('name'=> get_lang('CertificateMinScorePatternTitle'), 'token'=>'{CertificateMinScore}', 'description'=>get_lang('CertificateMinScorePatternDescription')),
              array('name'=> get_lang('UserScorePatternTitle'), 'token'=>'{UserScore}', 'description'=>get_lang('UserScorePatternDescription')),
              array('name'=> get_lang('SiteNamePatternTitle'), 'token'=>'{SiteName}', 'description'=>get_lang('SiteNamePatternDescription')),
              array('name'=> get_lang('SiteUrlPatternTitle'), 'token'=>'{SiteUrl}', 'description'=>get_lang('SiteUrlPatternDescription'))
          );
          return $patters;
      }

      /**
       * Replacement patterns in certificate content
       * @param     string  Certificate content
       * @return    string  The new certificate content
       */
      public function replacePatternCertificateContent($content) {
          $tokens = $this->getPatternsTokenName();
          if (!empty($tokens)) {
              foreach ($tokens as $token) {
                  if (strpos($content, $token) !== FALSE) {
                      $token_value = $this->getPatternTokenValue($token);
                      $content = str_replace($token, $token_value, $content);
                  }
              }
          }
          return $content;
      }


      /**
       * Get patterns tokens
       * @return    array   Pattern Tokens
       */
      public function getPatternsTokenName() {
          // get tokens
          $tokens = array();
          $patterns = $this->getCertificatePatterns();
          if (!empty($patterns)) {
              foreach ($patterns as $pattern) {
                  $tokens[] = $pattern['token'];
              }
          }
          return $tokens;
      }

      /**
       * Get pattern values
       * @param     string      Pattern token (example: {FirstName})
       * @return    mixed       Pattern value
       */
      public function getPatternTokenValue($variable) {
          $value = '';

          if (in_array($variable, $this->getPatternsTokenName())) {
              switch ($variable) {
                  case '{StudentFirstName}':
                      $student_info = api_get_user_info($this->user);
                      $value = $student_info['firstname'];
                      break;
                  case '{StudentLastName}':
                      $student_info = api_get_user_info($this->user);
                      $value = $student_info['lastname'];
                      break;
                  case '{StudentFullName}':
                      $student_info = api_get_user_info($this->user);
                      $value = api_get_person_name($student_info['firstname'], $student_info['lastname']);
                      break;
                  case '{TrainerFirstName}':
                      require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
                      require_once api_get_path(LIBRARY_PATH).'course.lib.php';
                      if ($this->certif_tool_type == 'session') {
                          // get session coach
                          $session_info = api_get_session_info($this->certif_tool_id);
                          $coach_info   = api_get_user_info($session_info['id_coach']);
                          $value = $coach_info['firstname'];
                      } else {
                          // get course trainer
                          $teachers = CourseManager::get_teacher_list_from_course_code($this->course_code);
                          $first_teacher_id = array_shift($teachers);
                          $value = $first_teacher_id['firstname'];
                      }
                      break;
                  case '{TrainerLastName}':
                      require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
                      require_once api_get_path(LIBRARY_PATH).'course.lib.php';
                      if ($this->certif_tool_type == 'session') {
                          // get session coach
                          $session_info = api_get_session_info($this->certif_tool_id);
                          $coach_info   = api_get_user_info($session_info['id_coach']);
                          $value = $coach_info['lastname'];
                      } else {
                          // get course trainer
                          $teachers = CourseManager::get_teacher_list_from_course_code($this->course_code);
                          $first_teacher_id = array_shift($teachers);
                          $value = $first_teacher_id['lastname'];
                      }
                      break;
                  case '{TrainerFullName}':
                      require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
                      require_once api_get_path(LIBRARY_PATH).'course.lib.php';
                      if ($this->certif_tool_type == 'session') {
                          // get session coach
                          $session_info = api_get_session_info($this->certif_tool_id);
                          $coach_info   = api_get_user_info($session_info['id_coach']);
                          $value = api_get_person_name($coach_info['firstname'], $coach_info['lastname']);
                      } else {
                          // get course trainer
                          $teachers = CourseManager::get_teacher_list_from_course_code($this->course_code);
                          $first_teacher_id = array_shift($teachers);
                          $value = api_get_person_name($first_teacher_id['firstname'], $first_teacher_id['lastname']);
                      }
                      break;
                  case '{Date}':
                      $value = date('d/m/Y');
                      break;
                  case '{ModuleName}':
                      if ($this->certif_tool_type == 'session') {
                          $session_info = api_get_session_info($this->certif_tool_id);
                          $value = $session_info['name'];
                      } else if ($this->certif_tool_type == 'quiz') {
                          $course_info = api_get_course_info($this->course_code);
                          $tbl_quiz = Database::get_course_table(TABLE_QUIZ_TEST, $course_info['dbName']);
                          $rs = Database::query("SELECT title FROM $tbl_quiz WHERE id = '".intval($this->certif_tool_id)."'");
                          if (Database::num_rows($rs) > 0) {
                              $row = Database::fetch_array($rs, 'ASSOC');
                              $value = $row['title'];
                          }
                      } else if ($this->certif_tool_type == 'module') {
                          $course_info = api_get_course_info($this->course_code);
                          $tbl_lp = Database::get_course_table(TABLE_LP_MAIN, $course_info['dbName']);
                          $rs = Database::query("SELECT name FROM $tbl_lp WHERE id = '".intval($this->certif_tool_id)."'");
                          if (Database::num_rows($rs) > 0) {
                              $row = Database::fetch_array($rs, 'ASSOC');
                              $value = $row['name'];
                          }
                      }
                      break;
                  case '{SiteName}':
                      $value = api_get_setting('siteName');
                      break;
                  case '{CertificateMinScore}':
                      $value = round($this->certif_min_score);
                      break;
                  case '{UserScore}':
                      $value = round($this->user_score);
                      break;
                  case '{SiteUrl}':
                      $value = api_get_path(WEB_PATH);
                      break;
              }
          }
          return $value;
      }


} //end class
?>
