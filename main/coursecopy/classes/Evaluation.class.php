<?php 
require_once 'Resource.class.php';

class Evaluation extends Resource {

    public $exam_name;
    public $quiz_id;
    public $modality;
    public $min_score;
    public $start_date;
    public $end_date;
    public $invitation_email_sentdate;
    public $feedback_email_sentdate;
    public $invitation_email;
    public $feedback_email;
    public $feedback_email_fail;
    public $certif_id; 
    public $picture_name;
     

    
    public function __construct($id, $exam_name, $quiz_id, $modality, $min_score, $start_date, $end_date, $invitation_email_sentdate, $feedback_email_sentdate, $invitation_email) {
        parent::__construct($id, RESOURCE_EVALUATION);
            $this->exam_name = $exam_name;
			$this->quiz_id = $quiz_id;
			$this->modality = $modality;
			$this->min_score = $min_score;
			$this->start_date = $start_date;
			$this->end_date = $end_date;
			$this->invitation_email_sentdate = $invitation_email_sentdate;
			$this->feedback_email_sentdate = $feedback_email_sentdate;
			$this->invitation_email = $invitation_email;
			$this->feedback_email = $feedback_email;
			$this->feedback_email_fail = $feedback_email_fail;
			$this->certif_id = $certif_id; 
			$this->picture_name = $picture_name;
     
    }


    public function show() {
        parent::show();
        echo $this->exam_name;
    }

}
