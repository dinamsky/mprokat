<?php

namespace UserBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Foto\FotoUtils;
use AppBundle\Menu\ServiceStat;
use UserBundle\Entity\Blocking;
use UserBundle\Entity\User;
use UserBundle\Entity\FormOrder;
use Doctrine\ORM\EntityManagerInterface as em;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuMarkModel;
use AppBundle\Menu\MenuSubFieldAjax;
use AppBundle\SubFields\SubFieldUtils;
use UserBundle\Entity\UserInfo;
use UserBundle\Security\CookieMaster;
use UserBundle\Security\Password;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use UserBundle\UserBundle;

class ProfileController extends Controller
{
    protected $mailer;
    protected $cookieMaster;

    public function __construct(\Swift_Mailer $mailer, CookieMaster $cookieMaster)
    {
        $this->mailer = $mailer;
        $this->cookieMaster = $cookieMaster;
    }

    /**
     * @Route("/account_recovery", name="account_recovery")
     */
    public function userAccountRecoveryAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {

        $city = $this->get('session')->get('city');
        $in_city = $city->getUrl();

        $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.total DESC');
        $generalTypes = $query->getResult();

        return $this->render('user/user_account_recovery.html.twig', [
            'share' => true,
            'city' => $city,

            'in_city' => $in_city,
            'cityId' => $city->getId(),
            'generalTypes' => $generalTypes,
            'lang' => $_SERVER['LANG'],

        ]);
    }

    /**
     * @Route("/account_send_code", name="account_send_code")
     */
    public function userAccountSendCodeAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $number = preg_replace('~[^0-9]+~','',$request->request->get('phone'));

        if(strlen($number)==11) $number = substr($number, 1);

