/***************************/
//@Author: Adrian "yEnS" Mato Gondelle & Ivan Guardado Castro
//@website: www.yensdesign.com
//@email: yensamg@gmail.com
//@license: Feel free to use it, but keep this credits please!					
/***************************/

$(document).ready(function(){
    //global vars - (Change my style)
    var form = $("#form-login");
    var name = $("#login-mob");
    var pass = $("#password-mob");
    var nameInfo = $("#nameInfo");
    
	
    //On blur
    name.blur(validateName);
    pass.blur(validatePass1);
    //On key press
    name.keyup(validateName);
    pass.keyup(validatePass1);
    //On Submitting
    form.submit(function(){
        if(validateName() & validatePass1())
            return true
        else
            return false;
    });
	
    function validateName(){
        //if it's NOT valid
        if(name.val().length < 4){
            name.addClass("error");
            //nameInfo.text("We want names with more than 3 letters!");
            nameInfo.addClass("error");
            return false;
        }
        //if it's valid
        else{
            name.removeClass("error");
            //nameInfo.text("What's your name?");
            nameInfo.removeClass("error");
            return true;
        }
    }
    function validatePass1(){
        //it's NOT valid
        if(pass.val().length < 2){
            pass.addClass("error");
            //pass1Info.text("Ey! Remember: At least 5 characters: letters, numbers and '_'");
            //pass1Info.addClass("error");
            return false;
        }
        //it's valid
        else{			
            pass.removeClass("error");
            //pass1Info.text("At least 5 characters: letters, numbers and '_'");
            //pass1Info.removeClass("error");
            validatePass2();
            return true;
        }
    }
    
});