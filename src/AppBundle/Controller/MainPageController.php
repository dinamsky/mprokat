<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Seo;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuMarkModel;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Card;
use AppBundle\Entity\City;
use AppBundle\Entity\GeneralType;

class MainPageController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(MenuGeneralType $mgt, MenuCity $mc, EntityManagerInterface $em, MenuMarkModel $mm)
    {

        $topSlider = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getTopSlider();

//        $cars = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getLimitedSlider(2);
//
//        $trucks = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getLimitedSlider(3);
//
//        $moto = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getLimitedSlider(8);
//
//        $bicycles = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getLimitedSlider(15);
//
//        $yachts = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getLimitedSlider(13);
//
//        $snow = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getLimitedSlider(10);
//
//        $heli = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getLimitedSlider(17);
//
//        $quad = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getLimitedSlider(9);

        $all = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getLimitedSliders([2,3,8,15,13,10,17,9]);



        $cars = $all[2];
        $trucks = $all[3];
        $moto = $all[8];
        $bicycles = $all[15];
        $yachts = $all[13];
        $snow = $all[10];
        $heli = $all[17];
        $quad = $all[9];

//        $cars = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getTopOne(2);
//        $trucks = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getTopOne(3);
//        $moto = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getTopOne(8);
//        $bicycles = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getTopOne(15);
//        $yachts = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getTopOne(13);
//        $snow = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getTopOne(10);
//        $heli = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getTopOne(17);
//        $quad = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->getTopOne(9);

        $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.total DESC');
        $generalTypes = $query->getResult();




        if(!$this->get('session')->has('city')){
            if($this->get('session')->has('geo')){
                $geo = $this->get('session')->get('geo');
                $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
                    ->where('c.header LIKE :geoname')
                    ->andWhere('c.parentId IS NOT NULL')
                    ->setParameter('geoname', '%'.$geo['city'].'%')
                    ->getQuery()
                    ->getResult();
                if ($city) {
                    $city = $city[0];
                }
                else {
                    $city = $this->getDoctrine()
                        ->getRepository(City::class)
                        ->find(77);
                }
            } else {
                $city = new City();
                $city->setCountry('RUS');
                $city->setHeader('Россия');
                $city->setParentId(0);
                $city->setGde('России');
                $city->setTempId(0);
                $city->setUrl('rus');
            }
            $this->get('session')->set('city', $city);
        } else {
            $city = $this->get('session')->get('city');
            if(is_array($city) and isset($city[0])) {
                $city = $city[0];
                $city->setGde('России');
            }
            if(is_array($city) and empty($city)){
                $city = new City();
                $city->setCountry('RUS');
                $city->setHeader('Россия');
                $city->setGde('России');
                $city->setParentId(0);
                $city->setTempId(0);
                $city->setUrl('rus');
            }
            if(!is_array($city)){
                $city->setGde('России');
            }
        }

        $in_city = $city->getUrl();

        //---
        $query = $em->createQuery('SELECT COUNT(c.id) FROM AppBundle:Card c');
        $totalCards = $query->getSingleScalarResult();

        $query = $em->createQuery('SELECT SUM(c.views) FROM AppBundle:Card c');
        $totalViews = $query->getSingleScalarResult();

        $query = $em->createQuery('SELECT COUNT(g.id) FROM AppBundle:GeneralType g');
        $totalCategories = $query->getSingleScalarResult();

        $total = array(
            'cards' => $totalCards,
            'views' => $totalViews,
            'categories' => $totalCategories
            );

//        $general = $this->getDoctrine()
//            ->getRepository(GeneralType::class)
//            ->find(2);

        $custom_seo = $this->getDoctrine()
            ->getRepository(Seo::class)
            ->findOneBy(['url' => 'main']);

        $query = $em->createQuery('SELECT c FROM AppBundle:City c WHERE c.total > 0 ORDER BY c.total DESC, c.header ASC');
        $popular_city = $query->getResult();


        $mark_arr = $mm->getExistMarks('',1);

        $mark_arr_sorted = $mark_arr['sorted_marks'];
        //$mark_arr_typed = $mark_arr['typed_marks'];
        $models_in_mark = $mark_arr['models_in_mark'];



        return $this->render('main_page/main.html.twig', [
//            'generalTopLevel' => $mgt->getTopLevel(),
//            'cards' => '',
//            'custom_fields' => '',
//            'general_type' => null,
            'city' => $city,
//            'mark_model' => array(),
//            'mark_groups' => $mm->getGroups(),
            'topSlider' => $topSlider,
            'cars' => $cars,
            'trucks' => $trucks,
            'heli' => $heli,
            'bicycles' => $bicycles,
            'snow' => $snow,
            'yachts' => $yachts,
            'moto' => $moto,
            'quad' => $quad,

//            'countries' => $mc->getCountry(),
//            'countryCode' => $city->getCountry(),
//            'regionId' => $city->getParentId(),
//            'regions' => $mc->getRegion($city->getCountry()),
//            'cities' => $mc->getCities($city->getParentId()),
            'cityId' => $city->getId(),

//            'gtid' => 2,
//            'pgtid' => 1,
//            'generalSecondLevel' => $mgt->getSecondLevel(1),

            'marks' => [],
            'models' => [],
            'mark' => $mark_arr_sorted[1][0]['mark'],
            'model' => $mark_arr_sorted[1][0]['models'][0],

//            'general' => $general,

            'generalTypes' => $generalTypes,

            'total' => $total,

            'custom_seo' => $custom_seo,

            'popular_city' => $popular_city,
            'mark_arr_sorted' => $mark_arr_sorted,
            'models_in_mark' => $models_in_mark,
            'in_city' => $in_city
        ]);
    }
}
