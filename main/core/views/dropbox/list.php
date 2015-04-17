<?php	

	isset($_REQUEST['view'])?$view = Security :: remove_XSS($_REQUEST['view']):$view='sent';	

	if(!empty($addCategoryForm)) {
		if(!empty($errormessage)) {
			echo '<div class="confirmation-message rounded">'.$errormessage.'</div>';
		}

		if( $message['type'] != 'confirmation') {
			echo $addCategoryForm;
		}
	}	

	if($view == 'received'){
	echo api_display_tool_title(get_lang('ReceivedFiles'));	

	$number_feedback = $totalNumberFeedback;

	// sorting and paging options
	$sorting_options = array();
	$paging_options = array();

	// the headers of the sortable tables
	$column_header=array();
	$column_header[] = array('',false,'');
	$column_header[] = array(get_lang('Type'),true,'style="width:40px"');
	$column_header[] = array(get_lang('ReceivedTitle'), TRUE, '');
	$column_header[] = array(get_lang('Size'), TRUE, '');
	$column_header[] = array(get_lang('Authors'), TRUE, '');
	$column_header[] = array(get_lang('LastResent'), true);
	
	if (api_get_session_id()==0)			
		$column_header[] = array(get_lang('Modify'), FALSE, '', 'nowrap style="text-align: center"');
	elseif (api_is_allowed_to_session_edit(false,true)){
		$column_header[] = array(get_lang('Modify'), FALSE, '', 'nowrap style="text-align: center"');
	}
	
	$column_header[] = array('RealDate', true);


	// An array with the setting of the columns -> 1: columns that we will show, 0:columns that will be hide
	$column_show[]=1;
	$column_show[]=1;
	$column_show[]=1;
	$column_show[]=1;
	$column_show[]=1;
	$column_show[]=1;
		
	if (api_get_session_id()==0)			
		$column_show[]=1;
	elseif (api_is_allowed_to_session_edit(false,true)){
		$column_show[]=1;
	}
	$column_show[]=0;

	// Here we change the way how the colums are going to be sort
	// in this case the the column of LastResent ( 4th element in $column_header) we will be order like the column RealDate
	// because in the column RealDate we have the days in a correct format "2008-03-12 10:35:48"

	$column_order[]=1;
	$column_order[]=2;
	$column_order[]=3;
	$column_order[]=4;
	$column_order[]=7;
	$column_order[]=6;
	$column_order[]=7;
	$column_order[]=8;

	$dropbox_data_recieved = $receivedDropboxData;

	// Displaying the table
	$additional_get_parameters=array('view'=>$_GET['view'], 'view_received_category'=>$_GET['view_received_category'],'view_sent_category'=>$_GET['view_sent_category']);
	$selectlist = array ('delete_received' => get_lang('Delete'));
    if (is_array($movelist)) {
        foreach ($movelist as $catid => $catname){
        	$selectlist['move_received_'.$catid] = get_lang('Move') . '->'. $catname;
    	}
    }
    
	if (api_get_session_id()!=0 && api_is_allowed_to_session_edit(false,true)==false) {		 
		$selectlist=array();
	}
    
	Display::display_sortable_config_table($column_header, $dropbox_data_recieved, $sorting_options, $paging_options, $additional_get_parameters,$column_show,$column_order, $selectlist);

	}

	if($view == 'sent'){
	echo api_display_tool_title(get_lang('SentFiles')).'</h3>';

	$number_feedback = $totalNumberFeedback;

	// sorting and paging options
	$sorting_options = array();
	$paging_options = array();

	// the headers of the sortable tables
	$column_header=array();

	$column_header[] = array('',false,'style="width:10px"');
	$column_header[] = array(get_lang('Type'),true,'style="width:10px"');
	$column_header[] = array(get_lang('SentTitle'), TRUE, 'style="width:400px"');
	$column_header[] = array(get_lang('Size'), TRUE, 'style="width:50px"');
	$column_header[] = array(get_lang('SentTo'), TRUE, 'style="width:130px"');
	$column_header[] = array(get_lang('LastResent'), TRUE, 'style="width:150px"');
		
        if (api_get_session_id()==0)		
		$column_header[] = array(get_lang('Modify'), FALSE, '', 'nowrap style="text-align: center;"');
	elseif (api_is_allowed_to_session_edit(false,true)){
		$column_header[] = array(get_lang('Modify'), FALSE, '', 'nowrap style="text-align: center;"');
	}
		
	$column_header[] = array('RealDate', FALSE);

	$column_show=array();
	$column_order=array();

	// An array with the setting of the columns -> 1: columns that we will show, 0:columns that will be hide
	$column_show[]=1;
	$column_show[]=1;
	$column_show[]=1;
	$column_show[]=1;
	$column_show[]=1;
	$column_show[]=1;
	if (api_get_session_id()==0)			
		$column_show[]=1;
	elseif (api_is_allowed_to_session_edit(false,true)){
		$column_show[]=1;
	}
	$column_show[]=0;

	// Here we change the way how the colums are going to be sort
	// in this case the the column of LastResent ( 4th element in $column_header) we will be order like the column RealDate
	// because in the column RealDate we have the days in a correct format "2008-03-12 10:35:48"

	$column_order[]=1;
	$column_order[]=2;
	$column_order[]=3;
	$column_order[]=4;
	$column_order[]=7;
	$column_order[]=6;
	$column_order[]=7;
	$column_order[]=8;

	$dropbox_data_sent = $sentDropboxData;

	if(empty($_GET['view'])) {
			$view = 'sent';
		}
		else {
			$view = $_GET['view'];
		}

	// Displaying the table
	$additional_get_parameters=array('view'=>Security::remove_XSS($view), 'view_received_category'=>Security::remove_XSS($_GET['view_received_category']),'view_sent_category'=>Security::remove_XSS($_GET['view_sent_category']));
	$selectlist = array ('delete_received' => get_lang('Delete'));
	if (api_get_session_id()!=0 && api_is_allowed_to_session_edit(false,true)==false) {		 
		$selectlist = array ('download_received'=>get_lang('Download'));
	}	
	Display::display_sortable_config_table($column_header, $dropbox_data_sent, $sorting_options, $paging_options, $additional_get_parameters,$column_show,$column_order, $selectlist);
	}
	
?>