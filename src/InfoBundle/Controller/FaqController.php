<?php

namespace InfoBundle\Controller;
use InfoBundle\Entity\Faq;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class FaqController extends Controller
{
    /**
     * @Route("/faq/{id}")
     */
    public function indexAction($id)
    {

        $city = $this->get('session')->get('city');

//        $news = $this->getDoctrine()
//            ->getRepository(News::class)
//            ->findBy(['country!=','USA'],['dateCreate' => 'DESC']);

        $em = $this->getDoctrine()->getManager();

        $arnd = $em->getRepository("UserBundle:Faq")->createQueryBuilder('n')
            ->where("n.position < 101")
            ->orderBy("n.position","ASC")
            ->getQuery()
            ->getResult();

        $ownr = $em->getRepository("UserBundle:Faq")->createQueryBuilder('n')
            ->where("n.position > 100")
            ->orderBy("n.position","ASC")
            ->getQuery()
            ->getResult();


        $faq = $em->getRepository("UserBundle:Faq")->createQueryBuilder('n')
            ->where("n.id = ".$id)
            ->getQuery()
            ->getResult();

        return $this->render('InfoBundle::all_faq.html.twig', [
            'arnd' => $arnd,
            'ownr' => $ownr,
            'city' => $city,
            'faq' => $faq[0],
            'lang' => $_SERVER['LANG'],

        ]);
    }

    /**
     * @Route("/faq")
     */
    public function contactsAction()
    {
        $city = $this->get('session')->get('city');

//        $news = $this->getDoctrine()
//            ->getRepository(News::class)
//            ->findBy(['country!=','USA'],['dateCreate' => 'DESC']);

        $em = $this->getDoctrine()->getManager();

        $arnd = $em->getRepository("UserBundle:Faq")->createQueryBuilder('n')
            ->where("n.position < 101")
            ->orderBy("n.position","ASC")
            ->getQuery()
            ->getResult();

        $ownr = $em->getRepository("UserBundle:Faq")->createQueryBuilder('n')
            ->where("n.position > 100")
            ->orderBy("n.position","ASC")
            ->getQuery()
            ->getResult();

        return $this->render('InfoBundle::all_faq.html.twig', [
            'arnd' => $arnd,
            'ownr' => $ownr,
            'city' => $city,
            'lang' => $_SERVER['LANG'],

        ]);
    }
}
