<?php

namespace AppBundle\Controller;
use AppBundle\Entity\GeneralType;
use AppBundle\Menu\MenuMarkModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapMoto extends Controller
{

    /**
     * @Route("/sitemap/sitemap-moto.xml", name="sitemap-moto", defaults={"_format"="xml"})
     */
    public function showAction(Request $request, MenuMarkModel $mm)
    {
        $em = $this->getDoctrine()->getManager();
        $urls = array();
        $hostname = $request->getSchemeAndHttpHost();


        foreach ($em->getRepository('AppBundle:City')->findBy(['country'=>'RUS']) as $city) {

            foreach ($em->getRepository('AppBundle:Mark')->findAll() as $mark) {
                $urls[] = array(
                    'loc' => $this->generateUrl('search', array('city' => $city, 'service' => 'all', 'general' => 'moto','mark' => $mark->getHeader()))
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