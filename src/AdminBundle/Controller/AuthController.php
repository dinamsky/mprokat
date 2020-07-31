<?php

namespace AdminBundle\Controller;

use AdminBundle\Entity\Admin;
use UserBundle\Security\Password;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class AuthController extends Controller
{
    /**
     * @Route("/admin/login", name="admin.login")
     */
    public function loginAction(Password $password)
    {
        $city = $this->get('session')->get('city');

        if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
        else {
            return $this->redirectToRoute('admin_main');
        }
    }

    /**
     * @Route("/adminSignIn", name="admin.signIn")
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
            'Неправильная пара логин/пароль!'
        );

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/adminLogout")
     */
    public function logoutAction(Request $request)
    {
        $response = new Response();
        $response->headers->clearCookie('the_hash');
        $response->sendHeaders();
        $this->get('session')->remove('admin');
        $this->addFlash(
            'notice',
            'Вы успешно вышли из аккаунта!'
        );
        return $this->redirectToRoute('homepage');
    }
}