        $like = '%'.implode("%",str_split($number)).'%';
        $query = $em->createQuery("SELECT u FROM UserBundle:UserInfo u WHERE u.uiKey='phone' AND u.uiValue LIKE '".$like."'");
        $result = $query->getResult();
        if($result) {
            $result = $result[0];
            $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($result->getUserId());

            $code = rand(10000000,99999999);
            $user->setActivateString('recover_'.$code);
            $em->persist($user);
            $em->flush();

            $message = urlencode('Код для восстановления аккаунта Multiprokat.com: '.$code);
            $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients='.$number.'&message='.$message;
            $sms_result = file_get_contents($url);

            $query = $em->createQuery('SELECT c FROM AppBundle:Card c WHERE c.userId = ?1');
            $query->setParameter(1, $user->getId());
            $cards = $query->getResult();
            if(count($cards)>0) {
                $city = $this->get('session')->get('city');
                $in_city = $city->getUrl();
                $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.total DESC');
                $generalTypes = $query->getResult();
                return $this->render('user/user_founded_cards.html.twig', [

                    'city' => $city,
                    'cards' => $cards,
                    'in_city' => $in_city,
                    'cityId' => $city->getId(),
                    'generalTypes' => $generalTypes,
                    'lang' => $_SERVER['LANG'],

                ]);
            } else return '';
        } else return new Response('not found');
    }


    /**
     * @Route("/account_do_recover", name="account_do_recover")
     */
    public function userAccountDoRecoverAction(EntityManagerInterface $em, Request $request, ServiceStat $stat, Password $password)
    {
        $code = 'recover_'.$request->request->get('code');
        $query = $em->createQuery("SELECT u FROM UserBundle:User u WHERE u.activateString=?1");
        $query->setParameter(1, $code);
        $result = $query->getResult();
        if($result) {

            $user = $result[0];

            $user->setActivateString('');
            $user->setEmail($request->request->get('email'));
            $user->setPassword($password->HashPassword($request->request->get('password')));
            $em->persist($user);
            $em->flush();

            $this->get('session')->set('logged_user', $user);
            $this->setAuthCookie($user);
            $this->addFlash(
                'notice',
                'Вы успешно вошли в аккаунт!'
            );

            return $this->redirectToRoute('user_cards');

        } else return new Response('not found');
    }


    private function setAuthCookie(User $user)
    {
        $response = new Response();
        $hash = $this->cookieMaster->setHash($user->getId());
        $cookie = new Cookie('the_hash', $hash.base64_encode($user->getId()), strtotime('now +1 year'));
        $response->headers->setCookie($cookie);
        $response->sendHeaders();
    }

    /**
     * @Route("/user/cards", name="user_cards")
     */
    public function userCardsAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());

        if(!$user->getIsBanned()) {
            $query = $em->createQuery('SELECT c FROM AppBundle:Card c WHERE c.userId = ?1');
            $query->setParameter(1, $this->get('session')->get('logged_user')->getId());
            $cards = $query->getResult();


            $city = $this->get('session')->get('city');
            $in_city = $city->getUrl();





            $stat_arr = [
                'url' => $request->getPathInfo(),
                'event_type' => 'visit',
                'page_type' => 'cabinet',
                'user_id' => $user->getId(),
            ];
            $stat->setStat($stat_arr);

            $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.total DESC');
            $generalTypes = $query->getResult();


            $totalSum = $this->countSum($user->getId());

            return $this->render('user/user_cards.html.twig', [
                'share' => true,
                'cards' => $cards,
                'city' => $city,

                'in_city' => $in_city,
                'cityId' => $city->getId(),
                'generalTypes' => $generalTypes,
                'lang' => $_SERVER['LANG'],
                'totalSum' => $totalSum

            ]);
        } else return new Response("",404);
    }

    /**
     * @Route("/user/messages", name="user_messages")
     */
    public function userMessagesAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());

        if(!$user->getIsBanned()) {


            $city = $this->get('session')->get('city');
            $in_city = $city->getUrl();
            $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.total DESC');
            $generalTypes = $query->getResult();

            $m_users = $users = [];
            $query = $em->createQuery('SELECT m FROM UserBundle:Message m WHERE m.fromUserId = ?1 OR m.toUserId = ?1 ORDER BY m.dateCreate ASC');
            $query->setParameter(1, $user->getId());
            $msgs = $query->getResult();


            $res = [];$cards = [];$blockings = [];$blockme = [];
            foreach ($msgs as $m){
                $m_users[$m->getFromUserId()] = 1;
                $m_users[$m->getToUserId()] = 1;

                if($m->getFromUserId() != $user->getId()) $chat_visitor_id = $m->getFromUserId();
                else $chat_visitor_id = $m->getToUserId();
                $res[$chat_visitor_id][$m->getCardId()][] = $m;

                $cards[$m->getCardId()] = $this->getDoctrine()
                    ->getRepository(Card::class)
                    ->find($m->getCardId());

            }

            foreach($m_users as $u=>$v){

                $u_object = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->find($u);

                $u_object->user_foto = false;

                foreach ($u_object->getInformation() as $info) {
                    if ($info->getUiKey() == 'foto' and $info->getUiValue() != '') $u_object->user_foto = '/assets/images/users/t/' . $info->getUiValue() . '.jpg';
                }

                $users[$u] = $u_object;

                $blockings[$u] = $this->getDoctrine()
                    ->getRepository(Blocking::class)
                    ->findBy([
                        'userId' => $this->get('session')->get('logged_user')->getId(),
                        'visitorId' => $u
                    ]);

                $blockme[$u] = $this->getDoctrine()
                    ->getRepository(Blocking::class)
                    ->findBy([
                        'userId' => $u,
                        'visitorId' => $this->get('session')->get('logged_user')->getId()
                    ]);

            }




            return $this->render('user/user_messages.html.twig', [
                'city' => $city,
                'in_city' => $in_city,
                'cityId' => $city->getId(),
                'generalTypes' => $generalTypes,
                'lang' => $_SERVER['LANG'],


                'messages' => $res,
                'users' => $users,
                'cards' => $cards,
                'blockings' => $blockings,
                'blockme' => $blockme

            ]);
        } else return new Response("",404);
    }

    /**
     * @Route("/user/delete_blocking_user_messages", name="delete_blocking_user_messages")
     */
    public function duUserMessagesAction(EntityManagerInterface $em, Request $request)
    {
        $id = $request->request->get('id');
        $action = $request->request->get('user_action');

        if($action=='delete'){
            $query = $em->createQuery('DELETE UserBundle:Message m WHERE (m.fromUserId = ?1 AND m.toUserId = ?2) OR (m.fromUserId = ?2 AND m.toUserId = ?1)');
            $query->setParameter(1, $id);
            $query->setParameter(2, $this->get('session')->get('logged_user')->getId());
            $query->execute();
        }

        if($action=='block'){
            $blocking = new Blocking();
            $blocking->setUserId($this->get('session')->get('logged_user')->getId());
            $blocking->setVisitorId($id);
            $em->persist($blocking);
            $em->flush();
        }

        if($action=='unblock'){
            $blocking = $this->getDoctrine()
            ->getRepository(Blocking::class)
            ->findOneBy([
                'userId' => $this->get('session')->get('logged_user')->getId(),
                'visitorId' => $id,
            ]);
            $em->remove($blocking);
            $em->flush();
        }

        return $this->redirectToRoute('user_messages');
    }

    /**
     * @Route("/user", name="user_main")
     */
    public function indexAction(Password $password, EntityManagerInterface $em)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());

        $city = $this->get('session')->get('city');
        $in_city = $city->getUrl();



        $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.total DESC');
        $generalTypes = $query->getResult();




        if(!$user->getIsBanned()) return $this->render('user/profile_main.html.twig',[
            'user' => $user,
            'city' => $city,

            'in_city' => $in_city,
            'cityId' => $city->getId(),
            'generalTypes' => $generalTypes,
            'lang' => $_SERVER['LANG'],

        ]);
        else return new Response("",404);
    }



    /**
     * @Route("/profile", name="user_profile")
     */
    public function editProfileAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());




        if(!$user->getIsBanned()) {


            $city = $this->get('session')->get('city');
            $in_city = $city->getUrl();



            $stat_arr = [
                'url' => $request->getPathInfo(),
                'event_type' => 'visit',
                'page_type' => 'cabinet',
                'user_id' => $user->getId(),
            ];
            $stat->setStat($stat_arr);

            $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.total DESC');
            $generalTypes = $query->getResult();

            $totalSum = $this->countSum($user->getId());


            return $this->render('user/user_profile.html.twig', [
                'user' => $user,
                'city' => $city,

                'in_city' => $in_city,
                'cityId' => $city->getId(),
                'generalTypes' => $generalTypes,
                'lang' => $_SERVER['LANG'],
                'totalSum' => $totalSum

            ]);
        }
        else return new Response("",404);
    }

    /**
     * @Route("/profile/save")
     */
    public function updateProfileAction(Request $request)
    {
        $post = $request->request;

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());

        $user->setHeader(trim(strip_tags($post->get('header'))));
        $user->setEmail(trim(strip_tags($post->get('email'))));
        $user->setWhois('standard_renter');
        $user->setTempPassword('');

        /**
         * @var $info UserInfo
         */

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('DELETE UserBundle\Entity\UserInfo u WHERE u.userId = ?1 AND u.uiKey != ?2');
        $query->setParameter(1, $user->getId());
        $query->setParameter(2, 'foto');
        $query->execute();

        foreach($post->get('info') as $uiKey =>$uiValue ){
            $info = new UserInfo();
            $info->setUiKey($uiKey);

//            if (in_array($uiKey,['website','about'])) $uiValue = strip_tags($uiValue);
            $uiValue = trim(strip_tags($uiValue));

            $info->setUiValue($uiValue);
            $info->setUser($user);
            $em->persist($info);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->get('session')->set('logged_user', $user);

        if($post->has('back_url') and $post->get('back_url') != '' ) return $this->redirect($post->get('back_url'));
        else return $this->redirectToRoute('user_profile');
    }

    /**
     * @Route("/user/sendAbuse")
     */
    public function sendAbuseAction(Request $request, \Swift_Mailer $mailer)
    {
        $_t = $this->get('translator');

        $post = $request->request;

        $card_id = $post->get('card_id');

        if ($this->captchaVerify($post->get('g-recaptcha-response'))) {

            foreach($post->get('abuse') as $ms){
                $msg[] = $ms.'<br>';
            }
            $msg[] = 'Жалоба отправлена со <a href="https://multiprokat.com/card/'.$card_id.'">страницы</a>';
            $msg = implode("",$msg);

            $message = (new \Swift_Message('Жалоба от пользователя'))
                ->setFrom(['mail@multiprokat.com' => 'Робот Мультипрокат'])
                ->setTo('mail@multiprokat.com')
                ->setBody(
                    $msg,
                    'text/html'
                );
            $mailer->send($message);

            $this->addFlash(
                'notice',
                $_t->trans('Ваша жалоба успешно отправлена!')
            );
        } else {
            $this->addFlash(
                'notice',
                $_t->trans('Каптча не пройдена!')
            );
        }

        return $this->redirect('/card/'.$card_id);

    }


    /**
 * @Route("/user/sendMessage")
 */
    public function sendMessageAction(Request $request, \Swift_Mailer $mailer)
    {
        $_t = $this->get('translator');

        $post = $request->request;


//        if($post->has('card_id')) {
//            $card_id = $post->get('card_id');
//
//            $card = $this->getDoctrine()
//                ->getRepository(Card::class)
//                ->find($card_id);
//            $user = $card->getUser();
//        }

        if($post->has('user_id')){
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($post->get('user_id'));
            $card = false;
            $card_id = false;
        }


        if ($this->captchaVerify($post->get('g-recaptcha-response'))) {
        //if (1==1) {




            $message = (new \Swift_Message($_t->trans('Сообщение от пользователя')))
                ->setFrom(['mail@multiprokat.com' => 'Робот Мультипрокат'])
                ->setTo($user->getEmail())
                ->setCc('mail@multiprokat.com')
                ->setBody(
                    $this->renderView(
                        $_SERVER['LANG'] == 'ru' ? 'email/request.html.twig' : 'email/request_'.$_SERVER['LANG'].'.html.twig',
                        array(
                            'header' => $user->getHeader(),
                            'message' => $post->get('message'),
                            'email' => $post->get('email'),
                            'name' => $post->get('name'),
                            'phone' => $post->get('phone'),
                            'card' => $card,
                            'user' => $user
                        )
                    ),
                    'text/html'
                );
            $mailer->send($message);

            $form_order = new FormOrder();
//            if ($card) $form_order->setCardId($card->getId());
            $form_order->setUserId($user->getId());

            $form_order->setContent($post->get('message'));
            $form_order->setTransport('');
            $form_order->setCityIn('');
            $form_order->setCityOut('');

            $form_order->setEmail($post->get('email'));
            $form_order->setPhone($post->get('phone'));
            $form_order->setName($post->get('name'));
            $form_order->setFormType('user_page_message');

            $em = $this->getDoctrine()->getManager();
            $em->persist($form_order);
            $em->flush();


            $this->addFlash(
                'notice',
                $_t->trans('Ваше сообщение успешно отправлено!')
            );
        } else {
            $this->addFlash(
                'notice',
                $_t->trans('Каптча не пройдена!')
            );
        }

        //if(isset($card)) return $this->redirect('/card/'.$card_id);
        if(isset($user)) return $this->redirect('/user/'.$user->getId());
    }

     /**
 * @Route("/user/bookMessage")
 */
    public function bookMessageAction(Request $request, \Swift_Mailer $mailer)
    {
        $_t = $this->get('translator');

        $post = $request->request;

        //var_dump($post);

        //var_dump($post->has('is_nonreged'));


        if ($this->captchaVerify($post->get('g-recaptcha-response')) or $post->has('is_nonreged')) {
        //if (1==1) {

            $card_id = $post->get('card_id');

            $card = $this->getDoctrine()
                ->getRepository(Card::class)
                ->find($card_id);
            $user = $card->getUser();

            $price = $deposit = $service = 0;
            foreach ($card->getCardPrices() as $cp){
                if($cp->getpriceId() == 2) $price = $cp->getValue();
                if($cp->getpriceId() == 10) $deposit = $cp->getValue();
            }

            $period = (strtotime(str_replace(".","-",$post->get('date_out'))) - strtotime(str_replace(".","-",$post->get('date_in'))))/60/60/24;

            if ($period < 1 ) $period = 1;

            $price = $period*$price;

            $service = ceil($price/100*15);

            if($service == 0) $service = 500;

            //$total = $price + $deposit + $service;
            $total = $price + $service;

            $is_admin_reged = false;
            foreach( $user->getInformation() as $info){
                if($info->getUiKey() == 'phone'){
                    $number = preg_replace('~[^0-9]+~','',$info->getUiValue());
                    if(strlen($number)==11) $number = substr($number, 1);

                    $ph = substr(preg_replace('/[^0-9]/', '', $info->getUiValue()),1);
                    $emz = explode("@",$user->getEmail());
                    if(strlen($emz[0])==11) $emz[0] = substr($emz[0], 1);
                    if ($ph == $emz[0]) $is_admin_reged = true;

                }
            }

            if($is_admin_reged){
                $message = urlencode('Добрый день! Поступила новая заявка на аренду вашего транспорта на сайте: https://multiprokat.com. Если у вас нет доступа к аккаунту - вы легко можете восстановить его по адресу: https://multiprokat.com/account_recovery');
            } else {
                $message = urlencode('Добрый день! Поступила новая заявка на аренду вашего транспорта на сайте: https://multiprokat.com. Увидеть заявку вы можете в личном кабинете вашего аккаунта, а так же на почте '.$user->getEmail().'. Если у вас нет доступа к аккаунту - вы легко можете восстановить его по адресу: https://multiprokat.com/account_recovery');
            }

            //dump($this->container->get('kernel')->getEnvironment());

            //$url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=' . $number . '&message=' . $message;
            //$sms_result = file_get_contents($url);


            $renter = $this->get('session')->get('logged_user');

            if(!$is_admin_reged) {
                $message = (new \Swift_Message($_t->trans('Запрос на бронирование')))
                    ->setFrom(['mail@multiprokat.com' => 'Робот Мультипрокат'])
                    ->setTo($user->getEmail())
                    ->setBcc('mail@multiprokat.com')
                    ->setBody(
                        $this->renderView(
                            $_SERVER['LANG'] == 'ru' ? 'email/book.html.twig' : 'email/book_' . $_SERVER['LANG'] . '.html.twig',
                            array(
                                'header' => $post->get('header'),
                                'date_in' => $post->get('date_in'),
                                'date_out' => $post->get('date_out'),
                                'city_in' => $post->get('city_in'),
                                'city_out' => $post->get('city_out'),
                                'alternative' => $post->get('alternative'), // this is comment
                                'email' => $post->get('email'),
                                'full_name' => $post->get('full_name'),
                                'phone' => $post->get('phone'),
                                'card' => $card,
                                'user' => $user,
                                'fio_renter' => $renter->getHeader(),
                                'passport4' => $post->get('passport4'),
                                'driving_license4' => $post->get('driving_license4'),
                            )
                        ),
                        'text/html'
                    );
                //$mailer->send($message);
            }

            $form_order = new FormOrder();
            $form_order->setCardId($card->getId());
            $form_order->setUserId($user->getId());
            $form_order->setCityIn($post->get('city_in'));
            $form_order->setCityOut($post->get('city_out'));
            $form_order->setDateIn(\DateTime::createFromFormat('d.m.Y', $post->get('date_in')));
            $form_order->setDateOut(\DateTime::createFromFormat('d.m.Y', $post->get('date_out')));
            $form_order->setContent('');
            $form_order->setTransport($post->get('header'));
            $form_order->setEmail('');
            $form_order->setPhone('');
            $form_order->setName('');
            $form_order->setFormType('new_transport_order');

            if($post->get('content') != ''){
                $msg = $post->get('content');
            } else {
                $msg = 'Добрый день! Отправляю вам заявку';
            }

            $messages[] = [
                'date' => date('d-m-Y'),
                'time' => date('H:i'),
                'from' => 'renter',
                'message' => $msg,
                'status' => 'send'
            ];


            $form_order->setMessages(json_encode($messages));
            $form_order->setRenterId($renter->getId());
            $form_order->setFioRenter($renter->getHeader());
            $form_order->setPassport4('');
            $form_order->setDrivingLicense4('');
            $form_order->setOwnerStatus('wait_for_accept');
            $form_order->setRenterStatus('wait_for_accept');
            $form_order->setIsNew(1);

            $form_order->setPrice($price);
            $form_order->setDeposit($deposit);
            $form_order->setService($service);
            $form_order->setTotal($total);

            $em = $this->getDoctrine()->getManager();
            $em->persist($form_order);
            $em->flush();

            $this->addFlash(
                'notice',
                'Ваша заявка успешно отправлена! Ожидайте ответа в личном кабинете, на email, по СМС'
            );

            // send SMS


        } else {
            $this->addFlash(
                'notice',
                $_t->trans('Каптча не пройдена!')
            );
        }

        if($post->has('is_nonreged')) {
            return $this->redirect('/profile');
        } else {
            return $this->redirect('/card/'.$card->getId());
        }

    }

