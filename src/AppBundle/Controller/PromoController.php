<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Promo;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuMarkModel;
use AppBundle\Menu\ServiceStat;
use Doctrine\ORM\EntityManagerInterface;
use MarkBundle\Entity\CarModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface as em;
use AppBundle\Entity\Card;
use AppBundle\Entity\CardField;
use AppBundle\Entity\City;
use AppBundle\Entity\GeneralType;
use AppBundle\SubFields\SubFieldUtils;
use Symfony\Component\HttpFoundation\Cookie;

class PromoController extends Controller
{

    /**
     * @Route("/promo", name="promo")
     */
    public function showCardAction(MenuGeneralType $mgt, SubFieldUtils $sf, MenuCity $mc, MenuMarkModel $markmenu, Request $request, ServiceStat $stat)
    {


        $em = $this->get('doctrine')->getManager();


        $city = $this->get('session')->get('city');


        $in_city = $city->getUrl();


//        $stat->setStat([
//            'url' => $request->getPathInfo(),
//            'event_type' => 'visit',
//            'page_type' => 'card',
//            'card_id' => '',
//            'user_id' => '',
//        ]);


        $promo = [];
        $dql = "SELECT p FROM AppBundle:Promo p WHERE p.pId=1 AND p.pKey != 'opinion'";
        $query = $em->createQuery($dql);
        foreach($query->getResult() as $row){
            $promo[$row->getPKey()] = str_replace("{{ city.gde }}", $city->getGde(),$row->getPValue());
        }

        $dql = "SELECT p FROM AppBundle:Promo p WHERE p.pId=1 AND p.pKey = 'opinion'";
        $query = $em->createQuery($dql);
        foreach($query->getResult() as $row){

            $r = json_decode($row->getPValue(),true);
            $r['id'] = $row->getId();
            $opinions[$r['sort']] = $r;
        }
        ksort($opinions);

        return $this->render('promo/promo.html.twig', [

            'city' => $city,

            'cityId' => $city->getId(),

            'in_city' => $in_city,

            'lang' => $_SERVER['LANG'],

            'promo' => $promo,
            'opinions' => $opinions,

            'mark_groups' => $markmenu->getGroups(),
            'marks' => $markmenu->getMarks(1),

        ]);
    }

    /**
     * @Route("/promo_ajax_counter")
     */
    public function countAction(Request $request)
    {
        $error = false;
        $city = $this->get('session')->get('city');
        $modelId = $request->request->get('modelId');

        $em = $this->get('doctrine')->getManager();

        $dql = 'SELECT c,f,p FROM AppBundle:Card c LEFT JOIN c.fotos f LEFT JOIN c.cardPrices p WHERE c.generalTypeId = 2 AND c.modelId = '.(int)$modelId.' AND c.cityId = '.$city->getId();
        $query = $em->createQuery($dql);
        $result = $query->getResult();

        $i = 0;
        $p = 0;
        foreach($result as $r){
            foreach ($r->getCardPrices() as $pr){
                if ($pr->getPriceId() == 2) $p = $p + $pr->getValue();
            }
            if($i<11) $for_slider[] = $r;
            $i++;
        }

        if(count($result) == 0) {
            $result = [1];
            $slider = '';
            $error = true;
        } else {
            $slider = $this->renderView('promo/promo_slider.html.twig',['slider'=>$for_slider]);
        }

        $res = array(
            'price' => round($p/count($result),0),
            'slider' => $slider,
            'error' => $error
        );

        return new Response(json_encode($res));
    }

}
