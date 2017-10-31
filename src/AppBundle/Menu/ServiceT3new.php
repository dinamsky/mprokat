<?php

namespace AppBundle\Menu;

use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Card;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface as si;

class ServiceT3new extends Controller
{
    private $em;

    public function __construct(em $em, si $session  )
    {
        $this->em = $em;
        $this->sess = $session;

    }

    public function getTop3()
    {

        $cityId = $this->sess->get('city')->getId();
        $query = $this->em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.cityId = ?1 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setParameter(1, $cityId);
        $query->setMaxResults(3);
        if(count($query->getResult())<3) {

            $query = $this->em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
            $query->setMaxResults(3);
        }

        return $query->getResult();



    }

    public function getNew()
    {

        $cityId = $this->sess->get('city')->getId();
        $query = $this->em->createQuery('SELECT c.id FROM AppBundle:Card c WHERE c.cityId = ?1 ORDER BY c.dateCreate DESC');
        $query->setParameter(1, $cityId);
        $query->setMaxResults(3);
        if(count($query->getResult())<3) {
            $query = $this->em->createQuery('SELECT c.id FROM AppBundle:Card c ORDER BY c.dateCreate DESC');
            $query->setMaxResults(3);
        }

        foreach ($query->getResult() as $cars_id) $cars_ids[] = $cars_id['id'];
        $dql = 'SELECT c,f,p,g,m FROM AppBundle:Card c LEFT JOIN c.fotos f LEFT JOIN c.cardPrices p LEFT JOIN c.city g LEFT JOIN c.markModel m WHERE c.id IN ('.implode(",",$cars_ids).')';
        $query = $this->em->createQuery($dql);
        return $query->getResult();
    }

    public function popularCities()
    {
        $query = $this->em->createQuery('SELECT c FROM AppBundle:City c WHERE c.total > 0 ORDER BY c.header ASC');
        return $query->getResult();
    }
}