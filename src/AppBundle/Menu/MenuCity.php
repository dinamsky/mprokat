<?php

namespace AppBundle\Menu;

use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\City;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
            'RUS'=>'Россия',
            'BLR'=>'Беларусь',
            'KAZ'=>'Казахстан'
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
        return $this->render('common/ajax_options.html.twig', [
            'options' => $this->getRegion($countryCode)
        ]);
    }

    /**
     * @Route("/ajax/getCity")
     */
    public function getCityAction(Request $request)
    {
        $regionId = $request->request->get('regionId');
        return $this->render('common/ajax_options.html.twig', [
            'options' => $this->getCities($regionId)
        ]);
    }

}