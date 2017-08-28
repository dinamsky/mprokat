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
        $main_dir = $_SERVER['DOCUMENT_ROOT'].'/assets/images';
        $thumbs = $_SERVER['DOCUMENT_ROOT'].'/assets/thumbs';
        $ff = 'fotos';
        $is_main = false;
        if(empty($card->getFotos())) $is_main = true;

        foreach($_FILES[$ff]['name'] as $k=>$v)
        {
            if (!empty($_FILES[$ff]['name'][$k]))
            {
                $ext = explode(".",basename($_FILES[$ff]['name'][$k]));
                $ext = strtolower($ext[(count($ext)-1)]);

                $foto = new Foto();
                $foto->setCard($card);
                $foto->setIsMain($is_main);
                $this->em->persist($foto);
                $this->em->flush();

                $is_main = false;

                $file_id = $foto->getId();

                $new_name = 'original_'.$file_id.'.'.$ext;
                if(move_uploaded_file($_FILES[$ff]['tmp_name'][$k],$main_dir.'/'.$new_name))
                {
                    //var_dump($_FILES);
                    if(preg_match('/[.](GIF)|(gif)$/', $new_name)) {
                        $im1 = imagecreatefromgif($main_dir.'/'.$new_name) ; //gif
                    }
                    if(preg_match('/[.](PNG)|(png)$/', $new_name)) {
                        $im1 = imagecreatefrompng($main_dir.'/'.$new_name) ;//png
                    }

                    if(preg_match('/[.](JPG)|(jpg)|(jpeg)|(JPEG)$/', $new_name)) {
                        $im1 = imagecreatefromjpeg($main_dir.'/'.$new_name); //jpg
                    }

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

                    $width = 200;
                    $height = 200;

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

                    unlink ($main_dir.'/'.$new_name);
                }
                else
                {
                    $this->em->remove($foto);
                    $this->em->flush();
                }
            }
        }
    }

    /**
     * @Route("/ajax/deleteFoto")
     */

    public function deleteFoto(Request $request)
    {
        // TODO set main if deleted

        $main_dir = $_SERVER['DOCUMENT_ROOT'].'/assets/images';
        $thumbs = $_SERVER['DOCUMENT_ROOT'].'/assets/thumbs';

        $post = $request->request;
        $id = $post->get('id');

        $foto = $this->em
            ->getRepository(Foto::class)
            ->find($id);

        $this->em->remove($foto);
        $this->em->flush();

        unlink ($main_dir.'/'.$id.'.jpg');
        unlink ($thumbs.'/'.$id.'.jpg');

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