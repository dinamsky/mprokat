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
     * @Route("/listing/{slug}/")
     */
    public function wpStubAction(MenuGeneralType $mgt, MenuCity $mc, EntityManagerInterface $em, MenuMarkModel $mm)
    {
        $city = $this->getDoctrine()
            ->getRepository(City::class)
            ->find(77);

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
