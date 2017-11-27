<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\City;
use AppBundle\Entity\Color;
use AppBundle\Entity\FieldInteger;
use AppBundle\Entity\FieldType;
use AppBundle\Entity\GeneralType;
use AppBundle\Entity\Mark;
use AppBundle\Entity\Seo;
use AppBundle\Entity\State;
use AppBundle\Entity\CardField;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuMarkModel;
use AppBundle\Menu\MenuSubFieldAjax;
use AppBundle\Menu\ServiceStat;
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
     * @Route("/rent/{city}/{service}/{general}/{mark}/{model}", name="search")
     */
    public function showCardsAction(
        $city = false, $service = false, $general = false, $mark = false, $model = false, $card = false,
        EntityManagerInterface $em, MenuGeneralType $mgt, MenuCity $mc, MenuMarkModel $mm, Request $request, ServiceStat $stat)
    {

        $mobileDetector = $this->get('mobile_detect.mobile_detector');

        if (strtolower($city) == 'rus') $city = false;
        if ($service == 'all') $service = false;
        if ($general == 'alltypes') $general = false;
        $get = $request->query->all();
        $view = 'grid_view';
        if (isset($get['view'])) $view = $get['view'];


        $_t = $this->get('translator');

        $is_body = false;
        $city_condition = '';
        $service_condition = '';
        $general_condition = '';
        $mark_condition = '';
        $body_condition = '';
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

            if($this->get('session')->get('city')->getUrl() != $city) {
                $city = $this->getDoctrine()
                    ->getRepository(City::class)
                    ->findOneBy(['url' => $city]);
                if(!$city) throw $this->createNotFoundException(); //404
                $this->get('session')->set('city', $city);
            } else $city = $this->get('session')->get('city');

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
            $city = new City();
            $city->setHeader('Россия');
            $city->setUrl('rus');
            $city->setGde('России');
        }

        if($service){
            if ($service == 'prokat') $service = 1;
            if ($service == 'arenda') $service = 2;
            if ($service == 'leasebuyout') $service = 3;
            $service_condition = ' AND c.serviceTypeId = '.$service;
        }



        if($general and $general != ''){
            $general = $this->getDoctrine()
                ->getRepository(GeneralType::class)
                ->findOneBy(['url' => $general]);

            if($general) {
                if ($general->getChildren()->isEmpty()) {
                    $gtId = $general->getId();
                    $general_condition = 'AND c.generalTypeId = ' . $gtId;
                    if (!$general->getParent()) $pgtId = $general->getId();
                    else $pgtId = $general->getParent()->getId();
                } else {
                    $generals = $general->getChildren();
                    foreach ($generals as $child) {
                        $general_ids[] = $child->getId();
                    }
                    $general_condition = ' AND c.generalTypeId IN (' . implode(',', $general_ids) . ',' . $general->getId() . ')';
                    $pgtId = $general->getId();
                    $gtId = 0;
                }
            } else throw $this->createNotFoundException();
        }


        if ($general and $general->getUrl() == 'cars') {
            $query = $em->createQuery('SELECT s FROM AppBundle:SubField s WHERE s.fieldId = 3 AND s.parentId = 1');
            $bodyTypes = $query->getResult();
            foreach($bodyTypes as $bt){
                $bodyTypeArray[] = $bt->getUrl();
                $bodyTypeEntity[$bt->getUrl()] = $bt;
            }
        } else $bodyTypes = false;


        if(isset($bodyTypeArray) and (($mark and in_array($mark,$bodyTypeArray)) or ($model and in_array($model,$bodyTypeArray)))){
            if (in_array($mark,$bodyTypeArray)) $bt_value = $bodyTypeEntity[$mark]->getId();
            if (in_array($model,$bodyTypeArray)) $bt_value = $bodyTypeEntity[$model]->getId();
            $query = $em->createQuery('SELECT f.cardId FROM AppBundle:FieldInteger f WHERE f.value = '.$bt_value.' AND f.cardFieldId = 3');

            $result = $query->getScalarResult();
            $bt_ids = [0];
            foreach ($result as $row){
                $bt_ids[] = $row['cardId'];
            }
            $body_condition = 'AND c.id IN ('.implode(",",$bt_ids).')';



            if($body_condition != '')

            $query = $em->createQuery('SELECT s FROM AppBundle:SubField s WHERE s.url = ?1');
            if (in_array($mark,$bodyTypeArray)) {
                $query->setParameter(1, $mark);
                $is_body = $mark;
                $mark = false;
            }
            if (in_array($model,$bodyTypeArray)) {
                $query->setParameter(1, $model);
                $is_body = $model;
                $model = false;
            }
            $bodyType = $query->getResult()[0];
        }



        if($mark){
            if($general) {
                $mark = $this->getDoctrine()
                    ->getRepository(CarMark::class)
                    ->findOneBy(['header' => $mark, 'carTypeId' => explode(",", $general->getCarTypeIds())]);
            } else {
                $mark = $this->getDoctrine()
                    ->getRepository(CarMark::class)
                    ->findOneBy(['header' => $mark]);
            }

            if(!$mark) throw $this->createNotFoundException(); //404

            $models = $mm->getModels($mark->getId());
            foreach ($models as $child) {
                $mark_ids[] = $child->getId();
            }
            $mark_condition = ' AND c.modelId IN (' . implode(',', $mark_ids) . ')';
            $marks = $mm->getMarks($mark->getCarTypeId());
        } else {
            //$mark = array('id' => 0,'groupname'=>'', 'header'=>false, 'carTypeId'=>0);
            $mark = new CarMark();
            if ($general) {
                $mark->setCarTypeId($general->getCarTypeIds());
                $mark->setHeader('');
            }
            else {
                $mark->setCarTypeId(1);

            }
            $mark->setHeader('');

            $marks = array();
            $models = false;
        }
