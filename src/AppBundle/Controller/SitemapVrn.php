<?php

namespace AppBundle\Controller;
use AppBundle\Entity\GeneralType;
use AppBundle\Menu\MenuMarkModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapVrn extends Controller
{

    /**
     * @Route("/sitemap/sitemap-vrn.xml", name="sitemap-vrn", defaults={"_format"="xml"})
     */
    public function showAction(Request $request, MenuMarkModel $mm)
    {
        $em = $this->getDoctrine()->getManager();
        $urls = array();
        $hostname = $request->getSchemeAndHttpHost();
        $city = 'Voronezh';
        foreach ($em->getRepository('AppBundle:GeneralType')->findAll() as $gt) {

            $urls[] = array(
                'loc' => $this->generateUrl('search', array('city' => $city, 'service' => 'all', 'general' => $gt->getUrl()))
            );
        }
        foreach ($em->getRepository('AppBundle:GeneralType')->findBy(['url'=>'cars']) as $gt) {
            foreach ($em->getRepository('AppBundle:Mark')->findAll() as $mark) {
                foreach ($mm->getModels($mark->getId()) as $model) {
                    $urls[] = array(
                        'loc' => $this->generateUrl('search', array('city' => $city, 'service' => 'all', 'general' => $gt->getUrl(),'mark' => $mark->getHeader(),'model' => $model->getHeader()))
                    );
                }
            }}
        foreach ($em->getRepository('AppBundle:GeneralType')->findAll() as $gt) {
            foreach ($em->getRepository('AppBundle:Mark')->findAll() as $mark) {
                    $urls[] = array(
                        'loc' => $this->generateUrl('search', array('city' => $city, 'service' => 'all', 'general' => $gt->getUrl(),'mark' => $mark->getHeader()))
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