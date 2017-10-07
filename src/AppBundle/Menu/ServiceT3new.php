<?php

namespace AppBundle\Menu;

use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Card;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ServiceT3new extends Controller
{
    private $em;

    public function __construct(em $em)
    {
        $this->em = $em;
    }

    public function getTop3()
    {
        $query = $this->em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setMaxResults(3);
        return $query->getResult();


//        $query = $this->em->createQuery('SELECT c.id FROM AppBundle:Card c JOIN c.tariff t ORDER BY t.weight DESC, c.dateTariffStart DESC');
//        $query->setMaxResults(3);
//        foreach ($query->getScalarResult() as $cars_id) $cars_ids[] = $cars_id['id'];
//        $dql = 'SELECT c,f,p FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.fotos f LEFT JOIN c.cardPrices p WHERE c.id IN ('.implode(",",$cars_ids).') ORDER BY t.weight DESC, c.dateTariffStart DESC';
//        $query = $this->em->createQuery($dql);
//        return $query->getResult();
    }

    public function getNew()
    {
//        $query = $this->em->createQuery('SELECT c FROM AppBundle:Card c ORDER BY c.dateCreate DESC');
//        $query->setMaxResults(5);
//        return $query->getResult();

        $query = $this->em->createQuery('SELECT c.id FROM AppBundle:Card c ORDER BY c.dateCreate DESC');
        $query->setMaxResults(3);
        foreach ($query->getResult() as $cars_id) $cars_ids[] = $cars_id['id'];
        $dql = 'SELECT c,f,p,g,m FROM AppBundle:Card c LEFT JOIN c.fotos f LEFT JOIN c.cardPrices p LEFT JOIN c.city g LEFT JOIN c.markModel m WHERE c.id IN ('.implode(",",$cars_ids).')';
        $query = $this->em->createQuery($dql);
        return $query->getResult();
    }

}