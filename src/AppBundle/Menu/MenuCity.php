<?php

namespace AppBundle\Menu;

use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\City;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class MenuCity extends Controller
{
    private $em;

    public function __construct(em $em)
    {
        $this->em = $em;
    }

    public function getCountry()
    {
        $array = array(
            ['id'=>'RUS', 'header'=>'Россия','url' => 'RUS'],
//            ['id'=>'BLR', 'header'=>'Беларусь','url' => 'BLR'],
//            ['id'=>'KAZ', 'header'=>'Казахстан','url' => 'KAZ']
        );
        return $array;
    }

    public function getCountryIdRange($countryCode)
    {
        $query = $this->em->createQuery('SELECT c FROM AppBundle:City c WHERE c.country = ?1');
        $query->setParameter(1, $countryCode);
        $query->setMaxResults(1);
        $first =  $query->getSingleResult()->getId();
        $query = $this->em->createQuery('SELECT c FROM AppBundle:City c WHERE c.country = ?1 ORDER BY c.id DESC');
        $query->setParameter(1, $countryCode);
        $query->setMaxResults(1);
        $last =  $query->getSingleResult()->getId();
        return ['first' => $first, 'last' => $last];
    }
    public function getRegion($country)
    {
        $query = $this->em->createQuery('SELECT c FROM AppBundle:City c WHERE c.parentId IS NULL AND c.country = ?1');
        $query->setParameter(1, $country);
        return $query->getResult();
    }

    public function getCities($parentId)
    {
        $query = $this->em->createQuery('SELECT c FROM AppBundle:City c WHERE c.parentId = ?1');
        $query->setParameter(1, $parentId);
        return $query->getResult();
    }

    public function getCity($cityId)
    {
        $query = $this->em->createQuery('SELECT c FROM AppBundle:City c WHERE c.id = ?1');
        $query->setParameter(1, $cityId);
        return $query->getResult();
    }

    /**
     * @Route("/ajax/getRegion")
     */
    public function getRegionAction(Request $request)
    {
        $countryCode = $request->request->get('countryCode');
        return $this->render('common/ajax_options_url.html.twig', [
            'options' => $this->getRegion($countryCode)
        ]);
    }

    /**
     * @Route("/ajax/setCity")
     */
    public function setCityAction(Request $request)
    {
        if($request->request->get('cityId') == 0 ){
            $city = new City();
            $city->setCountry('RUS');
            $city->setHeader('Россия');
            $city->setParentId(0);
            $city->setTempId(0);
            $city->setUrl('rus');
            $city->setGde('России');
            $this->get('session')->set('city', $city);
        } else $this->get('session')->set('city', $this->getCity($request->request->get('cityId'))[0]);

        return new Response();
    }

    /**
     * @Route("/ajax/getCity")
     */
    public function getCityAction(Request $request)
    {
        $regionId = $request->request->get('regionId');
        return $this->render('common/ajax_options_url.html.twig', [
            'options' => $this->getCities($regionId)
        ]);
    }


    /**
     * @Route("/ajax/getCityByInput")
     */
    public function getCityByInputAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $res = array();

        $cities = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
            ->where('c.header LIKE :ct')
            ->andWhere('c.parent IS NOT NUll')
            ->setParameter('ct', '%'.$request->request->get('q').'%')
            ->getQuery()
            ->getResult();

        foreach($cities as $c){
            $res[] = $c->getHeader().'|'.$c->getId();
        }

        return new Response(json_encode($res));
    }

    public function updateCityTotal($cityId,$modelId = '')
    {
//        $query = $this->em->createQuery('SELECT c FROM AppBundle:City c WHERE c.id = ?1');
//        $query->setParameter(1, $cityId);
//        $result = $query->getResult();
//
//        $ids = explode(",",$result[0]->getModels());
//        if (!in_array($modelId,$ids,true)) $ids[] = $modelId;

        //echo implode(",",$ids).'\r\n';
        $query = $this->em->createQuery('UPDATE AppBundle:City c SET c.total = c.total + 1 WHERE c.id = ?1');
        //$query->setParameter(1, implode(",",$ids));
        $query->setParameter(1, $cityId);
        $query->execute();
    }
}