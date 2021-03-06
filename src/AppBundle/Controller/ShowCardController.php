<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Deleted;
use AppBundle\Entity\Mark;
use AppBundle\Entity\Rates;
use AppBundle\Entity\SubField;
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
use UserBundle\Entity\Blocking;

class ShowCardController extends Controller
{

    /**
     * @Route("/card/{id}", requirements={"id": "\d+"}, name="showCard")
     */
    public function showCardAction($id, MenuGeneralType $mgt, SubFieldUtils $sf, MenuCity $mc, MenuMarkModel $mm, Request $request, ServiceStat $stat)
    {
        $_t = $this->get('translator');

        $em = $this->get('doctrine')->getManager();

        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($id);

        $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g ORDER BY g.total DESC');
        $generalTypes = $query->getResult();

        if (!$card) {
            $deleted = $this->getDoctrine()
                ->getRepository(Deleted::class)
                ->findOneBy(['cardId' => $id]);
            if ($deleted) {
                $city = $this->getDoctrine()
                    ->getRepository(City::class)
                    ->find($deleted->getCityId());
                $general = $this->getDoctrine()
                    ->getRepository(GeneralType::class)
                    ->find($deleted->getGeneralTypeId());
                $model = $this->getDoctrine()
                    ->getRepository(CarModel::class)
                    ->find($deleted->getModelId());

                $dql = 'SELECT c.id FROM AppBundle:Card c WHERE c.generalTypeId = ' . $general->getId() . ' AND c.cityId = ' . $city->getId() . ' ORDER BY c.dateUpdate DESC';
                $query = $em->createQuery($dql);
                $query->setMaxResults(10);
                foreach ($query->getScalarResult() as $row) $sim_ids[] = $row['id'];
                if (isset($sim_ids)) {
                    $dql = 'SELECT c,p,f FROM AppBundle:Card c LEFT JOIN c.cardPrices p LEFT JOIN c.fotos f WHERE c.id IN (' . implode(",", $sim_ids) . ') ORDER BY c.dateUpdate DESC';
                    $query = $em->createQuery($dql);
                    $similar = $query->getResult();
                } else $similar = false;

                $dql = 'SELECT c.id FROM AppBundle:Card c WHERE c.cityId = ' . $city->getId() . ' ORDER BY c.dateUpdate DESC';
                $query = $em->createQuery($dql);
                $query->setMaxResults(10);
                foreach ($query->getScalarResult() as $row) $sim_ids[] = $row['id'];
                if (isset($sim_ids)) {
                    $dql = 'SELECT c,p,f FROM AppBundle:Card c LEFT JOIN c.cardPrices p LEFT JOIN c.fotos f WHERE c.id IN (' . implode(",", $sim_ids) . ') ORDER BY c.dateUpdate DESC';
                    $query = $em->createQuery($dql);
                    $allincity = $query->getResult();
                } else $allincity = false;

                return $this->render('card/card_deleted.html.twig', [
                    'city' => $city,
                    'cityId' => $city->getId(),
                    'similar' => $similar,
                    'allincity' => $allincity,
                    'general' => $general,
                    'model' => $model,

                    'generalTypes' => $generalTypes,
                    'car_type_id' => $general->getCarTypeIds(),
                    'in_city' => $city->getUrl(),

                ]);
            } else throw $this->createNotFoundException(); //404
        };

        if ($card->getIsActive() == 0) {
            $this->addFlash(
                'notice',
                'Транспорт №' . $card->getId() . ' временно недоступен!<br>Если это ваш транспорт -<br>активируйте свой аккаунт через ссылку в письме регистрации,<br>тогда ваш транспорт станет доступным.'
            );
            return $this->redirectToRoute('homepage');
        }

        if ($card->getUser()->getIsBanned()) return new Response("", 404);

        $views = $this->get('session')->get('views');
        if (!isset($views[$card->getId()])) {
            $views[$card->getId()] = 1;
            $this->get('session')->set('views', $views);
            $card->setViews($card->getViews() + 1);
            $em->persist($card);
            $em->flush();
        }

        $subFields = $sf->getCardSubFields($card);

        $city = $card->getCity();

        if ($card->getVideo() != '') $video = explode("=", $card->getVideo())[1];
        else $video = false;

        if ($card->getStreetView() != '') $streetView = unserialize($card->getStreetView());
        else $streetView = false;


        // ---------------------------- start of similar ----------------------------------

        $dql = 'SELECT c.id FROM AppBundle:Card c WHERE c.cityId=?1 AND c.id != ?2 AND c.modelId=?3 ORDER BY c.dateUpdate DESC'; // -- get by model
        $query = $em->createQuery($dql);
        $query->setParameter(1, $card->getCityId());
        $query->setParameter(2, $card->getId());
        $query->setParameter(3, $card->getModelId());
        $query->setMaxResults(9);

        if (count($query->getScalarResult()) < 1) { // -- get by mark
            $dql = 'SELECT m.id FROM MarkBundle:CarModel m WHERE m.carMarkId=?1';
            $query = $em->createQuery($dql);
            $query->setParameter(1, $card->getMarkModel()->getCarMarkId());
            foreach ($query->getScalarResult() as $row) {
                $model_ids[] = $row['id'];
            }
            $dql = 'SELECT c.id FROM AppBundle:Card c WHERE c.cityId=?1 AND c.id != ?2 AND c.modelId IN ('.implode(",",$model_ids).') ORDER BY c.dateUpdate DESC';
            $query = $em->createQuery($dql);
            $query->setParameter(1, $card->getCityId());
            $query->setParameter(2, $card->getId());
            $query->setMaxResults(9);

            if (count($query->getScalarResult()) < 1) {
                $dql = 'SELECT c.id FROM AppBundle:Card c JOIN c.tariff t WHERE c.cityId=?1 AND c.id != ?2 AND c.generalTypeId = ' . $card->getGeneralTypeId() . ' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
                $query = $em->createQuery($dql);
                $query->setParameter(1, $card->getCityId());
                $query->setParameter(2, $card->getId());
                $query->setMaxResults(9);

                if (count($query->getScalarResult()) < 1) {
                    $dql = 'SELECT c.id FROM AppBundle:Card c JOIN c.tariff t WHERE c.cityId < 1261 AND c.id != ?2 AND c.generalTypeId = ' . $card->getGeneralTypeId() . ' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
                    $query = $em->createQuery($dql);
                    $query->setParameter(2, $card->getId());
                    $query->setMaxResults(9);

                    if (count($query->getScalarResult()) < 1) {
                        $dql = 'SELECT c.id FROM AppBundle:Card c JOIN c.tariff t WHERE c.cityId < 1261 AND c.id != ?2 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
                        $query = $em->createQuery($dql);
                        $query->setParameter(2, $card->getId());
                        $query->setMaxResults(9);
                    }
                }

            }
        }

        foreach ($query->getScalarResult() as $row) {
            $sim_ids[] = $row['id'];
        }
        $sim_ids = implode(",", $sim_ids);

        $dql = 'SELECT c,p,f FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.cardPrices p LEFT JOIN c.fotos f WHERE c.id IN (' . $sim_ids . ') ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
        $query = $em->createQuery($dql);

        $similar = $query->getResult();

        // ---------------------------- end of similar ----------------------------------

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
        if ($card->getServiceTypeId() == 1) $seo['service'] = $_t->trans('Прокат');
        if ($card->getServiceTypeId() == 2) $seo['service'] = $_t->trans('Аренда');
        if ($card->getServiceTypeId() == 3) $seo['service'] = $_t->trans('Аренда с правом выкупа');
//        $seo['type']['singular'] = $general->getChegoSingular();
//        $seo['type']['plural'] = $general->getChegoPlural();
        if ($_SERVER['LANG'] == 'ru') $seo['type']['singular'] = $general->getChegoSingular(); else $seo['type']['singular'] = $general->getUrl();
        if ($_SERVER['LANG'] == 'ru') $seo['type']['plural'] = $general->getChegoPlural(); else $seo['type']['plural'] = $general->getUrl();
        $seo['mark'] = $mark->getHeader();
        $seo['model'] = $model->getHeader();
        $seo['city']['chto'] = $city->getHeader();
        $seo['city']['gde'] = $city->getGde();
        if($seo['city']['gde'] == ' '){
            $seo['city']['gde'] = $city->getHeader();}

        $mark_arr = $mm->getExistMarks('',$mark->getCarTypeId());
        $mark_arr_sorted = $mark_arr['sorted_marks'];
        $mark_arr_typed = $mark_arr['typed_marks'];
        $models_in_mark = $mark_arr['models_in_mark'];

        //dump($city);
        //dump($mark_arr);



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


        $stat->setStat([
            'url' => $request->getPathInfo(),
            'event_type' => 'visit',
            'page_type' => 'card',
            'card_id' => $card->getId(),
            'user_id' => $card->getUserId(),
        ]);


        $bodyType = false;
        foreach($subFields as $sf){
            if($sf['value'] instanceof SubField){
                if($sf['value']->getFieldId() == 3) $bodyType = $sf['value'];
            }
        }

        $is_admin_card = false;
        foreach ($card->getUser()->getInformation() as $ui){
            if($ui->getUiKey() == 'phone'){
                $ph = substr(preg_replace('/[^0-9]/', '', $ui->getUiValue()),1);
                $emz = explode("@",$card->getUser()->getEmail());


                if ($ph == $emz[0]) $is_admin_card = true;

                if (preg_match('/^\d+$/', $emz[0])) $is_admin_card = true;

            }
        }

        $blk = [];
        $blockings = $this->getDoctrine()
                    ->getRepository(Blocking::class)
                    ->findBy([
                        'userId' => $card->getUser()->getId(),
                    ]);
        foreach ($blockings as $b){
            $blk[$b->getVisitorId()] = 1;
        }


        $visitor = $this->get('session')->get('logged_user');

        $phone = true;

        if($visitor){
            $phone = false;
            foreach ($visitor->getInformation() as $inf)
                if($inf->getUiKey() == 'phone') {
                $phone = $inf->getUiValue();
                break;
            }
            //if(isset($phone) and $phone != '') $phone = true;
        }
        $currency = $card -> getCurrency();
        $exchangeRate = $this->getExchangeRates($currency);

        $day = $hour = 0;
        foreach($card->getCardPrices() as $cp){
            if($cp->getPriceId() == 1 and $cp->getValue()!=0) $hour = $cp->getValue();
            if($cp->getPriceId() == 2 and $cp->getValue()!=0) $day = $cp->getValue();
        }



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

            'phone' => $phone,

            'mark_arr_sorted' => $mark_arr_sorted,
            'models_in_mark' => $models_in_mark,

            'generalTypes' => $generalTypes,
            'car_type_id' => $mark->getCarTypeId(),
            'opinions' => $opinions,
            'total_opinions' => $total_opinions,
            'in_city' => $in_city,
            'bodyType' => $bodyType,
            'page_type' => 'card',
            'lang' => (isset($_SERVER['LANG']) and !$_SERVER['LANG']) ? $_SERVER['LANG'] : 'ru',
            'reserved' => $card->getDateRentFinish() > new \DateTime() ? true : false,
            'is_admin_card' => $is_admin_card,

            'blockings' => $blk,
            'price_hour' => $hour,
            'price_day' => $day,
            'currency' => $currency,
            'exchange' => $exchangeRate,


        ]);
    }

    /**
     * @Route("/card_share/{id}", requirements={"id": "\d+"}, name="shareCard")
     */
    public function shareCardAction($id)
    {
        $this->get('session')->set('share', true);

        return $this->redirect('/card/'.$id);
    }

    /**
     * @Route("/rate_handler", name="rate_handler")
     */
    public function rateHandlerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $rate = new Rates();
        $rate->setCardId($request->request->get('card_id'));
        $rate->setRate($request->request->get('r_rate'));
        $rate->setComment($request->request->get('r_text'));

        $em->persist($rate);
        $em->flush();

        $this->addFlash(
            'notice',
            'Спасибо за вашу оценку!<br>'
        );

        return $this->redirect('/card/'.$request->request->get('card_id'));
    }
    public function getExchangeRates($currency)
    {
        $rates = array();
        $id = 'Nominal';
        $id1 = 'Valute';
        $id2 = $currency;
        $id3 = 'Value';

        $rates[] = json_decode(file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js'),true);
        $rate = $rates[0][$id1][$id2][$id3];
        $nominal = $rates[0][$id1][$id2][$id];
        if($currency='RUB'){
            $rate =1;
            $nominal =1;
        }
        return  $rate/$nominal;
    }

}
