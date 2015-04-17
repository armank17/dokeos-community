<?php

/** This file is part of KCFinder project
  *
  *      @desc Base configuration file
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

// IMPORTANT!!! Do not remove uncommented settings in this file even if
// you are using session configuration.
// See http://kcfinder.sunhater.com/install for setting descriptions

global $group_properties;
// dynamic streaming folder
$webpath = api_get_path(WEB_PATH);
$webpath = str_replace('http://', '', $webpath);
$webpath = str_replace('https://', '', $webpath);
$webpath = rtrim($webpath,'/');

$is_allowed = api_is_allowed_to_edit() || api_is_course_visible_for_user(api_get_user_id(), api_get_course_id());
$user_shared_folder = 'sf_user_'.api_get_user_id();
$_CONFIG = array(
    'disabled' => false,
    'denyZipDownload' => false,
    'denyUpdateCheck' => false,
    'denyExtensionRename' => false,
    'theme' => "oxygen",
    'uploadURL' => "upload",
    'uploadDir' => "",
    'dirPerms' => 0755,
    'filePerms' => 0644,
    'access' => array(
        'files' => array(
            'upload' => $is_allowed ,
            'delete' => $is_allowed,
            'copy' => $is_allowed,
            'move' => $is_allowed,
            'rename' => $is_allowed
        ),
        'dirs' => array(
            'create' => $is_allowed,
            'delete' => $is_allowed,
            'rename' => $is_allowed
        )
    ),
    'deniedExts' => "exe com msi bat php phps phtml php3 php4 cgi pl",
    'types' => array(
        // CKEditor & FCKEditor types
        'video'   =>  "flv mp4 asf avi mpg mpeg mov wmv",
        'audio'   =>  "mp3 wav",
        'files'   =>  "",
        'flash'   =>  "swf",
        'animations' => "swf",
        'images'  =>  "*img",
        'mascot'  =>  "*img",
        'mindmaps'  =>  "*img",
        'document' => "", 
        $webpath   => "flv mp4 asf avi mpg mpeg mov wmv",
        $user_shared_folder => "jpg png gif flv mp4 asf avi mpg mpeg mov wmv swf mp3 wav",
        'default_course_document' => "jpg png gif flv mp4 asf avi mpg mpeg mov wmv swf mp3 wav",
        'my_files' => "jpg png gif flv mp4 asf avi mpg mpeg mov wmv swf mp3 wav",
        $group_properties['directory'] => "jpg png gif flv mp4 asf avi mpg mpeg mov wmv swf mp3 wav",
        // TinyMCE types        
        'file'    =>  "",
        'media'   =>  "swf flv avi mpg mpeg qt mov wmv asf rm",
        'image'   =>  "*img",
    ),
    'filenameChangeChars' => array(/*
        ' ' => "_",
        ':' => "."
    */),
    'dirnameChangeChars' => array(/*
        ' ' => "_",
        ':' => "."
    */),
    'mime_magic' => "",
    'maxImageWidth' => 0,
    'maxImageHeight' => 0,
    'thumbWidth' => 100,
    'thumbHeight' => 100,
    'thumbsDir' => ".thumbs",
    'jpegQuality' => 90,
    'cookieDomain' => "",
    'cookiePath' => "",
    'cookiePrefix' => 'KCFINDER_',
    // THE FOLLOWING SETTINGS CANNOT BE OVERRIDED WITH SESSION CONFIGURATION
    '_check4htaccess' => true,
    //'_tinyMCEPath' => "/tiny_mce",
    '_sessionVar' => &$_SESSION['KCFINDER'],
    //'_sessionLifetime' => 30,
    //'_sessionDir' => "/full/directory/path",
    //'_sessionDomain' => ".mysite.com",
    //'_sessionPath' => "/my/path",
);

?>