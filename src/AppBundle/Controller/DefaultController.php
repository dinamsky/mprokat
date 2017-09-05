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
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface as em;
use AppBundle\Entity\Card;
use AppBundle\Entity\CardField;
use AppBundle\Entity\City;
use AppBundle\SubFields\SubFieldUtils;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(MenuGeneralType $mgt, MenuCity $mc, EntityManagerInterface $em)
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


        return $this->render('main_page/main.html.twig', [
            'generalTopLevel' => $mgt->getTopLevel(),
            'cards' => '',
            'custom_fields' => '',
            'countries' => $mc->getCountry(),
            'general_type' => null,
            'city' => array(
                'id' => 'RUS',
                'regions' => array()
            ),
            'mark_model' => array(),
            'top3' => $top3,
            'cars' => $cars,
            'trucks' => $trucks,
            'segways' => $segways,
            'bicycles' => $bicycles,
            'boats' => $boats,
            'yachts' => $yachts

        ]);
    }



    /**
     * @Route("/card/{id}", requirements={"id": "\d+"})
     */
    public function showCardAction($id, MenuGeneralType $mgt, SubFieldUtils $sf, MenuCity $mc, MenuMarkModel $mm)
    {
        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($id);

        $subFields = $sf->getCardSubFields($card);

        $city = array(
            'id'=> $card->getCityId(),
            'regions' => $mc->getRegion($mc->getCity($card->getCityId())[0]->getCountry()),
            'object' => $mc->getCity($card->getCityId())[0]
        );

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

        return $this->render('card/card_show.html.twig', [

            'card' => $card,
            'streetView' => $streetView,
            'video' => $video,

            'sub_fields' =>$subFields,

//            'general_type' => $card->getGeneralTypeId(),
//            'city' => $city,

            'countries' => $mc->getCountry(),
            'countryCode' => $card->getCity()->getCountry(),
            'regionId' => $card->getCity()->getParentId(),
            'regions' => $mc->getRegion($card->getCity()->getCountry()),
            'cities' => $mc->getCities($card->getCity()->getParentId()),
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


}
