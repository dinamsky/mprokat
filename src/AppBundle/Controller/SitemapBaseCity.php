<?php
// AppBundle/SitemapController.php

namespace AppBundle\Controller;
use AppBundle\Entity\GeneralType;
use AppBundle\Menu\MenuMarkModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapBaseCity extends Controller
{
    /**
     * @Route("/sitemap/sitemap-basecity.xml", name="sitemap-basecity", defaults={"_format"="xml"})
     */
    public function showAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $urls = array();
        $hostname = $request->getSchemeAndHttpHost();

        // add static urls


        foreach ($em->getRepository('AppBundle:City')->findBy(['country'=>'RUS']) as $city) {
        foreach ($em->getRepository('AppBundle:GeneralType')->findAll() as $gt){

                $urls[] = array(
                    'loc' => $this->generateUrl('search', array('city' => $city->getUrl(),'service'=>'all','general'=>$gt->getUrl()))
                );
            }
        }

        $response = new Response(
            $this->renderView('sitemap/sitemap.html.twig', array( 'urls' => $urls,
                'hostname' => $hostname)),
            200
        );
        $response->headers->set('Content-Type', 'text/xml');

        return $response;



    }

}