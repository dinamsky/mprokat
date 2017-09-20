<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\UserInfo;
use UserBundle\Security\Password;
use UserBundle\Entity\User;
use AdminBundle\Entity\Admin;

class SEOController extends Controller
{
    /**
     * @Route("/adminSEOPatterns")
     */
    public function SEOPatternsAction(Request $request)
    {
        if($request->isMethod('GET')) {
            if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
            else {
                $comments = $this->getDoctrine()
                    ->getRepository(Comment::class)
                    ->findAll();
                return $this->render('AdminBundle::admin_seo_patterns.html.twig', ['comments' => $comments]);
            }
        };
        if($request->isMethod('POST')) {

            $comment = $this->getDoctrine()
                ->getRepository(Comment::class)
                ->find($request->request->get('comment_id'));

            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();

            return $this->redirectToRoute('admin_main');
        }
    }

}
