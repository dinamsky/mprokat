<?php
// AppBundle/SitemapController.php

namespace AppBundle\Controller;
use AppBundle\Entity\GeneralType;
use AppBundle\Menu\MenuMarkModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapBase extends Controller
{
    /**
     * @Route("/sitemap/sitemap-base.xml", name="sitemap-base", defaults={"_format"="xml"})
     */
    public function showAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $urls = array();
        $hostname = $request->getSchemeAndHttpHost();

        // add static urls
        $urls[] = array('loc' => $this->generateUrl('homepage'));
        $urls[] = array('loc' => $this->generateUrl('contacts'));
        $urls[] = array('loc' => $this->generateUrl('news'));
        $urls[] = array('loc' => $this->generateUrl('faq'));
        $urls[] = array('loc' => $this->generateUrl('searchs'));
        $urls[] = array('loc' => $this->generateUrl('promo'));
        $urls[] = array('loc' => $this->generateUrl('regions'));


        foreach ($em->getRepository('InfoBundle:Article')->findAll() as $article) {
            $urls[] = array(
                'loc' => $this->generateUrl('article', array('slug' => $article->getId()))
            );
        }
        foreach ($em->getRepository('AppBundle:Card')->findAll() as $card) {
            $urls[] = array(
                'loc' => $this->generateUrl('showCard', array('id' => $card->getId()))
            );
        }
        foreach ($em->getRepository('AppBundle:City')->findBy(['country'=>'RUS']) as $city) {
            $urls[] = array(
                'loc' => $this->generateUrl('search', array('city' => $city->getUrl()))
            );
        }
        foreach ($em->getRepository('UserBundle:User')->findAll() as $user) {
            $urls[] = array(
                'loc' => $this->generateUrl('user_page', array('id' => $user->getId()))
            );
        }

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