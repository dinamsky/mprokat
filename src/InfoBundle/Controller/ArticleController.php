<?php

namespace InfoBundle\Controller;
use InfoBundle\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    /**
     * @Route("/article/{slug}")
     */
    public function indexAction($slug)
    {

        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findOneBy(['slug' => $slug]);

        if(!$article) return new Response('No such article',404);

        return $this->render('InfoBundle::article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/contacts")
     */
    public function contactsAction()
    {
        return $this->render('InfoBundle::contacts.html.twig', [

        ]);
    }
}
