<?php

class appcore_library_thumbnail_Thumbnail {

    var $image;
    var $type;
    var $width;
    var $height;

//---Método de leer la imagen
    function loadImage($name) {

//---Tomar las dimensiones de la imagen
        $info = getimagesize($name);

        $this->width = $info[0];
        $this->height = $info[1];
        $this->type = $info[2];

//---Dependiendo del tipo de imagen crear una nueva imagen
        switch ($this->type) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($name);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($name);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($name);
                break;
        }
    }

//---Método de guardar la imagen
    function save($name, $quality = 100) {

//---Guardar la imagen en el tipo de archivo correcto
        switch ($this->type) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->image, $name, $quality);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->image, $name);
                break;
            case IMAGETYPE_PNG:
                $pngquality = floor(($quality - 10) / 10);
                imagepng($this->image, $name, $pngquality);
                break;
        }
    }

//---Método de mostrar la imagen sin salvarla
    function show() {

//---Mostrar la imagen dependiendo del tipo de archivo
        switch ($this->type) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->image);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->image);
                break;
            case IMAGETYPE_PNG:
                imagepng($this->image);
                break;
        }
    }

//---Método de redimensionar la imagen sin deformarla
    function resize($value, $prop) {

//---Determinar la propiedad a redimensionar y la propiedad opuesta
        $prop_value = ($prop == 'width') ? $this->width : $this->height;
        $prop_versus = ($prop == 'width') ? $this->height : $this->width;

//---Determinar el valor opuesto a la propiedad a redimensionar
        $pcent = $value / $prop_value;
        $value_versus = $prop_versus * $pcent;

//---Crear la imagen dependiendo de la propiedad a variar
        $image = ($prop == 'width') ? imagecreatetruecolor($value, $value_versus) : imagecreatetruecolor($value_versus, $value);
        imagecreatetruecolor($value_versus, $value);
//---Hacer una copia de la imagen dependiendo de la propiedad a variar
        switch ($prop) {

            case 'width':
                imagecopyresampled($image, $this->image, 0, 0, 0, 0, $value, $value_versus, $this->width, $this->height);
                break;

            case 'height':
                imagecopyresampled($image, $this->image, 0, 0, 0, 0, $value_versus, $value, $this->width, $this->height);
                break;
        }

//---Actualizar la imagen y sus dimensiones
        $info = getimagesize($name);

        $this->width = imagesx($image);
        $this->height = imagesy($image);
        $this->image = $image;
    }

    function resize2($img, $w, $h, $newfilename) {

        //Check if GD extension is loaded
        if (!extension_loaded('gd') && !extension_loaded('gd2')) {
            trigger_error("GD is not loaded", E_USER_WARNING);
            return false;
        }

        //Get Image size info
        $imgInfo = getimagesize($img);
        switch ($imgInfo[2]) {
            case 1: $im = imagecreatefromgif($img);
                break;
            case 2: $im = imagecreatefromjpeg($img);
                break;
            case 3: $im = imagecreatefrompng($img);
                break;
            default: trigger_error('Unsupported filetype!', E_USER_WARNING);
                break;
        }

        //If image dimension is smaller, do not resize
        if ($imgInfo[0] <= $w && $imgInfo[1] <= $h) {
            $nHeight = $imgInfo[1];
            $nWidth = $imgInfo[0];
        } else {
            //yeah, resize it, but keep it proportional
            if ($w / $imgInfo[0] > $h / $imgInfo[1]) {
                $nWidth = $w;
                $nHeight = $imgInfo[1] * ($w / $imgInfo[0]);
            } else {
                $nWidth = $imgInfo[0] * ($h / $imgInfo[1]);
                $nHeight = $h;
            }
        }
        $nWidth = round($nWidth);
        $nHeight = round($nHeight);

        $newImg = imagecreatetruecolor($nWidth, $nHeight);

        /* Check if this image is PNG or GIF, then set if Transparent */
        if (($imgInfo[2] == 1) OR ($imgInfo[2] == 3)) {
            imagealphablending($newImg, false);
            imagesavealpha($newImg, true);
            $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
            imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);
        }
        imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);

        //Generate the file, and rename it to $newfilename
        umask(0);
        switch ($imgInfo[2]) {
            case 1: imagegif($newImg, $newfilename);
                break;
            case 2: imagejpeg($newImg, $newfilename);
                break;
            case 3: imagepng($newImg, $newfilename);
                break;
            default: trigger_error('Failed resize image!', E_USER_WARNING);
                break;
        }

        return $newfilename;
    }

//---Método de extraer una sección de la imagen sin deformarla
    function crop($cwidth, $cheight, $pos = 'center') {

//---Dependiendo del tamaño deseado redimensionar primero la imagen a uno de los valores
        if ($cwidth > $cheight) {
            $this->resize($cwidth, 'width');
        } else {
            $this->resize($cheight, 'height');
        }

//---Crear la imagen tomando la porción del centro de la imagen redimensionada con las dimensiones deseadas
        $image = imagecreatetruecolor($cwidth, $cheight);

        switch ($pos) {

            case 'center':
                imagecopyresampled($image, $this->image, 0, 0, abs(($this->width - $cwidth) / 2), abs(($this->height - $cheight) / 2), $cwidth, $cheight, $cwidth, $cheight);
                break;

            case 'left':
                imagecopyresampled($image, $this->image, 0, 0, 0, abs(($this->height - $cheight) / 2), $cwidth, $cheight, $cwidth, $cheight);
                break;

            case 'right':
                imagecopyresampled($image, $this->image, 0, 0, $this->width - $cwidth, abs(($this->height - $cheight) / 2), $cwidth, $cheight, $cwidth, $cheight);
                break;

            case 'top':
                imagecopyresampled($image, $this->image, 0, 0, abs(($this->width - $cwidth) / 2), 0, $cwidth, $cheight, $cwidth, $cheight);
                break;

            case 'bottom':
                imagecopyresampled($image, $this->image, 0, 0, abs(($this->width - $cwidth) / 2), $this->height - $cheight, $cwidth, $cheight, $cwidth, $cheight);
                break;
        }

        $this->image = $image;
    }

}