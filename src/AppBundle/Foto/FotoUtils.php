<?php

namespace AppBundle\Foto;

use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\Card;
use AppBundle\Entity\Foto;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;


class FotoUtils extends Controller
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

        foreach($_FILES[$ff]['name'] as $k=>$v)
        {
            if (!empty($_FILES[$ff]['name'][$k]))
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


    /**
     * @Route("/ajax/deleteFoto")
     */

    public function deleteFoto(Request $request)
    {
        // TODO set main if deleted

        $main_dir = '.web/assets/images/cards';
        $thumbs = '.web/assets/images/cards';

        $post = $request->request;
        $id = $post->get('id');

        $foto = $this->em
            ->getRepository(Foto::class)
            ->find($id);

        $this->em->remove($foto);
        $this->em->flush();

        unlink ($main_dir.'/'.$foto->getFolder().'/'.$id.'.jpg');
        unlink ($thumbs.'/'.$foto->getFolder().'/t/'.$id.'.jpg');

        return new Response();
    }

    /**
     * @Route("/ajax/mainFoto")
     */

    public function mainFoto(Request $request)
    {
        $post = $request->request;
        $id = $post->get('id');

        $foto = $this->em
            ->getRepository(Foto::class)
            ->find($id);

        $query = $this->em->createQuery('UPDATE AppBundle:Foto f SET f.isMain = 0 WHERE f.cardId = ?1');
        $query->setParameter(1, $foto->getCardId());
        $query->execute();

        $foto->setIsMain(true);
        $this->em->persist($foto);
        $this->em->flush();

        return new Response();
    }
}