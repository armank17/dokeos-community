<div class="book_wrapper">
    <a id="next_page_button"></a>
    <a id="prev_page_button"></a>
        <div id="loading" class="loading">Loading pages...</div>
            <div id="BookView" style="display:none;">
		<div class="b-load">
                      <?php
                       $obj=new NotebookModel();
                       $rs=$obj->getNotesListView();
                       foreach ($rs as $note){
                       ?>
                            <div style="text-align: justify; width: 330px; padding: 2px; margin-left: 1px; height: 520px;">
                                 <h1><?php  echo $note['title']; ?></h1>
                                 <p> <?php  echo $note['description']; ?></p>
                            </div>
                            <?php
                              }
                            ?>																																
		</div>
            </div>
</div>
<div id="dialog" title="<?php echo get_lang('Search'); ?>" style="display: none;">    
    <?php   
        searchNote(); 
    ?>
</div>
  
  <?php
function searchNote() {    
        $form = new FormValidator('notesearch', 'post', api_get_self().'?'.api_get_cidreq().'&amp;action=search');	
	$form->addElement('text', 'search_term', get_lang('Search'),'class="input_titles"');	
	$form->addElement('style_submit_button', 'submit', get_lang('Submit'), 'class="save"');

	// The validation or display
        $form->display();      
}
?>
