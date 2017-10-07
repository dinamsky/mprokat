<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Mark;
use AppBundle\Entity\SubField;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuMarkModel;
use Doctrine\ORM\EntityManagerInterface;
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

class ShowCardController extends Controller
{

    /**
     * @Route("/card/{id}", requirements={"id": "\d+"}, name="showCard")
     */
    public function showCardAction($id, MenuGeneralType $mgt, SubFieldUtils $sf, MenuCity $mc, MenuMarkModel $mm)
    {
        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($id);

        if ($card->getUser()->getIsBanned()) return new Response("",404);

        $views = $this->get('session')->get('views');
        if (!isset($views[$card->getId()])) {
            $views[$card->getId()] = 1;
            $this->get('session')->set('views', $views);
            $card->setViews($card->getViews() + 1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($card);
            $em->flush();
        }

        $subFields = $sf->getCardSubFields($card);

        $city = $card->getCity();

        if ($card->getVideo() != '') $video = explode("=",$card->getVideo())[1];
        else $video = false;

        if ($card->getStreetView() != '') $streetView = unserialize($card->getStreetView());
        else $streetView = false;

        $em = $this->get('doctrine')->getManager();
        $dql = 'SELECT c.id FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = '.$card->getGeneralTypeId().' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';

        $query = $em->createQuery($dql);
        $query->setMaxResults(10);
        foreach($query->getScalarResult() as $row){
            $sim_ids[] = $row['id'];
        }
        $sim_ids = implode(",",$sim_ids);


        $dql = 'SELECT c,p,f FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.cardPrices p LEFT JOIN c.fotos f WHERE c.id IN ('.$sim_ids.') ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';

        $query = $em->createQuery($dql);

        $similar = $query->getResult();




        $model = $mm->getModel($card->getModelId());
        $mark = $mm->getMark($model->getCarMarkId());
        $models = $mm->getModels($model->getCarMarkId());
        $marks = $mm->getMarks($model->getCarTypeId());

        $user_foto = false;
        foreach ($card->getUser()->getInformation() as $info){
           if($info->getUiKey() == 'foto' and $info->getUiValue()!='') $user_foto =  '/assets/images/users/t/'.$info->getUiValue().'.jpg';
        }


        $general = $this->getDoctrine()
            ->getRepository(GeneralType::class)
            ->find($card->getGeneralTypeId());


        $pgtid = $card->getGeneralType()->getParentId();
        if($pgtid == null) $pgtid = $card->getGeneralTypeId();

        $mainFoto = '';
        foreach($card->getFotos() as $foto){
            if ($foto->getIsMain()) $mainFoto = '/assets/images/cards/'.$foto->getFolder().'/'.$foto->getId().'.jpg';
        }


        $seo = [];
        if ($card->getServiceTypeId() == 1) $seo['service'] = 'Прокат';
        if ($card->getServiceTypeId() == 2) $seo['service'] = 'Аренда';
        $seo['type']['singular'] = $general->getChegoSingular();
        $seo['type']['plural'] = $general->getChegoPlural();
        $seo['mark'] = $mark->getHeader();
        $seo['model'] = $model->getHeader();
        $seo['city']['chto'] = $city->getHeader();
        $seo['city']['gde'] = $city->getGde();

        $mark_arr = $mm->getExistMarks('',$mark->getCarTypeId());
        $mark_arr_sorted = $mark_arr['sorted_marks'];
        $mark_arr_typed = $mark_arr['typed_marks'];
        $models_in_mark = $mark_arr['models_in_mark'];

        //dump($city);
        //dump($mark_arr);

        $query = $em->createQuery('SELECT c FROM AppBundle:City c WHERE c.total > 0 ORDER BY c.total DESC, c.header ASC');
        $popular_city = $query->getResult();

        $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g ORDER BY g.total DESC');
        $generalTypes = $query->getResult();

        $star = 0;
        $total_opinions = 0;
        foreach($card->getOpinions() as $op){
            $star = $star + $op->getStars();
            $total_opinions++;
        }
        if ($total_opinions > 0) $opinions = round($star/$total_opinions, 1);
        else $opinions = 0;


        if ($this->get('session')->has('city')){
            $in_city = $this->get('session')->get('city');
            if(is_array($in_city)) $in_city = $in_city[0]->getUrl();
            else $in_city = $city->getUrl();
        }
        else $in_city = $city->getUrl();

        return $this->render('card/card_show.html.twig', [

            'card' => $card,
            'streetView' => $streetView,
            'video' => $video,

            'sub_fields' =>$subFields,

//            'general_type' => $card->getGeneralTypeId(),
            'city' => $city,

//            'countries' => $mc->getCountry(),
//            'countryCode' => $city->getCountry(),
//            'regionId' => $city->getParentId(),
//            'regions' => $mc->getRegion($city->getCountry()),
//            'cities' => $mc->getCities($city->getParentId()),
            'cityId' => $card->getCityId(),

//            'generalTopLevel' => $mgt->getTopLevel(),
//            'generalSecondLevel' => $mgt->getSecondLevel($card->getGeneralType()->getParentId()),
//            'pgtid' => $pgtid,
//            'gtid' => $card->getGeneralTypeId(),
            'similar' => $similar,

            'mark_groups' => $mm->getGroups(),
            'mark' => $mark,
            'model' => $model,
            'marks' => $marks,
            'models' => $models,
            'general' => $general,

            'user_foto' => $user_foto,
            'mainFoto' => $mainFoto,
            'seo' => $seo,

            'popular_city' => $popular_city,

            'mark_arr_sorted' => $mark_arr_sorted,
            'models_in_mark' => $models_in_mark,

            'generalTypes' => $generalTypes,
            'car_type_id' => $mark->getCarTypeId(),
            'opinions' => $opinions,
            'total_opinions' => $total_opinions,
            'in_city' => $in_city

        ]);
    }
}
