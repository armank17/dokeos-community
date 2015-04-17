
function getLang(variable) {
    var mypath = typeof myDokeosWebPath !== 'undefined'?myDokeosWebPath:'/';    
    var urlLang = mypath+'main/index.php?module=i18n&cmd=Language&func=lang&variable='+variable;
    var translation = $.ajax({url: urlLang, async: false}).responseText;
    return translation;
}

function isCanvasSupported(){
	  var elem = document.createElement('canvas');
	  return (!!(elem.getContext && elem.getContext('2d')) && isFileReaderSupported());
}

function isFileReaderSupported(){
	return typeof FileReader != "undefined";
}





