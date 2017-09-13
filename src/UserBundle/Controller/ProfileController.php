<?php

namespace UserBundle\Controller;

use AppBundle\Entity\Card;
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
     * @Route("/user/{id}", name="user_page")
     */
    public function userPageAction($id)
    {

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find((int)$id);
        $user_foto = false;
        foreach ($user->getInformation() as $info){
            if($info->getUiKey() == 'foto' and $info->getUiValue()!='') $user_foto =  '/assets/images/users/t/'.$info->getUiValue().'.jpg';
        }
        return $this->render('user/user_page.html.twig',[
            'user' => $user,
            'user_foto' => $user_foto
        ]);
    }

    /**
     * @Route("/user", name="user_main")
     */
    public function indexAction(Password $password)
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
        $query = $em->createQuery('SELECT c FROM AppBundle:Card c WHERE c.userId = ?1');
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

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('DELETE UserBundle\Entity\UserInfo u WHERE u.userId = ?1');
        $query->setParameter(1, $user->getId());
        $query->execute();

        foreach($post->get('info') as $uiKey =>$uiValue ){
            $info = new UserInfo();
            $info->setUiKey($uiKey);
            $info->setUiValue($uiValue);
            $info->setUser($user);
            $em->persist($info);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->get('session')->set('logged_user', $user);

        return $this->redirectToRoute('user_profile');
    }

    /**
 * @Route("/user/sendMessage")
 */
    public function sendMessageAction(Request $request, \Swift_Mailer $mailer)
    {
        $post = $request->request;

        $card_id = $post->get('card_id');

        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($card_id);

        $user = $card->getUser();

        $message = (new \Swift_Message('Сообщение от пользователя'))
            ->setFrom(['robot@multiprokat.com' => 'Робот Мультипрокат'])
            ->setTo($user->getEmail())
            ->setCc('test.multiprokat@gmail.com')
            ->setBody(
                $this->renderView(
                    'email/request.html.twig',
                    array(
                        'header' => $user->getHeader(),
                        'message' => $post->get('message'),
                        'email' => $post->get('email'),
                        'name' => $post->get('name'),
                        'phone' => $post->get('phone'),
                    )
                ),
                'text/html'
            );
        $mailer->send($message);

        $this->addFlash(
            'notice',
            'Ваше сообщение успешно отправлено!'
        );

        return $this->redirect('/card/'.$card_id);
    }

    /**
     * @Route("/user/contactsMessage")
     */
    public function contactsMessageAction(Request $request, \Swift_Mailer $mailer)
    {
        $post = $request->request;

        $card_id = $post->get('card_id');

        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($card_id);

        $user = $card->getUser();

        $message = (new \Swift_Message('Сообщение от пользователя'))
            ->setFrom(['robot@multiprokat.com' => 'Робот Мультипрокат'])
            ->setTo('mail@multiprokat.com')
            ->setCc('test.multiprokat@gmail.com')
            ->setBody(
                $this->renderView(
                    'email/request.html.twig',
                    array(
                        'header' => $user->getHeader(),
                        'message' => $post->get('message'),
                        'email' => $post->get('email'),
                        'name' => $post->get('name'),
                        'phone' => $post->get('phone'),
                    )
                ),
                'text/html'
            );
        $mailer->send($message);

        $this->addFlash(
            'notice',
            'Ваше сообщение успешно отправлено!'
        );

        return $this->redirect('/card/'.$card_id);
    }


}