
/* For licensing terms, see /license.txt */

/**
 * Copyright (C) 2012 
 * AsciiMath plugin for CKEditor. Plugin developed by Dokeos Team based in the work of Peter Jipsen
 * 
 */
var DlgAsciiMathShowMathML = DlgAsciiMathShowMathML ? DlgAsciiMathShowMathML : 'Show MathML';
var DlgAsciiMathFormulaPreview = DlgAsciiMathFormulaPreview ? DlgAsciiMathFormulaPreview : 'Formula Preview';
var DlgAsciiIncompatibleBrowser = DlgAsciiIncompatibleBrowser ? DlgAsciiIncompatibleBrowser : 'Your browser is not able to show mathematical formulas. Please, use %s1 or Internet Explorer with %s2 plugin.' ;
var DlgAsciiIncompatibleBrowser = DlgAsciiIncompatibleBrowser.replace( '%s1', '<a href="http://www.mozilla.com" onclick="javascript: window.open(this.href,\'_blank\');return false;">Mozilla Firefox 1.5+</a>, <a href="http://www.opera.com" onclick="javascript: window.open(this.href,\'_blank\');return false;">Opera 9.5+</a>' ) ;
var DlgAsciiIncompatibleBrowser = DlgAsciiIncompatibleBrowser.replace( '%s2', '<a href="http://www.dessci.com/en/products/mathplayer/" onclick="javascript: window.open(this.href,\'_blank\');return false;">MathPlayer</a>' ) ;

var DlgAsciiMathOldIE = DlgAsciiMathOldIE ? DlgAsciiMathOldIE : 'Your browser is not able to show mathematical formulas. You need to upgrade to Internet Explorer 6.0+. Then you need to install the MathPlayer 2 plugin for Internet Explorer. Please, see %s for more information.' ;
var DlgAsciiMathOldIE = DlgAsciiMathOldIE.replace( '%s', '<a href="http://www.dessci.com/en/products/mathplayer/" onclick="javascript: window.open(this.href,\'_blank\');return false;">http://www.dessci.com/en/products/mathplayer/</a>' ) ;

var DlgAsciiMathInstallMathPlayer = DlgAsciiMathInstallMathPlayer ? DlgAsciiMathInstallMathPlayer : 'Your browser is not able to show mathematical formulas. You need to install the MathPlayer 2 plugin for Internet Explorer. Please, see %s for more information.' ;
var DlgAsciiMathInstallMathPlayer = DlgAsciiMathInstallMathPlayer.replace( '%s', '<a href="http://www.dessci.com/en/products/mathplayer/" onclick="javascript: window.open(this.href,\'_blank\');return false;">http://www.dessci.com/en/products/mathplayer/</a>' ) ;

var DlgAsciiMathOldMathPlayer = DlgAsciiMathOldMathPlayer ? DlgAsciiMathOldMathPlayer : 'Your browser is not able to show mathematical formulas. You need to upgrade the MathPlayer plugin for Internet Explorer to version 2. Please, see %s for more information.' ;
var DlgAsciiMathOldMathPlayer = DlgAsciiMathOldMathPlayer.replace( '%s', '<a href="http://www.dessci.com/en/products/mathplayer/" onclick="javascript: window.open(this.href,\'_blank\');return false;">http://www.dessci.com/en/products/mathplayer/</a>' ) ;

var dialog = parent.CKEDITOR.dialog.getCurrent();

function LoadSelection(){
    
    // Get the editor instance current
               
    var oEditor = parent.CKEDITOR.currentInstance; 
    // Get the selected inside of the editor

    var mySelection = oEditor.getSelection();
    

    // If the browser used is internet explorer

    if (CKEDITOR.env.ie) {
            mySelection.unlock(true);
            selection = mySelection.getNative().createRange().text;
    } else {
            selection = mySelection.getNative();
    }

    // Set the selected to inputText
    
    document.getElementById('inputText').value = selection;

    // Make the preview
    
    Preview();
}
window.onload = function()
{

	// Load the selected element information (if any).
	LoadSelection() ;

	var inputField = document.getElementById('inputText') ;
	inputField.focus() ;
}
function Set ( string )
{
    	var inputField = document.getElementById('inputText');
	inputField.value += string ;
	Preview() ;
	inputField.focus() ;
	return false ;
}
function Clear()
{
	var inputField = document.getElementById('inputText');
	inputField.value = '' ;
	Preview() ;
	inputField.focus() ;
	return false ;
}

