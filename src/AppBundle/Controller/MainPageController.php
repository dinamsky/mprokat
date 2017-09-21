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

            $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
                ->where('c.header LIKE :geoname')
                ->andWhere('c.parentId IS NOT NULL')
                ->setParameter('geoname', '%'.$geo['city'].'%')
                ->getQuery()
                ->getResult();

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

        $custom_seo = $this->getDoctrine()
            ->getRepository(Seo::class)
            ->findOneBy(['url' => 'main']);

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

            'total' => $total,

            'custom_seo' => $custom_seo
        ]);
    }
}
