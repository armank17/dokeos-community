<?php
// defino la carpeta para subir
$uploaddir = '../../../../../home/default_platform_document/ecommerce_thumb/';
// defino el nombre del archivo
$uploadfile = $uploaddir . 'session_'.basename($_FILES['userfile']['name']);
// Lo mueve a la carpeta elegida
umask(0);
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
  echo "success";
} else {
  echo "error";
}
?>
