<?php

namespace InfoBundle\Controller;
use Doctrine\ORM\EntityManagerInterface;
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
    public function contactsAction(EntityManagerInterface $em)
    {
        $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.weight, g.total DESC');
        $generalTypes = $query->getResult();

        $city = $this->get('session')->get('city');

        $in_city = $city->getUrl();

        return $this->render('InfoBundle::contacts.html.twig', [
            'generalTypes' => $generalTypes,
            'in_city' => $in_city,
            'city' => $city,
            'lang' => $_SERVER['LANG']
        ]);
    }
}
