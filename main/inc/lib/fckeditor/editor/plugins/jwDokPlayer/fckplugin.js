// Register the command.
FCKCommands.RegisterCommand( 'jwDokPlayer',
	new FCKDialogCommand( FCKLang['DlgJwDokPlayerTitle'], FCKLang['DlgJwDokPlayerTitle'],
	FCKConfig.PluginsPath + 'jwDokPlayer/jwDokPlayer.html', 800, 570 )
) ;

// Create and register the toolbar button.
var oJwDokPlayerItem = new FCKToolbarButton( 'jwDokPlayer', FCKLang['DlgJwDokPlayerTitle']) ;
oJwDokPlayerItem.IconPath	= FCKPlugins.Items['jwDokPlayer'].Path + 'jwPlayer.gif' ;
FCKToolbarItems.RegisterItem('jwDokPlayer', oJwDokPlayerItem );