//
    /**
     * @Route("/user/contactsMessage")
     */
    public function contactsMessageAction(Request $request, \Swift_Mailer $mailer)
    {
        $_t = $this->get('translator');

        $post = $request->request;

//        $card_id = $post->get('card_id');
//
//        $card = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->find($card_id);
//
//        $user = $card->getUser();

        $message = (new \Swift_Message('#'.rand(10000,99999).' Сообщение со страницы: Контакты'))
            ->setFrom(['mail@multiprokat.com' => 'Робот Мультипрокат'])
            ->setTo('mail@multiprokat.com')
            ->setBody(
                $this->renderView(
                    $_SERVER['LANG'] == 'ru' ? 'email/request.html.twig' : 'email/request_'.$_SERVER['LANG'].'.html.twig',
                    array(
                        'header' => 'Админ',
                        'message' => $post->get('message'),
                        'email' => $post->get('email'),
                        'name' => $post->get('name'),
                        'phone' => $post->get('phone'),
                    )
                ),
                'text/html'
            );
        $mailer->send($message);

        $this->addFlash(
            'notice',
            $_t->trans('Ваше сообщение в Администрацию успешно отправлено!')
        );

        return $this->redirect('/');
    }

    /**
     * @Route("/profile/saveFoto")
     */
    public function saveFotoAction(Request $request, FotoUtils $fu)
    {
        $post = $request->request;
        $em = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($post->get('user_id'));


        $query = $em->createQuery('DELETE UserBundle\Entity\UserInfo u WHERE u.userId = ?1 AND u.uiKey = ?2');
        $query->setParameter(1, $post->get('user_id'));
        $query->setParameter(2, 'foto');
        $query->execute();
        if (is_file($_SERVER['DOCUMENT_ROOT'].'/assets/images/users/'.$post->get('user_id')))
            unlink ($_SERVER['DOCUMENT_ROOT'].'/assets/images/users/'.$post->get('user_id'));

        if($post->has('delete')) return $this->redirectToRoute('user_profile');

        $userInfo = new UserInfo();
        $userInfo->setUser($user);
        $userInfo->setUiKey('foto');
        $userInfo->setUiValue('user_'.$post->get('user_id'));
        $em->persist($userInfo);
        $em->flush();



        $fu->uploadImage(
            'foto',
            'user_'.$post->get('user_id'),
            $_SERVER['DOCUMENT_ROOT'].'/assets/images/users',
            $_SERVER['DOCUMENT_ROOT'].'/assets/images/users/t');

        return $this->redirectToRoute('user_profile');
    }

    /**
     * @Route("/ajax/goPro")
     */
    public function goProAction(Request $request)
    {
        $post = $request->request;
        $em = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($post->get('user_id'));
        $user->setAccountTypeId(1);
        $em->persist($user);
        $em->flush();

        return new Response();
    }

    /**
     * @Route("/user/transport_orders", name="user_transport_orders")
     */
    public function userTorderAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());

        if(!$user->getIsBanned()) {
            $query = $em->createQuery('SELECT o FROM UserBundle:FormOrder o WHERE o.userId = ?1 OR o.renterId = ?1 ORDER BY o.dateCreate DESC');
            $query->setParameter(1, $this->get('session')->get('logged_user')->getId());
            $orders = $query->getResult();


            $city = $this->get('session')->get('city');
            $in_city = $city->getUrl();





            $stat_arr = [
                'url' => $request->getPathInfo(),
                'event_type' => 'orders_page',
                'page_type' => 'orders_list',
                'user_id' => $user->getId(),
            ];
            $stat->setStat($stat_arr);

            $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.total DESC');
            $generalTypes = $query->getResult();

            $totalSum = $this->countSum($user->getId());

            return $this->render('user/user_orders_list.html.twig', [
                'share' => true,
                'orders' => $orders,
                'city' => $city,
                'full' => true,
                'in_city' => $in_city,
                'cityId' => $city->getId(),
                'generalTypes' => $generalTypes,
                'lang' => $_SERVER['LANG'],
                'totalSum' => $totalSum
            ]);
        } else return new Response("",404);
    }

    /**
     * @Route("/ajax_set_ord_active", name="ajax_set_ord_active")
     */
    public function ajaxSetOrderActiveAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $id = $request->request->get('id');
        $order = $this->getDoctrine()
            ->getRepository(FormOrder::class)
            ->find($id);

        $user = 'renter';
        if($order->getUserId() == $request->request->get('user_id')) $user = 'owner';
