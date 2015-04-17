<!DOCTYPE html>
<html>
    <head>
    <title>Mobile</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
<!--	<title>Dokeos - Mobile</title>-->
        <link rel="stylesheet" href="/main/application/mobile/assets/jquery/jquery.mobile/jquery.mobile-1.1.1.css" />
<!--        <link rel="stylesheet" href="/main/application/mobile/assets/jquery/jquery.mobile/jquery.mobile.custom.css" />-->
        <script src="/main/application/mobile/assets/jquery/jquery.mobile/demos/js/jquery.js"></script>
        <script src="/main/application/mobile/assets/jquery/jquery.mobile/jquery.mobile-1.1.1.min.js"></script>
        <script src="/main/application/mobile/assets/scripts/script.js"></script>
    </head>
    
    <body>
        <div id="list-index" data-role="page" data-add-back-btn="true">
        <div data-role="header" data-position="inline">
    <!--        <a data-ajax="false" href="index.php?module=mobile&cmd=index" data-icon="home" data-iconpos="notext" class="ui-btn-right"></a>-->
            <h3>Dokeos - Mobile</h3>
                <a id="login-name" href="/main/index.php?module=mobile&cmd=close" data-ajax="false" data-icon="delete" class="ui-btn-right" title="Close">
                    Logout
                </a>
        </div>
	<!-- /navbar -->
        <!-- /footer -->
        <div data-role="content"> 

    <!--<div data-role="header" data-position="inline">
        <a href="index.html" data-icon="back" class="ui-btn-left">Back</a>
        <a href="http://dokeos.dev/" data-icon="home" data-ajax="false" class="ui-btn-left">Home</a>
        <h1>Course list</h1>
        <a href="http://dokeos.dev/main/core/views/mobile/index.php?action=logout&init=mobile" data-ajax="false" data-icon="delete" class="ui-btn-right">Logout</a>
    </div>-->