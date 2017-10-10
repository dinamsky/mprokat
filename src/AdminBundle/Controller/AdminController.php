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
            'Неправильная пара логин/пароль!'
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
            'Вы успешно вышли из аккаунта!'
        );
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/adminNewUser", name="adminNewUser")
     */
    public function newUserAction(Request $request, Password $password, \Swift_Mailer $mailer)
    {
        if($request->isMethod('GET')) {
            if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
            else {
                return $this->render('AdminBundle::admin_new_user.html.twig');
            }
        }
        if($request->isMethod('POST')) {

            $admin = $this->getDoctrine()
                ->getRepository(Admin::class)
                ->find($this->get('session')->get('admin')->getId());


            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findBy(array(
                    'email' => $request->request->get('email')
                ));

            $phone = $this->getDoctrine()
                ->getRepository(UserInfo::class)
                ->findOneBy(array(
                    'uiValue' => $request->request->get('phone')
                ));

            dump($phone);


            if ($phone) {
                $this->addFlash(
                    'notice',
                    'Номер телефона уже зарегистрирован! <br><br> <a href="/user/'.$phone->getUserId().'">посмотреть профиль пользователя</a>'
                );
                return $this->redirectToRoute('adminNewUser');
            }

            if ($user) {
                $this->addFlash(
                    'notice',
                    'Пользователь уже зарегистрирован!'
                );
                return $this->redirectToRoute('adminNewUser');
            }


            $user = new User();
            $user->setEmail($request->request->get('email'));
            $user->setLogin($request->request->get('header'));
            $user->setPassword($password->HashPassword($request->request->get('password')));
            $user->setHeader($request->request->get('header'));
            $user->setActivateString('');
            $user->setTempPassword('');
            $user->setIsActivated(true);
            $user->setAdmin($admin);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $userinfo = new UserInfo();
            $userinfo->setUser($user);
            $userinfo->setUiKey('phone');
            $userinfo->setUiValue($request->request->get('phone'));
            $em->persist($userinfo);
            $em->flush();

            $message = (new \Swift_Message('Администратор зарегистрировал аккаунт для вас на сайте multiprokat.com'))
                ->setFrom('mail@multiprokat.com')
                ->setTo($user->getEmail())
                ->setCc('test.multiprokat@gmail.com')
                ->setBody(
                    $this->renderView(
                        'email/admin_registration.html.twig',
                        array(
                            'header' => $user->getHeader(),
                            'password' => $request->request->get('password'),
                            'email' => $user->getEmail()
                        )
                    ),
                    'text/html'
                );
            $mailer->send($message);
            $this->addFlash(
                'notice',
                'Новый аккаунт успешно создан!'
            );
            return $this->redirectToRoute('admin_main');
        }
    }

    /**
     * @Route("/adminComments")
     */
    public function commentsAction(Request $request)
    {
        if($request->isMethod('GET')) {
            if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
            else {
                $comments = $this->getDoctrine()
                    ->getRepository(Comment::class)
                    ->findAll();
                return $this->render('AdminBundle::admin_comments.html.twig', ['comments' => $comments]);
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

    /**
     * @Route("/adminNewAdmin")
     */
    public function newAdminAction(Request $request)
    {
        if($request->isMethod('GET')) {
            if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
            else {
                return $this->render('AdminBundle::admin_new_admin.html.twig');
            }
        }
        if($request->isMethod('POST')) {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['email' => $request->request->get('user_email')]);

            $admin = new Admin();
            $admin->setPassword($user->getPassword());
            $admin->setEmail($user->getEmail());
            $admin->setRole($request->request->get('role'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($admin);
            $em->flush();

            $this->addFlash(
                'notice',
                'Администратор '.$user->getEmail().' успешно назначен!'
            );

            return $this->redirectToRoute('admin_main');
        }
    }

    /**
     * @Route("/adminEmails", name="adminEmails")
     */
    public function emailsAction()
    {
        if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
        else {

            $names = array(
                "admin_registration.html.twig" => 'Регистрация админом',
                "newmark.html.twig" => 'Новая марка',
                "recover.html.twig" => 'Восстановление пароля',
                "registration.html.twig" => 'Регистрация самостоятельная',
                "request.html.twig" => 'Запрос между пользователями'
            );


            $files = scandir('../app/Resources/views/email');
            foreach($files as $key => $file){
                if($file == '.' or $file == '..' or $file == 'email_base.html.twig') unset($files[$key]);
            }

            return $this->render('AdminBundle::admin_emails_list.html.twig', [
                'names' => $names,
                'emails' => $files
            ]);
        }
    }

    /**
     * @Route("/adminEditEmail/{file}")
     */
    public function editEmailAction($file = '', Request $request, \Swift_Mailer $mailer)
    {
        if($request->isMethod('GET')) {
            if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
            else {
                $email = file_get_contents('../app/Resources/views/email/'.$file);
                return $this->render('AdminBundle::admin_edit_email.html.twig', ['email' => $email, 'file' => $file]);
            }
        }
        if($request->isMethod('POST')) {
            file_put_contents('../app/Resources/views/email/'.$request->request->get('file'), $request->request->get('email'));
            if($request->request->has('sendCheck')){
                $card = $this->getDoctrine()
                    ->getRepository(Card::class)
                    ->findOneBy(['adminId' => 1]);

                $message = (new \Swift_Message('Тестовое письмо с multiprokat.com'))
                    ->setFrom('mail@multiprokat.com')
                    ->setTo($request->request->get('check_email'))
                    ->setBody(
                        $this->renderView(
                            'email/'.$request->request->get('file'),
                            array(
                                'header' => '*header*',
                                'password' => '*password*',
                                'email' => '*email*',
                                'card' => $card,
                                'mark' => '*mark*',
                                'code' => '112233',
                                'message' => '*message*',
                                'name' => '*name*',
                                'phone' => '*phone*'
                            )
                        ),
                        'text/html'
                    );
                $mailer->send($message);
                $this->addFlash(
                    'notice',
                    'Тестовое письмо отправлено!'
                );
            }
            return $this->redirectToRoute('adminEmails');
        }
    }
}
