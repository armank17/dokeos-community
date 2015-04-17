<!DOCTYPE HTML>
<html lang="en-US">
    <head>
        <title></title>
        <style>
            ul {
                margin:0;
                padding-left: 1.5em;
                line-height: 3.5em;
                list-style: none;
            }
            ul li { padding-left: .2em; }
            #contentDetail{
                padding: 60px;
            }
        </style>        
    </head>
    <body>
        <div style = "display: table; margin-left: auto; margin-right: auto;width: 100%;">
            <div style = "height: 70px; width: 100%; border-bottom: 1px solid #ececec; text-align: center;"><h1>Oops, page not found</h1></div>
            <div style = "float: left; height: 100%; width: 40%;">
                <div id="contentDetail">
                    <ul>
                        <li>Module: <?php echo $this->evaluate('module'); ?></li>
                        <li>Controller: <?php echo $this->evaluate('cmd'); ?></li>
                        <li>View: <?php echo $this->evaluate('func'); ?></li>
                    </ul>
                </div>
            </div>
            <div style = "float: right; height: 100%; width: 60%;"><img src="application/security/assets/images/404.png"></div>
            <div style = "clear: both; height: 35px; width: 100%;"></div>
        </div>    




    </body>
</html>