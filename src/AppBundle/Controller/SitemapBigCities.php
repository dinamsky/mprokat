<?php

namespace AppBundle\Controller;
use AppBundle\Entity\GeneralType;
use AppBundle\Menu\MenuMarkModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapBigCities extends Controller
{

    /**
     * @Route("/sitemap/sitemap-bc.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function showAction(Request $request, MenuMarkModel $mm)
    {
        foreach ($em->getRepository('AppBundle:City')->findBy(['country' => 'RUS']) as $city) {
            if ($city->getTotal() != 0) {
                foreach ($em->getRepository('AppBundle:GeneralType')->findBy(['url' => 'cars']) as $gt) {
                    foreach ($em->getRepository('AppBundle:Mark')->findAll() as $mark) {
                        //                    foreach ($mm->getModels($mark->getId()) as $model) {
                        $urls[] = array(
                            'loc' => $this->generateUrl('search', array('city' => $city->getUrl(), 'service' => 'all', 'general' => $gt->getUrl(), 'mark' => $mark->getHeader()))
                        );
                    }
                }
            }
        }
//  }

    }
}