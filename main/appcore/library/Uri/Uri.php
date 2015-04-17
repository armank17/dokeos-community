<?php
class appcore_library_Uri_Uri{
    
    public function redirect($path){
         header("Location: " . $path); exit;
    }
    
    public function reload(){
        $params = str_replace('/', '', $_SERVER['REQUEST_URI']);
        
        header("Location: " . api_get_path(WEB_PATH) . $params);
    }
    
    /**
    * @return string
    * @param string $path
    * @desc Returns the directory part of the path (path may include query)
    */
    public function dirname($path){
        if(empty($path)){
            return false;
        }
        $i=strpos($path,'?');
        $dir=$i?substr($path,0,$i):$path;

        $i=strrpos($dir,'/');
        $dir=$i?substr($dir,0,$i):'/';
        $dir[0]=='/' || $dir='/'.$dir;
        return $dir;
    }
    
    public function exception(){
        
    }
}