function Delete()
{
	Clear();
	dialog.hide();
}

function Preview()
{
	if ( document.getElementById('inputText').value != '' )
	{
		// Get the value of the input text
                var str = document.getElementById('inputText').value ;               
                // Get the div where is showed the preview
		var outnode = document.getElementById('outputNode') ;
                // Get the div where is showed the preview hidden
		var outfinal = document.getElementById('outputNodeFinal') ;
                // Make a new element XHTMl of div type
		var newnode = AMcreateElementXHTML( 'div' ) ;
                // Add the attribute id = "outputNode" to the new div created
		newnode.setAttribute( 'id', 'outputNode' ) ;
                // Replace the old div for the new div
		outnode.parentNode.replaceChild( newnode, outnode ) ;
                // Get the element called outputNode
		outnode = document.getElementById('outputNode') ;
                // Get the length of the outputNode
		var n = outnode.childNodes.length ;                
                // Loop for the outnode element
		for ( var i = 0; i < n; i++ )
		{
			outnode.removeChild( outnode.firstChild ) ; 
		}              
		outnode.appendChild( document.createComment( '`' + str + '`' ) ) ;
		AMprocessNode( outnode, true ) ;
                
                // Get the length of the outputNodeFinal
		var n = outfinal.childNodes.length ;                
                // Loop for the outfinal element
		for ( var i = 0; i < n; i++ )
		{
			outfinal.removeChild( outfinal.firstChild ) ; 
		}              
		outfinal.appendChild( document.createComment( '`' + str + '`' ) ) ;
		AMprocessNode( outfinal, true ) ;

	}else{ 
                // Get the div where is showed the preview
		var outnode = document.getElementById('outputNode');
                // Get the div where is showed the preview hidden
		var outfinal = document.getElementById('outputNodeFinal');
                // Get the length of the outputNode
		var n = outnode.childNodes.length ;
                // Loop for the outnode element
		for ( var i = 0; i < n; i++ )
		{
			outnode.removeChild( outnode.firstChild ) ;
		}
                // Get the length of the outputNodeFinal
		var n = outfinal.childNodes.length ;
                // Loop for the outnode element
		for ( var i = 0; i < n; i++ )
		{
			outfinal.removeChild( outfinal.firstChild ) ;
		}             
	}
}
function AMnode2string( inNode, indent )
{
	// thanks to James Frazer for contributing an initial version of this function
	var i, str = '' ;
	if ( inNode.nodeType == 1 )
	{
		var name = inNode.nodeName.toLowerCase() ; // (IE fix)
		str = '\r' + indent + '<' + name ;
		for ( i = 0; i < inNode.attributes.length; i++ )
		{
			if ( inNode.attributes[i].nodeValue != 'italic' &&
				inNode.attributes[i].nodeValue != '' &&  //stop junk attributes
				inNode.attributes[i].nodeValue != 'inherit' && // (mostly IE)
				inNode.attributes[i].nodeValue != undefined &&
				inNode.attributes[i].nodeName[0] != '-' )
			{
				str += ' ' + inNode.attributes[i].nodeName + '=' + '"' + inNode.attributes[i].nodeValue + '"' ;
			}
		}
		if ( name == 'math' )
		{
			str += ' xmlns="http://www.w3.org/1998/Math/MathML"' ;
		}
		str += '>' ;
		for ( i = 0; i < inNode.childNodes.length; i++ )
		{
			str += AMnode2string( inNode.childNodes[i], indent + '  ' ) ;
		}
		if ( name != 'mo' && name != 'mi' && name != 'mn' ) str += '\r' + indent ;
		str += '</' + name + '>' ;
	}
	else if( inNode.nodeType == 3 )
	{
		var st = inNode.nodeValue ;
		for ( i = 0; i < st.length; i++ )
		{
			if ( st.charCodeAt( i ) < 32 || st.charCodeAt( i ) > 126 )
			{
				str += '&#' + st.charCodeAt( i ) + ';' ;
			}
			else if ( st.charAt(i) == '<' && indent != '  ' ) str += '&lt;' ;
			else if ( st.charAt(i) == '>' && indent != '  ' ) str += '&gt;' ;
			else if ( st.charAt(i) == '&' && indent != '  ' ) str += '&amp;' ;
			else str += st.charAt( i ) ;
		}
	}
	return str ;
}

