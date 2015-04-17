<?php
interface AudiorecorderInterface
{
    public function getDialog($dialogId, $dialogTitle = '', $extra = array());
    public function returnJs();
    public function returnCss();
    public function saveAudio($aFile);
}
