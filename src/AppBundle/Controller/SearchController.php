<?php

namespace AppBundle\Controller;

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
use MarkBundle\Entity\CarMark;
use MarkBundle\Entity\CarModel;
use UserBundle\Security\Password;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SearchController extends Controller
{
    /**
     * @Route("/show/{city}/{service}/{general}/{mark}/{model}", name="search")
     */
    public function showCardsAction(
        $city = false, $service = false, $general = false, $mark = false, $model = false, $card = false,
        EntityManagerInterface $em, MenuGeneralType $mgt, MenuCity $mc, MenuMarkModel $mm, Request $request)
    {
        if (strtolower($city) == 'rus') $city = false;
        if ($service == 'all') $service = false;
        if ($general == 'alltypes') $general = false;
        $get = $request->query->all();
        $view = 'grid_view';
        if (isset($get['view'])) $view = $get['view'];



        $city_condition = '';
        $service_condition = '';
        $general_condition = '';
        $mark_condition = '';
        $order = ' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
        $sort = '0';
        if (isset($get['order'])){
            if($get['order'] == 'date_desc') $order = ' ORDER BY c.dateUpdate DESC';
            if($get['order'] == 'date_asc') $order = ' ORDER BY c.dateUpdate ASC';
            $sort = $get['order'];
        }

        $pgtId = 0;
        $gtId = 0;

        if($city){
            $city = $this->getDoctrine()
                ->getRepository(City::class)
                ->findOneBy(['url' => $city]);
            $countryCode = $city->getCountry();
            if($city->getChildren()->isEmpty()){
                $city_condition = 'AND c.cityId = '.$city->getId();
                $cityId = $city->getId();
                $regionId = $city->getParent()->getId();
                $cities = $city->getParent()->getChildren();
            } else {
                $cities = $city->getChildren();
                foreach($cities as $child){
                    $city_ids[] = $child->getId();
                }
                $city_condition = 'AND c.cityId IN ('.implode(',',$city_ids).')';
                $regionId = $city->getId();
                $cityId = 0;
            }
        } else {
            $countryCode = 'RUS';
            $regionId = 0;
            $cities = array();
            $cityId = 0;
        }

        if($service){
            if ($service == 'prokat') $service = 1; else $service = 2;
            $service_condition = ' AND c.serviceTypeId = '.$service;
        }



        if($general and $general != ''){
            $general = $this->getDoctrine()
                ->getRepository(GeneralType::class)
                ->findOneBy(['url' => $general]);

            if($general->getChildren()->isEmpty()){
                $gtId = $general->getId();
                $general_condition = 'AND c.generalTypeId = '.$gtId;
                if (!$general->getParent()) $pgtId = $general->getId();
                else $pgtId = $general->getParent()->getId();
            } else {
                $generals = $general->getChildren();
                foreach($generals as $child){
                    $general_ids[] = $child->getId();
                }
                $general_condition = ' AND c.generalTypeId IN ('.implode(',',$general_ids).','.$general->getId().')';
                $pgtId = $general->getId();
                $gtId = 0;
            }
        }

        if($mark){
            $mark = $this->getDoctrine()
                ->getRepository(CarMark::class)
                ->findOneBy(['header' => $mark, 'carTypeId' => explode(",",$general->getCarTypeIds())]);
            $models = $mm->getModels($mark->getId());
            foreach($models as $child){
                $mark_ids[] = $child->getId();
            }
            $mark_condition = ' AND c.modelId IN ('.implode(',',$mark_ids).')';
            $marks = $mm->getMarks($mark->getCarTypeId());
            //$models = $mm->getModels($mark->getId());
        } else {
            $mark = array('id' => 0,'groupname'=>'', 'header'=>false, 'carTypeId'=>0);
            $marks = array();
            $models = false;
        }

        if($model){
            $model = $this->getDoctrine()
                ->getRepository(CarModel::class)
                ->findOneBy(['header' => $model, 'carMarkId' => $mark->getId()]);

            $mark_condition = ' AND c.modelId = '.$model->getId();
        } else {
            $model = array('groupName' => 'cars','id' => 0,'header'=>false);
            if (!$models) $models = array();
        }

        $dql = 'SELECT count(c.id) FROM AppBundle:Card c WHERE 1=1 '.$city_condition.$service_condition.$general_condition.$mark_condition;
        $query = $em->createQuery($dql);

        $total_cards = $query->getSingleScalarResult();

        if ($request->query->has('page')) $page = $get['page']; else $page = 1;
        if ($request->query->has('onpage')) $cards_per_page = $get['onpage']; else $cards_per_page = 12;
        $pages_in_center = 5;
        $pager_center_start = 2;

        $total_pages = ceil($total_cards/$cards_per_page);
        $start = ($page-1)*$cards_per_page;

        if ($total_pages>($pages_in_center+1)) {
            if(($total_pages-$page) > $pages_in_center) $pager_center_start = $page;
            else $pager_center_start = $total_pages - $pages_in_center;
            if ($pager_center_start == 1) $pager_center_start = 2;
        }

        $dql = 'SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE 1=1 '.$city_condition.$service_condition.$general_condition.$mark_condition.$order;
        $query = $em->createQuery($dql);

        $query->setMaxResults($cards_per_page);
        $query->setFirstResult($start);
//        $cards = new Paginator($query, $fetchJoin = true);
//        $cards = $cards->getIterator();
        $cards = $query->getResult();


        if (isset($get['order'])){
            if($get['order'] == 'price_asc'){
                usort($cards, array('AppBundle\Controller\SearchController','price_sorting_asc'));
            }
            if($get['order'] == 'price_desc'){
                usort($cards, array('AppBundle\Controller\SearchController','price_sorting_desc'));
            }
        }


        return $this->render('search/search_main.html.twig', [

            'cards' => $cards,
            'view' => $view,
            'order' => $sort,
            'get_array' => $get,
            'total_cards' => $total_cards,
            'total_pages' => $total_pages,
            'pager_center_start' => $pager_center_start,
            'pages_in_center' => $pages_in_center,
            'current_page' => $page,
            'onpage' => $cards_per_page,

            'countries' => $mc->getCountry(),
            'countryCode' => $countryCode,
            'regionId' => $regionId,
            'regions' => $mc->getRegion($countryCode),
            'cities' => $cities,
            'cityId' => $cityId,
            'city' => $city,

            'generalTopLevel' => $mgt->getTopLevel(),
            'generalSecondLevel' => $mgt->getSecondLevel($pgtId),
            'pgtid' => $pgtId,
            'gtid' => $gtId,
            'general' => $general,

            'mark_groups' => $mm->getGroups(),
            'mark' => $mark,
            'model' => $model,
            'marks' => $marks,
            'models' => $models,

        ]);
    }

    private static function price_sorting_asc($a, $b)
    {
        $p1 = 0;
        $p2 = 0;

        foreach($a->getCardPrices() as $ap){
            if($ap->getPriceId() == 2) $p1 = $ap->getValue();
        }

        foreach($b->getCardPrices() as $bp){
            if($bp->getPriceId() == 2) $p2 = $bp->getValue();
        }

        if ($p1 == $p2) {
            return 0;
        }
        return ($p1 < $p2) ? -1 : 1;
    }

    private static function price_sorting_desc($a, $b)
    {
        $p1 = 0;
        $p2 = 0;

        foreach($a->getCardPrices() as $ap){
            if($ap->getPriceId() == 2) $p1 = $ap->getValue();
        }

        foreach($b->getCardPrices() as $bp){
            if($bp->getPriceId() == 2) $p2 = $bp->getValue();
        }

        if ($p1 == $p2) {
            return 0;
        }
        return ($p1 > $p2) ? -1 : 1;
    }

}


