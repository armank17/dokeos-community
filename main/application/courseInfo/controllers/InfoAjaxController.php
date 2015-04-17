<?php

class application_courseInfo_controllers_InfoAjax extends application_courseInfo_controllers_Info {

    public function __construct() {
        parent::__construct();
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
        require_once (api_get_path(LIBRARY_PATH) . 'SimpleImage.lib.php');
    }

    public function removeTempLogo() {
        //$logo_sys_path_temp = api_get_path(SYS_COURSE_PATH) . $this->courseInfo['path'] . '/temp/';
        //$name_img_temp = 'course_logo';
        $logo_sys_path_temp = api_get_path(SYS_COURSE_PATH) . $this->courseInfo['path'] . '/tempo_courselogo';
        $exts = array("jpg", "jpeg", "gif", "png");
        foreach ($exts as $ext) {
            $tmpfile = $logo_sys_path_temp . '.' . $ext;
            unlink($tmpfile);
        }
    }

    public function removeTempLogoHome() {
        $logo_sys_path_temp = api_get_path(SYS_PATH) . '/home/logo/tempo_logohome';
        $exts = array("jpg", "jpeg", "gif", "png");
        foreach ($exts as $ext) {
            $tmpfile = $logo_sys_path_temp . '.' . $ext;
            unlink($tmpfile);
        }
    }

    public function removeCourseLogo() {
        $logo_sys_path_temp = api_get_path(SYS_COURSE_PATH) . $this->courseInfo['path'] . '/';
        $name_img_temp = 'course_logo';
        $exts = array("jpg", "jpeg", "gif", "png");
        foreach ($exts as $ext) {
            $tmpfile = $logo_sys_path_temp . $name_img_temp . '.' . $ext;
            unlink($tmpfile);
        }
    }

    public function removeTempShopLogo() {
        $logo_sys_path_temp = api_get_path(SYS_CODE_PATH) . 'application/ecommerce/assets/images/';
        $name_img_temp = 'temp_shop_logo_' . $this->courseInfo['path'];
        $exts = array("jpg", "jpeg", "gif", "png");
        foreach ($exts as $ext) {
            $tmpfile = $logo_sys_path_temp . $name_img_temp . '.' . $ext;
            unlink($tmpfile);
        }
    }

    public function removeTempSlide() {
        $logo_sys_path_temp = api_get_path(SYS_PATH) . 'home/default_platform_document/';
        $name_img_temp = 'temp_slide_*';
        foreach (glob($logo_sys_path_temp . $name_img_temp) as $path_file) {
            unlink($path_file);
        }
    }

    public function removeTempHomeLogo() {
        $logo_sys_path_temp = api_get_path(SYS_PATH) . '/home/logo/';
        $name_img_temp = 'temp_home_logo';
        $exts = array("jpg", "jpeg", "gif", "png");
        foreach ($exts as $ext) {
            $tmpfile = $logo_sys_path_temp . $name_img_temp . '.' . $ext;
            unlink($tmpfile);
        }
    }

    public function uploadlogocourseEx() {
        extract($_POST);

        $logo_sys_path_temp = api_get_path(SYS_COURSE_PATH) . $this->courseInfo['path'] . '/temp/';
        $logo_sys_path = api_get_path(SYS_COURSE_PATH) . $this->courseInfo['path'] . '/';
        $namefile = 'photoimg';
        $name_img = 'course_logo';
        $set_width = 185; //500
        $set_height = 140; //375
        $pathinfo = pathinfo($_FILES[$namefile]['name']);
        $ext = strtolower($pathinfo['extension']);
        $time = time();
        $uploadfile = $logo_sys_path . $name_img . '.' . $ext;
        $tmpfile = $logo_sys_path_temp . $name_img . '.' . $ext;

        list($width, $height, $type, $attr) = getimagesize($_FILES[$namefile]['tmp_name']);

        if (move_uploaded_file($_FILES[$namefile]['tmp_name'], $tmpfile)) {
            if ($width > $set_width || $height > $set_height) {
                $img = new SimpleImage();
                $img->load($tmpfile);
                $maxSize = 375;
                $ratio_orig = $img->get_width() / $img->get_height();
                $width = $maxSize;
                $height = $maxSize;

                if ($ratio_orig < 1) {
                    $width = $height * $ratio_orig;
                } else {
                    $height = $width / $ratio_orig;
                }
                $img->resize($width, $height, $action)->save($tmpfile);
            } else { //implemented to fill white background image
                $img = new SimpleImage();
                $img->load($tmpfile);
                $image_rgb = imagecreatetruecolor(185, 140);
                $white = imagecolorallocate($image_rgb, 255, 255, 255);
                imagefill($image_rgb, 0, 0, $white);
                $width = $img->get_width();
                $height = $img->get_height();

                switch ($ext) {
                    case "png":
                        $transparent = imagecolorallocate($image_rgb, 255, 255, 255);
                        imagefill($image_rgb, 0, 0, $transparent);
                        $image = imagecreatefrompng($tmpfile);
                        break;
                    case "jpg":
                        $image = imagecreatefromjpeg($tmpfile);
                        break;
                    case "gif":
                        $image = imagecreatefromgif($tmpfile);
                        break;
                }
                imagecopyresampled($image_rgb, $image, (185 - $width) / 2, (140 - $height) / 2, 0, 0, $width, $height, $width, $height);

                switch ($ext) {
                    case "png":
                        imagepng($image_rgb, $tmpfile);
                        break;
                    case "jpg":
                        imagejpeg($image_rgb, $tmpfile, 100);
                        break;
                    case "gif":
                        imagegif($image_rgb, $tmpfile);
                        break;
                }
            }

            $new_file_path = pathinfo($tmpfile);

            echo json_encode(array(
                'src' => $new_file_path['basename'],
                'width' => $width,
                'height' => $height,
                'ext' => $ext
            ));
            exit;
        }
    }

