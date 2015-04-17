<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-15">
        <title></title>
    </head>
    <body>
        <?php
        if(!empty($this->nodeId)):
            echo (($this->pageInfo['display_title'] > 0)? '<h1>'. $this->pageInfo['title'] .'</h1>' : '').$this->pageInfo['content'];
        else:
            echo $this->get_lang('NoContentForThisPage');
        endif;
        ?>
    </body>
</html>