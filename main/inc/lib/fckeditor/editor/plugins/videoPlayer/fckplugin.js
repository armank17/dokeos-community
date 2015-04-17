// Register the command.
FCKCommands.RegisterCommand( 'videoPlayer',
	new FCKDialogCommand( FCKLang['DlgVideoPlayerTitle'], FCKLang['DlgVideoPlayerTitle'],
	FCKConfig.PluginsPath + 'videoPlayer/videoPlayer.html', 800, 480 )
) ;

// Create and register the toolbar button.
var oVideoPlayerItem = new FCKToolbarButton('videoPlayer', FCKLang['DlgVideoPlayerTitle']) ;
oVideoPlayerItem.IconPath	= FCKPlugins.Items['videoPlayer'].Path + 'videoPlayer.png' ;
FCKToolbarItems.RegisterItem( 'videoPlayer', oVideoPlayerItem ) ;