//
        if($model){
            $model = $this->getDoctrine()
                ->getRepository(CarModel::class)
                ->findOneBy(['header' => $model, 'carMarkId' => $mark->getId()]);

            if(!$model) throw $this->createNotFoundException(); //404

            $mark_condition = ' AND c.modelId = '.$model->getId();
        } else {
            $model = array('groupName' => 'cars','id' => 0,'header'=>$_t->trans('Любая модель'));
            if (!$models) $models = array();
        }

        $dql = 'SELECT count(c.id) FROM AppBundle:Card c WHERE c.isActive = 1 '.$city_condition.$service_condition.$general_condition.$mark_condition.$body_condition;
        $query = $em->createQuery($dql);

        $total_cards = $query->getSingleScalarResult();

        if ($request->query->has('page')) $page = $get['page']; else $page = 1;
        if ($request->query->has('onpage')) $cards_per_page = $get['onpage']; else $cards_per_page = 12;

        $pages_in_center = 5;
        if ($mobileDetector->isMobile()) $pages_in_center = 2;

        $pager_center_start = 2;

        $total_pages = ceil($total_cards/$cards_per_page);
        $start = ($page-1)*$cards_per_page;

        if ($total_pages>($pages_in_center+1)) {
            if(($total_pages-$page) > $pages_in_center) $pager_center_start = $page;
            else $pager_center_start = $total_pages - $pages_in_center;
            if ($pager_center_start == 1) $pager_center_start = 2;
        }


        if (isset($get['order'])){
            if($get['order'] == 'price_asc'){
                $order = ' ORDER BY p.value ASC';
            }
            if($get['order'] == 'price_desc'){
                $order = ' ORDER BY p.value DESC';
            }
        }

        $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g ORDER BY g.total DESC');
        $generalTypes = $query->getResult();

        $dql = 'SELECT c.id FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.cardPrices p WITH p.priceId = 2 WHERE c.isActive = 1 '.$city_condition.$service_condition.$general_condition.$mark_condition.$body_condition.$order;
        $query = $em->createQuery($dql);

        $query->setMaxResults($cards_per_page);
        $query->setFirstResult($start);



        $card_ids = $query->getResult();
        if($card_ids) {
            foreach ($card_ids as $c_id) $ids[] = $c_id['id'];
            $ids = implode(",", $ids);
        } else {
            $ids = 1;
        }


        $dql = 'SELECT c,p,f FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.cardPrices p WITH p.priceId > 0 LEFT JOIN c.fotos f WHERE c.id IN ('.$ids.')'.$order;
        $query = $em->createQuery($dql);
        $cards = $query->getResult();

        if (!$service) $p_service = 'all';
        else {
            if ($service == 1) $p_service = 'prokat';
            if ($service == 2) $p_service = 'arenda';
            if ($service == 3) $p_service = 'leasebuyout';
        }

        $seo = [];
        if ($p_service == 'all') $seo['service'] = $_t->trans('Прокат и аренда');
        if ($p_service == 'prokat') $seo['service'] = $_t->trans('Прокат');
        if ($p_service == 'arenda') $seo['service'] = $_t->trans('Аренда');
        if ($p_service == 'leasebuyout') $seo['service'] = $_t->trans('Аренда с правом выкупа');
        if (!$general) {
            $seo['type']['singular'] = $_t->trans('транспорта');
            $seo['type']['plural'] = $_t->trans('транспорта');
        } else {
            if ($_SERVER['LANG'] == 'ru') $seo['type']['singular'] = $general->getChegoSingular(); else $seo['type']['singular'] = $general->getUrl();
            if ($_SERVER['LANG'] == 'ru') $seo['type']['plural'] = $general->getChegoPlural(); else $seo['type']['plural'] = $general->getUrl();
        }
        if (!is_array($mark)) $seo['mark'] = $mark->getHeader();
        else $seo['mark'] = '';
        if (!is_array($model)) $seo['model'] = $model->getHeader();
        else $seo['model'] = '';
        if ($city) {
            $seo['city']['chto'] = $city->getHeader();
            $seo['city']['gde'] = $city->getGde();
        } else {
            $seo['city']['chto'] = 'России';
            $seo['city']['gde'] = 'России';
        }
        if ($body_condition != ''){
            $seo['bodyType'] = $bodyType->getChego();
        } else {
            $seo['bodyType'] = '';
        }

        $custom_seo = $this->getDoctrine()
            ->getRepository(Seo::class)
            ->findOneBy(['url' => $request->getPathInfo()]);



        if($general) {
            $carType = $general->getCarTypeIds();
        } else $carType = '';

        $mark_arr = $mm->getExistMarks("",$carType);

        $mark_arr_sorted = $mark_arr['typed_marks'];
        $models_in_mark = $mark_arr['models_in_mark'];



        $gtm_ids = $mm->getExistMarkGtId($city->getId());
        $all_gts = $mm->getExistGt($gtm_ids['gts']);
        if ($general) $all_marks = $mm->getExistMark($gtm_ids['models'],$general);
        else $all_marks = '';

        if(!$general) $general = ['url'=>'alltypes','header'=>'Любой тип транспорта'];

        if ($this->get('session')->has('city')){
            $in_city = $this->get('session')->get('city');
            if(is_array($in_city)) $in_city = $in_city[0]->getUrl();
            else $in_city = $in_city->getUrl();
        }
        else $in_city = $city->getUrl();

        $stat_arr = [
            'url' => $request->getPathInfo(),
            'event_type' => 'visit',
            'page_type' => 'catalog',
        ];

        if($total_cards == 0) $stat_arr['is_empty'] = true;

        $stat->setStat($stat_arr);


        // ---------------------------- start of similar ----------------------------------

        $similar = false;

        if($total_cards == 0) {

            $dql = 'SELECT c.id FROM AppBundle:Card c WHERE c.cityId=?1 AND c.modelId=?3 ORDER BY c.dateUpdate DESC'; // -- get by model

            $query = $em->createQuery($dql);
            $query->setParameter(1, $this->get('session')->get('city')->getId());

            dump($model);

            $query->setParameter(3, is_array($model) ? 0 : $model->getId());
            $query->setMaxResults(9);

            if (count($query->getScalarResult()) < 1) { // -- get by mark
                $dql = 'SELECT m.id FROM MarkBundle:CarModel m WHERE m.carMarkId=?1';
                $query = $em->createQuery($dql);
                $query->setParameter(1, $mark->getId());
                foreach ($query->getScalarResult() as $row) {
                    $model_ids[] = $row['id'];
                }
                $dql = 'SELECT c.id FROM AppBundle:Card c WHERE c.cityId=?1 AND c.modelId IN (' . implode(",", $model_ids) . ') ORDER BY c.dateUpdate DESC';
                $query = $em->createQuery($dql);
                $query->setParameter(1, $this->get('session')->get('city')->getId());

                $query->setMaxResults(9);

                if (count($query->getScalarResult()) < 1) {
                    $dql = 'SELECT c.id FROM AppBundle:Card c JOIN c.tariff t WHERE c.cityId=?1 AND c.generalTypeId = ' . $general->getId() . ' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
                    $query = $em->createQuery($dql);
                    $query->setParameter(1, $this->get('session')->get('city')->getId());

                    $query->setMaxResults(9);

                    if (count($query->getScalarResult()) < 1) {
                        $dql = 'SELECT c.id FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = ' . $general->getId() . ' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
                        $query = $em->createQuery($dql);

                        $query->setMaxResults(9);

                        if (count($query->getScalarResult()) < 1) {
                            $dql = 'SELECT c.id FROM AppBundle:Card c JOIN c.tariff t ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
                            $query = $em->createQuery($dql);

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
        }

        // ---------------------------- end of similar ----------------------------------




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
            'service' => $p_service,

            'countryCode' => $countryCode,
            'regionId' => $regionId,

            'cities' => $cities,
            'cityId' => $cityId,
            'city' => $city,

            'general' => $general,

            'mark' => $mark,
            'model' => $model,
            'marks' => $marks,
            'models' => $models,
            'car_type_id' => $mark->getCarTypeId(),

            'seo' => $seo,
            'custom_seo' => $custom_seo,


            'mark_arr_sorted' => $mark_arr_sorted,
            'models_in_mark' => $models_in_mark,

            'generalTypes' => $generalTypes,
            'all_gts' => $all_gts,
            'all_marks' => $all_marks,
            'gtm_ids' => $gtm_ids,

            'bodyTypes' => $bodyTypes,

            'in_city' => $in_city,
            'is_body' => $is_body,

            'page_type' => 'catalog',
            'lang' => $_SERVER['LANG'],
            'similar' => $similar


        ]);
    }

    /**
     * @Route("/{url}", name="remove_trailing_slash",
     *     requirements={"url" = ".*\/$"}, methods={"GET"})
     */
    public function removeTrailingSlashAction(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, 301);
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