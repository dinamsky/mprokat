<?php

namespace AppBundle\Controller;

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
use AppBundle\SubFields\SubFieldUtils;
use Symfony\Component\HttpFoundation\Cookie;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(MenuGeneralType $mgt, MenuCity $mc, EntityManagerInterface $em, MenuMarkModel $mm)
    {
        $query = $em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t ORDER BY t.weight DESC, c.dateTariffStart DESC');
        $query->setMaxResults(3);
        $top3 = $query->getResult();

        $query = $em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = 2 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setMaxResults(10);
        $cars = $query->getResult();

        $query = $em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = 3 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setMaxResults(10);
        $trucks = $query->getResult();

        $query = $em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = 14 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setMaxResults(10);
        $segways = $query->getResult();

        $query = $em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = 15 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setMaxResults(10);
        $bicycles = $query->getResult();

        $query = $em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = 12 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setMaxResults(10);
        $boats = $query->getResult();

        $query = $em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = 13 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setMaxResults(10);
        $yachts = $query->getResult();

        if($this->get('session')->has('geo')){

            $geo = $this->get('session')->get('geo');

            //dump($geo);

            $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
                ->andWhere('c.header LIKE :geoname')
                ->setParameter('geoname', '%'.$geo['city']['name_ru'].'%')
                ->getQuery()
                ->getResult();

            //dump($city);

            $city = $city[0]; // TODO make easier!
        } else {
            $city = new City();
            $city->setCountry('RUS');
            $city->setParentId(0);
            $city->setTempId(0);
        }


        return $this->render('main_page/main.html.twig', [
            'generalTopLevel' => $mgt->getTopLevel(),
            'cards' => '',
            'custom_fields' => '',
            'general_type' => null,
            'city' => $city,
            'mark_model' => array(),
            'mark_groups' => $mm->getGroups(),
            'top3' => $top3,
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

        $model = $mm->getMark($card->getModelId());
        $mark = $model->getParent();
        $models = $mark->getChildren();
        $marks = $mm->getMarks($model->getGroupName());

        $user_foto = false;
        foreach ($card->getUser()->getInformation() as $info){
           if($info->getUiKey() == 'foto' and $info->getUiValue()!='') $user_foto =  '/assets/images/users/t/'.$info->getUiValue().'.jpg';
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
            'pgtid' => $card->getGeneralType()->getParentId(),
            'gtid' => $card->getGeneralTypeId(),
            'similar' => $similar,

            'mark_groups' => $mm->getGroups(),
            'mark' => $mark,
            'model' => $model,
            'marks' => $marks,
            'models' => $models,

            'user_foto' => $user_foto

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


}
