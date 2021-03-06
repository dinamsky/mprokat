<?php

namespace UserBundle\Controller;

use AppBundle\Menu\ServiceStat;
use MarkBundle\Entity\CarModel;
use MarkBundle\Entity\CarMark;
use UserBundle\Entity\Message;
use UserBundle\Entity\UserInfo;
use UserBundle\Entity\UserOrder;
use AppBundle\Entity\CardFeature;
use AppBundle\Entity\CardPrice;
use AppBundle\Entity\Feature;
use AppBundle\Entity\Foto;
use AppBundle\Entity\Price;
use AppBundle\Entity\Tariff;
use AppBundle\Foto\FotoUtils;
use UserBundle\Entity\User;
use UserBundle\Entity\TempActivated;
use AppBundle\Entity\Card;
use AppBundle\Entity\City;
use AppBundle\Entity\Color;
use AppBundle\Entity\FieldInteger;
use AppBundle\Entity\FieldType;
use AppBundle\Entity\GeneralType;
use AppBundle\Entity\Mark;
use AppBundle\Entity\State;
use AppBundle\Entity\CardField;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuMarkModel;
use AppBundle\Menu\MenuSubFieldAjax;
use AppBundle\Menu\ServiceVerification;
use AppBundle\SubFields\SubFieldUtils;
use UserBundle\Security\CookieMaster;
use UserBundle\Security\Password;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use UserBundle\UserBundle;

class UserController extends Controller
{

    protected $mailer;
    protected $cookieMaster;

    public function __construct(\Swift_Mailer $mailer, CookieMaster $cookieMaster)
    {
        $this->mailer = $mailer;
        $this->cookieMaster = $cookieMaster;
    }

    /**
     * @Route("/ajax/getAllSubFields")
     */
    public function getAllSubField(Request $request, SubFieldUtils $sf)
    {
        $generalTypeId = $request->request->get('generalTypeId');

        $result = $sf->getSubFields($generalTypeId);

        return $this->render('all_subfields.html.twig', [
            'result' => $result,
            'lang' => $_SERVER['LANG']
        ]);
    }


    /**
     * @Route("/ajax/getSubField")
     */
    public function getSubLevel(Request $request, MenuSubFieldAjax $menu)
    {
        $subId = $request->request->get('subId');
        $fieldId = $request->request->get('fieldId');


        $result = $menu->getSubField($fieldId, $subId);

        if (!empty($result)) {
            return $this->render('common/ajax_select.html.twig', [
                'options' => $result,
                'lang' => $_SERVER['LANG']
            ]);
        } else {
            $response = new Response();
            $response->setStatusCode(204);
            return $response;
        }
    }

    private function setAuthCookie(User $user)
    {
        $response = new Response();
        $hash = $this->cookieMaster->setHash($user->getId());
        $cookie = new Cookie('the_mhash', $hash . base64_encode($user->getId()), strtotime('now +1 year'));
        $response->headers->setCookie($cookie);
        $response->sendHeaders();
    }

