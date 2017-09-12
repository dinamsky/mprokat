<?php

namespace UserBundle\Controller;

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
     * @Route("/card/new")
     */
    public function indexAction(MenuMarkModel $markmenu, MenuGeneralType $mgt, MenuCity $mc, Request $request, FotoUtils $fu, EntityManagerInterface $em, \Swift_Mailer $mailer)
    {

        if($this->get('session')->get('logged_user') === null and !$this->get('session')->has('admin')) return new Response("",404);

        $card = new Card();

        $conditions = $this->getDoctrine()
            ->getRepository(State::class)
            ->findAll();

        $tariffs = $this->getDoctrine()
            ->getRepository(Tariff::class)
            ->findAll();

        $colors = $this->getDoctrine()
            ->getRepository(Color::class)
            ->findAll();

        $features = $this->getDoctrine()
            ->getRepository(Feature::class)
            ->findBy(['parent'=>null]);

        $prices = $this->getDoctrine()
            ->getRepository(Price::class)
            ->findAll();

        if($request->isMethod('GET')) {


            if($this->get('session')->has('geo')){

                $geo = $this->get('session')->get('geo');

                $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
                    ->andWhere('c.header LIKE :geoname')
                    ->setParameter('geoname', '%'.$geo['city'].'%')
                    ->getQuery()
                    ->getResult();
                if ($city) $city = $city[0]; // TODO make easier!
                else {
                    $city = new City();
                    $city->setCountry('RUS');
                    $city->setParentId(0);
                    $city->setTempId(0);
                }
            } else {
                $city = new City();
                $city->setCountry('RUS');
                $city->setParentId(0);
                $city->setTempId(0);
            }

            $gt = $this->getDoctrine()
                ->getRepository(GeneralType::class)
                ->find(2);



            $response = $this->render('card/card_new.html.twig', [
                'generalTopLevel' => $mgt->getTopLevel(),
                'generalSecondLevel' => $mgt->getSecondLevel(1),
                'gt' => $gt,
                'custom_fields' => '',
                'mark_groups' => $markmenu->getGroups(),
                'marks' => $markmenu->getMarks('cars'),
                'conditions' => $conditions,
                'colors' => $colors,
                'features' => $features,
                'prices' => $prices,
                'tariffs' =>$tariffs,

                'countries' => $mc->getCountry(),
                'countryCode' => $city->getCountry(),
                'regionId' => $city->getParentId(),
                'regions' => $mc->getRegion($city->getCountry()),
                'cities' => $mc->getCities($city->getParentId()),
                'cityId' => $city->getId(),
                'city' => $city


            ]);
        }

        if($request->isMethod('POST')){
            $em = $this->getDoctrine()->getManager();
            $post = $request->request;
            $card->setHeader($post->get('header'));
            $card->setContent($post->get('content'));
            $card->setAddress($post->get('address'));
            $card->setCoords($post->get('coords'));
            $card->setVideo($post->get('video'));
            $card->setStreetView($post->get('streetView'));

            if($post->has('noMark')){
                $modelId = $this->getDoctrine()
                    ->getRepository(Mark::class)
                    ->find(1799);
            } else {
                $modelId = $this->getDoctrine()
                    ->getRepository(Mark::class)
                    ->find($post->get('modelId'));
            }
            $card->setMarkModel($modelId);

            $generalType = $this->getDoctrine()
                ->getRepository(GeneralType::class)
                ->find($post->get('generalTypeId'));
            $card->setGeneralType($generalType);

            $city = $this->getDoctrine()
                ->getRepository(City::class)
                ->find($post->get('cityId'));
            $card->setCity($city);

            $card->setProdYear($post->get('prodYear'));

            $condition = $this->getDoctrine()
                ->getRepository(State::class)
                ->find($post->get('conditionId'));
            $card->setCondition($condition);

            $card->setServiceTypeId($post->get('serviceTypeId'));

            if ($post->has('user_email') and $this->get('session')->has('admin')){
                $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->findOneBy(array(
                        'email' => $post->get('user_email')
                    ));
            } else {
                $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->find($this->get('session')->get('logged_user')->getId());
            }

            $card->setUser($user);

            $color = $this->getDoctrine()
                ->getRepository(Color::class)
                ->find($post->get('colorId'));
            $card->setColor($color);

            $tariff = $this->getDoctrine()
                ->getRepository(Tariff::class)
                ->find(1);

            $card->setTariff($tariff);

            $em->persist($card);

            $em->flush();


            if($post->has('noMark')){
                $message = (new \Swift_Message('Пользователь не нашел свою марку'))
                    ->setFrom('robot@multiprokat.com')
                    ->setTo('test.multiprokat@gmail.com')
                    ->setBody(
                        $this->renderView(
                            'email/newmark.html.twig',
                            array(
                                'mark' => $post->get('ownMark'),
                                'card' => $card
                            )
                        ),
                        'text/html'
                    );
                $mailer->send($message);
            }


            foreach($post->get('subField') as $fieldId=>$value) if($value!=0 and $value!=''){
                $subfield = $this->getDoctrine()
                    ->getRepository(FieldType::class)
                    ->find($fieldId);
                $storageTypeName = "\AppBundle\Entity\\".$subfield->getStorageType();
                $storage = new $storageTypeName();
                $storage->setCard($card);
                $storage->setCardFieldId($fieldId);
                $storage->setValue($value);

                $em->persist($storage);
            }

            if ($post->has('feature')) foreach ($post->get('feature') as $featureId=>$featureValue){
                $feature = $this->getDoctrine()
                    ->getRepository(Feature::class)
                    ->find($featureId);
                $cardFeature = new CardFeature();
                $cardFeature->setCard($card);
                $cardFeature->setFeature($feature);
                $em->persist($cardFeature);
            }

            if ($post->has('price')) foreach ($post->get('price') as $priceId=>$priceValue) if ($priceValue!="") {
                $price = $this->getDoctrine()
                    ->getRepository(Price::class)
                    ->find($priceId);
                $cardPrice = new CardPrice();
                $cardPrice->setCard($card);
                $cardPrice->setPrice($price);
                $cardPrice->setValue($priceValue);
                $em->persist($cardPrice);
            }

            $em->flush();

            $fu->uploadImages($card);

            if ($post->get('tariffId') == 1) $response = $this->redirectToRoute('user_cards');
            else {

                $tariff = $this->getDoctrine()
                    ->getRepository(Tariff::class)
                    ->find($post->get('tariffId'));

                $order = new UserOrder();
                $order->setUser($user);
                $order->setCard($card);
                $order->setTariff($tariff);
                $order->setPrice($tariff->getPrice());
                $order->setOrderType('tariff_'.$tariff->getId());
                $order->setStatus('new');
                $em->persist($order);
                $em->flush();

                $mrh_login = "multiprokat";
                $mrh_pass1 = "Wf1bYXSd5V8pKS3ULwb3";
                $inv_id    = $order->getId();
                $inv_desc  = "set_tariff";
                $out_summ  = $tariff->getPrice();

                $crc  = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");

                $url = "https://auth.robokassa.ru/Merchant/Index.aspx?MrchLogin=$mrh_login&".
                    "OutSum=$out_summ&InvId=$inv_id&Desc=$inv_desc&SignatureValue=$crc";

                $response = new RedirectResponse($url);
            }
        }

        return $response;
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
        $dql = 'SELECT u FROM UserBundle:User u WHERE u.email = ?1 OR u.login = ?1';
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
            ->setFrom('robot@multiprokat.com')
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
            $return_url = 'user_main';
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
                ->setFrom('robot@multiprokat.com')
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
     * @Route("/user/edit/card/{cardId}")
     */
    public function editCardAction($cardId, MenuGeneralType $mgt, MenuMarkModel $markmenu, MenuCity $mc, SubFieldUtils $sf)
    {
        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($cardId);

        if ( $this->get('session')->get('logged_user') !== null
            and $card->getUserId() != $this->get('session')->get('logged_user')->getId()
        ) return new Response("",404);

        if ( $this->get('session')->get('logged_user') === null
            and $this->get('session')->get('admin') === null
        ) return new Response("",404);

        $conditions = $this->getDoctrine()
            ->getRepository(State::class)
            ->findAll();

        $colors = $this->getDoctrine()
            ->getRepository(Color::class)
            ->findAll();

        $generalType = $this->getDoctrine()
        ->getRepository(GeneralType::class)
        ->find($card->getGeneralTypeId());

        $model = $this->getDoctrine()
            ->getRepository(Mark::class)
            ->find($card->getModelId());

        $marks = $markmenu->getMarks($model->getGroupName());

        $mark = $model->getParent();

        $models = $mark->getChildren();

        $city = $this->getDoctrine()
            ->getRepository(City::class)
            ->find($card->getCityId());

        $features = $this->getDoctrine()
            ->getRepository(Feature::class)
            ->findBy(['parent'=>null]);

        $prices = $this->getDoctrine()
            ->getRepository(Price::class)
            ->findAll();

        $tariffs = $this->getDoctrine()
            ->getRepository(Tariff::class)
            ->findAll();

        $gt = $this->getDoctrine()
            ->getRepository(GeneralType::class)
            ->find($card->getGeneralTypeId());

        if ($card->getFieldIntegers()->isEmpty()) $subfields = false;
        else $subfields = $sf->getSubFieldsEdit($card);


        return $this->render('card/card_edit.html.twig',[
            'card' => $card,
            'conditions' => $conditions,
            'colors' => $colors,
            'generalTopLevel' => $mgt->getTopLevel(),
            'generalSecondLevel' => $mgt->getSecondLevel($generalType->getParentId()),
            'gt' => $gt,
            'countries' => $mc->getCountry(),
            'mark' => $mark,
            'model' => $model,
            'marks' => $marks,
            'models' => $models,
            'mark_groups' => $markmenu->getGroups(),
            'countryCode' =>$city->getCountry(),
            'regionId' => $city->getParent()->getId(),
            'regions' => $mc->getRegion($city->getCountry()),
            'cities' => $city->getParent()->getChildren(),
            'city' => $city,
            'subfields' => $subfields,
            'features' => $features,
            'prices' => $prices,
            'tariffs' => $tariffs
        ]);
    }

    /**
     * @Route("/card/update")
     */
    public function saveCardAction(Request $request, FotoUtils $fu)
    {

        $post = $request->request;

        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($post->get('cardId'));

        $em = $this->getDoctrine()->getManager();

        if ($post->has('delete')){
            $fu->deleteAllFotos($card);
            $em->remove($card);
            $em->flush();
            if ($this->get('session')->get('admin') === null) return $this->redirectToRoute('user_cards');
            else return $this->redirectToRoute('search');
        }

        $card->setHeader($post->get('header'));
        $card->setContent($post->get('content'));
        $card->setAddress($post->get('address'));
        $card->setCoords($post->get('coords'));
        $card->setVideo($post->get('video'));
        $card->setStreetView($post->get('streetView'));

        $modelId = $this->getDoctrine()
            ->getRepository(Mark::class)
            ->find($post->get('modelId'));
        $card->setMarkModel($modelId);

        $generalType = $this->getDoctrine()
            ->getRepository(GeneralType::class)
            ->find($post->get('generalTypeId'));
        $card->setGeneralType($generalType);

        $city = $this->getDoctrine()
            ->getRepository(City::class)
            ->find($post->get('cityId'));
        $card->setCity($city);

        $card->setProdYear($post->get('prodYear'));

        $condition = $this->getDoctrine()
            ->getRepository(State::class)
            ->find($post->get('conditionId'));
        $card->setCondition($condition);

        $card->setServiceTypeId($post->get('serviceTypeId'));

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($card->getUserId());
        $card->setUser($user);

        $color = $this->getDoctrine()
            ->getRepository(Color::class)
            ->find($post->get('colorId'));
        $card->setColor($color);

        $tariff = $this->getDoctrine()
            ->getRepository(Tariff::class)
            ->find($post->get('tariffId'));

        //$card->setTariff($tariff);

        $em->persist($card);


        foreach($post->get('subField') as $fieldId=>$value) if($value!=0 and $value!=''){
            $subfield = $this->getDoctrine()
                ->getRepository(FieldType::class)
                ->find($fieldId);

            $dql = 'SELECT s FROM AppBundle:'.$subfield->getStorageType().' s WHERE s.cardId = ?1 AND s.cardFieldId = ?2';
            $query = $em->createQuery($dql);
            $query->setParameter(1, $card->getId());
            $query->setParameter(2, $fieldId);
            try {
                $storage = $query->getSingleResult();
            }
            catch (\Doctrine\ORM\NoResultException $e){
                $storageTypeName = "\AppBundle\Entity\\".$subfield->getStorageType();
                $storage = new $storageTypeName();
                $storage->setCard($card);
                $storage->setCardFieldId($fieldId);
            }

            $storage->setValue($value);
            $em->persist($storage);

        }

        $query = $em->createQuery('DELETE AppBundle\Entity\CardFeature c WHERE c.cardId = ?1');
        $query->setParameter(1, $card->getId());
        $query->execute();

        if ($post->has('feature')) foreach($post->get('feature') as $fid=>$f) {
            $feature = $this->getDoctrine()
                ->getRepository(Feature::class)
                ->find($fid);
            $cardFeature = new CardFeature();
            $cardFeature->setCard($card);
            $cardFeature->setFeature($feature);
            $em->persist($cardFeature);
        };

        $query = $em->createQuery('DELETE AppBundle\Entity\CardPrice c WHERE c.cardId = ?1');
        $query->setParameter(1, $card->getId());
        $query->execute();

        if ($post->has('price')) foreach($post->get('price') as $priceId=>$priceValue) {
            if ($priceValue != '' and $priceValue !=0) {
                $price = $this->getDoctrine()
                    ->getRepository(Price::class)
                    ->find($priceId);
                $cardPrice = new cardPrice();
                $cardPrice->setCard($card);
                $cardPrice->setPrice($price);
                $cardPrice->setValue($priceValue);
                $em->persist($cardPrice);
            }
        };

        $em->flush();

        $fu->uploadImages($card);

        if ($post->has('change_tariff') and $tariff->getId() > 1){

            if ($this->get('session')->has('admin')){ // admin able to change tariff without payment
                $card->setTariff($tariff);
                $card->setDateTariffStart(new \DateTime());
                $em->persist($card);
                $em->flush();
                return $this->redirectToRoute('search');
            } else {
                $order = new UserOrder();
                $order->setUser($user);
                $order->setCard($card);
                $order->setTariff($tariff);
                $order->setPrice($tariff->getPrice());
                $order->setOrderType('tariff_' . $tariff->getId());
                $order->setStatus('new');
                $em->persist($order);
                $em->flush();

                $mrh_login = "multiprokat";
                $mrh_pass1 = "Wf1bYXSd5V8pKS3ULwb3";
                $inv_id = $order->getId();
                $inv_desc = "set_tariff";
                $out_summ = $tariff->getPrice();

                $crc = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");

                $url = "https://auth.robokassa.ru/Merchant/Index.aspx?MrchLogin=$mrh_login&" .
                    "OutSum=$out_summ&InvId=$inv_id&Desc=$inv_desc&SignatureValue=$crc";

                return new RedirectResponse($url);
            }

        } else {
            if($post->has('change_tariff') and $tariff->getId() == 1){
                $card->setTariff($tariff);
                $em->persist($card);
                $em->flush();
            }
            if ($this->get('session')->get('admin') === null) return $this->redirectToRoute('user_cards');
            else return $this->redirectToRoute('search');
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

        $this->addFlash(
            'notice',
            'Ваш новый тариф успешно оплачен!'
        );

        return $this->redirectToRoute('homepage');
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

}
