  <div data-role="content" data-inset="true">   
       <form action="/FormActions/login.ashx" method="post">
           <fieldset>
           <label for="email">Email:</label>
           <input type="email" name="email" id="email" value=""  />
           <label for="password">Password:</label>
           <input type="password" name="password" id="password" value="" />
           <input id="Submit1" type="submit" value="Login" data-role="button" data-inline="true" data-theme="b" />
           <hr />
           Don't have a login? <a href="register.aspx">Sign Up</a>
           </fieldset>
       </form>
</div>