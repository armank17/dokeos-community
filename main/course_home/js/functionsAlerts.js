function getLang(variable) {
    var mypath = typeof myDokeosWebPath !== 'undefined'?myDokeosWebPath:'/';    
    var urlLang = mypath+'main/index.php?module=i18n&cmd=Language&func=lang&variable='+variable;
    var translation = $.ajax({url: urlLang, async: false}).responseText;
    return translation;
}
function Alert_Confim_Delete(link,title,text){
        title || (title = getLang("ConfirmationDialog"));
        text || (text = getLang("ConfirmYourChoice"));
       $.confirm(text, title, function() {
           window.location.href = link;
       });
}
function Alert_Confirm_Submit(survey_list){
        var title = getLang("ConfirmationDialog");
        var text = getLang("ConfirmYourChoice");
       $.confirm(text, title, function() {
           document.forms.survey_list.submit();
       });
}