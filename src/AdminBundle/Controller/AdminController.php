<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Security\Password;
use AdminBundle\Entity\Admin;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin_main")
     */
    public function indexAction(Password $password)
    {

        if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
        else {
            return $this->render('AdminBundle::admin_main.html.twig');
        }
    }

    /**
     * @Route("/adminSignIn")
     */
    public function signInAction(Request $request, Password $password)
    {
        $admins = $this->getDoctrine()
            ->getRepository(Admin::class)
            ->findBy(array(
                'email' => $request->request->get('email')
            ));

        foreach($admins as $admin){

            if ($password->CheckPassword($request->request->get('password'), $admin->getPassword())){
                $this->get('session')->set('admin', $admin);
                return $this->redirectToRoute('admin_main');
                break;
            }
        }

        $this->addFlash(
            'notice',
            'Wrong login/password!'
        );

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/adminLogout")
     */
    public function logoutAction(Request $request)
    {
        $this->get('session')->remove('admin');
        $this->addFlash(
            'notice',
            'You logged out from system!'
        );
        return $this->redirectToRoute('homepage');
    }
}
