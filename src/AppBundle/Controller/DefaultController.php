<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Mark;
use AppBundle\Entity\SubField;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuMarkModel;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface as em;
use AppBundle\Entity\Card;
use AppBundle\Entity\CardField;
use AppBundle\Entity\City;
use AppBundle\Entity\GeneralType;
use AppBundle\SubFields\SubFieldUtils;
use Symfony\Component\HttpFoundation\Cookie;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(MenuGeneralType $mgt, MenuCity $mc, EntityManagerInterface $em, MenuMarkModel $mm)
    {
//        $query = $em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t ORDER BY t.weight DESC, c.dateTariffStart DESC');
//        $query->setMaxResults(3);
//        $top3 = $query->getResult();

        $topSlider = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getTopSlider();

        $cars = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getLimitedSlider(2);

        $trucks = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getLimitedSlider(3);

        $segways = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getLimitedSlider(14);

        $bicycles = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getLimitedSlider(15);

        $boats = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getLimitedSlider(12);

        $yachts = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getLimitedSlider(13);

        $query = $em->createQuery('SELECT g,c FROM AppBundle:GeneralType g LEFT JOIN g.cards c');
        $generalTypes = $query->getResult();

        if($this->get('session')->has('geo')){

            $geo = $this->get('session')->get('geo');

            //dump($geo);

            $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
                ->where('c.header LIKE :geoname')
                ->andWhere('c.parentId IS NOT NULL')
                ->setParameter('geoname', '%'.$geo['city'].'%')
                ->getQuery()
                ->getResult();

            //dump($city);

            if ($city) $city = $city[0]; // TODO make easier!
            else $city = $this->getDoctrine()
                ->getRepository(City::class)
                ->find(77);
        } else {
            $city = new City();
            $city->setCountry('RUS');
            $city->setParentId(0);
            $city->setTempId(0);
        }

        $query = $em->createQuery('SELECT COUNT(c.id) FROM AppBundle:Card c');
        $totalCards = $query->getSingleScalarResult();

        $query = $em->createQuery('SELECT COUNT(u.id) FROM UserBundle:User u');
        $totalUsers = $query->getSingleScalarResult();

        $query = $em->createQuery('SELECT COUNT(g.id) FROM AppBundle:GeneralType g');
        $totalCategories = $query->getSingleScalarResult();

        $total = array(
            'cards' => $totalCards,
            'users' => $totalUsers,
            'categories' => $totalCategories
            );

        $general = $this->getDoctrine()
            ->getRepository(GeneralType::class)
            ->find(2);

        return $this->render('main_page/main.html.twig', [
            'generalTopLevel' => $mgt->getTopLevel(),
            'cards' => '',
            'custom_fields' => '',
            'general_type' => null,
            'city' => $city,
            'mark_model' => array(),
            'mark_groups' => $mm->getGroups(),
            'topSlider' => $topSlider,
            'cars' => $cars,
            'trucks' => $trucks,
            'segways' => $segways,
            'bicycles' => $bicycles,
            'boats' => $boats,
            'yachts' => $yachts,

            'countries' => $mc->getCountry(),
            'countryCode' => $city->getCountry(),
            'regionId' => $city->getParentId(),
            'regions' => $mc->getRegion($city->getCountry()),
            'cities' => $mc->getCities($city->getParentId()),
            'cityId' => $city->getId(),

            'gtid' => 2,
            'pgtid' => 1,
            'generalSecondLevel' => $mgt->getSecondLevel(1),

            'marks' => [],
            'models' => [],
            'mark' => ['id'=>0,'groupname'=>'','header'=>false, 'carTypeId'=>0],
            'model' => ['id'=>0, 'header'=>false],

            'general' => $general,

            'generalTypes' => $generalTypes,

            'total' => $total

        ]);
    }



    /**
     * @Route("/card/{id}", requirements={"id": "\d+"}, name="showCard")
     */
    public function showCardAction($id, MenuGeneralType $mgt, SubFieldUtils $sf, MenuCity $mc, MenuMarkModel $mm)
    {
        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($id);

        $views = $this->get('session')->get('views');
        if (!isset($views[$card->getId()])) {
            $views[$card->getId()] = 1;
            $this->get('session')->set('views', $views);
            $card->setViews($card->getViews() + 1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($card);
            $em->flush();
        }

        $subFields = $sf->getCardSubFields($card);

//        $city = array(
//            'id'=> $card->getCityId(),
//            'regions' => $mc->getRegion($mc->getCity($card->getCityId())[0]->getCountry()),
//            'object' => $mc->getCity($card->getCityId())[0]
//        );

        $city = $card->getCity();


        if ($card->getVideo() != '') $video = explode("=",$card->getVideo())[1];
        else $video = false;

        if ($card->getStreetView() != '') $streetView = unserialize($card->getStreetView());
        else $streetView = false;

        $dql = 'SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = '.$card->getGeneralTypeId().' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
        $em = $this->get('doctrine')->getManager();
        $query = $em->createQuery($dql);
        $query->setMaxResults(10);
        $similar = $query->getResult();

        $model = $mm->getModel($card->getModelId());
        $mark = $mm->getMark($model->getCarMarkId());
        $models = $mm->getModels($model->getCarMarkId());
        $marks = $mm->getMarks($model->getCarTypeId());

        $user_foto = false;
        foreach ($card->getUser()->getInformation() as $info){
           if($info->getUiKey() == 'foto' and $info->getUiValue()!='') $user_foto =  '/assets/images/users/t/'.$info->getUiValue().'.jpg';
        }


        $general = $this->getDoctrine()
            ->getRepository(GeneralType::class)
            ->find($card->getGeneralTypeId());


        $pgtid = $card->getGeneralType()->getParentId();
        if($pgtid == null) $pgtid = $card->getGeneralTypeId();

        $mainFoto = '';
        foreach($card->getFotos() as $foto){
            if ($foto->getIsMain()) $mainFoto = '/assets/images/cards/'.$foto->getFolder().'/'.$foto->getId().'.jpg';
        }


        return $this->render('card/card_show.html.twig', [

            'card' => $card,
            'streetView' => $streetView,
            'video' => $video,

            'sub_fields' =>$subFields,

//            'general_type' => $card->getGeneralTypeId(),
            'city' => $city,

            'countries' => $mc->getCountry(),
            'countryCode' => $city->getCountry(),
            'regionId' => $city->getParentId(),
            'regions' => $mc->getRegion($city->getCountry()),
            'cities' => $mc->getCities($city->getParentId()),
            'cityId' => $card->getCityId(),

            'generalTopLevel' => $mgt->getTopLevel(),
            'generalSecondLevel' => $mgt->getSecondLevel($card->getGeneralType()->getParentId()),
            'pgtid' => $pgtid,
            'gtid' => $card->getGeneralTypeId(),
            'similar' => $similar,

            'mark_groups' => $mm->getGroups(),
            'mark' => $mark,
            'model' => $model,
            'marks' => $marks,
            'models' => $models,
            'general' => $general,

            'user_foto' => $user_foto,
            'mainFoto' => $mainFoto

        ]);
    }

    /**
     * @Route("/mainCitySelector")
     */
    public function citySelectorAction(Request $request)
    {
        $post = $request->request;
        $countryCode = $post->get('countryCode');
        $regionId = $post->get('regionId');
        $cityId = $post->get('cityId');
        $general_type = $post->get('general_type');

        if (isset($countryCode)) $return = $countryCode;
        if (isset($regionId) and $regionId != 0 ) $return = $regionId;
        if (isset($cityId) and $cityId != 0) $return = $cityId;
        //dump($request);
        return $this->redirect('/type/'.$return.'/'.$general_type);
    }

    /**
     * @Route("/ajax/showPhone")
     */
    public function showPhoneAction(Request $request)
    {
        $post = $request->request;
        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($post->get('card_id'));

        foreach($card->getUser()->getInformation() as $info){
            if( $info->getUiKey() == 'phone') $phone = $info->getUiValue();
        }

        if($this->get('session')->has('phone')){
            $array = $this->get('session')->get('phone');
            $array[$card->getUserId()] = 1;
            $this->get('session')->set('phone', $array);
        } else {
            $this->get('session')->set('phone', [$card->getUserId() => 1]);
        }

        return new Response($phone, 200);
    }

    /**
     * @Route("/ajax/frontGeo")
     */
    public function frontGeoAction(Request $request, Response $response, EntityManagerInterface $em)
    {
        $post = $request->request;
        $geo = json_decode($post->get('geo'), true);
        $this->get('session')->set('geo', $geo);
        $this->get('session')->set('ip', $geo['ip']);
        $this->get('session')->set('sessId', $this->get('session')->getId());

        $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
            ->where('c.header LIKE :geoname')
            ->setParameter('geoname', '%'.$geo['city']['name_ru'].'%')
            ->getQuery()
            ->getResult();
        if ($city) $cityId = $city[0]->getId();
        else $cityId = 77;

        $response = new Response();
        $response->headers->setCookie(new Cookie('geo', $cityId));
        $response->send();
    }


    /**
     * @Route("/listing/{slug}/")
     */
    public function wpStubAction(MenuGeneralType $mgt, MenuCity $mc, EntityManagerInterface $em, MenuMarkModel $mm)
    {
        if($this->get('session')->has('geo')){

            $geo = $this->get('session')->get('geo');

            //dump($geo);

            $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
                ->andWhere('c.header LIKE :geoname')
                ->setParameter('geoname', '%'.$geo['city'].'%')
                ->getQuery()
                ->getResult();

            //dump($city);

            if ($city) $city = $city[0]; // TODO make easier!
            else $city = $this->getDoctrine()
                ->getRepository(City::class)
                ->find(77);
        } else {
            $city = new City();
            $city->setCountry('RUS');
            $city->setParentId(0);
            $city->setTempId(0);
        }


        $general = $this->getDoctrine()
            ->getRepository(GeneralType::class)
            ->find(2);

        $query = $em->createQuery('SELECT g,c FROM AppBundle:GeneralType g LEFT JOIN g.cards c');
        $generalTypes = $query->getResult();

        return $this->render('common/wp_stub.html.twig', [
            'generalTopLevel' => $mgt->getTopLevel(),
            'cards' => '',
            'custom_fields' => '',
            'general_type' => null,
            'city' => $city,
            'mark_model' => array(),
            'mark_groups' => $mm->getGroups(),

            'countries' => $mc->getCountry(),
            'countryCode' => $city->getCountry(),
            'regionId' => $city->getParentId(),
            'regions' => $mc->getRegion($city->getCountry()),
            'cities' => $mc->getCities($city->getParentId()),
            'cityId' => $city->getId(),

            'gtid' => 2,
            'pgtid' => 1,
            'generalSecondLevel' => $mgt->getSecondLevel(1),

            'marks' => [],
            'models' => [],
            'mark' => ['id'=>0,'groupname'=>'','header'=>false],
            'model' => ['id'=>0, 'header'=>false],

            'general' => $general,

            'generalTypes' => $generalTypes
        ]);
    }
}
