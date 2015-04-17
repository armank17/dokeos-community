<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>


	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><title>Dokeos 2.2 MOBILE - Dokeos credits</title>
	
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
					<div id="institution">Dokeos 2.2 MOBILE Credits</div>
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


<h1>Designer</h1>
<strong>Thomas De Praetere</strong> (thomas.depraetere@dokeos.com)

<h1>Developers</h1>
Professionals working for the Dokeos company
<ol>
  <li><strong>Christine Amory (christine.amory@dokeos.com)</strong>, Quiz templates, Mr Dokeos sketch style avatar</li>
  <li><strong>Frédéric Burlet (frederic.burlet@dokeos.com)</strong>, FCK Editor customization</li>
  <li><strong>Ludwig Chauvin (ludwig.chauvin@dokeos.com)</strong>, System performance, Reliability improvements</li>
  <li><strong>Patrick Cool </strong><strong> (patrick.cool@dokeos.com)</strong><strong>,</strong> Interface design, Translation tool, Quiz tool original design + design and development of many other tools</li>
  <li><strong>Françoise Desmartin (francoise.desmartin@dokeos.com)</strong>, Graphical design, Document templates, Mister Dokeos avatars</li>
  <li><strong>Noel Dieschburg (noel.dieschburg@dokeos.com)</strong>, LDAP, CAS, Authentication protocols, Sessions management, Security improvements</li>
  <li><strong>Isaac Flores (isaac.flores@dokeos.com)</strong>, Overall redesign of the software including Chat, Forum, Messages, Social Network, Ergonomics, Ajax design</li>
  <li><strong>Ludovic Gasc (ludovic.gasc@dokeos.com)</strong>, System performance and Reliability, Development Methodology</li>
  <li><strong>Arnaud Ligot (arnaud.ligot@dokeos.com)</strong>, Algorithmics, Videoconferencing, Optimization, Benchmarking, Unitary tests, Quality control</li>
  <li><strong>Eric Marguin (eric.marguin@dokeos.com)</strong>, Search, Reporting, Quiz, Flash developments, Scenario tool design, Templates engine, Mindmaps engine</li>
  <li><strong>Geerlie Moestar (geerlie.moestar@dokeos.com)</strong>, Documentation, Testing</li>
  <li><strong>Breetha Mohan (breetha.mohan@dokeos.com)</strong>, Quiz tool, Exams mode, Debugging, End user interfaces, Documentation, Core engine speed optimization</li>
  <li><strong>Claudio Montoya (claudio.montoya@dokeos.com)</strong>, Messages, Audio recorder, Mediabox, Videoconferencing</li>
  <li><strong>Julian Prud'homme (julien.prudhomme@dokeos.com)</strong>, Authoring tool, Reporting, Quiz, Authoring system, SCORM export</li>
  <li><strong>Kevin Vincendeau (kevin.vincendeau@dokeos.com)</strong>, Design and usability, CSS design</li>
  <li><strong>Christian Flores (christian.flores@dokeos.com)</strong>, Overall redesign of the software including Forum, Author tool, Social Network, Ergonomics</li>
</ol>


<h1>Community</h1>
A non-exhaustive list of people who contributed with ideas, code, documentation or any piece of intelligence to the project.

