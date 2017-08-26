<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Mark;
use AppBundle\Entity\SubField;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuMarkModel;
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
    public function indexAction(MenuGeneralType $mgt, MenuCity $mc)
    {
        return $this->render('default/index.html.twig', [
            'general_types' => $mgt->getGeneralTypeMenu(),
            'cards' => '',
            'custom_fields' => '',
            'countries' => $mc->getCountry(),
            'general_type' => 1,
            'city' => array(
                'id' => 'RUS',
                'regions' => array()
            ),
            'mark_model' => array()
        ]);
    }

    /**
     * @Route("/type/{cityId}/{id}", name="general_type")
     */
    public function showCardsByGeneralTypeAction($cityId = 0, $id = 0, em $em, MenuGeneralType $mgt, MenuCity $mc, MenuMarkModel $mm, Request $request)
    {


        //$post = $request->request;

//        $cityId = $post->get('cityId');
//        $general_type = $post->get('general_type');

//        if(isset($post['mark'])){
//            // do the count
//        }


        $gt = $mgt->getGeneralTypeMenu();
        $gt_ids = $mgt->getArrayOfChildIdsOfGeneralTypeMenu($gt, $id);
        $countries = array_keys($mc->getCountry());
        // get cars




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

        $result = $query->getResult();

        $query = $em->createQuery('SELECT f, t FROM AppBundle:CardField f JOIN f.fieldType t WHERE f.generalTypeId = ?1');
        $query->setParameter(1, $id);
        $fields = $query->getResult();


        foreach($result as $row) {
            $ids[] = $row->getId();
            $mark_id = $row->getMarkModel()->getId();
            $markIds[$mark_id] = $mark_id;
        }



        /**
         * @var $field CardField
         */
        foreach ($fields as $key=>$field) {

            //$query_string = 'SELECT i FROM AppBundle:'.$field->getFieldType()->getStorageType().' i JOIN i.subField s WHERE i.cardFieldId = :fieldId AND i.cardId IN ( :ids )';
            $query_string = 'SELECT i FROM AppBundle:'.$field->getFieldType()->getStorageType().' i WHERE i.cardId IN ( :ids ) AND i.cardFieldId=?1';
            $query = $em->createQuery($query_string);
            $query->setParameter(1, $field->getFieldType()->getId());
            $query->setParameter('ids', $ids);

            $fresult = $query->getResult();
            if($field->getFieldType()->getFormElementType() == 'ajaxMenu') {
                foreach ($fresult as $row) {
                    $values[] = $this->getDoctrine()
                        ->getRepository(SubField::class)
                        ->find($row->getValue());
                }
            } else {
                $values = $fresult;
            }

            $fields[$key]->setSelects($values);

        }

        /**
         * @var $city City
         */
        if(in_array($cityId, $countries)) {
            $city = array();
            $regions = $mc->getRegion($cityId);
        }
        else {
            $city = $mc->getCity($cityId)[0];
            $city->getChildren()->initialize();
            if (NULL != $city->getParent()) $city->getParent()->getChildren()->initialize();
            $regions = $mc->getRegion($mc->getCity($cityId)[0]->getCountry());
        }

        $city = array(
            'id'=> $cityId,
            'regions' => $regions,
            'object' => $city
        );


        return $this->render('default/index.html.twig', [
            'general_types' => $mgt->getGeneralTypeMenu(),
            'cards' => $result,
            'custom_fields' => $fields,
            'countries' => $mc->getCountry(),
            'general_type' => $id,
            'city' => $city,
            //'mark_model' => $mm->getLimitedArray($markIds),

        ]);
    }


    /**
     * @Route("/card/{id}", requirements={"id": "\d+"})
     */
    public function showCardAction($id, MenuGeneralType $mgt, SubFieldUtils $sf, MenuCity $mc)
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

        return $this->render('card/card_show.html.twig', [
            'general_types' => $mgt->getGeneralTypeMenu(),
            'card' => $card,
            'custom_fields' => '',
            'sub_fields' =>$subFields,
            'countries' => $mc->getCountry(),
            'general_type' => $card->getGeneralTypeId(),
            'city' => $city
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
