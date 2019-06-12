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
use Symfony\Component\Config\Definition\Exception\Exception;

class DocumentUtils extends Controller
{
    private $em;

    public function __construct(em $em)
    {
        $this->em = $em;
    }

    /**
     * return array (Document) (AppBundle\Entity\Document) or empty array
     */
    public function uploadDocuments(User $user, FormOrder $order, string $fileType = 'files')
    {
        $res = array();
        if ($user == null || $order == null){
            return $res;
        }
        $base_dir = '/assets/images/users/doci/'.$user->getId().'/'.$order->getId();
        $main_dir = $_SERVER['DOCUMENT_ROOT'].$base_dir;
        $thumbs = $main_dir.'/t';
        @mkdir($main_dir,'0755', true);
        @mkdir($thumbs,'0755', true);
        $ff = $fileType;
        //dump($_FILES);
        //dump($_POST['to_upload']);

        foreach($_FILES[$ff]['name'] as $k=>$v)
        {
            if (!empty($_FILES[$ff]['name'][$k]) and in_array($_FILES[$ff]['name'][$k],$_POST[$ff.'_in'],true))
            {
                $ext = explode(".",basename($_FILES[$ff]['name'][$k]));
                $ext = strtolower($ext[(count($ext)-1)]);

                $docs = new Document();
                $docs->setUser($user);
                $docs->setOrderId($order->getId());
                $docs->setFolder($base_dir);
                $docs->setName(basename($_FILES[$ff]['name'][$k]));
                $this->em->persist($docs);
                $this->em->flush();

                $file_id = $docs->getId();

                $isMove = $this->moveResizeDocs($_FILES[$ff]['tmp_name'][$k], $main_dir.'/'.$file_id.'.jpg', $thumbs.'/'.$file_id.'.jpg');

                if (!$isMove){
                    $this->em->remove($docs);
                    $this->em->flush();
                }
                $res[] = $docs;
            }
        }
        return $res;
    }

    public function moveResizeDocs($from_img, $to_img, $to_thumb_img)
    {
        $im1 = false;
        $res = false;
        $file = @file_get_contents($from_img);
        if ($file !== false) $im1 = @imagecreatefromstring($file);
        if ($im1 !== false) {

            $res1 = $this->saveResizeDocs($im1, $to_img, 1280, 900);
            $res2 = $this->saveResizeDocs($im1, $to_thumb_img, 400, 900);

            imagedestroy ($im1);
            unlink ($from_img);
            $res = $res1 && $res2;
        }
        return $res;
    }

    private function saveResizeDocs(&$im1, string $to_img, int $width, int $height){
        $res = false;
        try {
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
            $res = true;
        } catch (Exception $ex){
            $res = false;
        }
        return $res;
    }
 }