<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Mark;
use AppBundle\Entity\SubField;
use AppBundle\Entity\Seo;
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

class DefaultController extends Controller
{

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

    /**
     * @Route("/ajax/showPhone")
     */
    public function showPhoneAction(Request $request)
    {
        $post = $request->request;
        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($post->get('card_id'));

        foreach($card->getUser()->getInformation() as $info){
            if( $info->getUiKey() == 'phone') $phone = $info->getUiValue();
        }

        if($this->get('session')->has('phone')){
            $array = $this->get('session')->get('phone');
            $array[$card->getUserId()] = 1;
            $this->get('session')->set('phone', $array);
        } else {
            $this->get('session')->set('phone', [$card->getUserId() => 1]);
        }

        return new Response($phone, 200);
    }

    /**
     * @Route("/ajax/frontGeo")
     */
    public function frontGeoAction(Request $request, Response $response, EntityManagerInterface $em)
    {
        $post = $request->request;
        $geo = json_decode($post->get('geo'), true);
        $this->get('session')->set('geo', $geo);
        $this->get('session')->set('ip', $geo['ip']);
        $this->get('session')->set('sessId', $this->get('session')->getId());

        $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
            ->where('c.header LIKE :geoname')
            ->setParameter('geoname', '%'.$geo['city']['name_ru'].'%')
            ->getQuery()
            ->getResult();
        if ($city) $cityId = $city[0]->getId();
        else $cityId = 77;

        $response = new Response();
        $response->headers->setCookie(new Cookie('geo', $cityId));
        $response->send();
    }


    /**
     * @Route("/listing/{slug}/")
     */
    public function wpStubAction(MenuGeneralType $mgt, MenuCity $mc, EntityManagerInterface $em, MenuMarkModel $mm)
    {
        if($this->get('session')->has('geo')){

            $geo = $this->get('session')->get('geo');

            //dump($geo);

            $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
                ->andWhere('c.header LIKE :geoname')
                ->setParameter('geoname', '%'.$geo['city'].'%')
                ->getQuery()
                ->getResult();

            //dump($city);

            if ($city) $city = $city[0]; // TODO make easier!
            else $city = $this->getDoctrine()
                ->getRepository(City::class)
                ->find(77);
        } else {
            $city = new City();
            $city->setCountry('RUS');
            $city->setParentId(0);
            $city->setTempId(0);
        }


        $general = $this->getDoctrine()
            ->getRepository(GeneralType::class)
            ->find(2);

        $query = $em->createQuery('SELECT g,c FROM AppBundle:GeneralType g LEFT JOIN g.cards c');
        $generalTypes = $query->getResult();

        return $this->render('common/wp_stub.html.twig', [
            'generalTopLevel' => $mgt->getTopLevel(),
            'cards' => '',
            'custom_fields' => '',
            'general_type' => null,
            'city' => $city,
            'mark_model' => array(),
            'mark_groups' => $mm->getGroups(),

            'countries' => $mc->getCountry(),
            'countryCode' => $city->getCountry(),
            'regionId' => $city->getParentId(),
            'regions' => $mc->getRegion($city->getCountry()),
            'cities' => $mc->getCities($city->getParentId()),
            'cityId' => $city->getId(),

            'gtid' => 2,
            'pgtid' => 1,
            'generalSecondLevel' => $mgt->getSecondLevel(1),

            'marks' => [],
            'models' => [],
            'mark' => ['id'=>0,'groupname'=>'','header'=>false],
            'model' => ['id'=>0, 'header'=>false],

            'general' => $general,

            'generalTypes' => $generalTypes
        ]);
    }
}
