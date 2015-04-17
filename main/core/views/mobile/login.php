<?php
echo $this->data['head'];
?>
<div data-role="content">  
    <div data-role="content" data-inset="true">   
        <form data-ajax="false" action="<?php echo api_get_path(WEB_PATH) ?>index.php" method="post" name="formLogin" id="formLogin" accept-charset="UTF-8">
            <fieldset>
            <label for="email">User:</label>
            <input type="text" name="login" id="login" placeholder="user" autofocus required  />
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" placeholder="password" required />
            <input id="submitAuth" name="submitAuth" type="submit" value="Login" data-role="button" data-inline="true" data-theme="b" />
            <input type="hidden" name="fromMobile" id="fromMobile" placeholder="fromMobile" value="mobile<?php //echo $this->data['fromMobile']; ?>" />
            <hr />
            Don't have a login? <a data-ajax="false" href="#" >Sign Up</a>
            </fieldset>
        </form>
    </div>
</div>
<?php
echo $this->data['foot'];
?>