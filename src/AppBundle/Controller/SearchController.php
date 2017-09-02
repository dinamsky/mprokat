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


        $gt = $mgt->getGeneralTypeMenu();
        $gt_ids = $mgt->getArrayOfChildIdsOfGeneralTypeMenu($gt, $generalTypeId);
        $countries = array_keys($mc->getCountry());

        if(in_array($cityId, $countries)){
            $dql = 'SELECT c FROM AppBundle:Card c WHERE c.generalTypeId IN ( :ids ) AND c.cityId BETWEEN ?1 AND ?2';
            $range = $mc->getCountryIdRange($cityId);
            $query = $em->createQuery($dql);
            $query->setParameter(1, $range['first']);
            $query->setParameter(2, $range['last']);
            $query->setParameter('ids', $gt_ids);

        } else {
            $dql = 'SELECT c FROM AppBundle:Card c WHERE c.generalTypeId IN ( :ids ) AND c.cityId IN ( :cities )';
            $query = $em->createQuery($dql);
            $query->setParameter('cities', array_merge($mc->getCity($cityId),$mc->getCities($cityId)));
            $query->setParameter('ids', $gt_ids);
        }

        $cards = $query->getResult();


        return $this->render('search/search_main.html.twig', [

            'cards' => $cards,
            'view' => $view,
            'get_array' => $get,

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