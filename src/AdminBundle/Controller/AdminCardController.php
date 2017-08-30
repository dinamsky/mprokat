<?php

namespace AdminBundle\Controller;

use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuMarkModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminCardController extends Controller
{
    /**
     * @Route("/adminCards", name="adminCards")
     */
    public function adminCardsAction(EntityManagerInterface $em, MenuGeneralType $mgt, MenuCity $mc, MenuMarkModel $mm, Request $request)
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


        return $this->render('@Admin/AdminCardController/admin_cards.html.twig', [

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
