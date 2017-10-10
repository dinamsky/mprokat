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
            return $this->render('AdminBundle::admin_stat_main.html.twig');
        }
    }


    /**
     * @Route("/adminStatCityQuantity", name="adminStatCityQuantity")
     */
    public function cityStatAction(EntityManagerInterface $em)
    {

        if ($this->get('session')->get('admin') === null) return $this->render('AdminBundle::admin_enter_form.html.twig');
        else {

            $query = $em->createQuery('SELECT s FROM AppBundle:City s LEFT JOIN c.cards c WHERE s.parentId IS NOT NULL ');
            $bodyTypes = $query->getResult();


            return $this->render('AdminBundle::admin_stat_main.html.twig');
        }
    }
}
