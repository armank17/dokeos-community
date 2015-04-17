<?php
function __autoload($class_name) {
    $arrayClassName= explode("_",$class_name);
    $file = $arrayClassName[(count($arrayClassName)-1)];
    if(count($arrayClassName) >1)
        unset($arrayClassName[(count($arrayClassName)-1)]);    
        switch($arrayClassName[0])
        {
            case 'appcore':             require_once dirname(__FILE__).'/../../'.implode("/",$arrayClassName)."/".$file .'.php';break;
            case 'xajax':               require_once dirname(__FILE__).'/../../appcore/library/xajax/xajax_core/xajax.inc.php';break;
            case 'xajaxResponse':       require_once dirname(__FILE__).'/../../appcore/library/xajax/xajax_core/xajaxResponse.inc.php';break;
            case 'CKEditor':            require_once dirname(__FILE__).'/../../appcore/library/ckeditor/ckeditor.php';break;
            case 'Security':            require_once dirname(__FILE__).'/../../inc/lib/security.lib.php';break;
            case 'EcommercePaypal':     require_once dirname(__FILE__).'/../../core/model/ecommerce/EcommercePaypal.php';break;             
            case 'CatalogueModuleModel': require_once dirname(__FILE__).'/../../core/model/ecommerce/CatalogueModuleModel.php';break;        
            case 'Pager':               require_once dirname(__FILE__).'/../../inc/lib/pear/Pager/Sliding.php';break;
            case 'FormValidator':       require_once dirname(__FILE__).'/../../inc/lib/formvalidator/FormValidator.class.php';break;
            case 'WCAG':                require_once dirname(__FILE__).'/../../inc/lib/WCAG/WCAG_rendering.php';break;
            //case 'learnpath':           require_once dirname(__FILE__).'/../../coursecopy/classes/Learnpath.class.php';break;
            case 'Learnpath':           require_once dirname(__FILE__).'/../../coursecopy/classes/Learnpath.class.php';break;
            case 'Resource':            require_once dirname(__FILE__).'/../../coursecopy/classes/Resource.class.php';break;
            case 'CourseManager':       require_once dirname(__FILE__).'/../../inc/lib/course.lib.php';break;
            case 'SearchEngineManager': require_once dirname(__FILE__).'/../../inc/lib/searchengine.lib.php';break;
            case 'DokeosIndexer':           require_once dirname(__FILE__).'/../../inc/lib/search/DokeosIndexer.class.php';break;
            case 'learnpath':               require_once dirname(__FILE__).'/../../newscorm/learnpath.class.php';break;
            case 'OpenofficePresentation':  require_once dirname(__FILE__).'/../../newscorm/openoffice_presentation.class.php';break;
            case 'learnpathItem':           require_once dirname(__FILE__).'/../../newscorm/learnpathItem.class.php';break;
            case 'scorm':                   require_once dirname(__FILE__).'/../../newscorm/scorm.class.php';break;
            case 'aicc':                    require_once dirname(__FILE__).'/../../newscorm/aicc.class.php';break;        
            case 'DocumentManager':         require_once dirname(__FILE__).'/../../inc/lib/document.lib.php';break;
            case 'PclZip':                  require_once dirname(__FILE__).'/../../inc/lib/pclzip/pclzip.lib.php';break;
            case 'Exercise':                require_once dirname(__FILE__).'/../../exercice/exercise.class.php';break;  
            case 'Question':                require_once dirname(__FILE__).'/../../exercice/question.class.php';break;
            case 'Answer':                  require_once dirname(__FILE__).'/../../exercice/answer.class.php';break;
            case 'HTML2PDF':                require_once dirname(__FILE__).'/../../inc/lib/html2pdf/html2pdf.class.php';break;
            case 'AudiorecorderFactory':    require_once dirname(__FILE__).'/../../inc/lib/audiorecorder/src/AudiorecorderFactory.php';break;
            case 'UploadAjaxHandler':       require_once dirname(__FILE__).'/../../appcore/library/jquery/jquery.upload/server/php/UploadAjaxHandler.php';break;
            case 'TimeZone':                require_once dirname(__FILE__).'/../../inc/lib/timezone.lib.php';break;
            case 'Tracking':                require_once dirname(__FILE__).'/../../inc/lib/tracking.lib.php';break;
            case 'Export':                  require_once dirname(__FILE__).'/../../inc/lib/export.lib.inc.php';break;
            case 'pData':                   require_once dirname(__FILE__).'/../../inc/lib/pchart/pData.class.php';break;  
            case 'pChart':                  require_once dirname(__FILE__).'/../../inc/lib/pchart/pChart.class.php';break;  
            case 'pCache':                  require_once dirname(__FILE__).'/../../inc/lib/pchart/pCache.class.php';break;          
            case 'UserManager':     
            case 'CertificateManager':
            case 'SessionManager':
            case 'UrlManager':
            case 'WikiManager':
            case 'WorkManager':
            case 'MeetingManager':
            case 'SurveyManager':
            case 'SubLanguageManager':
            case 'GroupManager':
            case 'ForumManager':
            case 'ClassManager':
                                            $filename = strtolower($arrayClassName[0]).'.lib.php';
                                            require_once dirname(__FILE__)."/../../inc/lib/{$filename}";
                                            break;
            case 'PHPExcel':
                                            require_once dirname(__FILE__)."/../../appcore/library/PHPExcel/Classes/PHPExcel.php";break;//V 1.7.7
            case 'HTML':                
                                            require_once dirname(__FILE__)."/../../inc/lib/formvalidator/Element/elementlibs/CkEditorFactory.php";
                break;
            default:    if($arrayClassName[2] == 'controllers') {  
                            $file_include = dirname(__FILE__).'/../../'.implode("/",$arrayClassName)."/".$file .'Controller.php';
                            if (file_exists($file_include)) {
                                require_once $file_include;
                            }
                        }
                        else {
                            $file_include = dirname(__FILE__).'/../../'.implode("/",$arrayClassName)."/".$file .'.php';
                            if (file_exists($file_include)) {
                                require_once $file_include;
                            }
                        }
                        break;
        }
}