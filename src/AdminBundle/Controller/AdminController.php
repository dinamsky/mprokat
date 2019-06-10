<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\Faq;
use UserBundle\Entity\UserInfo;
use UserBundle\Security\Password;
use UserBundle\Entity\User;
use AdminBundle\Entity\Admin;
use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Menu\ServiceVerification;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin_main")
     */
    public function indexAction(Password $password)
    {

        $city = $this->get('session')->get('city');

        $in_city = $city->getUrl();

        if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
        else {
            return $this->render('AdminBundle::admin_main.html.twig',['city'=>$city,'in_city'=>$in_city]);
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
        $response = new Response();
        $response->headers->clearCookie('the_mhash');
        $response->sendHeaders();
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
    public function newUserAction(Request $request, Password $password, \Swift_Mailer $mailer, ServiceVerification $serVerif)
    {
        if($request->isMethod('GET')) {
            if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
            else {
                $city = $this->get('session')->get('city');
                return $this->render('AdminBundle::admin_new_user.html.twig',['city'=>$city]);
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

            $psw = md5($request->request->get('password'));

            $user = new User();
            $user->setEmail($request->request->get('email'));
            $user->setLogin($serVerif->getFormatPhone($request->request->get('phone')));
            $user->setPassword($password->HashPassword($psw));
            $user->setHeader($request->request->get('header'));
            $user->setActivateString('');
            $user->setTempPassword($psw);
            $user->setIsActivated(true);
            $user->setIsEmailCorrect(true);
            $user->setIsPhoneCorrect(true);
            $user->setAdmin($admin);
            $user->setIsSubscriber(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $userinfo = new UserInfo();
            $userinfo->setUser($user);
            $userinfo->setUiKey('phone');
            $userinfo->setUiValue($request->request->get('phone'));
            $em->persist($userinfo);
            $em->flush();


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

            if($user) {
                $admin = new Admin();
                $admin->setPassword($user->getPassword());
                $admin->setEmail($user->getEmail());
                $admin->setRole($request->request->get('role'));

                $em = $this->getDoctrine()->getManager();
                $em->persist($admin);
                $em->flush();

                $this->addFlash(
                    'notice',
                    'Администратор ' . $user->getEmail() . ' успешно назначен!'
                );
            } else {
                $this->addFlash(
                    'notice',
                    'Пользователь ' . $user->getEmail() . ' не найден!'
                );
            }
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

            $city = $this->get('session')->get('city');
            $in_city = $city->getUrl();

            $names = array(
                "admin_registration.html.twig" => 'Регистрация админом',
                "admin_registration_en.html.twig" => 'Регистрация админом EN',
                "newmark.html.twig" => 'Новая марка',
                "recover.html.twig" => 'Восстановление пароля',
                "recover_en.html.twig" => 'Восстановление пароля EN',
                "registration.html.twig" => 'Регистрация самостоятельная',
                "registration_en.html.twig" => 'Регистрация самостоятельная EN',
                "request.html.twig" => 'Запрос между пользователями',
                "request_en.html.twig" => 'Запрос между пользователями EN'
            );


            $files = scandir('../app/Resources/views/email');
            foreach($files as $key => $file){
                if($file == '.' or $file == '..' or $file == 'email_base.html.twig') unset($files[$key]);
            }

            return $this->render('AdminBundle::admin_emails_list.html.twig', [
                'names' => $names,
                'emails' => $files,
                'city' => $city,
                'in_city' => $in_city,
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

            $fs = new Filesystem();
            $fs->remove($this->container->getParameter('kernel.cache_dir'));

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


    /**
     * @Route("/admin_ajax_check_email", name="admin_ajax_check_email")
     */
    public function admin_ajax_check_emailAction(Request $request)
    {
        $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $request->request->get('email')]);
        if ($user) $content = 'Пользователь существует: ID <a href="/user/'.$user->getId().'">'.$user->getId().'</a>';
        else $content = 'Email свободен';
        return new Response($content, 200);
    }

    /**
     * @Route("/adminMessages", name="adminMessages")
     */
    public function messagesAction()
    {
        if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
        else {
            $em = $this->getDoctrine()->getManager();
            $m_users = $users = [];
            $query = $em->createQuery('SELECT m FROM UserBundle:Message m ORDER BY m.dateCreate DESC');
            $msgs = $query->getResult();


            $res = [];$cards = [];
            foreach ($msgs as $m){
                $m_users[$m->getFromUserId()] = 1;
                $m_users[$m->getToUserId()] = 1;

                $res[$m->getDateCreate()->format('d-m-Y')][] = $m;
                $cards[$m->getCardId()] = $this->getDoctrine()
                    ->getRepository(Card::class)
                    ->find($m->getCardId());

            }

            foreach($m_users as $u=>$v){
                $u_object = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->find($u);

                $users[$u] = $u_object;
            }

            $city = $this->get('session')->get('city');

            return $this->render('AdminBundle::admin_messages.html.twig', [
                'messages' => $res,
                'cards' => $cards,
                'users' => $users,
                'city' => $city,
            ]);
        }
    }

    /**
     * @Route("/adminOrders", name="adminOrders")
     */
    public function adminOrdersAction()
    {
        if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
        else {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery('SELECT o FROM UserBundle:FormOrder o WHERE o.isNew =1 ORDER BY o.dateCreate DESC');

            $orders = $query->getResult();

            $city = $this->get('session')->get('city');

            return $this->render('AdminBundle::admin_orders.html.twig', [
                'orders' => $orders,
                'city' => $city,
            ]);
        }
    }

    /**
     * @Route("/adminFaq", name="adminFaq")
     */
    public function adminFaqAction()
    {
        if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
        else {
            $em = $this->getDoctrine()->getManager();

            //$query = $em->createQuery('SELECT o FROM UserBundle:FormOrder o WHERE o.isNew =1 ORDER BY o.dateCreate DESC');

            //$orders = $query->getResult();

            $faq = $this->getDoctrine()
            ->getRepository(Faq::class)
            ->findBy([],['position'=>'ASC']);

            $city = $this->get('session')->get('city');

            return $this->render('AdminBundle::admin_faq.html.twig', [
                'faq' => $faq,
                'city' => $city,
            ]);
        }
    }

    /**
     * @Route("/adminFaqAdd", name="adminFaqAdd")
     */
    public function adminFaqAddAction(Request $request)
    {
        $city = $this->get('session')->get('city');
        if($request->isMethod('GET')) {
            return $this->render('AdminBundle::admin_faq_add.html.twig', [
                'city' => $city
            ]);
        }
        if($request->isMethod('POST')){
            $em = $this->getDoctrine()->getManager();
            $post = $request->request;
            $news = new Faq();
            $news->setHeader($post->get('header'));
            $news->setPosition((int)$post->get('position'));
            $news->setContent($post->get('content'));
            $em->persist($news);
            $em->flush();
            return $this->redirectToRoute('adminFaq');
        }
    }

    /**
     * @Route("/adminFaqEdit/{id}", name="adminFaqEdit")
     */
    public function adminFaqEdittAction($id='', Request $request)
    {
        $city = $this->get('session')->get('city');
        if($request->isMethod('GET')) {
            $f = $this->getDoctrine()
                ->getRepository(Faq::class)
                ->find((int)$id);
            return $this->render('AdminBundle::admin_faq_edit.html.twig', [
                'f' => $f,
                'city' => $city
            ]);
        }
        if($request->isMethod('POST')){
            $em = $this->getDoctrine()->getManager();
            $post = $request->request;
            $f = $this->getDoctrine()
                ->getRepository(Faq::class)
                ->find($post->get('id'));
            $f->setHeader($post->get('header'));
            $f->setPosition((int)$post->get('position'));
            $f->setContent($post->get('content'));
            $em->persist($f);
            $em->flush();
            return $this->redirect('/adminFaqEdit/'.$post->get('id'));
        }
    }

    /**
     * @Route("/adminFaqDelete", name="adminFaqDelete")
     */
    public function adminFaqDeleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $request->request;
        $f = $this->getDoctrine()
            ->getRepository(Faq::class)
            ->find($post->get('id'));
        $em->remove($f);
        $em->flush();
        return $this->redirectToRoute('adminFaq');
    }
}
