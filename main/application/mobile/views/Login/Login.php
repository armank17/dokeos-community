<script>
    function loginUser(){
        var theName = $.trim($("#login-mob").val());
        var thePass = $("#password-mob").val();
        if(theName.length > 0 && thePass.length > 0)
        {
            $.ajax({
                type: "POST",
                url: "index.php?module=mobile&cmd=login&func=logearse",
                data: ({usermob: theName,passmob: thePass}),
                cache: false,
                dataType: "text",
                success: function(dato){
                    if(dato == 1)
                        location.href='index.php?module=mobile&cmd=index';
                    else
                        alert('usuario o pass incorrect');
                    
                },
                timeout:8000
            });
            return false;
        }
    }
    
</script>

<form action="" method="POST" id="form-login" data-ajax="false" class="validate" onsubmit="return loginUser();">
    <fieldset>
        <label for="email">Users:</label>
        <input type="text" class="error" name="login-mob" id="login-mob" placeholder="user" autofocus required />
        <label for="password">Password:</label>
        <input type="password" name="password-mob" id="password-mob" placeholder="password" required   />

        <input id="btSumit" type="submit" value="Login" />
        <div id="resultLog"></div>
        <hr />
        <div class="center-button">
            Don't have a login? <a data-ajax="false" href="#" >Sign Up</a>
        </div>
        
    </fieldset>
</form>

