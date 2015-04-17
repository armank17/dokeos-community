<?php
require_once DIRNAME(__FILE__) . '/../../global.inc.php';

// Loading document repositories settings.
require_once api_get_path(LIBRARY_PATH).'ckeditor/repository.php' ;

// dynamic folder webpath used for streaming folder
$webpath = api_get_path(WEB_PATH);
$webpath = str_replace('http://', '', $webpath);
$webpath = str_replace('https://', '', $webpath);
$webpath = rtrim($webpath,'/');
$webpath = urlencode($webpath);

// shared folder path, generally it is used for students
$shared_folder = 'sf_user_'.api_get_user_id();

// Get dinamic path type depend on permissions
if (api_is_in_course()) {
    if (!api_is_in_group()) {
        $fb_imagetype = api_is_allowed_to_edit()?'images':$shared_folder;
        $fb_mascottype = api_is_allowed_to_edit()?'mascot':$shared_folder;
        $fb_mindmaptype = api_is_allowed_to_edit()?'mindmaps':$shared_folder;
        $fb_flashtype = api_is_allowed_to_edit()?'animations':$shared_folder;
        $fb_audiotype = api_is_allowed_to_edit()?'audio':$shared_folder;
        $fb_videotype = api_is_allowed_to_edit()?'video':$shared_folder;
    } else {
        global $group_properties;
        $fb_imagetype = $fb_mascottype = $fb_mindmaptype = $fb_flashtype = $fb_audiotype = $fb_videotype = $group_properties['directory'];  
    }
} else {
    if (api_is_platform_admin()) {
        $fb_imagetype = api_is_allowed_to_edit()?'images':$shared_folder;
        $fb_mascottype = api_is_allowed_to_edit()?'mascot':$shared_folder;
        $fb_mindmaptype = api_is_allowed_to_edit()?'mindmaps':$shared_folder;
        $fb_flashtype = api_is_allowed_to_edit()?'animations':$shared_folder;
        $fb_audiotype = api_is_allowed_to_edit()?'audio':$shared_folder;
        $fb_videotype = api_is_allowed_to_edit()?'video':$shared_folder;
    } else {
        $fb_imagetype = $fb_mascottype = $fb_mindmaptype = $fb_flashtype = $fb_audiotype = $fb_videotype = 'my_files';  
    }
}