///**
// * @Route("/translit")
// */
//public function translitAction()
//{
//
//    $cities = $this->getDoctrine()
//        ->getRepository(City::class)
//        ->findAll();
//
//    $em = $this->getDoctrine()->getManager();
//
//    foreach($cities as $city){
//        $city->setUrl($this->translit($city->getHeader()));
//        $em->persist($city);
//    }
//
//    $em->flush();
//
//    return new Response();
//}

//private function translit($string){
//    $converter = array(
//        'а' => 'a',   'б' => 'b',   'в' => 'v',
//        'г' => 'g',   'д' => 'd',   'е' => 'e',
//        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
//        'и' => 'i',   'й' => 'y',   'к' => 'k',
//        'л' => 'l',   'м' => 'm',   'н' => 'n',
//        'о' => 'o',   'п' => 'p',   'р' => 'r',
//        'с' => 's',   'т' => 't',   'у' => 'u',
//        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
//        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
//        'ь' => '',    'ы' => 'y',   'ъ' => '',
//        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
//
//        'А' => 'A',   'Б' => 'B',   'В' => 'V',
//        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
//        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
//        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
//        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
//        'О' => 'O',   'П' => 'P',   'Р' => 'R',
//        'С' => 'S',   'Т' => 'T',   'У' => 'U',
//        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
//        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
//        'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
//        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
//        ' ' => '_',   '.' => '.',   '«' => '',
//        '»' => '',   '"' => '', '№' => 'N', '“'=>'', '”'=>''
//    );
//    return strtr($string, $converter);
//}