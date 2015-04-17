/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function getLang(variable) {
    var urlLang = '/main/index.php?module=i18n&cmd=Language&func=lang&variable='+variable;
    var translation = $.ajax({url: urlLang, async: false}).responseText;
    return translation;
}
