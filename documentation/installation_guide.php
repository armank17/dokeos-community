<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>


	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><title>"Dokeos e-Learning Suite" - Installation guide</title>
	
	<link rel="stylesheet" href="../main/css/dokeos2_tablet/default.css" type="text/css" media="screen,projection" />
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
	<script type="text/javascript" src="../main/inc/lib/javascript/jquery-1.4.2.min.js" language="javascript" />
	<script type="text/javascript" language="javascript">
		jQuery(document).ready( function($) {
			// Expand or collapse the help
			$('#help-link').click(function () {
				$('#help-content').slideToggle('fast', function() {
					if ( $(this).hasClass('help-open') ) {
						$('#help a').css({'backgroundImage':'url("../main/img/screen-options-right.gif")'});
						$(this).removeClass('contextual-help-open');
					} else {
						$('#help a').css({'backgroundImage':'url("../main/img/screen-options-right-up.gif")'});
						$(this).addClass('help-open');
					}
				});
				return false;
			});
		});
	</script></head><body>
	<div id="header">
		<div id="header1">
				<div class="headerinner">
					<div id="institution">"Dokeos e-Learning Suite" Installation guide</div>
				</div>
		</div>
		<div id="header2">
			<div class="headerinner">
				<ul id="dokeostabs">
                                        <li class="install" id="current"><a href="../main/install/index.php">Installation</a></li>
                                        <li class="guide_na"><a href="../../documentation/installation_guide.php" target="_blank">Installation guide</a></li>
				</ul>
				<div style="clear: both;" class="clear"> </div>
			</div>
		</div>

	</div>

	<div id="main">
		<div class="actions">
			<a href="readme.html">About Dokeos</a> 
			<a href="installation_guide.html">Installation Guide</a>
			<a href="license.html">GPL license</a>
			<a href="credits.html">Credits</a>
			<a href="dependencies.html">Dependencies</a>
			<a href="changelog.html">Changelog</a>
			<a href="http://www.dokeos.com">Website</a>	
		</div>

		<div id="content">
			

			<h2>"Dokeos e-Learning Suite" Installation Guide</h2>
			<p>Thank you for downloading "Dokeos e-Learning Suite"<br />
</p>
			<ul>
				<li>Preview of <a href="http://www.dokeos.com/features/">Dokeos features&nbsp;</a></li>
				<li>Test Dokeos on you own <a href="http://www.dokeos.com/free-trial/">Dokeos Trial portal</a></li>
			</ul>


			<h2>Contents</h2>
			<ol>
				<li><a href="#1._Pre-requisites">Pre-requisites</a></li>
				<li><a href="#2._Installation_of_Dokeos_LMS">Installation of "Dokeos e-Learning Suite"<br />
</a></li>
				<li><a href="#3._Upgrade_from_a_previous_version_of">Upgrade from a previous version <br />
</a></li>
				<li><a href="#4._Troubleshooting">Troubleshooting</a></li>
				<li><a href="#5._Administration_section">Administration section</a></li>
				<li><a href="#6._LDAP">LDAP / Active Directory</a></li>
                                <li><a href="#7._Oogie_PowerPoint__Impress_conversion">OogieRapid Learning<br />
</a></li>
				<li><a href="#8._Videoconferencing">Videoconferencing</a></li>
				<li><a href="#9._Mathematical_formulas">Mathematics<br />