//        else $order->setIsActiveRenter(true);

        if ($user == 'owner') {
//            $order->setIsActiveOwner(true);
            $query = $em->createQuery('UPDATE UserBundle:FormOrder f SET f.isActiveOwner = 0 WHERE f.userId = ?1');
            $query->setParameter(1, $request->request->get('user_id'));
            $query->execute();

            $query = $em->createQuery('UPDATE UserBundle:FormOrder f SET f.isActiveRenter = 0 WHERE f.renterId = ?1');
            $query->setParameter(1, $request->request->get('user_id'));
            $query->execute();

            $query = $em->createQuery('UPDATE UserBundle:FormOrder f SET f.isActiveOwner = 1 WHERE f.id = ?1');
            $query->setParameter(1, $request->request->get('id'));
            $query->execute();

        }
        else {
//            $order->setIsActiveRenter(true);
            $query = $em->createQuery('UPDATE UserBundle:FormOrder f SET f.isActiveRenter = 0 WHERE f.renterId = ?1');
            $query->setParameter(1, $request->request->get('user_id'));
            $query->execute();

             $query = $em->createQuery('UPDATE UserBundle:FormOrder f SET f.isActiveOwner = 0 WHERE f.userId = ?1');
            $query->setParameter(1, $request->request->get('user_id'));
            $query->execute();

            $query = $em->createQuery('UPDATE UserBundle:FormOrder f SET f.isActiveRenter = 1 WHERE f.id = ?1');
            $query->setParameter(1, $request->request->get('id'));
            $query->execute();
        }