    /**
     * @Route("/userSignIn")
     */
    public function signInAction(Request $request, Password $password, ServiceStat $stat, ServiceVerification $serVerif)
    {
        $_t = $this->get('translator');

        $em = $this->getDoctrine()->getManager();
        $query = null;
        $isMail = $serVerif->isEmail($request->request->get('email'));
        if ($isMail) {
            $dql = 'SELECT u FROM UserBundle:User u WHERE u.isBanned = 0 AND u.email = ?1';
            $query = $em->createQuery($dql);
            $query->setParameter(1, $request->request->get('email'));
        } else {
            $dql = 'SELECT u FROM UserBundle:User u WHERE u.isBanned = 0 AND u.login = ?1';
            $query = $em->createQuery($dql);
            $query->setParameter(1, $serVerif->getFormatPhone($request->request->get('email')));
        }
        $users = $query->getResult();



        foreach ($users as $user) {

            if ($password->CheckPassword($request->request->get('password'), $user->getPassword())) {

                $this->get('session')->set('logged_user', $user);

                $this->setAuthCookie($user);

                //                $this->addFlash(
                //                    'notice',
                //                    $_t->trans('Вы успешно вошли в аккаунт!')
                //                );

                $this->get('session')->set('user_pic', false);
                foreach ($user->getInformation() as $info) {
                    if ($info->getUiKey() == 'foto') $this->get('session')->set('user_pic', $info->getUiValue());
                }

                $stat->setStat([
                    'url' => $request->getPathInfo(),
                    'event_type' => 'login',
                    'page_type' => 'profile',
                    'card_id' => 0,
                    'user_id' => $user->getId(),
                ]);

                return $this->redirect($request->request->get('return'));
                break;
            }
        }

        $this->addFlash(
            'notice',
            $_t->trans('Неправильная пара логин/пароль!')
        );

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/fromMail")
     */
    public function signInFromMailAction(Request $request, Password $password, ServiceVerification $serVerif)
    {
        if (!$this->get('session')->has('logged_user')) {

            $_t = $this->get('translator');

            $em = $this->getDoctrine()->getManager();
            $dql = 'SELECT u FROM UserBundle:User u WHERE u.isBanned = 0 AND (u.email = ?1 OR u.login = ?1)';
            $query = $em->createQuery($dql);


            if ($serVerif->isEmail($request->request->get('email'))) {
                $query->setParameter(1, urldecode($request->request->get('email')));
            } else {
                $query->setParameter(1, $serVerif->getFormatPhone($request->request->get('email')));
            }

            $users = $query->getResult();

            foreach ($users as $user) {

                if ($password->CheckPassword(urldecode($request->query->get('password')), $user->getPassword())) {

                    $this->get('session')->set('logged_user', $user);

                    $this->setAuthCookie($user);

                    $this->addFlash(
                        'notice',
                        $_t->trans('Вы успешно вошли в аккаунт!')
                    );

                    $this->get('session')->set('user_pic', false);
                    foreach ($user->getInformation() as $info) {
                        if ($info->getUiKey() == 'foto') $this->get('session')->set('user_pic', $info->getUiValue());
                    }

                    $this->get('session')->set('first_jump', true);

                    $route = 'user_cards';
                    if ($request->query->has('rentout')) $route = 'card_new';

                    return $this->redirectToRoute($route);
                    break;
                }
            }

            $this->addFlash(
                'notice',
                $_t->trans('Неправильная пара логин/пароль!')
            );
        }
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/userLogout")
     */
    public function logoutAction(Request $request)
    {
        $_t = $this->get('translator');

        $response = new Response();
        $response->headers->clearCookie('the_mhash');
        $response->sendHeaders();
        $this->get('session')->remove('logged_user');
        $this->addFlash(
            'notice',
            $_t->trans('Вы успешно вышли из аккаунта')
        );
        return $this->redirectToRoute('homepage');
    }

    // private function getFormatPhone(string $phone_in, string $country = 'RU', string $format = ''){
    //     $phone = $phone_in;
    //     $phone = preg_replace('/^(00)/','',$phone);
    //     // $phone = preg_replace('/^(8)/','7',$phone);
    //     $phone = preg_replace('~[^0-9]+~','',$phone);
    //     switch ($country) {
    //         case 'RU':
    //             if ( substr($phone,0,4) == '8800') {
    //                 break;
    //             }
    //             if ( substr($phone,0,1) == '8') {
    //                 $phone = preg_replace('/^(8)/','7',$phone);
    //             }
    //             if ( substr($phone,0,1) !== '7') {
    //                 $phone = '7'.$phone;
    //             }
    //             break;
    //         default:
    //             break;
    //     }
    //     return $phone;
    // }

    /**
     * @Route("/test_phone")
     */
    public function testPhone(ServiceVerification $serVerif)
    {
        $html = '<pre><br>';
        $phone = '+ 5 80 95 662 (46 ) - * 569';
        $phone_in = $phone;
        $html .= "{$phone}<br>";
        $phone = preg_replace('/^(00)/', '', $phone);
        $html .= "{$phone}<br>";
        $phone = preg_replace('/^(8)/', '7', $phone);
        $html .= "{$phone}<br>";
        $phone = preg_replace('~[^0-9]+~', '', $phone);
        $html .= "{$phone}<br>";
        // return $phone;
        var_dump($phone);

        $phone = $serVerif->getFormatPhone($phone_in, 'RU');
        $html .= "{$phone}<br>";
        $html .= '</pre>';
        echo $html;

        return new Response('');
    }

    /**
     * @Route("/userSignUpN")
     */
    public function signUpNewAction(Request $request, Password $password, \Swift_Mailer $mailer, ServiceVerification $serVerif)
    {
        $res = array(
            'error' => 0,
            'message' => []
        );

        $ok = true;

        $r = '';

        $user_l = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array(
                'login' => $serVerif->getFormatPhone($request->request->get('phone')),
                'isActivated' => true
            ));

        if ($user_l) {
            // $this->addFlash(
            //     'notice',
            //     'Пользователь уже зарегистрирован! Выполните вход'
            // );
            $r = 'user';
            $ok = false;
        }

        //$xn = explode("@",$request->request->get('email'));

        if ($ok) {

            $bu = $request->request->get('back_url');

            $code = rand(111111, 999999);
            $user = new User();
            $user->setEmail($request->request->get('email'));
            $user->setLogin($serVerif->getFormatPhone($request->request->get('phone')));
            $user->setPassword($password->HashPassword($code));
            //$user->setHeader($request->request->get('phone'));
            $user->setHeader(!empty($request->request->get('name')) ? $request->request->get('name') : 'Без имени');
            //$user->setHeader('');
            $user->setActivateString($code);
            $user->setTempPassword($bu);
            $user->setIsSubscriber(true);
            $user->setIsNew(true);
            $user->setWhois('new_renter');
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $number = $serVerif->getFormatPhone($request->request->get('phone'));
            //if(strlen($number)==11) $number = substr($number, 1);
            $message = urlencode('Ваш логин: ' . $user->getLogin() . '. Ваш пароль регистрации: ' . $code);
            $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=' . $number . '&message=' . $message;
            $sms_result = file_get_contents($url);
            $r = 'ok';
        }

        return new Response($r);
    }

    /**
     * @Route("/userSignUp")
     */
    public function signUpAction(Request $request, Password $password, \Swift_Mailer $mailer, ServiceVerification $serVerif)
    {
        $_t = $this->get('translator');

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(array(
                'email' => $request->request->get('email')
            ));

        $user_p = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(array(
                'login' => $serVerif->getFormatPhone($request->request->get('phone'))
            ));

        if ($user || $user_p) {
            $this->addFlash(
                'notice',
                $_t->trans('Пользователь уже зарегистрирован!')
            );
            return $this->redirectToRoute('homepage');
        }


        $code = md5(rand(0, 99999999));
        $user = new User();
        $user->setEmail($request->request->get('email'));
        $user->setLogin($serVerif->getFormatPhone($request->request->get('phone')));
        $user->setPassword($password->HashPassword($request->request->get('password')));
        $user->setHeader($request->request->get('header'));
        $user->setActivateString($code);
        $user->setTempPassword('');
        $user->setIsSubscriber(true);
        $user->setIsNew(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $user_info = new UserInfo();
        $user_info->setUser($user);
        $user_info->setUiKey('phone');
        $user_info->setUiValue($user->getLogin());
        $em->persist($user_info);
        $em->flush();

        $message = (new \Swift_Message($_t->trans('Регистрация на сайте multiprokat.com')))
            ->setFrom('mail@multiprokat.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    $_SERVER['LANG'] == 'ru' ? 'email/registration.html.twig' : 'email/registration_' . $_SERVER['LANG'] . '.html.twig',
                    array(
                        'header' => $user->getHeader(),
                        'code' => $code
                    )
                ),
                'text/html'
            );
        $mailer->send($message);

        $this->addFlash(
            'notice',
            $_t->trans('На вашу почту было отправлено письмо для активации аккаунта!<br><br>Не забудьте проверить папку спам!!!')
        );
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/qreg_ajax_1")
     */
    public function qreg_ajax_1_Action(Request $request, Password $password, \Swift_Mailer $mailer, ServiceVerification $serVerif)
    {
        $ok = true;

        $r = '';

        $user_l = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array(
                'login' => $serVerif->getFormatPhone($request->request->get('phone')),
                'isActivated' => true
            ));

        $em = $this->getDoctrine()->getManager();

        if (!$user_l) {
            $user_info = $this->getDoctrine()
            ->getRepository(UserInfo::class)
            ->findOneBy(array(
                'uiKey' => 'phone',
                'uiValue' => $serVerif->getFormatPhone($request->request->get('phone')),
            ));
            if ($user_info){
                $user_l = $user_info->getUser();
                $user_l->setLogin($serVerif->getFormatPhone($request->request->get('phone')));
                
                $em->persist($user_l);
                $em->flush();
            }
        }

        if ($user_l) {
            $r = 'user';

            $code = rand(111111, 999999);
            $activated = new TempActivated();
            $activated->setUserId($user_l->getId());
            $activated->setCode($code);
            $activated->setName('in_user_phone');
            $activated->setValue($serVerif->getFormatPhone($request->request->get('phone')));

            $em->persist($activated);
            $em->flush();

            $number = $serVerif->getFormatPhone($request->request->get('phone'));
            //if(strlen($number)==11) $number = substr($number, 1);
            $message = urlencode('Ваш пароль авторизации: ' . $code);
            $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=' . $number . '&message=' . $message;
            $sms_result = file_get_contents($url);

            return new Response($r);
        }

        //$xn = explode("@",$request->request->get('email'));

        if ($ok) {

            $bu = $request->request->get('back_url');

            $code = rand(111111, 999999);
            $user = new User();
            $user->setEmail($request->request->get('email'));
            $user->setLogin($serVerif->getFormatPhone($request->request->get('phone')));
            $user->setPassword($password->HashPassword($code));
            //$user->setHeader($request->request->get('phone'));
            $user->setHeader(!empty($request->request->get('name')) ? $request->request->get('name') : 'Без имени');
            //$user->setHeader('');
            $user->setActivateString($code);
            $user->setTempPassword($bu);
            $user->setIsSubscriber(true);
            $user->setIsNew(true);
            $user->setWhois('new_renter');

            $em->persist($user);
            $em->flush();

            $number = $serVerif->getFormatPhone($request->request->get('phone'));
            //if(strlen($number)==11) $number = substr($number, 1);
            $message = urlencode('Ваш логин: ' . $number . '. Ваш пароль регистрации: ' . $code);
            $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=' . $number . '&message=' . $message;
            $sms_result = file_get_contents($url);
            $r = 'ok';
        }

        return new Response($r);
    }

    /**
     * @Route("/qreg_ajax_2")
     */
    public function qreg_ajax_2_Action(Request $request, Password $password, \Swift_Mailer $mailer, ServiceVerification $serVerif)
    {
        $code = $request->request->get('regcode');
        $phone_req = $serVerif->getFormatPhone($request->request->get('phone_req'));
       
        //регистрация уже активированных ранее пользователей
        if (!empty($phone_req)){
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(array(
                    'login' => $phone_req,
                    'isActivated' => true
                ));

            if ($user) {
                $activated = $this->getDoctrine()
                    ->getRepository(TempActivated::class)
                    ->findOneBy(array(
                            'code' => $code,
                            'name' => 'in_user_phone',
                            'userId' => $user->getId(),
                        ));
                if ($activated){
                    $activated->setIsUse(true);

                    $user->setIsPhoneCorrect(true);
                    $user->setActivateString('');
                    $user->setIsActivated(true);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($activated);
                    $em->persist($user);
                    $em->flush();
                    
                    $this->get('session')->set('logged_user', $user);
                    $this->setAuthCookie($user);

                    $r = 'ok';
                    return new Response($r);
                }
            }
        }

        //регистрация новых пользователей
        //TODO: необходимо переделать на новый механизм активирвоания кода
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array(
                'activateString' => $code
            ));

        if ($user) {

            $phone = $user->getLogin();

            //$user->setTempPassword('');
            //$user->setHeader('');
            $user->setIsActivated(true);
            $user->setIsPhoneCorrect(true);
            $user->setActivateString('');
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $user_info = new UserInfo();
            $user_info->setUser($user);
            $user_info->setUiKey('phone');
            $user_info->setUiValue($phone);
            $em->persist($user_info);
            $em->flush();

            $this->get('session')->set('logged_user', $user);
            $this->setAuthCookie($user);

            $r = 'ok';
        } else {
            $r = 'bad';
        }

        return new Response($r);
    }

