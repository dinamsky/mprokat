<?php

namespace UserBundle\Controller;

use UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface as em;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuMarkModel;
use AppBundle\Menu\MenuSubFieldAjax;
use AppBundle\SubFields\SubFieldUtils;
use UserBundle\Entity\UserInfo;
use UserBundle\Security\Password;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use UserBundle\UserBundle;

class ProfileController extends Controller
{
    /**
     * @Route("/user", name="user_main")
     */
    public function indexAction()
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());
        return $this->render('user/profile_main.html.twig',['user' => $user]);
    }

    /**
     * @Route("/user/cards", name="user_cards")
     */
    public function userCardsAction(em $em)
    {
        $query = $em->createQuery('SELECT * FROM AppBundle:Card c WHERE c.userId = ?1');
        $query->setParameter(1, $this->get('session')->get('logged_user')->getId());
        $cards = $query->getResult();
        return $this->render('user/user_cards.html.twig',['cards' => $cards]);
    }

    /**
     * @Route("/profile", name="user_profile")
     */
    public function editProfileAction()
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());

        return $this->render('user/user_profile.html.twig',['user' => $user]);
    }

    /**
     * @Route("/profile/save")
     */
    public function updateProfileAction(Request $request)
    {
        $post = $request->request;

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());

        $user->setHeader($post->get('header'));
        /**
         * @var $info UserInfo
         */
        foreach($user->getInformation() as &$info){
            $info->setUiValue($post->get($info->getUiKey()));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->get('session')->set('logged_user', $user);

        return $this->redirectToRoute('user_profile');
    }
}