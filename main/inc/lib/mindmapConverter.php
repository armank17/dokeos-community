<?php
class MindmapConverter {
	
	private $_source;
	private $_dokeosMind;
	private $_port;
	private $_server;
	private $_deleteAfterConversion = false;
	private $_insertInDocumentTool = false;
	
	public function __construct($server, $port)
	{
		
		$this->_server = $server;
		$this->_port = $port;
		
	}
	
	public function convertToPng($source, $dest){

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$result = socket_connect($socket, $this->_server, $this->_port);
		socket_write($socket, "$source\n");
		socket_write($socket, "$dest\n");
		$success = socket_read($socket, 2048);
		if($this->_insertInDocumentTool){
			$this->createDocument($dest);
		}
		if($this->_deleteSourceAfterConversion){
			unlink($source);
		}
		socket_close($socket);
		
	}
	
	public function setDeleteSourceAfterConversion($delete){
		
		$this->_deleteSourceAfterConversion = $delete;
		
	}
	
	public function setInsertInDocumentTool($insert){
		
		$this->_insertInDocumentTool = $insert;
		
	}
	
	public function createDocument($filePath){
		
		global $_course, $_user;
		
		require_once api_get_path(LIBRARY_PATH).'/document.lib.php';
		require_once api_get_path(LIBRARY_PATH).'/fileUpload.lib.php';
		
		$to_group_id = 0;
		if (api_is_in_group())
		{
			global $group_properties;
			$to_group_id = $group_properties['id'];
		}
		
		$repository_path = api_get_path(REL_COURSE_PATH).api_get_course_path().'/document/';
		$documentPath = substr($filePath, strpos($filePath, $repository_path) + strlen($repository_path) - 1);
		
		$doc_id = add_document($_course, $documentPath, 'file', filesize($filePath), pathinfo($filePath, PATHINFO_FILENAME));
		api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'DocumentAdded', $_user['user_id'], $to_group_id);
		item_property_update_on_folder($_course, dirname($filePath), $_user['user_id']);
		
		
	}
	
	
}