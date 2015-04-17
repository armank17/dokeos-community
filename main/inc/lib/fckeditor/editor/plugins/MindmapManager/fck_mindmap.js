var dialog		= window.parent ;
var oEditor = window.parent.InnerDialogLoaded() ;
var FCK		= oEditor.FCK ;
var FCKLang		= oEditor.FCKLang ;
var FCKConfig	= oEditor.FCKConfig ;
var FCKTools	= oEditor.FCKTools ;
var mindmap_url="";

// Set the language direction.
window.document.dir = oEditor.FCKLang.Dir ;

// We have to avoid javascript errors if some language variables have not been defined.
FCKLang['UploadSelectFileFirst'] = FCKLang['UploadSelectFileFirst'] ? FCKLang['UploadSelectFileFirst'] : 'Please, select a file before pressing the upload button.' ;
FCKLang['FileSuccessfullyUploaded'] = FCKLang['FileSuccessfullyUploaded'] ? FCKLang['FileSuccessfullyUploaded'] : 'Your file has been successfully uploaded.' ;
FCKLang['FileRenamed'] = FCKLang['FileRenamed'] ? FCKLang['FileRenamed'] : 'A file with the same name is already available. The uploaded file has been renamed to ' ;
FCKLang['InvalidFileType'] = FCKLang['InvalidFileType'] ? FCKLang['InvalidFileType'] : 'Invalid file type.' ;
FCKLang['SecurityError'] = FCKLang['SecurityError'] ? FCKLang['SecurityError'] : 'Security error. You probably don\'t have enough permissions to upload. Please check your server.' ;
FCKLang['ConnectorDisabled'] = FCKLang['ConnectorDisabled'] ? FCKLang['ConnectorDisabled'] : 'The upload feature (connector) is disabled.' ;
FCKLang['UploadError'] = FCKLang['UploadError'] ? FCKLang['UploadError'] : 'Error on file upload. Error number: ' ;

// Set the dialog tabs.
window.parent.AddTab( 'Info', FCKLang.DlgMindmapTab ) ;
window.parent.AddTab( 'Upload', FCKLang.DlgMindmapUpload ) ;

function OnDialogTabChange( tabCode )
{
	ShowE('divInfo'		, ( tabCode == 'Info' ) ) ;
	ShowE('divUpload'	, ( tabCode == 'Upload' ) ) ;
}



function window_onload(tab_to_select)
{
	// Translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	if (!tab_to_select)
	{
		window.parent.SetSelectedTab( 'Info' ) ;
		//window.parent.SetSelectedTab( 'Upload' ) ;
	}
	else
	{
		window.parent.SetSelectedTab( tab_to_select ) ;
	}

	// Load the selected element information (if any).
	LoadSelection() ;

	// Show/Hide the "Browse Server" button.
	GetE('tdBrowse').style.display = 'none' ;

	window.parent.SetAutoSize( true ) ;

	// Activate the "OK" button.
	//window.parent.SetOkButton( true ) ;
}
//#### The OK button was hit.
function Ok()
{
	if ( GetE('mindmapUrl').value.length == 0 )
	{
		window.parent.SetSelectedTab( 'Info' ) ;
		GetE('mindmapUrl').focus() ;
		alert( 'Please insert the URL.' ) ;
		return false ;
	}

	oImage	= FCK.EditorDocument.createElement('img');
	SetAttribute(oImage, 'src', GetE('mindmapUrl').value);
	FCK.InsertElement( oImage ) ;

	return true ;
}

function SetUrl( url )
{
	document.getElementById('mindmapUrl').value = url ;
	//updatePreview();
	Ok();
	window.parent.Cancel();
}
