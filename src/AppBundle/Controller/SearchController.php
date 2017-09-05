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
     * @Route("/search", name="search")
     */
    public function showCardsByGeneralTypeAction(EntityManagerInterface $em, MenuGeneralType $mgt, MenuCity $mc, MenuMarkModel $mm, Request $request)
    {

        $get = $request->query->all();

        if (!$request->query->has('countryCode')) {
            $get['countryCode'] = 'RUS';
            $get['regionId'] = 0;
            $get['cityId'] = 0;
        }

        if (!$request->query->has('pgtId')) {
            $get['pgtId'] = 0;
            $get['gtId'] = 0;
        }

        $view = 'grid_view';
        if ($request->query->has('view') and $get['view'] != '') $view = $get['view'];

        $cityId = 'RUS';
        if ($request->query->has('countryCode') and $get['countryCode'] != 0) $cityId = $get['countryCode'];
        if ($request->query->has('regionId') and $get['regionId'] != 0) $cityId = $get['regionId'];
        if ($request->query->has('cityId') and $get['cityId'] != 0) $cityId = $get['cityId'];

        $generalTypeId = 1;
        if ($request->query->has('pgtId') and $get['pgtId'] != 0) $generalTypeId = $get['pgtId'];
        if ($request->query->has('gtId') and $get['gtId'] != 0) $generalTypeId = $get['gtId'];

        if ($request->query->has('order') and $get['order'] != ''){
            $order = '';
        } else {
            $order = ' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
        }


        if($request->query->has('modelId') and $get['modelId'] != '') {

            if ($get['modelId'] == 0){
                $mark = $mm->getMark($get['mark']);
                $marks = $mm->getMarks($mark->getGroupName());
                $models = $mark->getChildren();
                foreach($mm->getModels((int)$get['mark']) as $model){
                    $model_ids[] = $model->getId();
                }
                $model = new Mark();
                $model->setTempId(0);
                $model_qry = 'AND c.modelId IN ( '.implode(",",$model_ids). ')';
            } else {
                $model = $this->getDoctrine()
                    ->getRepository(Mark::class)
                    ->find((int)$get['modelId']);
                $marks = $mm->getMarks($model->getGroupName());
                $mark = $model->getParent();
                $models = $mark->getChildren();
                $model_qry = 'AND c.modelId = '.(int)$get['modelId'];
            }

        } else {
            $marks = $mm->getMarks('cars');
            $models = array();
            $mark = new Mark();
            $mark->setTempId(0);
            $model = new Mark();
            $model->setTempId(0);
            $model_qry = '';
        }

        $gt = $mgt->getGeneralTypeMenu();
        $gt_ids = $mgt->getArrayOfChildIdsOfGeneralTypeMenu($gt, $generalTypeId);
        $countries = array_keys($mc->getCountry());

        if(in_array($cityId, $countries)){
            $dql = 'SELECT count(c.id) FROM AppBundle:Card c WHERE c.generalTypeId IN ( :ids ) AND c.cityId BETWEEN ?1 AND ?2 '.$model_qry;
            $range = $mc->getCountryIdRange($cityId);
            $query = $em->createQuery($dql);
            $query->setParameter(1, $range['first']);
            $query->setParameter(2, $range['last']);
            $query->setParameter('ids', $gt_ids);

        } else {
            $dql = 'SELECT count(c.id) FROM AppBundle:Card c WHERE c.generalTypeId IN ( :ids ) AND c.cityId IN ( :cities ) '.$model_qry;
            $query = $em->createQuery($dql);
            $query->setParameter('cities', array_merge($mc->getCity($cityId),$mc->getCities($cityId)));
            $query->setParameter('ids', $gt_ids);
        }

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



        if(in_array($cityId, $countries)){
            $dql = 'SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId IN ( :ids ) AND c.cityId BETWEEN ?1 AND ?2 '.$model_qry.$order;
            $range = $mc->getCountryIdRange($cityId);
            $query = $em->createQuery($dql);
            $query->setParameter(1, $range['first']);
            $query->setParameter(2, $range['last']);
            $query->setParameter('ids', $gt_ids);

        } else {
            $dql = 'SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId IN ( :ids ) AND c.cityId IN ( :cities ) '.$model_qry.$order;
            $query = $em->createQuery($dql);
            $query->setParameter('cities', array_merge($mc->getCity($cityId),$mc->getCities($cityId)));
            $query->setParameter('ids', $gt_ids);
        }
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
            'countryCode' => $get['countryCode'],
            'regionId' => $get['regionId'],
            'regions' => $mc->getRegion($get['countryCode']),
            'cities' => $mc->getCities($get['regionId']),
            'cityId' => $get['cityId'],

            'generalTopLevel' => $mgt->getTopLevel(),
            'generalSecondLevel' => $mgt->getSecondLevel($get['pgtId']),
            'pgtid' => $get['pgtId'],
            'gtid' => $get['gtId'],

            'mark_groups' => $mm->getGroups(),
            'mark' => $mark,
            'model' => $model,
            'marks' => $marks,
            'models' => $models,

        ]);
    }

}


//$query = $em->createQuery('SELECT f, t FROM AppBundle:CardField f JOIN f.fieldType t WHERE f.generalTypeId = ?1');
//$query->setParameter(1, $id);
//$fields = $query->getResult();
//
//
//foreach($result as $row) {
//    $ids[] = $row->getId();
//    $mark_id = $row->getMarkModel()->getId();
//    $markIds[$mark_id] = $mark_id;
//}
//
//
//
//
//
//$city = array(
//    'id'=> $cityId,
//    'regions' => $regions,
//    'object' => $city
//);


///**
// * @var $field CardField
// */
//foreach ($fields as $key=>$field) {
//
//    //$query_string = 'SELECT i FROM AppBundle:'.$field->getFieldType()->getStorageType().' i JOIN i.subField s WHERE i.cardFieldId = :fieldId AND i.cardId IN ( :ids )';
//    $query_string = 'SELECT i FROM AppBundle:'.$field->getFieldType()->getStorageType().' i WHERE i.cardId IN ( :ids ) AND i.cardFieldId=?1';
//    $query = $em->createQuery($query_string);
//    $query->setParameter(1, $field->getFieldType()->getId());
//    $query->setParameter('ids', $ids);
//
//    $fresult = $query->getResult();
//    if($field->getFieldType()->getFormElementType() == 'ajaxMenu') {
//        foreach ($fresult as $row) {
//            $values[] = $this->getDoctrine()
//                ->getRepository(SubField::class)
//                ->find($row->getValue());
//        }
//    } else {
//        $values = $fresult;
//    }
//
//    $fields[$key]->setSelects($values);
//
//}
//
///**
// * @var $city City
// */
//if(in_array($cityId, $countries)) {
//    $city = array();
//    $regions = $mc->getRegion($cityId);
//}
//else {
//    $city = $mc->getCity($cityId)[0];
//    $city->getChildren()->initialize();
//    if (NULL != $city->getParent()) $city->getParent()->getChildren()->initialize();
//    $regions = $mc->getRegion($mc->getCity($cityId)[0]->getCountry());
//}