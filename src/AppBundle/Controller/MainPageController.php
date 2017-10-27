<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Seo;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuMarkModel;
use AppBundle\Menu\ServiceStat;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Card;
use AppBundle\Entity\City;
use AppBundle\Entity\GeneralType;
use Symfony\Component\HttpFoundation\Request;

class MainPageController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(MenuGeneralType $mgt, MenuCity $mc, EntityManagerInterface $em, MenuMarkModel $mm, Request $request, ServiceStat $stat)
    {

        $topSlider = $this->getDoctrine()
            ->getRepository(Card::class)
            ->getTopSlider();

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

        $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.total DESC');
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



        $mark_arr = $mm->getExistMarks('',1);
        $mark_arr_sorted = $mark_arr['sorted_marks'];
        $models_in_mark = $mark_arr['models_in_mark'];

        $stat_arr = [
            'url' => $request->getPathInfo(),
            'event_type' => 'visit',
            'page_type' => 'main',
        ];
        $stat->setStat($stat_arr);

        return $this->render('main_page/main.html.twig', [

            'city' => $city,

            'topSlider' => $topSlider,
            'cars' => $cars,
            'trucks' => $trucks,
            'heli' => $heli,
            'bicycles' => $bicycles,
            'snow' => $snow,
            'yachts' => $yachts,
            'moto' => $moto,
            'quad' => $quad,

            'cityId' => $city->getId(),

            'marks' => [],
            'models' => [],
            'mark' => $mark_arr_sorted[1][0]['mark'],
            'model' => $mark_arr_sorted[1][0]['models'][0],

            'generalTypes' => $generalTypes,

            'total' => $total,

            'custom_seo' => $custom_seo,

            'mark_arr_sorted' => $mark_arr_sorted,
            'models_in_mark' => $models_in_mark,
            'in_city' => $in_city,


        ]);
    }
}
