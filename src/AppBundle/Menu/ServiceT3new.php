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
        $query = $this->em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t ORDER BY t.weight DESC, c.dateTariffStart DESC');
        $query->setMaxResults(3);
        return $query->getResult();
    }

    public function getNew()
    {
        $query = $this->em->createQuery('SELECT c FROM AppBundle:Card c ORDER BY c.dateCreate DESC');
        $query->setMaxResults(5);
        return $query->getResult();
    }

}