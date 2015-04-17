<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>


	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><title> Dokeos 2.2 MOBILE - Readme</title>
	
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
					<div id="institution">About Dokeos<br />
</div>
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

		<div id="header4">
			<div class="headerinner">
				<div id="help-content">
					<strong>General help</strong><br />
					<a href="../../documentation/installation_guide.html" target="_blank">read the installation guide (the document you are currently looking at)</a><br />
					<a href="http://dokeos.com/en/manual/installation" target="_blank">Read a more up to date installation manual on http://www.dokeos.com</a><br /><br />
				</div>
				<div id="help">
					<a href="#" style="" id="help-link">Help</a>
				</div>
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
			<h1>About Dokeos</h1>
			<p>Dokeos
is an elearning and course management web application and is free
software (GNU GPL). It's translated into 30 languages,SCORM compliant,
and light and flexible.</p>
			<p>Dokeos supports many different kinds of learning and collaboration
			activities. Teachers/trainers can create, manage and publish their
			courses through the web. Students/trainees can follow courses, read
			content or participate actively through groups, forums, chat.</p>
			<p>Technically, Dokeos is a web application written in PHP that stores
			data in a&nbsp; MySQL database. Users access it using a web browser.</p>
			<p>If you would like to know more or help develop this software, please visit our homepage at <a href="http://www.dokeos.com">http://www.dokeos.com</a></p>

			<h1>SCORM</h1> 
			Dokeos FREE imports AICC, Scorm 1.2 and SCORM 2004
			contents. For more information on Scorm normalisation, see
			http://www.adlnet.org

			<h1>Licence</h1>
			<p>Dokeos is distributed under the GNU General Public license (GPL). Read the <a href="licence.html">GNU General Public license (GPL)</a> .</p>


			<h1>Portability</h1>
			<p>Dokeos
is an AMP software. This means it should work on any platform running
Apache + MySQL + PHP. It is then supposed to work on the following
Operating Systems :</p>
			<ul>
				<li> Linux</li>
				<li> Windows (98, Me, NT4, 2000, XP, VISTA)</li>
				<li> Unix</li>
				<li> Mac OS X</li>
			</ul>
			<p>It has been tested on </p>
			<ul>
				<li> Fedora, Mandrake, Red Hat Enterprise Server, Ubuntu, Debian </li>
				<li> Windows XP, Windows 2000, Seven<br />
</li>
				<li> Mac OS X 10.3</li>
			</ul>
			<p>Email functions remain silent on systems where there is no mail
			sending software (Sendmail, Postfix, Hamster...), which is the case by
			default on a Windows machine.</p>


			<h1>Interoperability</h1>
			<p>Dokeos imports Scorm compliant learning contents. It imports then
			"On the shelve" contents from many companies : NETg, Skillsoft, Explio,
			Microsoft, Macromedia, etc.</p>
			<p>Admin interface imports users through CSV and XML. You can create a
			CSV file from a list of users in MS Excel. OpenOffice can export to
			both CSV and XML formats.</p>
			<p>Many database management systems, like Oracle, SAP, Access, SQL-Server, LDAP ... export to CSV and/or XML.</p>
			<p>Dokeos includes a LDAP module that allows admin to deactivate MySQL
			authentication and replace it by connection to a LDAP directory.</p>
			<p>Client side, Dokeos runs on any browser : Firefox, MS Internet
			Explorer (7.0+), Netscape (4.7+), Mozilla (1.2+), Safari, Opera, ...</p>

			<h1>Dokeos.com</h1>
			<p>Dokeos is also a consulting company helping companies in their e-learning projects.<br />
			   You can find more information on our services on <a href="http://www.dokeos.com/en/node/61">http://www.dokeos.com/en/node/61</a></p><br />
<h2>Contact<br />
</h2>
<span style="font-weight: bold;">United States</span>&nbsp; : Dokeos, Faneuil Hall Marketplace Boston, Massachusetts 02109 United
States Tel. +1 617-973-5180 Email: support@dokeos.com<br />


<br />


<span style="font-weight: bold;">Europe</span> : Dokeos, 54/56 avenue Hoche 75008 Paris Tel : +33 (0)1 56 60 54 90 Email : sales.fr@dokeos.com<br />
<br />
<br />
<br />
</div>
	</div>
</body></html>