</a></li>
				<li><a href="#10._MultipleURL">Multisite</a></li>
  <li><a href="#11contact">Contact</a><br />
  </li>

			</ol>

		<h2><a name="1._Pre-requisites" id="1._Pre-requisites">1. Pre-requisites</a></h2>
		<p>Dokeos can be installed on Windows, Linux, Mac OS X and UNIX servers
		indifferently. However, we recommend the use of Linux server for
		optimal flexibility, remote control and scalability.</p>
		<p>Dokeos is mainly a LCMS running <strong>Apache 2, 2.0</strong>, <strong>MySQL 5.1</strong> and <strong>PHP 5.3</strong> (the so called <strong>AMP</strong> trilogy).
		All these software are open source and freely available. </p>
		<p>To run Dokeos on your server, you need to install WAMP, LAMP or MAMP:
		</p><ul>
			<li>To install <strong>WAMP</strong> (AMP on Windows), we recommend the <a href="http://www.apachefriends.org/en/xampp.html">XAMPP</a> .exe installer</li>
			<li>To install <strong>LAMP</strong> (AMP on Linux), use the Package manager of your favourite distribution (Synaptic, RPMFinder etc.).
				For instance, on a Ubuntu server,&nbsp;use Shell or Synaptic following the
	    		<a href="http://ubuntuguide.org/wiki/Ubuntu:Feisty#Apache_HTTP_Server">Ubuntuguide on Apache</a> and the following sections</li>
			<li>To install <strong>MAMP</strong> (AMP on Mac OS X), refer to the <a href="http://www.mamp.info/en/index.php">MAMP</a> dedicated website</li>
		</ul>

		<h3>MySQL database server</h3>
		You will need a login and password allowing to administrate and create
		at least one database. By default, Dokeos will create a new database
		for each course created. It means your host should allow you to create
		and administrate several databases. You can also install Dokeos using
		only one database, in that case you have to select this option during
		the installation, but before you must to create a database in your shared hosting.

		<h2><a name="2._Installation_of_Dokeos_LMS" id="2._Installation_of_Dokeos_LMS">2. Installation of "Dokeos e-Learning Suite"</a></h2>
		<ul>
			<li><a href="http://www.dokeos.com/node/33">Download "Dokeos e-Learning Suite"</a></li>
			<li>Unzip it</li>
			<li>Copy the dokeos directory in your Apache web directory. This can be <strong>C:\xampp\htdocs\</strong> on a Windows server or <strong>/var/www/html/</strong> on a Linux server</li>
			<li>Open your web browser (Internet Explorer, Firefox, Chrome, Safari, ...) and type
				<strong>http://localhost/dokeos/</strong> if you install locally or
				<strong>http://www.domain.com/dokeos/</strong> if you install remotely</li>
			<li>Follow the web installation process. You can accept all default values. Consider changing the admin password to remember it.</li>
		</ul>
		<p>The following directories need to be readable, writeable and executable for everyone:
		</p><ul>
			<li>dokeos/main/inc/conf/</li>
			<li>dokeos/main/upload/users/</li>
			<li>dokeos/main/default_course_document/</li>
			<li>dokeos/archive/</li>
			<li>dokeos/courses/</li>
			<li>dokeos/home/</li>
		</ul>
