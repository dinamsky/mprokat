<?php

namespace UserBundle\Controller;

use MarkBundle\Entity\CarModel;
use MarkBundle\Entity\CarMark;
use UserBundle\Entity\UserOrder;
use AppBundle\Entity\CardFeature;
use AppBundle\Entity\CardPrice;
use AppBundle\Entity\Feature;
use AppBundle\Entity\Foto;
use AppBundle\Entity\Price;
use AppBundle\Entity\Tariff;
use AppBundle\Foto\FotoUtils;
use UserBundle\Entity\User;
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
use AppBundle\SubFields\SubFieldUtils;
use UserBundle\Security\Password;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserController extends Controller
{

    /**
     * @Route("/ajax/getAllSubFields")
     */
    public function getAllSubField(Request $request, SubFieldUtils $sf)
    {
        $generalTypeId = $request->request->get('generalTypeId');

        $result = $sf->getSubFields($generalTypeId);

        return $this->render('all_subfields.html.twig', [
            'result' => $result,
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

        if(!empty($result)) {
            return $this->render('common/ajax_select.html.twig', [
                'options' => $result
            ]);
        }
        else{
            $response = new Response();
            $response->setStatusCode(204);
            return $response;
        }
    }

    /**
     * @Route("/userSignIn")
     */
    public function signInAction(Request $request, Password $password)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = 'SELECT u FROM UserBundle:User u WHERE u.isBanned = 0 AND (u.email = ?1 OR u.login = ?1)';
        $query = $em->createQuery($dql);
        $query->setParameter(1, $request->request->get('email'));
        $users = $query->getResult();



        foreach($users as $user){

            if ($password->CheckPassword($request->request->get('password'), $user->getPassword())){

                $this->get('session')->set('logged_user', $user);
                $this->addFlash(
                    'notice',
                    'Вы успешно вошли в аккаунт!'
                );

                $this->get('session')->set('user_pic', false);
                foreach($user->getInformation() as $info){
                    if($info->getUiKey() == 'foto') $this->get('session')->set('user_pic', $info->getUiValue());
                }

                return $this->redirect($request->request->get('return'));
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
     * @Route("/userLogout")
     */
    public function logoutAction(Request $request)
    {
        $this->get('session')->remove('logged_user');
        $this->addFlash(
            'notice',
            'Вы успешно вышли из аккаунта'
        );
        return $this->redirectToRoute('homepage');
    }


    /**
     * @Route("/userSignUp")
     */
    public function signUpAction(Request $request, Password $password, \Swift_Mailer $mailer)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(array(
                'email' => $request->request->get('email')
            ));

        if ($user) {
            $this->addFlash(
                'notice',
                'Пользователь уже зарегистрирован!'
            );
            return $this->redirectToRoute('homepage');
        }


        $code = md5(rand(0,99999999));
        $user = new User();
        $user->setEmail($request->request->get('email'));
        $user->setLogin('');
        $user->setPassword($password->HashPassword($request->request->get('password')));
        $user->setHeader($request->request->get('header'));
        $user->setActivateString($code);
        $user->setTempPassword('');

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $message = (new \Swift_Message('Регистрация на сайте multiprokat.com'))
            ->setFrom('mail@multiprokat.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'email/registration.html.twig',
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
            'На вашу почту было отправлено письмо для активации аккаунта!'
        );
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/activate_account/{code}", name="activate_account")
     */
    public function activateAccountAction($code)
    {
        $return_url = 'homepage';

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array(
                'activateString' => $code
            ));

        if($user){
            $message = 'Ваш аккаунт успешно активирован!';
            if ($user->getTempPassword() != '') {
                $user->setPassword($user->getTempPassword());
                $message = 'Ваш новый пароль успешно активирован!';
            }
            $user->setTempPassword('');
            $user->setIsActivated(true);
            $user->setActivateString('');
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->get('session')->set('logged_user', $user);
            $this->addFlash(
                'notice',
                $message
            );

            foreach($user->getCards() as $card){
                $card->setIsActive(true);
                $em->persist($card);
                $em->flush();
            }

            $return_url = 'user_cards';
        } else {
            $this->addFlash(
                'notice',
                'Произошла ошибка, попробуйте еще раз.'
            );
        }

        return $this->redirectToRoute($return_url);
    }

    /**
     * @Route("/userRecover")
     */
    public function recoverAction(Request $request, \Swift_Mailer $mailer, Password $password)
    {
        if($request->request->get('password1') == $request->request->get('password2')) {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(array(
                    'email' => $request->request->get('email'),
                ));

            $code = md5(rand(0, 99999999));
            $user->setActivateString($code);
            $user->setTempPassword($password->HashPassword($request->request->get('password1')));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $message = (new \Swift_Message('Восстановление пароля на сайте multiprokat.com'))
                ->setFrom('mail@multiprokat.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'email/recover.html.twig',
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
                'Вам отправлено письмо с активацией нового пароля'
            );

            return $this->redirect($request->request->get('return'));
        } else {
            $this->addFlash(
                'notice',
                'Пароли не совпадают!'
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
        else{
            $em = $this->getDoctrine()->getManager();

            $order = $this->getDoctrine()
                ->getRepository(UserOrder::class)
                ->find($inv_id);

            $order->setStatus('paid');

            if($order->getOrderType() == 'accountPRO'){
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
        $mrh_pass1 = "Wf1bYXSd5V8pKS3ULwb3";  // merchant pass1 here
        $out_summ = $_REQUEST["OutSum"];
        $inv_id = $_REQUEST["InvId"];
        $crc = $_REQUEST["SignatureValue"];
        $crc = strtoupper($crc);  // force uppercase
        $my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass1"));

        if ($my_crc != $crc)
        {
            $text = "bad sign\n";
            return new Response($text, 200);
        }

        $message = 'Ваш новый тариф успешно оплачен!';
        $url = '/user/cards';

        $order = $this->getDoctrine()
            ->getRepository(UserOrder::class)
            ->find($inv_id);

        if ($order->getOrderType() == 'accountPRO') {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($order->getUserId());
            $this->get('session')->set('logged_user', $user);

            $message = 'Ваш PRO аккаунт успешно оплачен!';
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
        $this->addFlash(
            'notice',
            'К сожалению оплата не прошла!'
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
            ->setParameter('eml', '%'.$request->request->get('q').'%')
            ->getQuery()
            ->getResult();

        foreach($users as $user){
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
            $order->setPrice(ceil(450*100/110));
            $order->setOrderType('accountPRO');
            $order->setStatus('new');
            $em->persist($order);
            $em->flush();

            $mrh_login = "multiprokat";
            $mrh_pass1 = "Wf1bYXSd5V8pKS3ULwb3";
            $inv_id = $order->getId();
            $inv_desc = "set_account_PRO";
            $out_summ = ceil(450*100/110);

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
            ->findOneBy(['email'=>$request->request->get('email')]);
        if ($user) return new Response('ok');
        else return new Response('new');
    }
}