//        $em->persist($order);
//        $em->flush();

        return new Response("");
    }

    /**
     * @Route("/ajax_owner_accept", name="ajax_owner_accept")
     */
    public function ownerAcceptAction(EntityManagerInterface $em, Request $request, ServiceStat $stat, \Swift_Mailer $mailer)
    {
        $id = $request->request->get('id');
        $order = $this->getDoctrine()
            ->getRepository(FormOrder::class)
            ->find($id);


        $messages = json_decode($order->getMessages(),true);
        $messages[] = [
            'date' => date('d-m-Y'),
            'time' => date('H:i'),
            'from' => 'system',
            'message' => 'Ваша заявка одобрена. Ожидается оплата',
            'status' => 'send'
        ];

        $order->setMessages(json_encode($messages));
        $order->setOwnerStatus('accepted');
        $order->setRenterStatus('wait_for_pay');
        $em->persist($order);
        $em->flush();

        $this->addFlash(
                'notice',
                'Вы только что одобрили заявку #'.$id.'!<br> Мы уведомили арендатора - ожидайте оплаты.<br><a href="/assets/docs/rent_contract.docx">Скачайте</a> и распечатайте договор аренды.'
            );

        // ------- send SMS -------

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($order->getRenterId());
        foreach( $user->getInformation() as $info){
            if($info->getUiKey() == 'phone'){
                $number = preg_replace('~[^0-9]+~','',$info->getUiValue());
                if(strlen($number)==11) $number = substr($number, 1);
            }
        }

        $message = urlencode('Владелец одобрил вашу заявку №'.$id.'. Можно оплачивать');
        $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=' . $number . '&message=' . $message;
        $sms_result = file_get_contents($url);

        // ---------------------------


        $msg = (new \Swift_Message('Владелец одобрил вашу заявку №'.$id.'. Можно оплачивать'))
                ->setFrom('mail@multiprokat.com')
                ->setTo($user->getEmail())
                ->setBody('Владелец одобрил вашу заявку №'.$id.'. Можно оплачивать','text/html');
        $mailer->send($msg);


        return new Response("");
    }

    /**
     * @Route("/ajax_owner_reject", name="ajax_owner_reject")
     */
    public function ownerRejectAction(EntityManagerInterface $em, Request $request, ServiceStat $stat, \Swift_Mailer $mailer)
    {
        $id = $request->request->get('id');
        $order = $this->getDoctrine()
            ->getRepository(FormOrder::class)
            ->find($id);

        $messages = json_decode($order->getMessages(),true);
        $messages[] = [
            'date' => date('d-m-Y'),
            'time' => date('H:i'),
            'from' => 'system',
            'message' => 'Ваша заявка отклонена',
            'status' => 'send'
        ];

        $order->setMessages(json_encode($messages));

        $order->setOwnerStatus('rejected');
        $order->setRenterStatus('rejected');
        $em->persist($order);
        $em->flush();

        $this->addFlash(
                'notice',
                'Вы только что отклонили заявку #'.$id.'!<br> Мы уведомили арендатора'
            );


        // ------- send SMS -------

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($order->getRenterId());
        foreach( $user->getInformation() as $info){
            if($info->getUiKey() == 'phone'){
                $number = preg_replace('~[^0-9]+~','',$info->getUiValue());
                if(strlen($number)==11) $number = substr($number, 1);
            }
        }

        $message = urlencode('Владелец отклонил вашу заявку №'.$id);
        $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=' . $number . '&message=' . $message;
        $sms_result = file_get_contents($url);

        // ---------------------------

        $msg = (new \Swift_Message('Владелец отклонил вашу заявку №'.$id))
                ->setFrom('mail@multiprokat.com')
                ->setTo($user->getEmail())
                ->setBody('Владелец отклонил вашу заявку №'.$id,'text/html');
        $mailer->send($msg);

        return new Response("");
    }


    private function cut_num($s)
    {
        $s = preg_replace('/\+?\([0-9]+\)[0-9]+/', '*', $s);
        $s = preg_replace('/[0-9]{7}/', '*', $s);
        $s = preg_replace('/[0-9]{2}\-[0-9]{2}\-[0-9]{3}/', '*', $s);
        $s = preg_replace('/[0-9]{3}\-[0-9]{2}\-[0-9]{2}/', '*', $s);
        return $s;
    }

    /**
     * @Route("/ajax_owner_answer", name="ajax_owner_answer")
     */
    public function ownerAnswerAction(EntityManagerInterface $em, Request $request, ServiceStat $stat, \Swift_Mailer $mailer)
    {
        $id = $request->request->get('id');
        $order = $this->getDoctrine()
            ->getRepository(FormOrder::class)
            ->find($id);

        $messages = json_decode($order->getMessages(),true);
        $messages[] = [
            'date' => date('d-m-Y'),
            'time' => date('H:i'),
            'from' => 'owner',
            'message' => $this->cut_num($request->request->get('answer')),
            'status' => 'send'
        ];

        if($request->request->get('answer') != $this->cut_num($request->request->get('answer'))){
            $messages[] = [
                'date' => date('d-m-Y'),
                'time' => date('H:i'),
                'from' => 'system',
                'message' => '@multiprokat_bot: Номера телефонов будут доступны после успешной оплаты заказа',
                'status' => 'send'
            ];
        }

        $order->setMessages(json_encode($messages));
        //$order->setOwnerStatus('answered');
        //$order->setRenterStatus('wait_for_answer');
        $em->persist($order);
        $em->flush();

        // ------- send SMS -------

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($order->getRenterId());
        foreach( $user->getInformation() as $info){
            if($info->getUiKey() == 'phone'){
                $number = preg_replace('~[^0-9]+~','',$info->getUiValue());
                if(strlen($number)==11) $number = substr($number, 1);
            }
        }

        $message = urlencode('Владелец ответил на ваше сообщение в заявке №'.$id);
        if(isset($number)) {
            $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=' . $number . '&message=' . $message;
            $sms_result = file_get_contents($url);
        }

        // ---------------------------

        $msg = (new \Swift_Message('Владелец ответил на ваше сообщение в заявке №'.$id))
                ->setFrom('mail@multiprokat.com')
                ->setTo($user->getEmail())
                ->setBody('Владелец ответил на ваше сообщение в заявке №'.$id,'text/html');
        $mailer->send($msg);

        $msg = (new \Swift_Message('Мультипрокат. Владелец ответил на сообщение в заявке №'.$id))
                ->setFrom('mail@multiprokat.com')
                ->setTo('mail@multiprokat.com')
                ->setBody($request->request->get('answer'),'text/html');
        $mailer->send($msg);

        return new Response("");
    }

    /**
     * @Route("/ajax_renter_answer", name="ajax_renter_answer")
     */
    public function renterAnswerAction(EntityManagerInterface $em, Request $request, ServiceStat $stat, \Swift_Mailer $mailer)
    {
        $id = $request->request->get('id');
        $order = $this->getDoctrine()
            ->getRepository(FormOrder::class)
            ->find($id);

        $messages = json_decode($order->getMessages(),true);
        $messages[] = [
            'date' => date('d-m-Y'),
            'time' => date('H:i'),
            'from' => 'renter',
            'message' => $this->cut_num($request->request->get('answer')),
            'status' => 'send'
        ];

        if($request->request->get('answer') != $this->cut_num($request->request->get('answer'))){
            $messages[] = [
                'date' => date('d-m-Y'),
                'time' => date('H:i'),
                'from' => 'system',
                'message' => '@multiprokat_bot: Номера телефонов будут доступны после успешной оплаты заказа',
                'status' => 'send'
            ];
        }

        $order->setMessages(json_encode($messages));
        //$order->setOwnerStatus('wait_for_answer');
        //$order->setRenterStatus('answered');
        $em->persist($order);
        $em->flush();

        // ------- send SMS -------

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($order->getUserId());
        foreach( $user->getInformation() as $info){
            if($info->getUiKey() == 'phone'){
                $number = preg_replace('~[^0-9]+~','',$info->getUiValue());
                if(strlen($number)==11) $number = substr($number, 1);
            }
        }

        $message = urlencode('Арендатор ответил на ваше сообщение в заявке №'.$id);
        if(isset($number)) {
            $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=' . $number . '&message=' . $message;
            $sms_result = file_get_contents($url);
        }

        // ---------------------------

        $msg = (new \Swift_Message('Арендатор ответил на ваше сообщение в заявке №'.$id))
                ->setFrom('mail@multiprokat.com')
                ->setTo($user->getEmail())
                ->setBody('Арендатор ответил на ваше сообщение в заявке №'.$id,'text/html');
        $mailer->send($msg);

        $msg = (new \Swift_Message('Мультипрокат. Арендатор ответил на сообщение в заявке №'.$id))
                ->setFrom('mail@multiprokat.com')
                ->setTo('mail@multiprokat.com')
                ->setBody($request->request->get('answer'),'text/html');
        $mailer->send($msg);

        return new Response("");
    }

    private function gen_payment($orderid, $price)
    { //generate  array payment
        $array["payment"] = array("orderId" => $orderid,
            "action" => "pay",
            "price" => $price);
        return $array;
    }

    private function to_array_costumerinfo($costumerinfo)
    {
        $array["customerInfo"] = array("email" => $costumerinfo->email,
            "phone" => $costumerinfo->phone);
        return ($array);
    }


    private function get_secret(){
        return [
            'id' => "1110",
            'secret' => 'hpljOY3gop'
        ];
    }

    /**
     * @Route("/pay_for_order/{id}", name="pay_for_order")
     */
    public function payForOrderAction($id, EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $order = $this->getDoctrine()
            ->getRepository(FormOrder::class)
            ->find($id);

//        $card = $this->getDoctrine()
//            ->getRepository(FormOrder::class)
//            ->find($order->getCardId());


        $s = $this->get_secret();

        $renter = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($order->getRenterId());

        $phone = '+79870000000';
        foreach ($renter->getInformation() as $i){
            if ($i->getUiKey() == 'phone') $phone = '+'.str_replace(array("(",")","+"," "),"",trim($i->getUiValue()));
        }

        $merchantId = $s['id'];
        $secret = $s['secret'];

        $url = "https://secure.mandarinpay.com/api/transactions";

        //$reqid = time() ."_". microtime(true) ."_". rand();
        //$hash = hash("sha256", $merchantId ."-". $reqid ."-". $secret);
        //$xauth =  $merchantId ."-".$hash ."-". $reqid;


        $eml = $renter->getEmail();
        if($eml == '') $eml = "noemail@nodomain.za";

        $array_content = [
            "payment" => [
                "orderId" => $id,
                "action" => "pay",
                "price" => $order->getTotal().'.00',
            ],
            "customerInfo" => [
                "email" => $eml,
                "phone" => $phone
            ]
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://secure.mandarinpay.com/api/transactions",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($array_content),
          CURLOPT_HTTPHEADER => array(
            "authorization: Basic ".base64_encode($merchantId.':'.$secret),
            "cache-control: no-cache",
            "content-type: application/json",
          ),
        ));

        $response = json_decode(curl_exec($curl),true);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $this->addFlash(
                'notice',
                'Проблемы на платежном сервисе. Приносим свои извинения'
            );
            return $this->redirectToRoute('user_transport_orders');
        } else {


            if(!isset($response['userWebLink']) or $response['userWebLink'] == ''){
                var_dump($response);
                return new Response();
            } else {
                return $this->redirect($response['userWebLink']);
            }


        }

    }

    function check_sign($req)
    {

        $s = $this->get_secret();
        $secret = $s['secret'];

        $sign = $req['sign'];
        unset($req['sign']);
        $to_hash = '';
        if (!is_null($req) && is_array($req)) {
                ksort($req);
                $to_hash = implode('-', $req);
        }

        $to_hash = $to_hash .'-'. $secret;
        $calculated_sign = hash('sha256', $to_hash);
        return $calculated_sign == $sign;
    }

    /**
     * @Route("/user_order_pay_success", name="user_order_pay_success")
     */
    public function userOrderPaySuccessAction(EntityManagerInterface $em, Request $request, ServiceStat $stat, \Swift_Mailer $mailer)
    {
        $cb = (array)$request->request->all();

        $check = $this->check_sign($cb);

        if($check) {
            $order = $this->getDoctrine()
                ->getRepository(FormOrder::class)
                ->find($cb['orderId']);

            $id = $order->getId();

            //$pincode = rand(1111,9999);

            if($cb['status'] == 'success') {
                $order->setOwnerStatus('wait_for_rent');
                $order->setRenterStatus('wait_for_finish');


                $messages = json_decode($order->getMessages(),true);
                $messages[] = [
                    'date' => date('d-m-Y'),
                    'time' => date('H:i'),
                    'from' => 'system_ok',
                    'message' => 'Заявка оплачена. Деньги за заявку №'.$id.' получены',
                    'status' => 'send'
                ];

                $order->setMessages(json_encode($messages));

                //$order->setPincodeForOwner($pincode);
                $em->persist($order);
                $em->flush();

                $this->addFlash(
                    'notice',
                    'Заявка #' . $id . ' успешно оплачена!<br> Владелец свяжется с вами для обсуждения нюансов.<br><a href="/assets/docs/rent_contract.docx">Скачайте</a> и распечатайте договор аренды.'
                );


                // ------- send SMS -------

                $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->find($order->getUserId());
                foreach( $user->getInformation() as $info){
                    if($info->getUiKey() == 'phone'){
                        $number = preg_replace('~[^0-9]+~','',$info->getUiValue());
                        if(strlen($number)==11) $number = substr($number, 1);
                    }
                }

                $message = urlencode('Арендатор оплатил заявку №'.$id);
                $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=' . $number . '&message=' . $message;
                $sms_result = file_get_contents($url);

                // ---------------------------

                $msg = (new \Swift_Message('Арендатор оплатил заявку №'.$id))
                ->setFrom('mail@multiprokat.com')
                ->setTo($user->getEmail())
                ->setBody('Арендатор оплатил заявку №'.$id,'text/html');
                $mailer->send($msg);

                $msg = (new \Swift_Message('Арендатор оплатил заявку №'.$id))
                ->setFrom('mail@multiprokat.com')
                ->setTo('mail@multiprokat.com')
                ->setBody('Арендатор оплатил заявку №'.$id,'text/html');
                $mailer->send($msg);

                return new Response('OK', 200);
            } else {
                return new Response('BAD', 400);
            }


        } else {
            return new Response('BAD', 400);
        }
    }

    /**
     * @Route("/ajax_owner_pincode", name="ajax_owner_pincode")
     */
    public function ajaxOwnerPincodeAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $id = $request->request->get('id');
        $pincode = $request->request->get('pincode');

        $order = $this->getDoctrine()
            ->getRepository(FormOrder::class)
            ->find($id);
        if($order->getPincodeForOwner() == $pincode) {
            $order->setOwnerStatus('rent_in_process');
            $order->setRenterStatus('rent_in_process');
            $order->setDatePincodeStart(new \DateTime('now'));
            $em->persist($order);
            $em->flush();
            $this->addFlash(
                'notice',
                'Отлично! Пинкод введен верно. Заявка аренды #'.$order->getId().' активирована'
            );

        } else {
            $this->addFlash(
                'notice',
                'Пинкод введен неверно! Попробуйте еще раз!'
            );

        }
        return new Response("");
    }

    /**
     * @Route("/user_owner_finish", name="user_owner_finish")
     */
    public function ajaxOwnerFinishAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $id = $request->request->get('id');
        $order = $this->getDoctrine()
            ->getRepository(FormOrder::class)
            ->find($id);
        $order->setOwnerStatus('order_finished');
        $order->setRenterRating($request->request->get('rating'));
        $order->setRenterResultDetails(implode(",",$request->request->get('details')));
        $order->setDateOwnerButtonFinish(new \DateTime('now'));
        $em->persist($order);
        $em->flush();
        $this->addFlash(
            'notice',
            'Великолепно! Заявка аренды #'.$order->getId().' завершена. Ваши средства будут переведены вам в соответствии с договором.<br>Спасибо что выбрали Multiprokat.com для решения ваших задач!'
        );
        return $this->redirectToRoute('user_transport_orders');
    }

    /**
     * @Route("/user_renter_finish", name="user_renter_finish")
     */
    public function ajaxRenterFinishAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $id = $request->request->get('id');
        $order = $this->getDoctrine()
            ->getRepository(FormOrder::class)
            ->find($id);
        $order->setRenterStatus('order_finished');
        $order->setOwnerRating($request->request->get('rating'));
        $order->setOwnerResultDetails(implode(",",$request->request->get('details')));
        $order->setDateOwnerButtonFinish(new \DateTime('now'));
        $em->persist($order);
        $em->flush();

        if($order->getDeposit()>0){
            $message = 'Великолепно! Заявка аренды #'.$order->getId().' завершена.<br>Ваш залог будет возвращен вам в соответствии с договором.<br>Спасибо что выбрали Multiprokat.com для решения ваших задач!';
        } else {
            $message = 'Великолепно! Заявка аренды #'.$order->getId().' завершена.<br>Спасибо что выбрали Multiprokat.com для решения ваших задач!';
        }

        $this->addFlash(
            'notice',
            $message
        );
        return $this->redirectToRoute('user_transport_orders');
    }

    /**
     * @Route("/user_go_new", name="user_go_new")
     */
    public function userGoNewAction(EntityManagerInterface $em, Request $request, ServiceStat $stat, \Swift_Mailer $mailer)
    {
        $id = $request->request->get('id');
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        $user->setAccountTypeId(1);
        $user->setIsNew(true);
        $em->persist($user);
        $em->flush();

        $stat->setStat([
            'url' => $request->getPathInfo(),
            'event_type' => 'go_new',
            'page_type' => 'profile',
            'card_id' => 0,
            'user_id' => $user->getId(),
        ]);

        $msg = (new \Swift_Message('Пользователь перешел на новую систему'))
                ->setFrom('mail@multiprokat.com')
                ->setTo('mail@multiprokat.com')
                ->setBody('Только что перешел <a href="https://multiprokat.com/user/'.$user->getId().'">пользователь</a>','text/html');
        $mailer->send($msg);

        $this->addFlash(
            'notice',
            'Поздравляем! Вы успешно переведены на новую систему обработки заказов и получили статус PRO!'
        );

        return $this->redirectToRoute('user_profile');
    }

    /**
     * @Route("/user/{id}", name="user_page")
     */
    public function userPageAction($id, MenuMarkModel $mm, EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find((int)$id);

        if(!$user->getIsBanned()) {
            $user_foto = false;
            foreach ($user->getInformation() as $info) {
                if ($info->getUiKey() == 'foto' and $info->getUiValue() != '') $user_foto = '/assets/images/users/t/' . $info->getUiValue() . '.jpg';
            }

            $city = $this->get('session')->get('city');
            $in_city = $city->getUrl();

            $mark_arr = $mm->getExistMarks('',1);
            $mark_arr_sorted = $mark_arr['sorted_marks'];
            $models_in_mark = $mark_arr['models_in_mark'];

            $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.total DESC');
            $generalTypes = $query->getResult();

            $is_admin_card = false;

            foreach ($user->getInformation() as $ui){
                if($ui->getUiKey() == 'phone'){
                    $ph = substr(preg_replace('/[^0-9]/', '', $ui->getUiValue()),1);
                    $emz = explode("@",$user->getEmail());

                    if ($ph == $emz[0]) $is_admin_card = true;

                    if (preg_match('/^\d+$/', $emz[0])) $is_admin_card = true;
                }
            }

            $stat_arr = [
                'url' => $request->getPathInfo(),
                'event_type' => 'visit',
                'page_type' => 'profile',
                'user_id' => $user->getId(),
                'is_admin_card' => $is_admin_card,
            ];
            $stat->setStat($stat_arr);

            return $this->render('user/user_page.html.twig', [
                'user' => $user,
                'user_foto' => $user_foto,
                'city' => $city,
                'is_admin_card' => $is_admin_card,
                'mark_arr_sorted' => $mark_arr_sorted,
                'models_in_mark' => $models_in_mark,
                'in_city' => $in_city,
                'cityId' => $city->getId(),
                'mark' => $mark_arr_sorted[1][0]['mark'],
                'model' => $mark_arr_sorted[1][0]['models'][0],
                'generalTypes' => $generalTypes,
                'lang' => $_SERVER['LANG']
            ]);
        } else return new Response("",404);
    }

    private function captchaverify($recaptcha){
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "secret"=>"6LcGCzUUAAAAAH0yAEPu8N5h9b5BB8THZtFDx3r2","response"=>$recaptcha));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        return $data->success;
    }


    private function countSum($user_id)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find((int)$user_id);

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT o FROM UserBundle:FormOrder o WHERE o.userId = ?1 AND o.ownerStatus = ?2');
        $query->setParameter(1, $user_id);
        $query->setParameter(2, 'wait_for_rent');
        $orders = $query->getResult();

        $total = 0;
        foreach ($orders as $o){
            $total = $total + $o->getPrice();
        }
        return $total;
    }

    /**
     * @Route("/test_test", name="test_test")
     */
    public function ttAction(EntityManagerInterface $em, Request $request, ServiceStat $stat, \Swift_Mailer $mailer)
    {

        $s = 'мой номер: 89174100960 телефона +7(917)4100960 а так же +7(917)41-00-960 и еще 8(917)4100960';
        echo $s.'<br>';
        echo preg_replace('/\+?\([0-9]+\)[0-9]+/', '*', $s);
        echo '<br>';
        echo preg_replace('/[0-9]{7}/', '*', $s);
        echo '<br>';
        echo preg_replace('/[0-9]{2}\-[0-9]{2}\-[0-9]{3}/', '*', $s);
        echo preg_replace('/[0-9]{3}\-[0-9]{2}\-[0-9]{2}/', '*', $s);



        return new Response();
    }

    /**
     * @Route("/edit_message", name="edit_message")
     */
    public function editMessageAction(EntityManagerInterface $em, Request $request)
    {

        $i = $request->request->get('i');
        $m = $request->request->get('message');
        $id = $request->request->get('id');

        $order = $this->getDoctrine()
            ->getRepository(FormOrder::class)
            ->find($id);

        $messages = json_decode($order->getMessages(),true);
        foreach ($messages as $k=>$ms){
            if($k == $i){
                $messages[$k]['message'] = $m;
            }
        }
        $order->setMessages(json_encode($messages));

        $em->persist($order);
        $em->flush();

        $this->get('session')->set('active_message',$id);


        return $this->redirectToRoute('adminOrders');
    }

    /**
     * @Route("/new_system_message", name="new_system_message")
     */
    public function newSystemMessageAction(EntityManagerInterface $em, Request $request)
    {

        $m = $request->request->get('message');
        $id = $request->request->get('id');

        $order = $this->getDoctrine()
            ->getRepository(FormOrder::class)
            ->find($id);

        $messages = json_decode($order->getMessages(),true);
        $messages[] = [
                    'date' => date('d-m-Y'),
                    'time' => date('H:i'),
                    'from' => 'system',
                    'message' => '@multiprokat_bot: '.$m,
                    'status' => 'send'
                ];
        $order->setMessages(json_encode($messages));

        $em->persist($order);
        $em->flush();

        $this->get('session')->set('active_message',$id);

        return $this->redirectToRoute('adminOrders');
    }

    /**
     * @Route("/ajax_admin_message_select", name="ajax_admin_message_select")
     */
    public function ajaxAdminMessageSelectAction(Request $request)
    {
        $id = $request->request->get('id');
        $this->get('session')->set('active_message',$id);
        return new Response();
    }

}