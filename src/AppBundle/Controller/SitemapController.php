<?php
// AppBundle/SitemapController.php

namespace AppBundle\Controller;
use AppBundle\Entity\GeneralType;
use AppBundle\Menu\MenuMarkModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller
{
    /**
     * @Route("/sitemap/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function showAction(Request $request, MenuMarkModel $mm) {
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




        // add static urls with optional tags
//        $urls[] = array('loc' => $this->generateUrl('fos_user_security_login'), 'changefreq' => 'monthly', 'priority' => '1.0');
//        $urls[] = array('loc' => $this->generateUrl('cookie_policy'), 'lastmod' => '2018-01-01');

        // add dynamic urls, like blog posts from your DB
//        foreach ($em->getRepository('InfoBundle:Article')->findAll() as $article) {
//            $urls[] = array(
//                'loc' => $this->generateUrl('article', array('slug' => $article->getId()))
//            );
//        }
//        foreach ($em->getRepository('AppBundle:Card')->findAll() as $card) {
//            $urls[] = array(
//                'loc' => $this->generateUrl('showCard', array('id' => $card->getId()))
//            );
//        }
//        foreach ($em->getRepository('AppBundle:City')->findAll() as $city) {
//            $urls[] = array(
//                'loc' => $this->generateUrl('search', array('city' => $city->getUrl()))
//            );
//        }
//        foreach ($em->getRepository('UserBundle:User')->findAll() as $user) {
//            $urls[] = array(
//                'loc' => $this->generateUrl('user_page', array('id' => $user->getId()))
//            );
//        }


//        foreach ($em->getRepository('AppBundle:GeneralType')->findAll() as $gt){
//            foreach ($em->getRepository('AppBundle:City')->findBy(['country'=>'RUS']) as $city) {
//                $urls[] = array(
//                    'loc' => $this->generateUrl('search', array('city' => $city->getUrl(),'service'=>'all','general'=>$gt->getUrl()))
//                );
//            }
//        }



//                    $em = $this->getDoctrine()->getManager();
//                    $qb = $em -> createQueryBuilder();
//        $query=$qb->select(array('c'))
//            ->from('AppBundle:City','c')
//            ->where('c.country=RUS')
//            ->andWhere($qb->expr()->gt('c.total',0))
//            ->getQuery();
//        $cities = $query->getResult();


                    foreach ($em->getRepository('AppBundle:City')->findBy(['country' => 'RUS']) as $city) {
                        if ($city->getTotal()!=0){
                        foreach ($em->getRepository('AppBundle:GeneralType')->findBy(['url'=>'cars']) as $gt) {
                        foreach ($em->getRepository('AppBundle:Mark')->findAll() as $mark) {
    //                    foreach ($mm->getModels($mark->getId()) as $model) {
                        $urls[] = array(
                            'loc' => $this->generateUrl('search', array('city' => $city->getUrl(), 'service' => 'all', 'general' => $gt->getUrl(),'mark' => $mark->getHeader()))
                        );
                    }
                }}
            }
      //  }

//        foreach ($em->getRepository('AppBundle:City')->findBy(['country' => 'RUS']) as $city) {
//            foreach ($em->getRepository('AppBundle:GeneralType')->findBy(['url'=>'trucks']) as $gt) {
//                foreach ($em->getRepository('AppBundle:Mark')->findAll() as $mark) {
//                    foreach ($mm->getModels($mark->getId()) as $model) {
//                        $urls[] = array(
//                            'loc' => $this->generateUrl('search', array('city' => $city->getUrl(), 'service' => 'all', 'general' => $gt->getUrl(),'model' => $model->getHeader()))
//                        );
//                    }
//                }
//            }
//        }
//        foreach ($em->getRepository('AppBundle:City')->findBy(['country' => 'RUS']) as $city) {
//            foreach ($em->getRepository('AppBundle:GeneralType')->findBy(['url'=>'limo']) as $gt) {
//                foreach ($em->getRepository('AppBundle:Mark')->findAll() as $mark) {
//                    foreach ($mm->getModels($mark->getId()) as $model) {
//                        $urls[] = array(
//                            'loc' => $this->generateUrl('search', array('city' => $city->getUrl(), 'service' => 'all', 'general' => $gt->getUrl(), 'model' => $model->getHeader()))
//                        );
//                    }
//                }
//            }
//        }

        // add image urls
//        $products = $em->getRepository('AppBundle:Card')->findAll();
//        foreach ($products as $item) {
//            foreach($card->getFotos() as $foto){
//            $images = array(
//                'loc' => $item->getImagePath(), // URL to image
//                'title' => $item->getTitle()    // Optional, text describing the image
//            );}
//
//            $urls[] = array(
//                'loc' => $this->generateUrl('showCard', array('slug' => $item->getProductSlug())),
//                'image' => $images              // set the images for this product url
//            );
//        }


        // return response in XML format
        $response = new Response(
            $this->renderView('sitemap/sitemap.html.twig', array( 'urls' => $urls,
                'hostname' => $hostname)),
            200
        );
        $response->headers->set('Content-Type', 'text/xml');

        return $response;

    }

}
