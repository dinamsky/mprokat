<?php
// AppBundle/SitemapController.php

namespace AppBundle\Controller;
use AppBundle\Entity\GeneralType;
use AppBundle\Menu\MenuMarkModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapBaseReg extends Controller
{
    /**
     * @Route("/sitemap/sitemap-basereg.xml", name="sitemap-basereg", defaults={"_format"="xml"})
     */
    public function showAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $urls = array();
        $hostname = $request->getSchemeAndHttpHost();
        $query = $em->createQuery("SELECT c.id FROM AppBundle:City c WHERE c.country = 'RUS'");
        foreach ($query->getScalarResult() as $row) {
            $city_ids[] = $row['id'];
        }
        $query = $em->createQuery('SELECT c.parentId FROM AppBundle:City c WHERE c.parentId IS NOT NULL AND c.id IN (' . implode(",", $city_ids) . ')');
        foreach ($query->getScalarResult() as $row) {
            $region_ids[] = $row['parentId'];
        }
        $region_ids = array_unique($region_ids);
        $query = $em->createQuery('SELECT r FROM AppBundle:City r WHERE r.id IN (' . implode(",", $region_ids) . ')');
        $regions = $query->getResult();

        foreach ($regions as $city) {
            foreach ($em->getRepository('AppBundle:GeneralType')->findAll() as $gt) {

                $urls[] = array(
                    'loc' => $this->generateUrl('search', array('city' => $city, 'service' => 'all', 'general' => $gt->getUrl()))
                );
            }
        }
//        foreach ($regions as $city) {
//            foreach ($em->getRepository('AppBundle:GeneralType')->findAll() as $gt) {
//                foreach ($em->getRepository('AppBundle:Mark')->findAll() as $mark) {
//                    $urls[] = array(
//                        'loc' => $this->generateUrl('search', array('city' => $city, 'service' => 'all', 'general' => $gt->getUrl(), 'mark' => $mark->getHeader()))
//                    );
//                }
//            }
//        }
            $response = new Response(
                $this->renderView('sitemap/sitemap.html.twig', array('urls' => $urls,
                    'hostname' => $hostname)),
                200
            );
            $response->headers->set('Content-Type', 'text/xml');

            return $response;


        }


}