    /**
     * @Route("/activate_account/{code}", name="activate_account")
     */
    public function activateAccountAction($code)
    {
        $_t = $this->get('translator');

        $return_url = 'homepage';

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array(
                'activateString' => $code
            ));

        if ($user) {
            $message = 'Ваш аккаунт успешно активирован!';
            if ($user->getTempPassword() != '') {
                $user->setPassword($user->getTempPassword());
                $message = $_t->trans('Ваш новый пароль успешно активирован!');
            } else if (!$user->getIsEmailCorrect() && $user->getIsActivated()) {
                $msg = (new \Swift_Message('Подтверждение email на сайте multiprokat.com'))
                    ->setFrom('mail@multiprokat.com')
                    ->setTo('mail@multiprokat.com')
                    ->setBody('Только что был успешно подтвержден email <a href="https://multiprokat.com/user/' . $user->getId() . '">пользователя</a>', 'text/html');
                $this->mailer->send($msg);;
            } else {
                $msg = (new \Swift_Message('Регистрация на сайте multiprokat.com'))
                    ->setFrom('mail@multiprokat.com')
                    ->setTo('mail@multiprokat.com')
                    ->setBody('Только что был успешно зарегистрирован <a href="https://multiprokat.com/user/' . $user->getId() . '">пользователь</a>', 'text/html');

                $this->mailer->send($msg);
            }
            $user->setTempPassword('');
            $user->setIsActivated(true);
            $user->setIsEmailCorrect(true);
            $user->setActivateString('');
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->get('session')->set('logged_user', $user);

            $this->setAuthCookie($user);

            $this->addFlash(
                'notice',
                $message
            );

            foreach ($user->getCards() as $card) {
                $card->setIsActive(true);
                $em->persist($card);
                $em->flush();
            }

            $return_url = 'user_cards';
        } else {
            $this->addFlash(
                'notice',
                $_t->trans('Произошла ошибка, попробуйте еще раз.')
            );
        }

