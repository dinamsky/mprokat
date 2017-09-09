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
use UserBundle\Security\Password;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

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

        if($general){
            $general = $this->getDoctrine()
                ->getRepository(GeneralType::class)
                ->findOneBy(['url' => $general]);
            if($general->getChildren()->isEmpty()){
                $gtId = $general->getId();
                $general_condition = 'AND c.generalTypeId = '.$gtId;
                if (!$general->getParent()) $pgtId = 0;
                else $pgtId = $general->getParent()->getId();
            } else {
                $generals = $general->getChildren();
                foreach($generals as $child){
                    $general_ids[] = $child->getId();
                }
                $general_condition = ' AND c.generalTypeId IN ('.implode(',',$general_ids).')';
                $pgtId = $general->getId();
                $gtId = 0;
            }
        }

        if($mark){
            $mark = $this->getDoctrine()
                ->getRepository(Mark::class)
                ->findOneBy(['header' => $mark, 'parentId' => NULL]);
            $models = $mark->getChildren();
            foreach($models as $child){
                $mark_ids[] = $child->getId();
            }
            $mark_condition = ' AND c.modelId IN ('.implode(',',$mark_ids).')';
            $marks = $mm->getMarks($mark->getGroupName());
        } else {
            $mark = array('id' => 0);
            $marks = array();
        }

        if($model){
            $model = $this->getDoctrine()
                ->getRepository(Mark::class)
                ->findOneBy(['header' => $model]);

            $mark_condition = ' AND c.modelId = '.$model->getId();
        } else {
            $model = array('groupName' => 'cars','id' => 0);
            $models = array();
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
        $cards = $query->getResult();

        return $this->render('search/search_main.html.twig', [

            'cards' => $cards,
            'view' => $view,
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

            'mark_groups' => $mm->getGroups(),
            'mark' => $mark,
            'model' => $model,
            'marks' => $marks,
            'models' => $models,

        ]);
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



//$get = $request->query->all();
//
//if (!$request->query->has('countryCode')) {
//    $get['countryCode'] = 'RUS';
//    $get['regionId'] = 0;
//    $get['cityId'] = 0;
//}
//
//if (!$request->query->has('pgtId')) {
//    $get['pgtId'] = 0;
//    $get['gtId'] = 0;
//}
//
//$view = 'grid_view';
//if ($request->query->has('view') and $get['view'] != '') $view = $get['view'];
//
//$cityId = 'RUS';
//if ($request->query->has('countryCode') and $get['countryCode'] != 0) $cityId = $get['countryCode'];
//if ($request->query->has('regionId') and $get['regionId'] != 0) $cityId = $get['regionId'];
//if ($request->query->has('cityId') and $get['cityId'] != 0) $cityId = $get['cityId'];
//
//$generalTypeId = 1;
//if ($request->query->has('pgtId') and $get['pgtId'] != 0) $generalTypeId = $get['pgtId'];
//if ($request->query->has('gtId') and $get['gtId'] != 0) $generalTypeId = $get['gtId'];
//
//if ($request->query->has('order') and $get['order'] != ''){
//    $order = '';
//} else {
//    $order = ' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
//}
//
//
//if($request->query->has('modelId') and $get['modelId'] != '') {
//
//    if ($get['modelId'] == 0){
//        $mark = $mm->getMark($get['mark']);
//        $marks = $mm->getMarks($mark->getGroupName());
//        $models = $mark->getChildren();
//        foreach($mm->getModels((int)$get['mark']) as $model){
//            $model_ids[] = $model->getId();
//        }
//        $model = new Mark();
//        $model->setTempId(0);
//        $model_qry = 'AND c.modelId IN ( '.implode(",",$model_ids). ')';
//    } else {
//        $model = $this->getDoctrine()
//            ->getRepository(Mark::class)
//            ->find((int)$get['modelId']);
//        $marks = $mm->getMarks($model->getGroupName());
//        $mark = $model->getParent();
//        $models = $mark->getChildren();
//        $model_qry = 'AND c.modelId = '.(int)$get['modelId'];
//    }
//
//} else {
//    $marks = $mm->getMarks('cars');
//    $models = array();
//    $mark = new Mark();
//    $mark->setTempId(0);
//    $model = new Mark();
//    $model->setTempId(0);
//    $model_qry = '';
//}
//
//$gt = $mgt->getGeneralTypeMenu();
//$gt_ids = $mgt->getArrayOfChildIdsOfGeneralTypeMenu($gt, $generalTypeId);
//$countries = array_keys($mc->getCountry());