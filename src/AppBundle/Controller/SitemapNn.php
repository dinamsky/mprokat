<?php

namespace AppBundle\Controller;
use AppBundle\Entity\GeneralType;
use AppBundle\Menu\MenuMarkModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapNn extends Controller
{

    /**
     * @Route("/sitemap/sitemap-nn.xml", name="sitemap-nn", defaults={"_format"="xml"})
     */
    public function showAction(Request $request, MenuMarkModel $mm)
    {
        $em = $this->getDoctrine()->getManager();
        $urls = array();
        $hostname = $request->getSchemeAndHttpHost();
        $city = 'Nizhniy_Novgorod';
        foreach ($em->getRepository('AppBundle:GeneralType')->findAll() as $gt) {

            $urls[] = array(
                'loc' => $this->generateUrl('search', array('city' => $city, 'service' => 'all', 'general' => $gt->getUrl()))
            );
        }

        foreach ($em->getRepository('AppBundle:GeneralType')->findAll() as $gt) {
            foreach ($em->getRepository('AppBundle:Mark')->findAll() as $mark) {
                $urls[] = array(
                    'loc' => $this->generateUrl('search', array('city' => 'Nizhniy_Novgorod', 'service' => 'all', 'general' => $gt->getUrl(),'mark' => $mark->getHeader()))
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