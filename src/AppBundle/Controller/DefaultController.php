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

    /**
     * @Route("/regions")
     */
    public function showRegionsAction(EntityManagerInterface $em)
    {
        $query = $em->createQuery('SELECT c.cityId FROM AppBundle:Card c ');
        foreach($query->getScalarResult() as $row){
            $city_ids[] = $row['cityId'];
        }
        $query = $em->createQuery('SELECT c.parentId FROM AppBundle:City c WHERE c.parentId IS NOT NULL AND c.id IN ('.implode(",",$city_ids).')');
        foreach($query->getScalarResult() as $row){
            $region_ids[] = $row['parentId'];
        }
        $region_ids = array_unique($region_ids);
        $query = $em->createQuery('SELECT r FROM AppBundle:City r WHERE r.id IN ('.implode(",",$region_ids).')');
        $regions = $query->getResult();
        return $this->render('main_page/regions.html.twig', [
            'regions' => $regions
        ]);
    }

    /**
     * @Route("/region/{id}")
     */
    public function showRegionAction($id, EntityManagerInterface $em)
    {
        $region = $this->getDoctrine()
            ->getRepository(City::class)
            ->find($id);
        $query = $em->createQuery('SELECT c.cityId FROM AppBundle:Card c ');
        foreach($query->getScalarResult() as $row){
            $city_ids[] = $row['cityId'];
        }
        $query = $em->createQuery('SELECT c FROM AppBundle:City c WHERE c.parentId ='.$id.' AND c.id IN ('.implode(",",$city_ids).')');
        $cities = $query->getResult();
        return $this->render('main_page/cities.html.twig', [
            'cities' => $cities,
            'region' => $region
        ]);
    }

    /**
     * @Route("/ajax/plusLike")
     */
    public function plusLikeAction(Request $request)
    {
        $post = $request->request;
        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($post->get('card_id'));

        if($this->get('session')->has('likes')){
            $array = $this->get('session')->get('likes');
            if(!isset($array[$card->getId()])) {
                $array[$card->getId()] = 1;
                $card->setLikes($card->getLikes() + 1);
            }
            $this->get('session')->set('likes', $array);
        } else {
            $this->get('session')->set('likes', [$card->getId() => 1]);
            $card->setLikes($card->getLikes() + 1);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($card);
        $em->flush();

        return new Response('', 200);
    }
}
