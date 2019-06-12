<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManagerInterface as em;
use AppBundle\Entity\Document;
use AppBundle\Document\DocumentUtils;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DocController extends Controller
{
    private $em;

    public function __construct(em $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/document/{id}/image.jpg", requirements={"id": "\d+"}, name="get_document")
     */
    public function getDocument($id){
        $doc = $this->em
                ->getRepository(Document::class)
                ->findOneBy(['id' => $id]);
        if ($doc){
            
            $file = $_SERVER['DOCUMENT_ROOT'].$doc->getFolder().'/'.$doc->getId().'.jpg';
            // var_dump($file);
            $response = new BinaryFileResponse($file);

            return $response;
        }
        return new Response();
    }

    /**
     * @Route("/document/{id}/t/image.jpg", requirements={"id": "\d+"}, name="get_document_t")
     */
    public function getDocumentThumbs($id){
        $doc = $this->em
                ->getRepository(Document::class)
                ->findOneBy(['id' => $id]);
        if ($doc){
            
            $file = $_SERVER['DOCUMENT_ROOT'].$doc->getFolder().'/t/'.$doc->getId().'.jpg';
            // var_dump($file);
            $response = new BinaryFileResponse($file);

            return $response;
        }
        return new Response();
    }

}