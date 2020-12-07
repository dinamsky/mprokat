<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Seo;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuMarkModel;
use AppBundle\Menu\ServiceStat;
use Doctrine\ORM\EntityManagerInterface;
use MarkBundle\Entity\CarMark;
use MarkBundle\Entity\CarModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Card;
use AppBundle\Entity\City;
use AppBundle\Entity\GeneralType;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;

class MainPageController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(MenuGeneralType $mgt, MenuCity $mc, EntityManagerInterface $em, MenuMarkModel $mm, Request $request, ServiceStat $stat)
    {



        $topSlider = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getTopSlider($this->get('session')->get('city')->getId());

        $top10Slider = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getTop10Slider($this->get('session')->get('city')->getId());

        $top13_10Slider = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getTop13_10($this->get('session')->get('city')->getId());

        $getOwnerTop10Slider = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getOwnerTop10Slider($this->get('session')->get('city')->getId());
    

        $all = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getLimitedSliders([2,4,5,6,8,9,10,13,14,15,17,18,28,29,30,44], $this->get('session')->get('city')->getId());
        $buggy = $all[44];
        $cars = $all[2];
        $quadro = $all[9];
        $wedding = $all[6];
         $snow = $all[10];
         $specialmachines = $all[18];
         $bicycles = $all[15];
         $vans = $all[5];
         $airplanes = $all[29];
         $rv = $all[30];
         $segways = $all[14];
         $limo = $all[4];
         $scooters = $all[28];
        // $trucks = $all[3];
        $yachts = $all[13];
        $bikes = $all[8];
        shuffle($cars);
        // $moto = $all[8];
         $heli = $all[17];

        $countsGr = [

            'snowride'=>count($snow),
            'quadro'=>count($quadro),
            'buggy' => count($buggy),
            'segways' => count($segways),
            'bikes'=>count($bikes),
            'bicycles'=>count($bicycles),
            'scooters'=>count($scooters),
            'vans'=>count($vans),
            'rv'=>count($rv),
            'limo'=>count($limo),
            'wedding'=>count($wedding),
            'heli'=> count($heli),
           'yachts'=>count($yachts),
            'airplanes'=>count($airplanes),
            'special-machines' => count($specialmachines),
           // 'trucks'=>count($trucks),
        ];
        $grHeader = [
            'buggy' => 'Багги',
            'snowride'=>'Снегоходы',
            'heli'=>'Вертолеты',
            'bikes'=>'Мотоциклы',
            'wedding'=>'Свадебные авто',
            'quadro'=>'Квадроциклы',
            'yachts'=>'Яхты',
            'rv'=>'Автодома',
            'airplanes'=>'Самолеты',
            'vans'=>'Микроавтобусы',
            'bicycles'=>'Велосипеды',
            'special-machines' => 'Спецтехника',
            'segways' => 'Сегвеи',
            'scooters' => 'Мотороллеры',
            'limo' => 'Лимузины',
            //'trucks'=>'Грузовые',
        ];
//        asort($countsGr);

        $totalCount = 0;
        $keyGr = 0;
        $grCount = [];

        foreach ($countsGr as $key => $val) {
            switch ($val) {
                case 0:
                    break;
                case 1:
                    $totalCount += $val;
                    $grCount[$key] = [
                        'group' => $keyGr,
                        'count' => 1,
                        'view' => true
                    ];
                    if ($totalCount > 3) {
                        $totalCount = 0;
                        $keyGr++; 
                    }
                    break;
                case 2:
                case 3:
                    $totalCount += $val;
                    $grCount[$key] = [
                        'group' => $keyGr,
                        'count' => 2,
                        'view' => true
                    ];
                    if ($totalCount > 3) {
                        $totalCount = 0;
                        $keyGr++; 
                    }
                    break;
                default:
                    if ($totalCount > 0){
                        $grCount[$key] = [
                            'group' => $keyGr,
                            'count' => 4 - $totalCount,
                            'view' => true
                        ];
                    } else {
                        $grCount[$key] = [
                            'group' => $keyGr,
                            'count' => 4,
                            'view' => true
                        ];
                    }
                    $totalCount = 0;
                    $keyGr++;
                    break;
            } 
        }

        


        $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.weight, g.total DESC');
        $generalTypes = $query->getResult();

        $city = $this->get('session')->get('city');

        $in_city = $city->getUrl();

        $query = $em->createQuery('SELECT COUNT(c.id) FROM AppBundle:Card c');
        $totalCards = $query->getSingleScalarResult();

        $query = $em->createQuery('SELECT SUM(c.views) FROM AppBundle:Card c');
        $totalViews = $query->getSingleScalarResult();

        $query = $em->createQuery('SELECT COUNT(g.id) FROM AppBundle:GeneralType g');
        $totalCategories = $query->getSingleScalarResult();

        $query = $em->createQuery('SELECT COUNT(DISTINCT c.cityId) FROM AppBundle:Card c');
        $totalCities = $query->getSingleScalarResult();

        $total = array(
            'cards' => $totalCards,
            'views' => $totalViews,
            'categories' => $totalCategories,
            'cities' => $totalCities
            );

        $custom_seo = $this->getDoctrine()
            ->getRepository(Seo::class)
            ->findOneBy(['url' => 'main']);


        $main_mark = $this->getDoctrine()
            ->getRepository(CarMark::class)
            ->findOneBy(['id' => 79]);


        $main_model = $this->getDoctrine()
            ->getRepository(CarModel::class)
            ->findOneBy(['carMarkId' => 79,'header' => 'Solaris']);

        $mark_arr = $mm->getExistMarks('',1);
        $mark_arr_sorted = $mark_arr['sorted_marks'];
        $models_in_mark = $mark_arr['models_in_mark'];

        $stat_arr = [
            'url' => $request->getPathInfo(),
            'event_type' => 'visit',
            'page_type' => 'main',
        ];
        $stat->setStat($stat_arr);


//        $translator = new Translator('ru', new MessageSelector());
//
//        $translator->addLoader('pofile', new PoFileLoader());
//        $translator->addResource('pofile', 'messages.en.po', 'en');




        return $this->render('main_page/main.html.twig', [

            'city' => $city,

            'topSlider' => $topSlider,
            'top10Slider' => $top10Slider,
            'top13_10Slider' => $top13_10Slider,
            'top10OwnerSlider' => $getOwnerTop10Slider,
            

            'cars' => $cars,
            // 'heli' => $heli,
            // // 'snow' => $snow,
            // // 'moto' => $moto,
            // 'wedding' => $wedding,
            // 'quadro' => $quadro,
            // // 'trucks' => $trucks,
            // 'yachts' => $yachts,
            // 'bikes' => $bikes,

            'blockCars' => [
                'buggy' => $buggy,
                'bikes' => $bikes,
                'special-machines'=>$specialmachines,
                 'heli' => $heli,
                 'snowride' => $snow,
                'quadro' => $quadro,
                'bicycles'=>$bicycles,
                'wedding' => $wedding,
               'vans' => $vans,
                'airplanes' => $airplanes,
                'yachts' => $yachts,
                'rv'=>$rv,
                'segways' => $segways,
                'scooters' => $scooters,
                'limo' => $limo,

            ],
            'positionCars' => $grCount,
            'positionHeader' => $grHeader,
            'cityId' => $city->getId(),

            'marks' => [],
            'models' => [],
            //'mark' => $mark_arr_sorted[1][0]['mark'],
            'mark' => $main_mark,
            //'model' => $mark_arr_sorted[1][0]['models'][0],
            'model' => $main_model,

            'generalTypes' => $generalTypes,

            'total' => $total,

            'custom_seo' => $custom_seo,

            'mark_arr_sorted' => $mark_arr_sorted,
            'models_in_mark' => $models_in_mark,
            'in_city' => $in_city,
            'lang' => $_SERVER['LANG']

        ]);
    }
}
