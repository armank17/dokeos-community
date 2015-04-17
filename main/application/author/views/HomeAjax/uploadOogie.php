<?php
if (!empty($this->messages['msn'])) {
    if ($this->messages['error']) {   
        echo Display::display_error_message($this->messages['msn'], false, true);
    }
    else {
        echo Display::display_confirmation_message($this->messages['msn'], false, true);
    }
}