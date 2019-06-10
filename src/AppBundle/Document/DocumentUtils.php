<?php

namespace AppBundle\Document;

use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use UserBundle\Entity\User;
use UserBundle\Entity\FormOrder;
use AppBundle\Entity\Document;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;


class DocumentUtils extends Controller
{
    private $em;

    public function __construct(em $em)
    {
        $this->em = $em;
    }

    public function uploadImages(Card $card)
    {
        $main_dir = $_SERVER['DOCUMENT_ROOT'].'/assets/images/cards/'.date('Y').'/'.date('m');
        $thumbs = $_SERVER['DOCUMENT_ROOT'].'/assets/images/cards/'.date('Y').'/'.date('m').'/t';
        @mkdir($main_dir,'0755', true);
        @mkdir($thumbs,'0755', true);
        $ff = 'fotos';
        $is_main = false;
        if($card->getFotos()->isEmpty()) $is_main = true;

        //dump($_FILES);
        //dump($_POST['to_upload']);

        foreach($_FILES[$ff]['name'] as $k=>$v)
        {
            if (!empty($_FILES[$ff]['name'][$k]) and in_array($_FILES[$ff]['name'][$k],$_POST['to_upload'],true))
            {
                $ext = explode(".",basename($_FILES[$ff]['name'][$k]));
                $ext = strtolower($ext[(count($ext)-1)]);

                $foto = new Foto();
                $foto->setCard($card);
                $foto->setIsMain($is_main);
                $foto->setFolder(date('Y').'/'.date('m'));
                $this->em->persist($foto);
                $this->em->flush();

                $is_main = false;

                $file_id = $foto->getId();

                $file = @file_get_contents($_FILES[$ff]['tmp_name'][$k]);
                $im1 = @imagecreatefromstring($file);
                if ($im1 !== false)
                {
                    $width = 1280;
                    $height = 900;

                    $w_src1 = imagesx($im1);
                    $h_src1 = imagesy($im1);
                    $ratio = $w_src1/$h_src1;
                    if ($width/$height > $ratio) {
                        $width = $height*$ratio;
                    } else {
                        $height = $width/$ratio;
                    }
                    $image_p = imagecreatetruecolor($width, $height);
                    $bgColor = imagecolorallocate($image_p, 255,255,255);
                    imagefill($image_p , 0,0 , $bgColor);
                    imagecopyresampled($image_p, $im1, 0, 0, 0, 0, $width, $height, $w_src1, $h_src1);

                    imagejpeg($image_p, $main_dir.'/'.$file_id.'.jpg');

                    $width = 400;
                    $height = 300;

                    $w_src1 = imagesx($im1);
                    $h_src1 = imagesy($im1);
                    $ratio = $w_src1/$h_src1;
                    if ($width/$height > $ratio) {
                        $width = $height*$ratio;
                    } else {
                        $height = $width/$ratio;
                    }
                    $image_p = imagecreatetruecolor($width, $height);
                    $bgColor = imagecolorallocate($image_p, 255,255,255);
                    imagefill($image_p , 0,0 , $bgColor);
                    imagecopyresampled($image_p, $im1, 0, 0, 0, 0, $width, $height, $w_src1, $h_src1);
                    imagejpeg($image_p, $thumbs.'/'.$file_id.'.jpg');

                    unlink ($_FILES[$ff]['tmp_name'][$k]);
                }
                else
                {
                    $this->em->remove($foto);
                    $this->em->flush();
                }
            }
        }
    }

    public function moveResizeImage($from_img, $to_img, $to_thumb_img)
    {
        $im1 = false;
        $file = @file_get_contents($from_img);
        if ($file !== false) $im1 = @imagecreatefromstring($file);
        if ($im1 !== false) {

            $width = 1280;
            $height = 900;

            $w_src1 = imagesx($im1);
            $h_src1 = imagesy($im1);
            $ratio = $w_src1 / $h_src1;
            if ($width / $height > $ratio) {
                $width = $height * $ratio;
            } else {
                $height = $width / $ratio;
            }
            $image_p = imagecreatetruecolor($width, $height);
            $bgColor = imagecolorallocate($image_p, 255, 255, 255);
            imagefill($image_p, 0, 0, $bgColor);
            imagecopyresampled($image_p, $im1, 0, 0, 0, 0, $width, $height, $w_src1, $h_src1);

            imagejpeg($image_p, $to_img);

            $width = 400;
            $height = 300;

            $w_src1 = imagesx($im1);
            $h_src1 = imagesy($im1);
            $ratio = $w_src1 / $h_src1;
            if ($width / $height > $ratio) {
                $width = $height * $ratio;
            } else {
                $height = $width / $ratio;
            }
            $image_p = imagecreatetruecolor($width, $height);
            $bgColor = imagecolorallocate($image_p, 255, 255, 255);
            imagefill($image_p, 0, 0, $bgColor);
            imagecopyresampled($image_p, $im1, 0, 0, 0, 0, $width, $height, $w_src1, $h_src1);
            imagejpeg($image_p, $to_thumb_img);
        }
    }


    public function uploadImage($foto_var, $new_name, $target_folder = '', $thumb_folder = '')
    {
        $main_dir = $target_folder;
        $thumbs = $thumb_folder;
        if ($target_folder != '') @mkdir($main_dir,'0755', true);
        if ($thumb_folder != '') @mkdir($thumbs,'0755', true);
        $ff = $foto_var;

        if ($new_name == 'translit') $new_name = basename($this->translit($_FILES[$ff]['name']));
        if ($new_name == 'md5') $new_name = basename(md5($_FILES[$ff]['name']));

        if ($_FILES[$ff]['tmp_name']!='') {

            $file = file_get_contents($_FILES[$ff]['tmp_name']);

            $im1 = imagecreatefromstring($file);
            if ($im1 !== false) {
                if ($target_folder != '') {
                    $width = 1280;
                    $height = 900;

                    $w_src1 = imagesx($im1);
                    $h_src1 = imagesy($im1);
                    $ratio = $w_src1 / $h_src1;
                    if ($width / $height > $ratio) {
                        $width = $height * $ratio;
                    } else {
                        $height = $width / $ratio;
                    }
                    $image_p = imagecreatetruecolor($width, $height);
                    $bgColor = imagecolorallocate($image_p, 255, 255, 255);
                    imagefill($image_p, 0, 0, $bgColor);
                    imagecopyresampled($image_p, $im1, 0, 0, 0, 0, $width, $height, $w_src1, $h_src1);

                    imagejpeg($image_p, $main_dir . '/' . $new_name . '.jpg');

                }
                if ($thumb_folder != '') {
                    $width = 400;
                    $height = 300;

                    $w_src1 = imagesx($im1);
                    $h_src1 = imagesy($im1);
                    $ratio = $w_src1 / $h_src1;
                    if ($width / $height > $ratio) {
                        $width = $height * $ratio;
                    } else {
                        $height = $width / $ratio;
                    }
                    $image_p = imagecreatetruecolor($width, $height);
                    $bgColor = imagecolorallocate($image_p, 255, 255, 255);
                    imagefill($image_p, 0, 0, $bgColor);
                    imagecopyresampled($image_p, $im1, 0, 0, 0, 0, $width, $height, $w_src1, $h_src1);
                    imagejpeg($image_p, $thumbs . '/' . $new_name . '.jpg');
                }
                unlink($_FILES[$ff]['tmp_name']);
            }
        }
    }
}