    public function uploadImageCrop2() {
        $side = $_REQUEST['side'];
        $side = ($side == 'answer') ? "answer" : "option";
        $logo_sys_path = api_get_path(SYS_PATH) . $_REQUEST['path'];
        $post_id = $_REQUEST['post_id'];
        $name_img = $_REQUEST['name'];
        $min_width = $_REQUEST['min_width'];
        $min_height = $_REQUEST['min_height'];
        $max_width = $_REQUEST['max_width'];
        $max_height = $_REQUEST['max_height'];
        $input_file = $side . "_file_" . $post_id;

        $pathinfo = pathinfo($_FILES[$input_file]['name']);

        $pathname = strtolower(str_replace(" ", "_", $pathinfo['filename'])) . '_' . $side;

        //remove temp files
        $exts = array("jpg", "jpeg", "gif", "png");
        foreach ($exts as $ext) {
            $tmpfile = $logo_sys_path . $name_img . $pathname . '.' . $ext;

            unlink($tmpfile);
        }


        $ext = strtolower($pathinfo['extension']);
        $tmpfile = $logo_sys_path . $name_img . $pathname . '.' . $ext;

        list($width, $height) = getimagesize($_FILES[$input_file]['tmp_name']);

        switch ($ext) {
            case "png":
                $image = imagecreatefrompng($_FILES[$input_file]['tmp_name']);
                break;
            case "jpg":
                $image = imagecreatefromjpeg($_FILES[$input_file]['tmp_name']);
                break;
            case "gif":
                $image = imagecreatefromgif($_FILES[$input_file]['tmp_name']);
                break;
            default:
                $image = imagecreatefromjpeg($_FILES[$input_file]['tmp_name']);
                break;
        }

        if ($width <= $min_width && $height <= $min_height) {
            $new_width = $min_width;
            $new_height = $min_height;
            $set_width = $width;
            $set_height = $height;
            $scale_image = self::resize($image, $set_width, $set_height, $new_width, $new_height, $width, $height);
        } else {
            $new_width = $max_width;
            $new_height = min($height, $max_height);
            if ($width >= $max_width) { //scale to width
                $set_width = min($width, $max_width);
                $set_height = ($height * $set_width) / $width;
                $scale_image = self::resize($image, $set_width, $set_height, $set_width, $set_height, $width, $height);
                if ($set_height > $max_height) {
                    $width = $set_width;
                    $height = $set_height;
                    $set_height = $max_height;
                    $set_width = ($width * $set_height) / $height;
                    if ($min_width == 720)
                        $new_width = max($width, $set_width);
                    else
                        $new_width = min($width, $set_width);
                    $scale_image = self::resize($scale_image, $set_width, $set_height, $new_width, $new_height, $width, $height);
                }
            } else {
                $set_height = min($height, $max_height);
                $set_width = ($width * $set_height) / $height;
                $new_width = max($min_width, $set_width);
                $new_height = max($min_height, $set_height);
                $scale_image = self::resize($image, $set_width, $set_height, $new_width, $new_height, $width, $height);
            }
        }
        switch ($ext) {
            case "png":
                imagepng($scale_image, $tmpfile);
                break;
            case "jpg":
                imagejpeg($scale_image, $tmpfile, 100);
                break;
            case "gif":
                imagegif($scale_image, $tmpfile);
                break;
            default:
                imagejpeg($scale_image, $tmpfile, 100);
                break;
        }
    }

