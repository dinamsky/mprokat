<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Seo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\UserInfo;
use UserBundle\Security\Password;
use UserBundle\Entity\User;
use AdminBundle\Entity\Admin;

class StatController extends Controller
{
    /**
     * @Route("/adminStat", name="adminStat")
     */
    public function indexStatAction()
    {

        if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
        else {
            return $this->render('AdminBundle:stat:admin_stat_main.html.twig');
        }
    }


    /**
     * @Route("/adminStatCityQuantity", name="adminStatCityQuantity")
     */
    public function cityStatAction(EntityManagerInterface $em)
    {

        if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
        else {

            $query = $em->createQuery('SELECT s,c FROM AppBundle:City s LEFT JOIN s.cards c WHERE s.parentId IS NOT NULL');
            $cities = $query->getResult();
            foreach($cities as $c){
                $res[$c->getId()] = count($c->getCards());
                $cit[$c->getId()] = $c;
            }
            arsort($res);

            return $this->render('AdminBundle:stat:admin_stat_city.html.twig',[
                'cities' => $cit,
                'sort' => $res,
            ]);
        }
    }
}
