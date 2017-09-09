<?php

namespace InfoBundle\Controller;
use InfoBundle\Entity\News;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminNewsController extends Controller
{
    /**
     * @Route("/adminNews", name="adminNews")
     */
    public function indexAction()
    {

        $news = $this->getDoctrine()
            ->getRepository(News::class)
            ->findAll();

        return $this->render('InfoBundle:news:admin_news_list.html.twig', [
            'all_news' => $news,
        ]);
    }

    /**
     * @Route("/adminNewsEdit/{id}")
     */
    public function editAction($id = '', Request $request)
    {

        if($request->isMethod('GET')) {
            $news = $this->getDoctrine()
                ->getRepository(News::class)
                ->find((int)$id);

            return $this->render('InfoBundle:news:admin_news_edit.html.twig', [
                'news' => $news,
            ]);
        }

        if($request->isMethod('POST')){
            $em = $this->getDoctrine()->getManager();
            $post = $request->request;
            $news = $this->getDoctrine()
                ->getRepository(News::class)
                ->find($post->get('id'));
            $news->setHeader($post->get('header'));
            $news->setSlug($post->get('slug'));
            $news->setContent($post->get('content'));
            $em->persist($news);
            $em->flush();
            return $this->redirectToRoute('adminNews');
        }
    }


}