    public function uploadImageCrop() {
        $logo_sys_path = api_get_path(SYS_PATH) . $_POST['path'];
        $name_img = $_POST['name'] . '_' . api_get_user_id();
        $min_width = $_POST['min_width'];
        $min_height = $_POST['min_height'];
        $max_width = $_POST['max_width'];
        $max_height = $_POST['max_height'];
        $input_file = 'picture';

        //remove temp files
        $exts = array("jpg", "jpeg", "gif", "png");
        foreach ($exts as $ext) {
            $tmpfile = $logo_sys_path . $name_img . '.' . $ext;
            unlink($tmpfile);
        }

        $pathinfo = pathinfo($_FILES[$input_file]['name']);
        $ext = strtolower($pathinfo['extension']);
        if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif') {
            $tmpfile = $logo_sys_path . $name_img . '.' . $ext;
            list($width, $height) = getimagesize($_FILES[$input_file]['tmp_name']);

            switch ($ext) {
                case "png":
                    $image = imagecreatefrompng($_FILES[$input_file]['tmp_name']);
                    break;
                case "jpg":
                    $image = imagecreatefromjpeg($_FILES[$input_file]['tmp_name']);
                    break;
                case "gif":
                    $image = imagecreatefromgif($_FILES[$input_file]['tmp_name']);
                    break;
                default:
                    $image = imagecreatefromjpeg($_FILES[$input_file]['tmp_name']);
                    break;
            }

            if ($width <= $min_width && $height <= $min_height) {
                $new_width = $min_width;
                $new_height = $min_height;
                $set_width = $width;
                $set_height = $height;
                $scale_image = self::resize($image, $set_width, $set_height, $new_width, $new_height, $width, $height, 'center');
            } else {
                $new_width = $max_width;
                $new_height = min($height, $max_height);
                if ($width >= $max_width) { //scale to width
                    $set_width = min($width, $max_width);
                    $set_height = ($height * $set_width) / $width;
                    $scale_image = self::resize($image, $set_width, $set_height, $set_width, max($set_height, $min_height), $width, $height);
                    if ($set_height > $max_height) {
                        $width = $set_width;
                        $height = $set_height;
                        $set_height = $max_height;
                        $set_width = ($width * $set_height) / $height;
                        if ($min_width == 720)
                            $new_width = max($width, $set_width);
                        else
                            $new_width = min($width, $set_width);
                        $scale_image = self::resize($scale_image, $set_width, $set_height, $new_width, $new_height, $width, $height);
                    }
                } else {
                    $set_height = min($height, $max_height);
                    $set_width = ($width * $set_height) / $height;
                    $new_width = max($min_width, $set_width);
                    $new_height = max($min_height, $set_height);
                    $scale_image = self::resize($image, $set_width, $set_height, $new_width, $new_height, $width, $height);
                }
            }
            self::saveImage('.' . $ext, $scale_image, $tmpfile);
        }
    }

    function resize($img, $set_width, $set_height, $new_width, $new_height, $img_width, $img_height, $pos = 'left') {
        $x = 0; $y = 0;
        if ($pos == 'center') {
            $x = ($new_width - $img_width) / 2;
            $y = ($new_height - $img_height) / 2;
        }
        $scale_image = imagecreatetruecolor($new_width, $new_height);
        $white = imagecolorallocate($scale_image, 255, 255, 255);
        imagefill($scale_image, 0, 0, $white);
        imagecopyresampled($scale_image, $img, $x, $y, 0, 0, $set_width, $set_height, $img_width, $img_height);
        imagedestroy($img);
        return $scale_image;
    }

    function saveImage($ext, $image, $src_path) {
        switch ($ext) {
            case ".png":
                imagepng($image, $src_path);
                break;
            case ".jpg":
                imagejpeg($image, $src_path, 100);
                break;
            case ".gif":
                imagegif($image, $src_path);
                break;
            default:
                imagejpeg($image, $src_path, 100);
                break;
        }
    }

    public function cropImageUpload() {
        global $_configuration;
        $active_multisite = $_configuration['multiple_access_urls'];
        $access_url_id = intval(api_get_current_access_url_id());

        $ext = $_POST["ext"];
        $src_path = api_get_path(SYS_PATH) . $_POST['path_image'] . '_' . api_get_user_id() . $ext;
        $dest_image = end(explode('/', $_POST['dest_image']));
        if ($dest_image != 'logo_home' && $dest_image != 'course_logo')
            $dest_path = api_get_path(SYS_PATH) . $_POST['dest_image'] . '_' . api_get_user_id();
        else
            $dest_path = api_get_path(SYS_PATH) . $_POST['dest_image'];

        if ($active_multisite == true) { //multi-site active
            if ($dest_image == 'logo_home') {
                $dest_path = $dest_path . '_site_' . $access_url_id;
            }
        }

        $new_width = intval($_POST['w']);
        $new_height = intval($_POST['h']);
        //remove temp files
        if ($_POST['remove'] == 'true') {
            $exts = array("jpg", "jpeg", "gif", "png");
            foreach ($exts as $ext_img) {
                unlink($dest_path . '.' . $ext_img);
            }
        }
        $dest_path = $dest_path . $ext;

        $image_rgb = imagecreatetruecolor($new_width, $new_height);
        switch ($ext) {
            case ".png":
                $transparent = imagecolorallocate($image_rgb, 255, 255, 255);
                imagefill($image_rgb, 0, 0, $transparent);
                $image = imagecreatefrompng($src_path);
                break;
            case ".jpg":
                $image = imagecreatefromjpeg($src_path);
                break;
            case ".gif":
                $image = imagecreatefromgif($src_path);
                break;
            default:
                $image = imagecreatefromjpeg($src_path);
                break;
        }
        imagecopyresampled($image_rgb, $image, 0, 0, intval($_POST['x']), intval($_POST['y']), $new_width, $new_height, $new_width, $new_height);
        if ($_POST['resize'] == 'true') { //resize image
            $scale_width = intval($_POST['to_width']);
            $scale_height = intval($_POST['to_height']);
            if ($new_width > $scale_width || $new_height > $scale_height) {
                $image_rgb = self::resize($image_rgb, $scale_width, $scale_height, $scale_width, $scale_height, $new_width, $new_height);
            }
        }
        self::saveImage($ext, $image_rgb, $src_path);
        rename($src_path, $dest_path);

        if ($_POST['resize'] == 'false') {
            list($thumb_width, $thumb_height) = getimagesize($dest_path);
            if ($thumb_width > 150 || $thumb_height > 130) {
                $scale_height = ($thumb_height * 150) / $thumb_width;
                $thumbnail_img = self::resize($image_rgb, 150, $scale_height, 150, $scale_height, $thumb_width, $thumb_height);
                if ($scale_height > 130) {
                    $thumb_width = 19500 / $scale_height;
                    $thumbnail_img = self::resize($thumbnail_img, $thumb_width, 130, $thumb_width, 130, 150, $scale_height);
                }
                $thumbnail_path = str_replace('author', 'thumbnail', $dest_path);
                self::saveImage($ext, $thumbnail_img, $thumbnail_path);
            }
            //save file in documents
            $name_img_real = end(explode('/', $_POST['dest_image'])) . '_' . api_get_user_id() . $ext;
            $dir_path = $_POST['folder'];
            $_course['dbName'] = '';
            $doc_id = add_document($_course, $dir_path . $name_img_real, 'file', filesize($dest_path), $name_img_real);
            api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'DocumentAddedFromLearnpath', api_get_user_id(), $_SESSION['_gid'], null, null, null, api_get_session_id());
        }
    }

    public function cropImageUploadQuiz() {
        $ext = $_POST["ext"];
        $src_path = api_get_path(SYS_PATH) . $_POST['path_image'] . $ext;
        $dest_image = end(explode('/', $_POST['dest_image']));
        if ($dest_image != 'logo_home' && $dest_image != 'course_logo')
            $dest_path = api_get_path(SYS_PATH) . $_POST['dest_image'];
        else
            $dest_path = api_get_path(SYS_PATH) . $_POST['dest_image'];

        $new_width = intval($_POST['w']);
        $new_height = intval($_POST['h']);
        //remove temp files
        if ($_POST['remove'] == 'true') {
            $exts = array("jpg", "jpeg", "gif", "png");
            foreach ($exts as $ext_img) {
                unlink($dest_path . '.' . $ext_img);
            }
        }
        $dest_path = $dest_path . $ext;

        $image_rgb = imagecreatetruecolor($new_width, $new_height);
        switch ($ext) {
            case ".png":
                $transparent = imagecolorallocate($image_rgb, 255, 255, 255);
                imagefill($image_rgb, 0, 0, $transparent);
                $image = imagecreatefrompng($src_path);
                break;
            case ".jpg":
                $image = imagecreatefromjpeg($src_path);
                break;
            case ".gif":
                $image = imagecreatefromgif($src_path);
                break;
            default:
                $image = imagecreatefromjpeg($src_path);
                break;
        }
        imagecopyresampled($image_rgb, $image, 0, 0, intval($_POST['x']), intval($_POST['y']), $new_width, $new_height, $new_width, $new_height);
        if ($_POST['resize'] == 'true') { //resize image
            $scale_width = intval($_POST['to_width']);
            $scale_height = intval($_POST['to_height']);
            if ($new_width > $scale_width || $new_height > $scale_height) {
                $image_rgb = self::resize($image_rgb, $scale_width, $scale_height, $scale_width, $scale_height, $new_width, $new_height);
            }
        }
        switch ($ext) { //save image
            case ".png":
                imagepng($image_rgb, $src_path);
                break;
            case ".jpg":
                imagejpeg($image_rgb, $src_path, 100);
                break;
            case ".gif":
                imagegif($image_rgb, $src_path);
                break;
            default:
                imagejpeg($image_rgb, $src_path, 100);
                break;
        }
        rename($src_path, $dest_path);

        if ($_POST['resize'] == 'false') {
            list($thumb_width, $thumb_height) = getimagesize($dest_path);
            if ($thumb_width > 150 || $thumb_height > 130) {
                $thumbnail_img = self::resize($image_rgb, 150, 130, 150, 130, $thumb_width, $thumb_height);
                $thumbnail_path = str_replace('author', 'thumbnail', $dest_path);
                switch ($ext) { //save thumbnail
                    case ".png":
                        imagepng($thumbnail_img, $thumbnail_path);
                        break;
                    case ".jpg":
                        imagejpeg($thumbnail_img, $thumbnail_path, 100);
                        break;
                    case ".gif":
                        imagegif($thumbnail_img, $thumbnail_path);
                        break;
                    default:
                        imagejpeg($thumbnail_img, $thumbnail_path, 100);
                        break;
                }
            }
        }
    }

    public function croplogocourse() {
        extract($_POST);

        $logo_sys_path_temp = api_get_path(SYS_COURSE_PATH) . $this->courseInfo['path'] . '/temp/';
        $logo_sys_path = api_get_path(SYS_COURSE_PATH) . $this->courseInfo['path'] . '/';
        $name_img = 'course_logo';
        $ext = $_POST["ext"];
        $filename_temp = $logo_sys_path_temp . $name_img . '.' . $ext;
        $filename = $logo_sys_path . $name_img . '.' . $ext;

        foreach (array('jpg', 'jpeg', 'png', 'gif') as $xt)
            unlink($logo_sys_path . $name_img . "." . $xt);

        list($width, $height) = getimagesize($filename_temp);
        $new_width = 185;
        $new_height = 140;

        $image_p = imagecreatetruecolor($new_width, $new_height);

        switch ($ext) {
            case "png":
                imagesavealpha($image_p, true);
                imagealphablending($image_p, false);
                $transparent = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
                imagefill($image_p, 0, 0, $transparent);
                $image = imagecreatefrompng($filename_temp);
                break;
            case "jpg":
                $image = imagecreatefromjpeg($filename_temp);
                break;
            case "gif":
                $image = imagecreatefromgif($filename_temp);
                break;
        }
        imagecopyresampled($image_p, $image, 0, 0, $_POST['x'], $_POST['y'], $new_width, $new_height, $_POST['w'], $_POST['h']);

        switch ($ext) {
            case "png":
                imagepng($image_p, $filename);
                break;
            case "jpg":
                imagejpeg($image_p, $filename, 100);
                break;
            case "gif":
                imagegif($image_p, $filename);
                break;
        }
        unlink($filename_temp);

        $ECObj = new application_ecommerce_models_ModelEcommerce();
        $ECItem = $ECObj->getItemByCourse($this->courseInfo['id']);
        $ECObj->updateImageById($name_img . '.' . $ext, $ECItem['id']);
        exit;
    }

    public function uploadImage() {
        extract($_POST);

        $to = strtoupper($to);
        if (!$this->imageParam[$to]['PERMISSION']['FUNC']())
            exit;
        $image_sys_path_temp = $this->imageParam[$to]['PATH']['SYS'];
        $image_sys_path = $this->imageParam[$to]['PATH']['SYS'];
        $name_image_temp = $this->imageParam[$to]['NOMENCLATURE']['TEMP'];
        $name_image = $this->imageParam[$to]['NOMENCLATURE']['FINAL'];
        $fi = array_pop($_FILES);
        $fi_name = $fi['name'];
        $fi_tmp_name = $fi['tmp_name'];
        $inputfile = 'picture';
        $set_width = 500;
        $set_height = 375;
        $pathinfo = pathinfo($fi_name);
        $ext = $pathinfo['extension'];
        $filename_temp = $image_sys_path_temp . $name_image_temp . '.' . $ext;
        $filename = $image_sys_path . $name_image . '.' . $ext;

        list($width, $height, $type, $attr) = getimagesize($fi_tmp_name);

        if (move_uploaded_file($fi_tmp_name, $filename_temp)) {
            if ($width > $set_width || $height > $set_height) {
                $img = new SimpleImage();
                $img->load($filename_temp);
                $maxSize = $set_height;
                $ratio_orig = $img->get_width() / $img->get_height();
                $width = $maxSize;
                $height = $maxSize;
                if ($ratio_orig < 1) {
                    $width = $height * $ratio_orig;
                } else {
                    $height = $width / $ratio_orig;
                }
                $img->resize($width, $height, $action)->save($filename_temp);
            }

            $new_file_path = pathinfo($filename);
            echo json_encode(array(
                'src' => $new_file_path['basename'],
                'width' => $width,
                'height' => $height,
                'ext' => $ext
            ));
            exit;
        }
    }

    public function cropImage() {
        extract($_POST);

        if (!$this->imageParam[$to]['PERMISSION']['FUNC']())
            exit;
        $image_sys_path_temp = $this->imageParam[$to]['PATH']['SYS'] . $_REQUEST['category'];
        $image_sys_path = $this->imageParam[$to]['PATH']['SYS'] . $_REQUEST['category'];
        $name_image_temp = $this->imageParam[$to]['NOMENCLATURE']['TEMP'];
        $name_image = $this->imageParam[$to]['NOMENCLATURE']['FINAL'];

        if ($action == "no-canvas") {
            $filename_temp = $image_sys_path_temp . $name_image_temp . '.' . $ext;
            $filename = $image_sys_path . $name_image . '.' . $ext;

            foreach ($this->imageExt as $xt) {
                unlink($image_sys_path . $name_image . "." . $xt);
            }

            $new_width = $this->imageParam[$to]['DIMENSION']['W'];
            $new_height = $this->imageParam[$to]['DIMENSION']['H'];
            $image_p = imagecreatetruecolor($new_width, $new_height);

            switch ($ext) {
                case "png":
                    imagesavealpha($image_p, true);
                    imagealphablending($image_p, false);
                    $transparent = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
                    imagefill($image_p, 0, 0, $transparent);
                    $image = imagecreatefrompng($filename_temp);
                    break;
                case "jpg":
                    $image = imagecreatefromjpeg($filename_temp);
                    break;
                case "gif":
                    $image = imagecreatefromgif($filename_temp);
                    break;
            }
            imagecopyresampled($image_p, $image, 0, 0, $x, $y, $new_width, $new_height, $w, $h);

            switch ($ext) {
                case "png":
                    imagepng($image_p, $filename);
                    break;
                case "jpg":
                    imagejpeg($image_p, $filename, 100);
                    break;
                case "gif":
                    imagegif($image_p, $filename);
                    break;
            }
            unlink($filename_temp);

            $new_file_path = pathinfo($filename);

            echo json_encode(array(
                'src' => $new_file_path['basename']
            ));
        } else {
            $ext = 'png';
            $filename_temp = $image_sys_path_temp . $name_image_temp . '.' . $ext;
            $filename = $image_sys_path . $name_image . '.' . $ext;

            list($info, $data) = explode(",", $i);

            $binary = base64_decode($data);
            $image = fopen($filename_temp, 'wb');

            fwrite($image, $binary);
            fclose($image);

            $new_file_path = pathinfo($filename);
            $new_file_path_temp = pathinfo($filename_temp);

            if ($this->imageParam[$to]['PATH']['SWITCH']) {
                rename($filename_temp, $filename);
            }

            echo json_encode(array(
                'final' => $new_file_path['basename'],
                'temp' => $new_file_path_temp['basename']
            ));
        }

        exit;
    }

}

?>
