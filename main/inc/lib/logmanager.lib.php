<?php

class logmanager {

    private $file;
    private $path;

    public function __construct() {
        global $_configuration;
        $this->path = $_configuration['root_sys'];
        $this->file = 'log.txt';
    }

    public static function write_log($data) {
        global $_configuration;
        $file = $_configuration['root_sys'] . 'log.txt';
        // The new person to add to the file
        $message = $data;
        // Write the contents to the file, 
        // using the FILE_APPEND flag to append the content to the end of the file
        // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
        if (is_writable($file)) {
            file_put_contents($file, $message, FILE_APPEND | LOCK_EX);
        }
    }

}