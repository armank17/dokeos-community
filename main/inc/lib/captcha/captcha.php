<?php
include_once('../../global.inc.php');

// het random nr. aanmaken en gecodeerd opslaan in php sessie
$randomnr = rand(1000, 9999);
$_SESSION['captcha'] = md5($randomnr);

// captcha plaatje met nummer maken - afmetingen kun je aanpassen gebruikte font

$im = imagecreatetruecolor(85, 38);

// Kleurenbepaling

$white = imagecolorallocate($im, 255, 255, 255);
$grey = imagecolorallocate($im, 128, 128, 128);
$lgrey = imagecolorallocate($im, 192, 192, 192);
$black = imagecolorallocate($im, 0, 0, 0);

// zwarte rechthoek tekenen - afmetingen kun je aanpassen aan verschillende fonts

imagefilledrectangle($im, 0, 0, 200, 35, $black);

// hier - font.ttf' vervangen met de locatie van je eigen font bestand

$font = dirname(__FILE__) . '/font.ttf';

// schaduw toevoegen

imagettftext($im, 15, 0, 22, 24, $grey, $font, $randomnr);
imagettftext($im, 15, 0, 29, 22, $lgrey, $font, $randomnr);

// randomnr. toevoegen

imagettftext($im, 15, 0, 15, 26, $white, $font, $randomnr);

// voorkomen dat afbeelding ge-cached wordt

header("Expires: Wed, 1 Jan 1997 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// plaatje verzenden naar browser

header ("Content-type: image/gif");
imagegif($im);
imagedestroy($im);
?>