<ol>
  <li><strong>Mustapha Alouani</strong>, LDAP authentication</li>
  <li><strong>Martine Baurin</strong>, Users import concept</li>
  <li><strong>Pierre Bayet</strong>, Users profiling concept</li>
  <li><strong>Jan Bols, </strong>Mindmaps, Configuration Settings</li>
  <li><strong>Hubert Borderiou</strong>, Bugfixing</li>
  <li><strong>Olivier Brouckaert</strong>, Chat, Installation, Quiz</li>
  <li><strong>Oigul Cade</strong>, Hotspots tool usability concept</li>
  <li><strong>Olivier Cauberghe,</strong> Documents, MIndmaps</li>
  <li><strong>Franco Cedillo</strong>, Tiny bugfixing</li>
  <li><strong>Bob Claeys</strong>, Podcasting usability</li>
  <li><strong>Dominique Cotte</strong>, Wiki improvements concept</li>
  <li><strong>Cloé Delevaque</strong>, Course list and Course catalog concept</li>
  <li><strong>Isabel Deprez</strong>, Documentation</li>
  <li><strong>Jan Derriks</strong>, for various bugfixes and suggestions</li>
  <li><strong>Alexis Doutriaux</strong>, Videoconferencing concept</li>
  <li><strong>Evie Embrechts</strong>, Announcements, Installation, LDAP, Messages, Users</li>
  <li><strong>Diego Escalante Urrelo</strong>, Search</li>
  <li><strong>Christian Fasanando</strong>, Forum, User Image</li>
  <li><strong>Annick Filot</strong>, Mindmaps integration concept</li>
  <li><strong>René Haentjens,</strong> Metadata, Dropbox</li>
  <li><strong>Rudy Hanson</strong>, Interface, CSS</li>
  <li><strong>Elie Harfouche</strong>, User tool</li>
  <li><strong>Koen Heirbaut</strong>, Bugfixing</li>
  <li><strong>Jhon Hinojosa</strong>, Special exports</li>
  <li><strong>Sebastien Jacobs</strong>, Booking</li>
  <li><strong>Michel Jouhanneau</strong>, Quiz scoring reliability</li>
  <li><strong>Toon Keppens</strong>, Blog</li>
  <li><strong>An Kesenne</strong>, Survey and survey reporting concept</li>
  <li><strong>Maxence Maire</strong>, Ideas on SCORM LMS import of Serious Games</li>
  <li><strong>Michel Margolle</strong>, Installation process improvement</li>
  <li><strong>Sandra Mathijs, </strong>Course navigation menu, Interface, CSS</li>
  <li><strong>Marcel Menges</strong>, Agenda and Planning concept</li>
  <li><strong>Ravin Minocha</strong>, Authoring</li>
  <li><strong>Bart Mollet, </strong>Sortable table, Course copy, Course management, Users and many other tools</li>
  <li><strong>Julio Allen Montoya Armas</strong>, Messages, Survey, Social Network, User tool</li>
  <li><strong>Michel Moreau-Belliard</strong>, LDAP</li>
  <li><strong>Denes Nagy</strong>, Learning Path, Quiz</li>
  <li><strong>Emmanuel Nurit</strong>, Authoring system design</li>
  <li><strong>Noa Orizales Iglesias</strong>, Interface, CSS</li>
  <li><strong>Alain Paris</strong>, User-centered portal concept</li>
  <li><strong>Emmanuel Pecquet</strong>, Documentation master</li>
  <li><strong>Hugues Peeters</strong>, Focus on ergonomics, Simplicity, Imitation of Ms-Office, many key concepts of Dokeos</li>
  <li><strong>Pierrick Peigné</strong>, Authentication security configuration concept</li>
  <li><strong>Alexandre Pelissard, </strong>Fck Editor</li>
  <li><strong>Daniel Enrique Perales Gomez,</strong> Bugfixing</li>
  <li><strong>Furio Petrossi</strong>, Dokeos portable</li>
  <li><strong>Sebastien Piraux</strong>, Statistics</li>
  <li><strong>Arthur Jonathan Portugal,</strong> Documentation, Testing</li>
  <li><strong>Gaëtan Poupeney</strong>, Web services concept</li>
  <li><strong>Marie Prat</strong>, E-learning, Utiliser les outils du Web 2.0 pour développer un projet, ENI Editions</li>
  <li><strong>Marc Pybourdin</strong>, Serious Game concept</li>
  <li><strong>Juan Carlos Raña Trabado</strong>, Wiki, FCKeditor, Mimetex</li>
  <li><strong>Eric Remy</strong>, Agenda</li>
  <li><strong>Pablo Rey</strong>, Announcements</li>
  <li><strong>Ricardo Rodriguez</strong>, Unitary tests</li>
  <li><strong>Wolfgang Schneider</strong>, Course navigation menu, Interface, CSS</li>
  <li><strong>Marie-Rose Schollaert</strong>, Widgets- user-centered portal concept</li>
  <li><strong>Kris Seynaeve</strong>, Widgets- user-centered portal, RSS-feed concept</li>
  <li><strong>Priya Siani, </strong>Survey</li>
  <li><strong>Stefan Spruyt</strong>, SCORM-compliance benchmarking</li>
  <li><strong>Michel Taillet</strong>, Image Zones delineation quiz concept</li>
  <li><strong>Sophie Tremauville</strong>, Gradebook usability and overall concept</li>
  <li><strong>Toon Van Hoecke, </strong>Item property, Agenda</li>
  <li><strong>Luk Van Landuyt</strong>, Blog, Hotspots</li>
  <li><strong>Thierry Vivier</strong>, Internet Explorer compliance</li>
  <li><strong>Sebastian Wagner, </strong>Videoconferencing</li>
  <li><strong>Ivan Tcholakov</strong>, FCKeditor, Internationalisation</li>
  <li><strong>Université Jean Monnet de Saint Etienne</strong>, LDAP</li>
  <li><strong>Vincenzo Valentini</strong>, Clinical Cases concept</li>
  <li><strong>Kevin Van Den Haute</strong>, Blog, Hotspots, Authoring</li>
  <li><strong>Bert Vanderkimpen, Ghent University</strong>, Documents tool</li>
  <li><strong>Kristof Van Steenkiste</strong>, Booking</li>
  <li><strong>Dietrich Van Damme</strong>, Bugfixing</li>
  <li><strong>Ans Van der Ven,</strong> Mindmapping integration concept</li>
  <li><strong>Carlos Vargas</strong>, Forum</li>
  <li><strong>Ronny Velasquez</strong>, Bugfixing</li>
  <li><strong>Frederik Vermeire, </strong>Announcements</li>
  <li><strong>Marco Villegas Vega</strong>, Search</li>
  <li><strong>Jérôme Warnier</strong>, System performance and Reliability improvements</li>
  <li><strong>Yannick Warnier</strong>, Learning path, Database architecture, Extensions, Search, Quiz and many other tools</li>
  <li><strong>Marc Wattel</strong>, Oogie rapid Learning concept</li>
  <li><strong>Paul Wolstencroft,</strong> SCORM import and third party content interoperability</li>
</ol>


			<h1>Translations</h1>
			There are too much translators to list them all. Please see http://www.dokeos.com/DLTT/ to see a list of the translators.

			<h1>Forgotten?</h1>
			Although we try to be as exhaustive as possible it is very
			likely that we forgot some. Not because your contribution is not
			valuable. But because we just forgot to write your name down. Help us
			fix this, writing an email to sales@dokeos.com. <br />

			<div style="font-style: italic; padding-top: 20px;"><span style="font-weight: bold;">United States</span>&nbsp; : Dokeos, Faneuil Hall Marketplace Boston, Massachusetts 02109 United
States Tel. +1 617-973-5180 Email: support@dokeos.com<br />

<br />

<span style="font-weight: bold;">Europe</span> : Dokeos, 54/56 avenue Hoche 75008 Paris Tel : +33 (0)1 56 60 54 90 Email : sales.fr@dokeos.com</div>
		</div>
	</div>
</body></html>