$baseHref = api_get_path(WEB_PATH);
$urlAppend = $_configuration['url_append'];
echo <<<CONFIG
CKEDITOR.editorConfig = function(config) {
    config.enterMode = CKEDITOR.ENTER_BR;
    config.autoParagraph = false;
    config.fillEmptyBlocks = false;
    config.ignoreEmptyParagraph = true;
    config.defaultLanguage = 'en';
    config.language = 'en';
    // Define if the toolbar can be collapsed by the user. If disabled, the collapser button will not be displayed
    config.toolbarCanCollapse = false;

    config.filebrowserBrowseUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/browse.php?type=document&lms=dokeos';
    config.filebrowserImageBrowseUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/browse.php?type={$fb_imagetype}&lms=dokeos';
    config.filebrowserMascotmanagerBrowseUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/browse.php?type={$fb_mascottype}&lms=dokeos';
    config.filebrowserMindmapsBrowseUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/browse.php?type={$fb_mindmaptype}&lms=dokeos';
    config.filebrowserFlashBrowseUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/browse.php?type={$fb_flashtype}&lms=dokeos';
    config.filebrowserVideoplayerBrowseUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/browse.php?type={$fb_flashtype}&lms=dokeos';
    config.filebrowserAudioBrowseUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/browse.php?type={$fb_audiotype}&lms=dokeos';
    config.filebrowserVideoBrowseUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/browse.php?type={$fb_videotype}&lms=dokeos';
    config.filebrowserImagemanagerBrowseUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/browse.php?type={$fb_imagetype}&lms=dokeos';
    config.filebrowserStreamingBrowseUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/browse.php?type={$webpath}&lms=dokeos';  

    config.filebrowserUploadUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/upload.php?type=document&lms=dokeos';
    config.filebrowserImageUploadUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/upload.php?type={$fb_imagetype}&lms=dokeos';
    config.filebrowserMascotmanagerUploadUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/upload.php?type={$fb_mascottype}&lms=dokeos';
    config.filebrowserMindmapsUploadUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/upload.php?type={$fb_mindmaptype}&lms=dokeos';
    config.filebrowserFlashUploadUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/upload.php?type={$fb_flashtype}&lms=dokeos';
    config.filebrowserVideoplayerUploadUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/upload.php?type={$fb_flashtype}&lms=dokeos';
    config.filebrowserAudioUploadUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/upload.php?type={$fb_audiotype}&lms=dokeos';
    config.filebrowserVideoUploadUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/upload.php?type={$fb_videotype}&lms=dokeos';
    config.filebrowserImagemanagerUploadUrl = '{$urlAppend}/main/inc/lib/ckeditor/kcfinder/upload.php?type={$fb_imagetype}&lms=dokeos';   

    config.fileImgMapUrl = '{$urlAppend}/main/inc/lib/ckeditor/plugins/imgmap/popup.html?lms=dokeos';
        
   config.toolbar = 'Notebook';
   config.toolbar_Notebook =
        [
          [ 'Undo','Redo'] ,
          [ 'Link','Unlink' ],
          [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio'],
          [ 'SpecialChar'],
          [ 'NumberedList','BulletedList','-','Outdent','Indent','-','TextColor'],
          [ 'Font','FontSize'],
          [ 'Bold','Italic','Underline'],
          [ 'JustifyLeft','JustifyCenter','JustifyRight']
        ];

   config.toolbar = 'NotebookStudent';
   config.toolbar_NotebookStudent =
        [
                [ 'Undo','Redo'],
                [ 'Link','Unlink' ],
                [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio'],
                [ 'SpecialChar'],
                [ 'NumberedList','BulletedList','-','Outdent','Indent','-','TextColor'],
                [ 'Font','FontSize'],
                [ 'Bold','Italic','Underline'],
                [ 'JustifyLeft','JustifyCenter','JustifyRight']
        ];

   config.toolbar = 'Documents';
   config.toolbar_Documents =
        [
                [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo'] ,
                [ 'Link','Unlink','Anchor'] ,
                [ 'ImageManager','MascotManager','-','Mindmaps','Audio','VideoPlayer','Streaming', 'ImgMap','Iframe'] ,
                [ 'asciimath','Table','HorizontalRule', 'SpecialChar','ShowBlocks'] ,
                [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor' ] ,
                [ 'Source'] ,
                [ 'Styles'],['Format','Font','FontSize'] ,
                [ 'Bold','Italic','Underline'] ,
                [ 'JustifyLeft','JustifyCenter','JustifyRight']

        ];
   
   config.toolbar = 'Authoring';
   config.toolbar_Authoring =
        [
                 ['Format','Font','FontSize'],
                 ['Bold','Italic','Underline'],
                 ['JustifyLeft','JustifyCenter','JustifyRight'],
                 ['NumberedList','BulletedList','-','Outdent','Indent','TextColor'],
                 ['Link','Unlink','Anchor'],
                 ['asciimath','Table'],
                 ['Maximize','-','PasteFromWord'],
                 ['Source']
        ];
   
   config.toolbar = 'Node';
   config.toolbar_Node =
        [
                 ['Format','Font','FontSize'],
                 ['Bold','Italic','Underline'],
                 [ 'ImageManager','MascotManager','-','Mindmaps','Audio','VideoPlayer','Streaming', 'ImgMap','Iframe'] ,
                 ['JustifyLeft','JustifyCenter','JustifyRight'],
                 ['NumberedList','BulletedList','-','Outdent','Indent','TextColor'],
                 ['Link','Unlink','Anchor'],
                 ['asciimath','Table'],
                 ['Maximize','-','PasteFromWord'],
                 ['Source']
        ];

    config.toolbar = 'node_Notice';
   config.toolbar_node_Notice =
        [
                 ['Format','Font','FontSize'],
                 ['Bold','Italic','Underline'],
                 ['JustifyLeft','JustifyCenter','JustifyRight'],
                 ['NumberedList','BulletedList','-','Outdent','Indent','TextColor'],
                 ['Link','Unlink','Anchor'],
                 ['asciimath','Table'],
                 ['Maximize','-','PasteFromWord'],
                 ['Source']
        ];
       
   config.toolbar = 'DocumentsStudent';
   config.toolbar_DocumentsStudent =
        [
                [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo'] ,
                [ 'Link','Unlink','Anchor'] ,
                [ 'ImageManager','MascotManager','-','Mindmaps','Audio','VideoPlayer','ImgMap','Iframe'] ,
                [ 'asciimath','Table','HorizontalRule', 'SpecialChar','ShowBlocks'] ,
                [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor' ] ,
                [ 'Source'] ,
                [ 'Styles'],['Format','Font','FontSize'] ,
                [ 'Bold','Italic','Underline'] ,
                [ 'JustifyLeft','JustifyCenter','JustifyRight']
        ];

   config.toolbar = 'TrainingDescription';
   config.toolbar_TrainingDescription =
                [
                         [ 'Undo','Redo'] ,
                         [ 'Link','Unlink'] ,
                         [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio'] ,
                         [ 'SpecialChar'] ,
                         [ 'NumberedList','BulletedList','-','TextColor'] ,
                         [ 'Font','FontSize'] ,
                         [ 'Bold','Italic','Underline'] ,
                         [ 'JustifyLeft','JustifyCenter','JustifyRight'] ,
                         [ 'Source']
                ];
   config.toolbar = 'LearningPathDocuments';
   config.toolbar_LearningPathDocuments =
        [
                 [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo'] ,
                 [ 'Link','Unlink'] ,
                 [ 'ImageManager','MascotManager','Mindmaps','Audio','VideoPlayer','ImgMap'] ,
                 [ 'asciimath','Table','HorizontalRule', 'SpecialChar','ShowBlocks'] ,
                 [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor' ] ,
                 [ 'Source'] ,
                 [ 'Styles'],['Format','Font','FontSize'] ,
                 [ 'Bold','Italic','Underline'] ,
                 [ 'JustifyLeft','JustifyCenter','JustifyRight']
        ];

   config.toolbar = 'Glossary';
   config.toolbar_Glossary =
                [
                         [ 'Undo','Redo'] ,
                         [ 'Link','Unlink'] ,
                         [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio'] ,
                         [ 'SpecialChar'] ,
                         [ 'NumberedList','BulletedList','-','TextColor'] ,
                         [ 'Font','FontSize'] ,
                         [ 'Bold','Italic','Underline'] ,
                         [ 'JustifyLeft','JustifyCenter','JustifyRight'] ,
                         [ 'Source']
                ];

    config.toolbar = 'Announcements';
    config.toolbar_Announcements =
		[
			 [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo' ] ,
			 [ 'Link','Unlink' ] ,
			 [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio','Smiley'] ,
			 [ 'Table'] ,
			 [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor'] ,
			 [ 'Bold','Italic','Underline'] ,
			 [ 'JustifyLeft','JustifyCenter','JustifyRight'] ,
			 [ 'Format','Font','FontSize','Source']
		];

    config.toolbar = 'AnnouncementsStudent';
    config.toolbar_AnnouncementsStudent =
		[
			 [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo' ] ,
			 [ 'Link','Unlink' ] ,
			 [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio','Smiley'] ,
			 [ 'Table'] ,
			 [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor'] ,
			 [ 'Bold','Italic','Underline'] ,
			 [ 'JustifyLeft','JustifyCenter','JustifyRight'] ,
			 [ 'Format','Font','FontSize','Source']
		];

   config.toolbar = 'AgendaStudent';
   config.toolbar_AgendaStudent =
        [
                 [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo' ] ,
                 [ 'Link','Unlink' ] ,
                 [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio','Smiley','PageBreak','Iframe'] ,
                 [ 'Table','HorizontalRule', 'SpecialChar','ShowBlocks'] ,
                 [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor'] ,
                 [ 'Bold','Italic','Underline'] ,
                 [ 'JustifyLeft','JustifyCenter','JustifyRight'],['Styles'] ,
                 [ 'Format','Font','FontSize']
        ];

   config.toolbar = 'Agenda';
   config.toolbar_Agenda =
        [
                 [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo' ] ,
                 [ 'Link','Unlink' ] ,
                 ['ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio','Smiley','PageBreak','Iframe'] ,
                 [ 'Table','HorizontalRule', 'SpecialChar','ShowBlocks'] ,
                 [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor'] ,
                 [ 'Bold','Italic','Underline'] ,
                 [ 'JustifyLeft','JustifyCenter','JustifyRight'],['Styles'] ,
                 [ 'Format','Font','FontSize']
        ];

   config.toolbar = 'Survey';
   config.toolbar_Survey =
        [
                 [ 'Maximize','-','PasteFromWord','-','Undo','Redo' ] ,
                 [ 'Link','Unlink' ] ,
                 [ 'ImageManager','MascotManager'] ,
                 [ 'Table', 'SpecialChar'] ,
                 [ 'NumberedList','BulletedList','TextColor'] ,
                 [ 'Bold','Italic','Underline','Font'] ,
                 [ 'FontSize']
        ];

   config.toolbar = 'Project';
   config.toolbar_Project =
        [
                 [ 'Format','Font','FontSize'],
                 ['Styles'],
                 [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo','Link','Unlink' ] ,                 
                 [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio','Smiley'] ,
                 [ 'Table', 'SpecialChar'] ,
                 [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor'] ,
                 [ 'Bold','Italic','Underline','JustifyLeft','JustifyCenter','JustifyRight']     
        ];

   config.toolbar = 'ProjectStudent';
   config.toolbar_ProjectStudent =
        [
                 [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo' ] ,
                 [ 'Link','Unlink' ] ,
                 [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio','Smiley'] ,
                 [ 'Table', 'SpecialChar'] ,
                 [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor'] ,
                 [ 'Bold','Italic','Underline'] ,
                 [ 'JustifyLeft','JustifyCenter','JustifyRight'],['Styles'] ,
                 [ 'Format','Font','FontSize']
        ];

   config.toolbar = 'Introduction';
   config.toolbar_Introduction =
        [
                 [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo','CreateDiv' ] ,
                 [ 'Link','Unlink' ] ,
                 [ 'ImageManager','MascotManager','Mindmaps','ImgMap', 'VideoPlayer','Audio','Smiley','PageBreak'] ,
                 [ 'Table','HorizontalRule', 'asciimath', 'SpecialChar','ShowBlocks'] ,
                 [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor'] ,
                 [ 'Bold','Italic','Underline'] ,
                 [ 'JustifyLeft','JustifyCenter','JustifyRight'] ,
                 [ 'Format','Font','FontSize'] ,
                 [ 'Source']
        ];

   config.toolbar = 'Forum';
   config.toolbar_Forum =
        [
                 [ 'Bold','Italic','Underline'] ,
                 [ 'TextColor','BulletedList','NumberedList'] ,
                 [ 'Copy','PasteFromWord','Link','Unlink'] ,
                 [ 'Undo','Redo'] ,
                 [ 'ImageManager','MascotManager','Mindmaps','Audio','VideoPlayer'] ,
                 [ 'asciimath','Table','SpecialChar'],['Styles'],['Format','FontSize','Preview','Maximize']
        ];

   config.toolbar = 'ForumStudent';
   config.toolbar_ForumStudent =
        [
                 [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo' ] ,
                 [ 'Link','Unlink' ] ,
                 [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio','Smiley','PageBreak','Iframe'] ,
                 [ 'Table','HorizontalRule', 'SpecialChar','ShowBlocks'] ,
                 [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor'] ,
                 [ 'Bold','Italic','Underline'] ,
                 [ 'JustifyLeft','JustifyCenter','JustifyRight'],['Styles'] ,
                 [ 'Format','Font','FontSize'] ,
                 [ 'Source']
        ];

   config.toolbar = 'Profile';
   config.toolbar_Profile =
                [
                         [ 'Undo','Redo'] ,
                         [ 'Link','Unlink'] ,
                         [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio'] ,
                         [ 'SpecialChar'] ,
                         [ 'NumberedList','BulletedList','-','TextColor'] ,
                         [ 'Font','FontSize'] ,
                         [ 'Bold','Italic','Underline'] ,
                         [ 'JustifyLeft','JustifyCenter','JustifyRight'] ,
                         [ 'Source']
                ];

   config.toolbar = 'Messages';
   config.toolbar_Messages =
                [
                         [ 'Bold','Italic','Underline'] ,
                         [ 'TextColor','BulletedList','NumberedList'] ,
                         [ 'PasteFromWord','Link','Smiley','ImageManager','MascotManager','Mindmaps'],
                         [ 'Source']
                ];
                
   config.toolbar = 'QuizScenario';
   config.toolbar_QuizScenario =
                [
                         [ 'Bold','Italic','Underline'] ,
                         [ 'TextColor','BulletedList','NumberedList'] ,
                         ['Link','ImageManager']
                ];

   config.toolbar = 'PortalNews';
   config.toolbar_PortalNews =
                [
                         [ 'Bold','Italic','Underline'] ,
                         [ 'TextColor','BulletedList','NumberedList'] ,
                         [ 'Copy','PasteFromWord','Link','Smiley']
                ];

   config.toolbar = 'Catalogue';
   config.toolbar_Catalogue =
                [
                         [ 'Undo','Redo'] ,
                         [ 'Link','Unlink'] ,
                         [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio'] ,
                         [ 'SpecialChar'] ,
                         [ 'NumberedList','BulletedList','-','TextColor'] ,
                         [ 'Font','FontSize'] ,
                         [ 'Bold','Italic','Underline'] ,
                         [ 'JustifyLeft','JustifyCenter','JustifyRight'] ,
                         [ 'Source']
                ];

   config.toolbar = 'Quiz_Media';
   config.toolbar_Quiz_Media =
           [];

   config.toolbar = 'TestQuestionDescription';
   config.toolbar_TestQuestionDescription =
           [];

   config.toolbar = 'CertificateTemplates';
   config.toolbar_CertificateTemplates =
           [
                 ['FontSize','Font','CertificateTokens','CertificateBg','ImageManager','TextColor'] ,
                 ['Bold','Italic','Underline', '-','NumberedList','BulletedList','-','JustifyLeft','JustifyCenter','JustifyRight'] ,
                 ['Maximize','Source']
           ];
   config.toolbar = 'TestAnswerFeedback';
   config.toolbar_TestAnswerFeedback =
           [
                 ['FontSize','Font','ImageManager','TextColor'] ,
                 ['Bold','Italic','Underline', '-','NumberedList','BulletedList','-','JustifyLeft','JustifyCenter','JustifyRight']
           ];
   config.toolbar = 'TestFreeAnswer';
   config.toolbar_TestFreeAnswer =
           [
                 ['FontSize','ImageManager','TextColor'] ,
                 ['Bold','Italic','Underline','-','JustifyLeft','JustifyCenter','JustifyRight']
           ];
   config.toolbar = 'TestProposedFeedback';
   config.toolbar_TestProposedFeedback =
           [];

   config.toolbar = 'TestProposedAnswer';
   config.toolbar_TestProposedAnswer =
           [];
   //config.toolbarGroupCycling = false;

   config.toolbar = 'AdminTemplates';
   config.toolbar_AdminTemplates =
        [
                 [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo' ] ,
                 [ 'Link','Unlink' ] ,
                 [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio','Smiley','PageBreak','Iframe'] ,
                 [ 'Table','HorizontalRule', 'SpecialChar','ShowBlocks'] ,
                 [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor'] ,
                 [ 'Bold','Italic','Underline'] ,
                 [ 'JustifyLeft','JustifyCenter','JustifyRight'],['Styles'] ,
                 [ 'Format','Font','FontSize'] ,
                 [ 'Source']
        ];

   config.toolbar = 'PortalHomePage';
   config.toolbar_PortalHomePage =
        [
                 [ 'Save','Maximize','-','PasteFromWord','-','Undo','Redo' ] ,
                 [ 'Link','Unlink' ] ,
                 [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio','Smiley','PageBreak','Iframe'] ,
                 [ 'Table','HorizontalRule', 'SpecialChar','ShowBlocks'] ,
                 [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor'] ,
                 [ 'Bold','Italic','Underline'] ,
                 [ 'JustifyLeft','JustifyCenter','JustifyRight'],['Styles'] ,
                 [ 'Format','Font','FontSize'] ,
                 [ 'Source']
        ];
   config.toolbar = 'EmailTemplates';
   config.toolbar_EmailTemplates =
        [
                 [ 'Bold','Italic','Underline'] ,
                 [ 'TextColor','BulletedList','NumberedList'] ,
                 [ 'Copy','PasteFromWord','Link','Smiley']
        ];

   // This is the full tollbar for all buttons.
    config.toolbar = 'FullTollbar';
    config.toolbar_FullTollbar =
    [
           [ 'Save','Undo','Redo','Maximize','-','PasteFromWord','-','Undo','Redo','CreateDiv' ],
           [ 'Link','Unlink' ] ,
           [ 'ImageManager','MascotManager','Mindmaps','VideoPlayer','Audio','Smiley','PageBreak'] ,
           [ 'Table','HorizontalRule', 'SpecialChar','ShowBlocks',] ,
           [ 'NumberedList','BulletedList','-','Outdent','Indent','TextColor'] ,
           [ 'Bold','Italic','Underline'] ,
           [ 'JustifyLeft','JustifyCenter','JustifyRight'],['Styles'] ,
           [ 'Format','Font','FontSize'] ,
           [ 'Source'] ,
           ['NewPage','Preview'] ,
           ['Cut','Copy','Paste','PasteText','-','Print', 'SpellChecker', 'Scayt'] ,
           ['Find','Replace','SelectAll','RemoveFormat'] ,
           ['Strike','-','Subscript','Superscript'] ,
           ['JustifyBlock','BGColor', 'About']
    ];
};
CONFIG;
