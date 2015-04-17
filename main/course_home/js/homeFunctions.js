
function getLang(variable) {
    var mypath = typeof myDokeosWebPath !== 'undefined'?myDokeosWebPath:'/';    
    var urlLang = mypath+'main/index.php?module=i18n&cmd=Language&func=lang&variable='+variable;
    var translation = $.ajax({url: urlLang, async: false}).responseText;
    return translation;
}
