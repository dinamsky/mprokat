<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManagerInterface as em;
use AppBundle\Entity\Document;
use AppBundle\Document\DocumentUtils;
use UserBundle\Entity\FormOrder;

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
     * @Route("/document/{id}/image", requirements={"id": "\d+"}, name="get_document")
     */
    public function getDocument($id){
        return $this->getBinaryFileResponse($id, false);
    }

    /**
     * @Route("/document/{id}/t/image", requirements={"id": "\d+"}, name="get_document_t")
     */
    public function getDocumentThumbs($id){
        return $this->getBinaryFileResponse($id, true);
    }

    private function getBinaryFileResponse(int $id, bool $thum = false){
        $doc = $this->em
            ->getRepository(Document::class)
            ->findOneBy(['id' => $id]);
        if ($doc == null){
            return new Response();
        }
        if (!$this->get('session')->has('logged_user')) {
            return new Response();
        }
        $curUserId = (int) $this->get('session')->get('logged_user')->getId();
        $order = $this->em
             ->getRepository(FormOrder::class)
             ->findOneBy(['id' => $doc->getOrderId()]);
        if ($order == null){
            return new Response();
        }

        if ($this->get('session')->has('admin') || ($curUserId == $order->getUserId()) || ($curUserId == $order->getRenterId())){
            
            $file = $_SERVER['DOCUMENT_ROOT'].$doc->getFolder().'/'.($thum?'t/':'').$doc->getId().'.jpg';
            $response = new BinaryFileResponse($file);
            return $response;
        }
        return new Response();
    }

}