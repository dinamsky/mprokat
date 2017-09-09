<?php

namespace InfoBundle\Controller;
use InfoBundle\Entity\News;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends Controller
{
    /**
     * @Route("/news/{slug}")
     */
    public function indexAction($slug)
    {

        $news = $this->getDoctrine()
            ->getRepository(News::class)
            ->findOneBy(['slug' => $slug]);

        if(!$news) return new Response('No such article',404);

        return $this->render('InfoBundle:news:one_news.html.twig', [
            'news' => $news,
        ]);
    }

    /**
     * @Route("/news")
     */
    public function contactsAction()
    {
        $news = $this->getDoctrine()
            ->getRepository(News::class)
            ->findAll();

        return $this->render('InfoBundle:news:all_news.html.twig', [
            'all_news' => $news,
        ]);
    }
}