function ShowMathML()
{
        
	if ( document.getElementById('inputText').value != '' )
	{
                // Get the list of subelements inside of the elemen math             
                var math = document.getElementById('outputNode').getElementsByTagName( 'math' )[0] ;
                
                // If math exist then show the mathml
		if ( math )
		{
			// Declare variable for the width
                        var width ; 
                        
                        // Get the width of the element outputNode
			if ( document.getElementById('outputNode').offsetWidth )
			{
				width = document.getElementById('outputNode').offsetWidth ;
			}
                        
                        // Set the new value of math
			math.parentNode.innerHTML = '<pre>' + CKEDITOR.tools.htmlEncode(AMnode2string( math, '' )) + '</pre>' ;
                      
                        // if CKEditor is running on a Gecko based browser, like Firefox                    
                        if ( width && CKEDITOR.env.gecko )
			{
                            document.getElementById('outputNode').style.width = width + 'px' ;                       
			}
                        document.getElementById( 'show_mathml').value = DlgAsciiMathFormulaPreview;
		}else{                                          
			document.getElementById( 'show_mathml').value = DlgAsciiMathShowMathML;
                        Preview() ;  
                   
		}
	}else{
		Preview() ;
	}
}

function CheckBrowserCompatibility( show_message )
{
	if ( CKEDITOR.env.Gecko )
	{
		// The browser is compatible, it is genuine Gecko - Firefox, etc.
		return true ;
	}
	else if ( CKEDITOR.env.ie )
	{
		// Internet Explorer.
		if ( CKEDITOR.env.ie6Compat)
		{
			if ( IsMathPlayerInstalled() )
			{
				var start = navigator.appVersion.indexOf( 'MathPlayer' ) ;
				if ( start != -1 )
				{
					// The browser is Internet Explorer 6.0+ with properly set up plugin MathPalyer 2.
					return true ;
				}
				else
				{
					// Notify reader they need to upgrade to MathPlayer 2.
					if ( show_message )
					{
						document.write( '<span style="color:red;">' + DlgAsciiMathOldMathPlayer + '</span>' ) ;
					}
					return false ;
				}
			}
			else
			{
				// Direct reader to MathPlayer page.
				if ( show_message )
				{
					document.write( '<span style="color:red;">' + DlgAsciiMathInstallMathPlayer + '</span>' ) ;
				}
				return false ;
			}
		}
		else
		{
			// The browser is a very old version of Internet Explorer, it have to be upgraded.
			if ( show_message )
			{
				document.write( '<span style="color:red;">' + DlgAsciiMathOldIE + '</span>' ) ;
			}
			return false ;
		}
	}
	else if ( CKEDITOR.env.opera  && parseFloat( navigator.appVersion, 10 ) >= 9.5 )
	{
		return true ;
	}

	// The browser is not compatible.
	if ( show_message )
	{
		document.write( '<span style="color:red;">' + DlgAsciiIncompatibleBrowser + '</span>' ) ;
	}
	return false ;
}

// Returns true if MathPlayer is installed.
function IsMathPlayerInstalled()
{
	try
	{
		var oMP = new ActiveXObject( 'MathPlayer.Factory.1' ) ;
		return true ;
	}
	catch(e)
	{
		return false ;
	}
}
