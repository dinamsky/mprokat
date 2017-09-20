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
     * @Route("/adminSEOPatterns", name="adminSEOPatterns")
     */
    public function SEOPatternsAction(Request $request)
    {
        $path = '../app/Resources/views/seo_templates/';
        if($request->isMethod('GET')) {
            if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
            else {
                return $this->render('AdminBundle::admin_seo_patterns.html.twig', [
                    'p_title' => @file_get_contents($path.'p_title.html.twig'),
                    'p_description' => @file_get_contents($path.'p_description.html.twig'),
                    'c_title' => @file_get_contents($path.'c_title.html.twig'),
                    'c_description' => @file_get_contents($path.'c_description.html.twig'),
                ]);
            }
        };
        if($request->isMethod('POST')) {
            $post = $request->request;
            file_put_contents($path.'p_title.html.twig', $post->get('p_title'));
            file_put_contents($path.'p_description.html.twig', $post->get('p_description'));
            file_put_contents($path.'c_title.html.twig', $post->get('c_title'));
            file_put_contents($path.'c_description.html.twig', $post->get('c_description'));
            $this->addFlash(
                'notice',
                'Шаблоны успешно сохранены!'
            );
            return $this->redirectToRoute('adminSEOPatterns');
        }
    }

}