(where 'dokeos' is the directory in which you installed Dokeos). On
Linux, Mac OS X and BSD operating systems you can use the CHMOD 777
command for this (although we recommend you seek advice from an
experienced system administrator). In Windows, you may need to check
the properties of the folders (by right-clicking on them). <p>The following files need to be readable and writeable for the web browser, only during the installation process:</p>
		<ul>
			<li>dokeos/main/inc/conf/configuration.php (if present)</li>
		</ul>

		<p>On Linux, Mac OS X and BSD operating systems you can use the CHMOD
		666 command for this (although we recommend you seek advice from an
		experienced system administrator).
		In Windows, you may need to check the properties of the files and
		folders (by right-clicking on them).</p>

		<p><strong>NOTES:</strong><br />
		Do not modify the home_*.html files directly. Instead, choose "Configure the homepage" in the Dokeos administration section.</p>

		<p><strong>Windows</strong> : with combination packages like XAMPP, out of the box, login and password for MySQL should probably remain empty.</p>

		<h3>Configuration and security after installation</h3>
		<ul>
			<li><strong>Protect your configuration file: </strong>
			make sure no one can overwrite it. You can find the config file in <em>(dokeos folder)</em>/main/inc/conf/configuration.php.
			Make it read-only (windows/xwindows: right-click the file to edit the
			properties. linux/bsd/macosx: use the chmod 444 command). The config
			file is created by Apache so you may need to be root user to change its
			permissions.</li>
			<li><strong>Protect your installation folder: </strong>
			if the <em>(dokeos folder)</em>/main/install
			folder is still accessible, someone could install over your existing
			version (you could lose your data that way). Move the folder somewhere
			out of the web directories so it is not accessible, change its name, or
			edit its properties so no one can read or execute it.</li>
			<li><strong>For better security: </strong>
			making the files world-writable will help you install, and solves many
			issues for people without much admin experience. However, it's better
			security to make the owner of the apache process (often called apache
			or www-data) also owner of all the dokeos files and folders. Ths way,
			these files need only be readable and writable by the Apache process
			owner, not by the entire world.</li>
			<li><strong>Configure your Dokeos installation: </strong>
			in the administration section of Dokeos, you can use the Dokeos Config Settings to adjust the behavior of your installation.</li>
			<li><strong>Configure Dokeos mail: </strong>
			most of Dokeos uses the mail settings from the php.ini file. However,
			the announcements tool uses phpMailer (another free software project)
			and the settings for this tool can be adjusted in <em>(dokeos folder)</em>/main/inc/conf/mail.conf.php.</li>
		</ul>

		<h3>PHP configuration</h3>
		To get the best of Dokeos, you need to finetune PHP settings. Consider :
		<ul>
			<li>Editing php.ini file (on windows can be located at <span style="font-weight: bold;">C:\xampp\php\php.ini</span>, on Ubuntu Linux : <span style="font-weight: bold;">/etc/php5/apache2/php.ini</span></li>
			<li>search the word "max" and increase values to optimise the server</li>
			<li>you may want to end up&nbsp;with the following values : </li>
		</ul>
		<div class="code">
			max_execution_time = 300&nbsp;&nbsp;&nbsp; ; Maximum execution time of each script, in seconds<br />
			max_input_time = 600 ; Maximum amount of time each script may spend parsing request data<br />
			memory_limit = 512M&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ; Maximum amount of memory a script may consume (128MB)<br />
			post_max_size = 64M<br />
			upload_max_filesize = 200M
		</div>


		<p>Some users could meet problems if their PHP settings don't fit
		these ones:</p>
		<ul>
		  <li>short_open_tag       = On</li>
		  <li>safe_mode            = Off</li>
		  <li>magic_quotes_gpc     = Off</li>
		  <li>magic_quotes_runtime = Off</li>
		</ul>

		<p>Past Dokeos versions required register_globals to be set to On. This is
		no longer necessary, this can be set to Off and Dokeos will work fine.</p>
                <p><strong>Note:</strong> if you are using PHP 5.3 or higher, you need to set your date.timezone setting to whatever your server's timezone is. For example, if your server is in the 'America/Lima' timezone, set this in your php.ini:
                    date.timezone = 'America/Lima'</p>

                <p><strong>Note:</strong> PHP 5.3.9 introduces a new setting "max_input_vars", which limits the number of elements you can send in one single form. If you are dealing with numerous users, make sure you set this setting higher than its default value of 1000.</p>

		<p><strong>BSD users:</strong> these php libraries have to be included during php installation:</p>
		<ul>
		  <li>php-mysql The mysql shared extension for php</li>
		  <li>php-pcre The pcre shared extension for php</li>
		  <li>php-session The session shared extension for php</li>
		  <li>php-zlib The zlib shared extension for php</li>
		  <li>(optional) php-ldap if you want to be able to use LDAP authentication</li>
		</ul>

		<p>You might also add the following php modules and packages:</p>
		<ul>
			<li>php-ctype</li>
			<li>php-gd</li>
			<li>php-iconv</li>
			<li>php-json</li> 
			<li>php-mbstring</li></ul>
		


		<h2><a name="3._Upgrade_from_a_previous_version_of" id="3._Upgrade_from_a_previous_version_of">3. Upgrade from a previous version<br />
</a></h2>
		<p>Before upgrading we <strong>heavily</strong> recommend you do a full backup of the previous Dokeos directories and databases. If you are unsure how to achieve this
		please ask your hosting provider for advice.</p>
<h3>3.0 From Dokeos 2.x<br />
</h3>

		<em>If you upgrade from Dokeos 2.x</em> :
		
<ul>
<li>check that you haven't left any customised stylesheet or image*</li><li>download the "Dokeos e-Learning Suite" install package from the <a href="http://www.dokeos.com/dokeos-community-edition-download/">Dokeos download page</a></li><li> unzip the new files of "Dokeos e-Learning Suite" over the files of the older version</li><li> point your browser on your portal URL + main/install/</li><li> choose your language and click&nbsp;<em>Upgrade from 2.x<br />
</em></li>
</ul>

		<em> </em>
<p>
	
		</p>
<h3>3.1 From Dokeos 1.8.x</h3>
		<em>If you upgrade from Dokeos 1.8.x</em> :
		<ul>
			<li>check that you haven't left any customised stylesheet or image*</li>
			<li>download the "Dokeos e-Learning Suite" install package from the <a href="http://www.dokeos.com/dokeos-community-edition-download/">Dokeos download page</a></li>
	  		<li> unzip the new files of "Dokeos e-Learning Suite" over the files of the older version</li>
	  		<li> point your browser on your portal URL + main/install/</li>
	  		<li> choose your language and click&nbsp;<em>Upgrade from 1.8.x.</em></li>
		</ul>
		<em>* Styles and images are located in the main/css or main/img
		directories. You can still recover them from your backup if you have made it.
		 Any modified style or image that uses the default style/image name will be
		 overwritten by the next step. To avoid loosing your customisations, always
		 ensure you copy the styles/images under a new name and use and modify the
		 copy, not the original. The original will always be overwritten by newer
		 versions. In Dokeos 1.8.5, we have changed the name of several CSS themes.
		 Backwards compatibility is ensured by the fact that an upgrade only adds the
		new themes, but you should try and use these new themes rather than sticking
		to the old ones which will be deprecated shortly (not maintained).</em>

		<h3>3.2 From Dokeos 1.6.x</h3>
		<p>An easy way to do that is to create a subdirectory called <em>old_version</em>
		in your current Dokeos directory and move everything in there using a
		simple "move" command (i.e. under Linux: mkdir old_version; mv *
		old_verion/), then make the old_version/ directory writeable by the web
		server so that courses/ and upload/ directories can be moved from the
		old to the new installation.
		</p><p>The complete process is as follow:
		</p><ul>
	  		<li> move the current Dokeos directory contents to a subdirectory called <em>old_version</em> and make it writeable by the web server. This
			is important to allow the move of the courses/ and upload/ directories to the new install</li>
	  		<li> download the "Dokeos e-Learning Suite" package from the <a href="http://www.dokeos.com/dokeos-community-edition-download/">Dokeos download page</a></li>
	  		<li> unzip the new files of "Dokeos e-Learning Suite" in the main Dokeos directory. The new directory <em>main</em> should be located directly inside your Dokeos root folder</li>
	  		<li> point your browser on your portal URL</li>
	  		<li> choose your language and click&nbsp;<em>Upgrade from 1.6.x</em> and confirm the current directory of the old version</li>
		</ul>
		<em> NOTE: </em> The upgrade from 1.6.x to "Dokeos e-Learning Suite" implies a revision of
		the customised graphics and styles. The new version uses a complete new
		set of icons and styles, which means the ones from version 1.6 cannot be
		simply reused. The good news is the version "Dokeos e-Learning Suite" allows you to create
		your own style in a separate css folder.
		

		<h3>WARNING</h3>
		<p>Do not delete the previous Dokeos installation directory before installing
		the new one. When the update is successfully finished, you can remove
		the old path.</p>
		<p>Do not modify the home_*.html files directly. Instead, choose "Configure the homepage" in the Dokeos administration section.</p>

		<h3>3.3 In both cases</h3>

		The following directories need to be readable, writeable and executable for the web server:
		<ul>
		  <li>dokeos/main/inc/conf/</li>
		  <li>dokeos/main/upload/users/</li>
		  <li>dokeos/main/default_course_document/</li>
		  <li>dokeos/archive/</li>
		  <li>dokeos/courses/</li>
		  <li>dokeos/home/</li>
		</ul>

		On Linux, Mac OS X and BSD operating systems you can quick-fix this using the
		CHMOD 777 command, but if you are unsure, we recommend you seek advice for
		your own OS.
		In Windows, you may need to check the properties of the folders.
		<h3>3.4 Quick-upgrade from 1.8.x or "Dokeos e-Learning Suite" guide for Linux servers<br />
</h3>


		The following quick-upgrade guide assumes that:
		<ul>
		  <li>the Dokeos database username (for MySQL) is "dokeos_db_user" and your login is "dokeos_user"</li>
		  <li>the Dokeos installation is currently in /var/www/dokeos/ and it has 777 permissions</li>
		  <li>your portal's URL is http://www.portalurl.com/</li>
		</ul>

		On the command-line, type:
		<ul>
		  <li>cd /tmp</li>
		  <li>mysqldump -u dokeos_db_user -p --all-databases --result-file=/home/dokeos_user/dokeos_old.sql</li>
		  <li>cp -ra /var/www/dokeos /home/dokeos_user/backup_dokeos</li>
		  <li>mkdir /var/www/dokeos/old_version</li>
		  <li>mv /var/www/dokeos/* /var/www/dokeos/old_version/</li>
		  <li>chmod -R 0777 /var/www/dokeos/old_version/</li>
		  <li>Download the "Dokeos e-Learning Suite" package</li>
		  <li>unzip zxvf dokeosce.zip</li>
		  <li>sudo cp -ra dokeosce/* /var/www/dokeos/</li>
		  <li>rm dokeosce.zip</li>
		  <li>sudo rm -r dokeosce/</li>
		</ul>

		Then:
		<ul>
		  <li>Direct your browser to http://www.portalurl.com/main/install/</li>
		  <li>Proceed with the installation</li>
		  <li>Review the directories permissions</li>
		</ul>


		<h2><a name="4._Troubleshooting" id="4._Troubleshooting">4. Troubleshooting</a></h2>
		<p>If you have&nbsp;problems, go to the <a href="http://www.dokeos.com">Dokeos website</p>


		<h2><a name="5._Administration_section" id="5._Administration_section">5. Administration section</a></h2>
		<p>To access the Dokeos administration section, open browser,
		go to your Dokeos adress and log in with the admin user.
		Then you will see a "Platform admin section" link in the header of the
		web page. There you can manage users, courses, sessions, portal look
		and feel, homepage content, course categories etc.</p>


		<h2><a name="6._LDAP" id="6._LDAP">6. LDAP</a></h2>
		<p><em>This part is optional, only organisations with an LDAP server will need to read this.</em>
		An LDAP module is already provided in Dokeos, but it has to be configured to make it work.</p>
	
		<h3>Compiling</h3>
		<p>Linux
servers: It's possible that you have to recompile php with ldap
support. Newer distributions also allow downloading rpms for additional
packages.</p>

		<h3>Activating LDAP in Dokeos</h3>
		<p>In (dokeos folder)/main/inc/conf/configuration.php, around line 90, you see</p>
		<div class="code">
			//for new login module<br />
			//uncomment these to activate ldap<br />
			//$extAuthSource['ldap']['login'] = "./main/auth/ldap/login.php";<br />
			//$extAuthSource['ldap']['newUser'] = "./main/auth/ldap/newUser.php";<br />
		</div>
		remove the // from the last two lines to activate LDAP.<br />

		<h3>Settings</h3>
		<p>Ask the LDAP server admin for the settings:
		</p><ul>
			<li>ldap server name</li>
			<li>ldap server port (usually 389)</li>
			<li>ldap dc</li>
		</ul>
		<p>Since 1.8.5, you have to change the LDAP settings inside the "Portal
		administration" panel, under "Dokeos configuration settings", section
		"LDAP".
		</p>

		<p>As an example, you should find the following kind of values:<br />
		LDAP main server's address: "myldapserver.com";  // your ldap server<br />
		LDAP main server's port: 389;                 // your ldap server's port number<br />
		LDAP domain: "dc=xx, dc=yy, dc=zz"; //domain<br />
		</p>

		<h3>Teacher/student status</h3>
		<p>By default, Dokeos will check if the "employeenumber" field has a value. If it has, then Dokeos will
		consider this user as being a teacher.<br />
		If you want to change this behaviour, you can edit main/auth/ldap/authldap.php, function ldap_put_user_info_locally(),
		and change the <em>if (empty($info_array[$tutor_field]))</em> condition to whatever suits you.<br />
		You can also remove this check by removing the condition and leaving only the <em>$status = STUDENT;</em> line.</p>

		<h3>Protected LDAP servers</h3>
		<p>Some LDAP servers do not support anonymous use of the directory services.<br />
		In this case, you should fill in the appropriate fields in the
		administration panel (e.g. "manager" and "mypassword") and Dokeos will
		try to authenticate using these, or fall back to anonymous mode before
		giving up.</p>

		<h3>LDAP import into sessions</h3>
		<p>There is a new set of scripts now that allow you to insert users
		from LDAP directly into a Dokeos session. This, however, relies on a
		set of static choices in the LDAP contact attributes.<br />
		The fields used intensively by the Dokeos module are:
			</p><ul>
				<li>uid, which is matched to the username in Dokeos</li>
				<li>userPassword, which is matched to the user password, although
					this part will only work for non-encrypted passwords for now, but it
					shouldn't be necessary if using the LDAP server as authentication</li>
				<li>ou should end with the year of the person registration or any
					criteria you will use to filter users, so that they can be retrieved on
					that criteria</li>
				<li>sn is used as the lastname field in Dokeos</li>
				<li>givenName is used as the firstname field in Dokeos</li>
				<li>mail is used as the email field in Dokeos</li>
			</ul>
		


		<h2><a name="7._Oogie_PowerPoint__Impress_conversion" id="7._Oogie_PowerPoint__Impress_conversion">7. Oogie Rapid learning<br />
</a></h2> Powerpoint conversion is available in Dokeos PRO. This
feature will convert your slides into SCORM compliant courses and allow
audio record on top of slides + adding quizzes in between the
slides.&nbsp; <br />

	<span style="font-weight: bold;"><br />
	</span>
			  
	<h3>7.2.&nbsp;Audio-recorder</h3>
	Audio recording is available in Dokeos PRO and managed externally and audio is uploaded through MP3 format files<br />
	<br />

			  

	<h2><a name="8._Videoconferencing" id="8._Videoconferencing">8. Videoconferencing</a></h2>
	Videoconferencing is available in Dokeos PRO. <i><br />
<br />
</i><h2><a name="9._Mathematical_formulas" id="9._Mathematical_formulas">9. Mathematics</a></h2>
			<i>This part is optional, only organisations wanting to use mathematical formulas inside the online editor might want to read this.</i><br />
			You can enable mathematical equations writing inside the Dokeos online editor (FCKEditor) by applying the following steps:
			<ul>
				<li>1. Configure your Apache installation to add a cgi-bin directory that contains a symbolic link to the mimetex.cgi in <em>dokeos/main/inc/lib/mimetex/</em>(*see below)</li>
				<li>2. Reload your Apache configuration</li>
				<li>3. Edit the <em>dokeos/main/inc/lib/fckeditor/myconfig.js</em> and
				<ul>
					<li>3.1. Add <b>FCKConfig.Plugins.Add("mimetex", "en", sOtherPluginPath ) ;</b> at the end of the file</li>
					<li>3.2. Add <b>'mimetex'</b> at the end of the FCKConfig.ToolbarSets lines where you want the LaTeX icon to appear
					(there is one FCKConfig.ToolbarSets by tool). For example:
					<div class="code">FCKConfig.ToolbarSets["Test"] = [<br />&nbsp;&nbsp;['Bold','Italic','Underline','StrikeThrough','Subscript','Superscript','Link','Unlink','ImageManager','MP3','OrderedList','UnorderedList','Table','mimetex']<br />] ;</div>
					</li>
				</ul>
				</li>
				<li>4. For Windows servers only, update <i>dokeos/main/inc/lib/fckeditor/editor/plugins/mimetex/mimetex.html</i> to replace <b>mimetex.cgi</b> by <b>mimetex.exe</b></li>
				<li>5. Clear your browser's cache to test it (very important). This can be done using your browser's settings page</li>
			</ul>
			<i>Adding the corresponding cgi-bin directory to your Apache configuration could be done, in Apache 2, like this:</i>

			<div class="code">
			ScriptAlias /cgi-bin/ /var/www/cgi-bin/<br />
			&lt;Directory "/var/www/cgi-bin"&gt;<br />
			&nbsp;&nbsp;AllowOverride None<br />
			&nbsp;&nbsp;Options ExecCGI -MultiViews +SymLinksIfOwnerMatch<br />
			&nbsp;&nbsp;Order allow,deny<br />
			&nbsp;&nbsp;Allow from all<br />
			&lt;/Directory&gt;<br />
			</div>


			<i>Adding a symbolic link can be done, under Windows, by creating a
				shortcut to the mimetex.exe file from the cgi-bin directory, or under
				Linux by issuing the following command:</i>
			<div class="code">ln
-s /var/www/dokeos/main/inc/lib/mimetex/mimetex.cgi
/var/www/cgi-bin/mimetex.cgi/div&gt; This procedure should make a new
icon available in your Dokeos online editor, which will make it
possible to insert mathematical formulas into your documents. <h2><a name="10._MultipleURL" id="10._MultipleURL">10. Multisite<br />
</a></h2>
			With Dokeos PRO you can run same portal under multiple
URLs. This is a typical use for a Corporate company with various
Business Objects, Training company with various clients or University
with various departments. <br />
<br />
<br />
<h2><a name="11contact" id="10._MultipleURL">11. Contact<br />
</a></h2>
<span style="font-weight: bold;">United States</span>&nbsp; : Dokeos, Faneuil Hall Marketplace Boston, Massachusetts 02109 United
States Tel. +1 617-973-5180 Email: support@dokeos.com<br />
<br />
<span style="font-weight: bold;">Europe</span> : Dokeos, 54/56 avenue Hoche 75008 Paris Tel : +33 (0)1 56 60 54 90 Email : sales.fr@dokeos.com<br />
<br />
<br />



			<div style="font-style: italic; padding-top: 20px;"><br />
<br />
</div>
		</div>
	</div>
</div></body></html>