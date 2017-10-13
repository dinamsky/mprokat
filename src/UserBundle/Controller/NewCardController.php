<?php

namespace UserBundle\Controller;

use AppBundle\Menu\ServiceStat;
use MarkBundle\Entity\CarModel;
use MarkBundle\Entity\CarMark;
use UserBundle\Entity\UserOrder;
use UserBundle\Entity\User;
use AppBundle\Entity\CardFeature;
use AppBundle\Entity\CardPrice;
use AppBundle\Entity\Feature;
use AppBundle\Entity\Foto;
use AppBundle\Entity\Price;
use AppBundle\Entity\Tariff;
use AdminBundle\Entity\Admin;
use AppBundle\Foto\FotoUtils;

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

class NewCardController extends Controller
{
    /**
     * @Route("/card/new")
     */
    public function indexAction(MenuMarkModel $markmenu, MenuGeneralType $mgt, MenuCity $mc, Request $request, FotoUtils $fu, EntityManagerInterface $em, \Swift_Mailer $mailer, ServiceStat $stat)
    {


        if($this->get('session')->get('logged_user') === null and !$this->get('session')->has('admin')) return new Response("",404);

        if(!$this->get('session')->has('admin')) {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['id' => $this->get('session')->get('logged_user')->getId()]);
            if ($user->getIsBanned()) return new Response("", 404);

            if ($user->getAccountTypeId() == 0 and count($user->getCards()) > 2){
                $this->addFlash(
                    'notice',
                    'В стандартном аккаунте вам доступно не более 2-х объявлений.<br>Оплатите PRO аккаунт для неограниченного количества объявлений'
                );
                return new RedirectResponse('/user/cards');
            }
        }





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
                    ->where('c.header LIKE :geoname')
                    ->andWhere('c.parentId IS NOT NULL')
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

            if ($this->get('session')->has('admin')) $admin = $this->getDoctrine()
                                                            ->getRepository(Admin::class)
                                                            ->find($this->get('session')->get('admin')->getId());
            else $admin = false;

            $query = $em->createQuery('SELECT c FROM AppBundle:City c WHERE c.total > 0 ORDER BY c.total DESC, c.header ASC');
            $popular_city = $query->getResult();

            $stat_arr = [
                'url' => '/card/new',
                'event_type' => 'set_form',
                'page_type' => 'form',
                'user_id' => $user->getId(),
            ];
            $stat->setStat($stat_arr);


            $response = $this->render('card/card_new.html.twig', [
                'generalTopLevel' => $mgt->getTopLevel(),
                'generalSecondLevel' => $mgt->getSecondLevel(1),
                'gt' => $gt,
                'custom_fields' => '',
                'mark_groups' => $markmenu->getGroups(),
                'marks' => $markmenu->getMarks(1),
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
                'city' => $city,

                'admin' => $admin,

                'popular_city' => $popular_city,

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


            if ($post->get('generalTypeId') == 0) $gt = $post->get('generalTypeTopLevelId');
            else $gt = $post->get('generalTypeId');

            $generalType = $this->getDoctrine()
                ->getRepository(GeneralType::class)
                ->find($gt);
            $card->setGeneralType($generalType);


            if($post->has('noMark')){
//                $model = $this->getDoctrine()
//                    ->getRepository(CarModel::class)
//                    ->find(20991);
                $mark = $this->getDoctrine()
                    ->getRepository(CarMark::class)
                    ->find($post->get('mark'));


                $model = new CarModel();
                $model->setCarTypeId($generalType->getCarTypeIds());
                $model->setHeader(strip_tags(trim(mb_strtoupper(mb_substr($post->get('ownMark'), 0, 1)))));
                $model->setMark($mark);
                $model->setTotal(1);
                $em->persist($model);
                $em->flush();

            } else {
                $model = $this->getDoctrine()
                    ->getRepository(CarModel::class)
                    ->find($post->get('modelId'));
            }
            $card->setMarkModel($model);




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

            $admin = $this->getDoctrine()
                ->getRepository(Admin::class)
                ->find(1);

            $card->setAdmin($admin);

            if ($this->get('session')->has('admin')){
                $admin = $this->getDoctrine()
                    ->getRepository(Admin::class)
                    ->find($this->get('session')->get('admin')->getId());
                $card->setAdmin($admin);
            }

            $em->persist($card);

            $em->flush();


            $stat_arr = [
                'url' => '/card/new',
                'event_type' => 'new_card',
                'page_type' => 'form',
                'user_id' => $user->getId(),
                'card_id' => $card->getId(),
            ];
            $stat->setStat($stat_arr);


            if($post->has('noMark')){
                $message = (new \Swift_Message('Пользователь не нашел свою марку'))
                    ->setFrom('mail@multiprokat.com')
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

            $mc->updateCityTotal($card->getCityId(),$card->getModelId());

            $markmenu->updateModelTotal($card->getModelId());

            if ($post->get('tariffId') == 1) {
                if ($this->get('session')->has('admin')){
                    $response = $this->redirectToRoute('admin_main');
                } else {
                    $response = $this->redirectToRoute('user_cards');
                }
            }
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

            $this->addFlash(
                'notice',
                'Не забудьте поделиться вашим объявлением в социальных сетях!'
            );

        }

        return $response;
    }

}
