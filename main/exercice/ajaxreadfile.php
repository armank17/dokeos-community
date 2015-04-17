<?php
//ini_set('memory_limit', '400M');
/** Error reporting */
error_reporting(E_ALL);

//date_default_timezone_set('Europe/London');

$language_file[]='exercice';
// including the global file that gets the general configuration, the databases, the languages, ...
require ('../inc/global.inc.php');

require_once (api_get_path(LIBRARY_PATH).'PHPExcel-1.7.7/Classes/PHPExcel.php');

$error = "";
$msg = "";
$html = "";
$fileElementName = 'fileToUpload';
if(!empty($_FILES[$fileElementName]['error']))
{
    switch($_FILES[$fileElementName]['error'])
    {
        case '1':
//            $error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            $error = 'FileExceedsMaxSizeInPHP_ini';
            break;
        case '2':
//            $error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
            $error = 'FileExceedsMaxSizeInHTML_form';
            break;
        case '3':
//            $error = 'The uploaded file was only partially uploaded';
            $error = get_lang('UploadWasPartial');
            break;
        case '4':
//            $error = 'No file was uploaded.';
            $error = get_lang('NoFileUploaded');
            break;
        case '6':
//            $error = 'Missing a temporary folder';
            $error = get_lang('MissingTemporaryFolder');
            break;
        case '7':
//            $error = 'Failed to write file to disk';
            $error = get_lang('FailedToWriteFile');
            break;
        case '8':
//            $error = 'File upload stopped by extension';
            $error = get_lang('FileUploadStopped');
            break;
        case '999':
        default:
//            $error = 'No error code avaiable';
            $error = get_lang('NoErrorCode');
    }
}
elseif(empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none')
{
//    $error = 'No file was uploaded..';
    $error = get_lang('NoFileUploaded');
}
else 
{
    $msg .= " File Name: " . $_FILES[$fileElementName]['name'] . ", ";
    $msg .= " File Size: " . filesize($_FILES[$fileElementName]['tmp_name']);
    
    $sys_course_path = api_get_path(SYS_COURSE_PATH);
    $temp_path = $sys_course_path.$_course['path'].'/temp/';
    
    if (move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $temp_path.$_FILES[$fileElementName]['name'])) {
        $inputFileName = $temp_path.$_FILES[$fileElementName]['name'];
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        
        if ($inputFileType)
        {
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objReader->setReadDataOnly(true); 
            $objPHPExcel = $objReader->load($inputFileName);

            $objPHPExcel->setActiveSheetIndex(0);
            $sheetContent = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

            $html .= '<table>';
            foreach ($sheetContent as $row)
            {
                $html .= '<tr>';
                foreach ($row as $col)
                {
                    $html .= '<td>'. $col .'</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</table>';
        }
        else
            $error = get_lang('NoTypeFileAllowed');
    }
    else
    {
        $inputFileName = $temp_path.'Test.ods';
//        $inputFileName = $temp_path.'Test.xls';
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);

        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true); 
        $objPHPExcel = $objReader->load($inputFileName);
        
        $objPHPExcel->setActiveSheetIndex(0);
        $sheetContent = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        
        $html .= '<table>';
        foreach ($sheetContent as $row)
        {
            $html .= '<tr>';
            foreach ($row as $col)
            {
                $html .= '<td>'. $col .'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
    }

    // for security reason, we force to remove all uploaded file
    @unlink($_FILES[$fileElementName]);
}
$data = array(
            'error' => $error,
            'msg'   => $msg,
            'html'  => htmlentities($html)
            );
echo json_encode($data);
?>