        return $this->redirectToRoute($return_url);
        //
    }


    /**
     * @Route("/userRecoverTel")
     */
    public function recoverTelAction(Request $request, Password $password, ServiceVerification $serVerif)
    {
        $_t = $this->get('translator');
        $r = 'false';

        if ($request->request->get('password1') == $request->request->get('password2')) {
            $user_l = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(array(
                    'login' => $serVerif->getFormatPhone($request->request->get('phone')),
                ));

            if ($user_l) {
                $code = rand(100000, 999999);
                $user_l->setActivateString($code);
                $user_l->setTempPassword($password->HashPassword($request->request->get('password1')));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user_l);
                $em->flush();

                $number = $serVerif->getFormatPhone($request->request->get('phone'));
                //if(strlen($number)==11) $number = substr($number, 1);
                $message = urlencode('Ваш пароль восстановления: ' . $code);
                $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=' . $number . '&message=' . $message;
                $sms_result = file_get_contents($url);
                $r = 'ok';
            } else {
                $this->addFlash(
                    'notice',
                    'Данного логина не существует!'
                );
            }
        } else {
            $this->addFlash(
                'notice',
                $_t->trans('Пароли не совпадают!')
            );
        }
        return new Response($r);
    }

    /**
     * @Route("/userRecoverTelCode", name="user_recover_tel_code")
     */
    public function userRecoverTelCodeAction(Request $request)
    {
        $_t = $this->get('translator');
        $r = 'false';

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array(
                'activateString' => $request->request->get('code')
            ));

        if ($user) {
            if ($user->getTempPassword() != '') {
                $user->setPassword($user->getTempPassword());
                $message = $_t->trans('Ваш новый пароль успешно активирован!');
            }
            $user->setTempPassword('');
            $user->setIsActivated(true);
            $user->setIsPhoneCorrect(true);
            $user->setActivateString('');
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->get('session')->set('logged_user', $user);

            $this->setAuthCookie($user);

            $this->addFlash(
                'notice',
                $message
            );

            foreach ($user->getCards() as $card) {
                $card->setIsActive(true);
                $em->persist($card);
                $em->flush();
            }
            $r = 'ok';
        } else {
            $this->addFlash(
                'notice',
                $_t->trans('Произошла ошибка, попробуйте еще раз.')
            );
        }

        return new Response($r);
    }

    /**
     * @Route("/userRecover")
     */
    public function recoverAction(Request $request, \Swift_Mailer $mailer, Password $password)
    {
        $_t = $this->get('translator');

        if ($request->request->get('password1') == $request->request->get('password2')) {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(array(
                    'email' => $request->request->get('email'),
                ));

            if ($user) {
                $code = md5(rand(0, 99999999));
                $user->setActivateString($code);
                $user->setTempPassword($password->HashPassword($request->request->get('password1')));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $message = (new \Swift_Message($_t->trans('Восстановление пароля на сайте multiprokat.com')))
                    ->setFrom('mail@multiprokat.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            $_SERVER['LANG'] == 'ru' ? 'email/recover.html.twig' : 'email/recover_' . $_SERVER['LANG'] . '.html.twig',
                            array(
                                'header' => $user->getHeader(),
                                'code' => $code
                            )
                        ),
                        'text/html'
                    );
                $errorsRecip = [];
                $mailer->send($message, $errorsRecip);

                if (empty($errorsRecip)) {
                    $this->addFlash(
                        'notice',
                        $_t->trans('Вам отправлено письмо с активацией нового пароля')
                    );
                } else {
                    $this->addFlash(
                        'notice',
                        $_t->trans('Сбой при отправке письма на указанный email: ' . implode(';', $errorsRecip))
                    );
                }
            } else {
                $this->addFlash(
                    'notice',
                    'Данного логина не существует!'
                );
            }
            return $this->redirect($request->request->get('return'));
        } else {
            $this->addFlash(
                'notice',
                $_t->trans('Пароли не совпадают!')
            );
            return $this->redirect($request->request->get('return'));
        }
    }

    /**
     * @Route("/robokassa/resultUrl")
     */
    public function robokassaResultUrlAction(Request $request)
    {
        $mrh_pass2 = "sMTO0qtfg6fhxV009rZT"; //pass2

        $out_summ = $_REQUEST["OutSum"];
        $inv_id = $_REQUEST["InvId"];
        $crc = strtoupper($_REQUEST["SignatureValue"]);

        $my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass2"));
        $text = "OK$inv_id\n";
        if ($my_crc != $crc) $text = "bad sign\n";
        else {
            $em = $this->getDoctrine()->getManager();

            $order = $this->getDoctrine()
                ->getRepository(UserOrder::class)
                ->find($inv_id);

            $order->setStatus('paid');

            if ($order->getOrderType() == 'accountPRO') {
                $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->find($order->getUserId());
                $user->setAccountTypeId(1);
                $em->persist($user);
                $em->flush();
                $this->get('session')->set('logged_user', $user);
            } else {
                $tariff = $this->getDoctrine()
                    ->getRepository(Tariff::class)
                    ->find($order->getTariffId());
                $card = $this->getDoctrine()
                    ->getRepository(Card::class)
                    ->find($order->getCardId());
                $card->setDateTariffStart(new \DateTime());
                $card->setTariff($tariff);
                $em->persist($order);
                $em->persist($card);
                $em->flush();
            }
        }
        return new Response($text, 200);
    }

    /**
     * @Route("/robokassa/successUrl")
     */
    public function robokassaSuccessUrlAction(Request $request)
    {
        $_t = $this->get('translator');

        $mrh_pass1 = "Wf1bYXSd5V8pKS3ULwb3";  // merchant pass1 here
        $out_summ = $_REQUEST["OutSum"];
        $inv_id = $_REQUEST["InvId"];
        $crc = $_REQUEST["SignatureValue"];
        $crc = strtoupper($crc);  // force uppercase
        $my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass1"));

        if ($my_crc != $crc) {
                $text = "bad sign\n";
                return new Response($text, 200);
            }

        $message = $_t->trans('Ваш новый тариф успешно оплачен!');
        $url = '/user/cards';

        $order = $this->getDoctrine()
            ->getRepository(UserOrder::class)
            ->find($inv_id);

        if ($order->getOrderType() == 'accountPRO') {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($order->getUserId());
            $this->get('session')->set('logged_user', $user);

            $message = $_t->trans('Ваш PRO аккаунт успешно оплачен!');
            $url = '/user/cards';
        }

        $this->addFlash(
            'notice',
            $message
        );

        //return $this->redirectToRoute('homepage');
        return new RedirectResponse($url);
    }

    /**
     * @Route("/robokassa/failUrl")
     */
    public function robokassaFailUrlAction(Request $request)
    {
        $_t = $this->get('translator');

        $this->addFlash(
            'notice',
            $_t->trans('К сожалению оплата не прошла!')
        );

        return $this->redirectToRoute('user_cards');
    }

    /**
     * @Route("/ajax/getUser")
     */
    public function getUserAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $emails = array();

        $users = $em->getRepository("UserBundle:User")->createQueryBuilder('u')
            ->where('u.email LIKE :eml')
            ->setParameter('eml', '%' . $request->request->get('q') . '%')
            ->getQuery()
            ->getResult();

        foreach ($users as $user) {
            $emails[] = $user->getEmail();
        }

        return new Response(json_encode($emails));
    }

    /**
     * @Route("/userPayPro/{user_id}")
     */
    public function payProAction($user_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($user_id);

        if ($user) {

            $tariff = $this->getDoctrine()
                ->getRepository(Tariff::class)
                ->find(1);
            $card = $this->getDoctrine()
                ->getRepository(Card::class)
                ->findOneBy(['isActive' => true]);

            $order = new UserOrder();
            $order->setUser($user);
            $order->setCard($card);
            $order->setTariff($tariff);
            $order->setPrice(ceil(450 * 100 / 110));
            $order->setOrderType('accountPRO');
            $order->setStatus('new');
            $em->persist($order);
            $em->flush();

            $mrh_login = "multiprokat";
            $mrh_pass1 = "Wf1bYXSd5V8pKS3ULwb3";
            $inv_id = $order->getId();
            $inv_desc = "set_account_PRO";
            $out_summ = ceil(450 * 100 / 110);

            $crc = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");

            $url = "https://auth.robokassa.ru/Merchant/Index.aspx?MrchLogin=$mrh_login&" .
                "OutSum=$out_summ&InvId=$inv_id&Desc=$inv_desc&SignatureValue=$crc";

            return new RedirectResponse($url);
        } else throw $this->createNotFoundException(); //404
    }

    /**
     * @Route("/user_checkmail")
     */
    public function checkMailAction(Request $request)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['email' => $request->request->get('email')]);
        if ($user) return new Response('ok');
        else return new Response('new');
    }

    /**
     * @Route("/promote_card/{id}")
     */
    public function promoteCardAction($id, ServiceStat $stat)
    {
        $this->get('session')->set('promote', true);

        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($id);

        $stat_arr = [
            'url' => '/user/edit/card/' . $id,
            'event_type' => 'promote_card',
            'page_type' => 'form',
            'user_id' => $card->getUserId(),
            'card_id' => $id,
        ];

        $stat->setStat($stat_arr);

        return new RedirectResponse('/user/edit/card/' . $id);
    }



    /**
     * @Route("/user_controller_ajax_tariff_cancel/")
     */
    public function cancelPromoteCardAction(ServiceStat $stat)
    {
        $user = $this->get('session')->get('logged_user');

        $stat_arr = [
            'url' => '',
            'event_type' => 'cancel_promote_card',
            'page_type' => 'form',
        ];

        if ($user) $stat_arr['user_id'] = $user->getId();

        $stat->setStat($stat_arr);

        return new Response();
    }

    /**
     * @Route("/ajax/get_chat_messages")
     */
    public function getChatMsg(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $res = array();

        $query = $em->createQuery('SELECT m FROM UserBundle:Message m WHERE m.cardId=?3 AND ((m.fromUserId = ?1 AND m.toUserId = ?2) OR (m.fromUserId = ?2 AND m.toUserId = ?1)) ORDER BY m.dateCreate ASC');
        if ($request->request->has('last_id')) {
            $query = $em->createQuery('SELECT m FROM UserBundle:Message m WHERE m.id>' . $request->request->get('last_id') . ' AND m.cardId=?3 AND ((m.fromUserId = ?1 AND m.toUserId = ?2) OR (m.fromUserId = ?2 AND m.toUserId = ?1)) ORDER BY m.dateCreate ASC');
        }

        $query->setParameter(1, $request->request->get('user_id'));
        $query->setParameter(2, $request->request->get('visitor_id'));
        $query->setParameter(3, $request->request->get('card_id'));
        $msgs = $query->getResult();




        $query = $em->createQuery('UPDATE UserBundle:Message m SET m.isReadVisitor = 1 WHERE m.cardId=?3 AND m.fromUserId = ?2 AND m.toUserId = ?1');
        if ($request->request->has('last_id')) {
            $query = $em->createQuery('UPDATE UserBundle:Message m SET m.isReadVisitor = 1 WHERE m.id>' . $request->request->get('last_id') . ' AND m.cardId=?3 AND m.fromUserId = ?2 AND m.toUserId = ?1');
        }
        $query->setParameter(1, $request->request->get('user_id'));
        $query->setParameter(2, $request->request->get('visitor_id'));
        $query->setParameter(3, $request->request->get('card_id'));
        $query->execute();


        $query = $em->createQuery('UPDATE UserBundle:Message m SET m.isRead = 1 WHERE m.cardId=?3 AND m.fromUserId = ?1 AND m.toUserId = ?2');
        if ($request->request->has('last_id')) {
            $query = $em->createQuery('UPDATE UserBundle:Message m SET m.isRead = 1 WHERE m.id>' . $request->request->get('last_id') . ' AND m.cardId=?3 AND m.fromUserId = ?1 AND m.toUserId = ?2');
        }
        $query->setParameter(1, $request->request->get('user_id'));
        $query->setParameter(2, $request->request->get('visitor_id'));
        $query->setParameter(3, $request->request->get('card_id'));
        $query->execute();



        $last_id = $request->request->get('last_id');
        foreach ($msgs as $m) {
            $msg[$m->getDateCreate()->format('d-m-Y')][] = $m;
            $last_id = $m->getId();
        }


        if (isset($msg)) foreach ($msg as $date => $msgs) {
            if (!$request->request->has('last_id')) $message[] = '<div class="messages_date_delimiter"><span>' . $date . '</span></div>';
            foreach ($msgs as $m) {
                $css_class = 'user_message';
                if ($m->getFromUserId() == $request->request->get('visitor_id')) $css_class = 'visitor_message';

                $msg = '<div class="uk-clearfix"><div class="' . $css_class . '">';
                if ($m->getIsAttachment()) $msg .= '<div><img src="/assets/images/chat/' . $m->getId() . '.jpg" class="message_image"></div>';
                $msg .= $m->getMessage() . '<div class="message_time">' . $m->getDateCreate()->format('H:i') . '</div></div></div>';
                $message[] = $msg;
            }
        } else $message = [];

        $res['messages'] = implode("", $message);
        $res['last_id'] = $last_id;
        return new Response(json_encode($res));
    }

    /**
     * @Route("/ajax/send_chat_message")
     */
    public function sendChatMsg(Request $request, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();

        $msg = new Message();
        $msg->setFromUserId($request->request->get('visitor_id'));
        $msg->setToUserId($request->request->get('user_id'));
        $msg->setCardId($request->request->get('card_id'));
        $msg->setMessage($request->request->get('message'));
        $msg->setIsAttachment($request->request->get('is_file'));
        $em->persist($msg);
        $em->flush();

        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($request->request->get('card_id'));

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($request->request->get('user_id'));

        $visitor = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($request->request->get('visitor_id'));

        $message = (new \Swift_Message('#' . $msg->getId() . ' Вам пришло новое сообщение'))
            ->setFrom(['mail@multiprokat.com' => 'Робот Мультипрокат'])
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    $_SERVER['LANG'] == 'ru' ? 'email/chat.html.twig' : 'email/chat_' . $_SERVER['LANG'] . '.html.twig',
                    array(
                        'user' => $user,
                        'message' => $request->request->get('message'),
                        'card' => $card,
                        'visitor' => $visitor,
                    )
                ),
                'text/html'
            );
        $mailer->send($message);


        return new Response($msg->getId());
    }

    /**
     * @Route("/ajax_chat_upload")
     */
    public function uploadChatMsg(Request $request, FotoUtils $fu)
    {
        if ($request->request->get('filename') != '') {
            $fu->uploadImage(
                'chat_foto',
                $request->request->get('filename'),
                $_SERVER['DOCUMENT_ROOT'] . '/assets/images/chat',
                ''
            );
        }


        return new Response('<script>parent.refresh_chat();</script>');
    }


    /**
     * @Route("/e_ph_ajax_1")
     */
    public function edit_phone_ajax_1_Action(Request $request, \Swift_Mailer $mailer, ServiceVerification $serVerif)
    {
        // $res = array(
        //     'error' => 0,
        //     'message' => []
        // );
        $r = '';
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());

        if ($user) {
            $code = rand(111111, 999999);
            $activated = new TempActivated();
            $activated->setUserId($user->getId());
            $activated->setCode($code);
            $activated->setName('edit_phone');
            $activated->setValue($serVerif->getFormatPhone($request->request->get('phone')));

            $em = $this->getDoctrine()->getManager();
            $em->persist($activated);
            $em->flush();

            $number = $serVerif->getFormatPhone($request->request->get('phone'));
            //if(strlen($number)==11) $number = substr($number, 1);
            $message = urlencode('Код активации: ' . $code);
            $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=' . $number . '&message=' . $message;
            $sms_result = file_get_contents($url);

            $r = 'ok';
        }


        return new Response($r);
    }

    /**
     * @Route("/e_ph_ajax_2")
     */
    public function edit_phone_2_Action(Request $request, \Swift_Mailer $mailer)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array(
                'id' => $this->get('session')->get('logged_user')->getId()
            ));

        $code = $request->request->get('code');
        $activated = $this->getDoctrine()
            ->getRepository(TempActivated::class)
            ->findOneBy(array(
                'code' => $code,
                'userId' => $user->getId(),
                'name' => 'edit_phone'
            ));
        if ($activated) {
            $activated->setIsUse(true);
            $user->setIsActivated(true);
            $user->setIsPhoneCorrect(true);
            $user->setLogin($activated->getValue());

            $user_info = $this->getDoctrine()
                ->getRepository(UserInfo::class)
                ->findOneBy(array(
                    'userId' => $user->getId(),
                    'uiKey' => 'phone'
                ));
            if (empty($user_info)) {
                $user_info = new UserInfo();
                $user_info->setUser($user);
                $user_info->setUiKey('phone');
            }
            $user_info->setUiValue($activated->getValue());

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->persist($user_info);
            $em->persist($activated);
            $em->flush();

            return new Response('ok');
        }
        return new Response('bad_code');
    }

    /**
     * @Route("/test_sms")
     */
    public function testSMS()
    {


        $message = urlencode('Тестирование имени отправителя');
        $url = 'https://mainsms.ru/api/mainsms/message/send?apikey=72f5f151303b2&project=multiprokat&sender=MULTIPROKAT&recipients=9177313547&message=' . $message;
        $result = file_get_contents($url);
        var_dump($result);

        return new Response('');
    }

     /**
     * @Route("/test_sms_info")
     */
    public function testSMSInfo()
    {


        $message = urlencode('Тестирование имени отправителя');
        $url = 'https://mainsms.ru/api/mainsms/message/info?apikey=72f5f151303b2&project=multiprokat&phones=9177313547';
        $url = 'https://mainsms.ru/api/mainsms/message/info?apikey=72f5f151303b2&project=multiprokat&phones=002072842703';
        $result = file_get_contents($url);
        var_dump($result);
        $html =  json_decode($result);
        print_r($html);
        return new Response('');
    }
}
