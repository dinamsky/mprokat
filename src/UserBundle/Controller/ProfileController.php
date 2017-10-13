<?php

namespace UserBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Foto\FotoUtils;
use AppBundle\Menu\ServiceStat;
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
     * @Route("/user/cards", name="user_cards")
     */
    public function userCardsAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());

        if(!$user->getIsBanned()) {
            $query = $em->createQuery('SELECT c FROM AppBundle:Card c WHERE c.userId = ?1');
            $query->setParameter(1, $this->get('session')->get('logged_user')->getId());
            $cards = $query->getResult();


            if(!$this->get('session')->has('city')){
                if($this->get('session')->has('geo')){
                    $geo = $this->get('session')->get('geo');
                    $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
                        ->where('c.header LIKE :geoname')
                        ->andWhere('c.parentId IS NOT NULL')
                        ->setParameter('geoname', '%'.$geo['city'].'%')
                        ->getQuery()
                        ->getResult();
                    if ($city) {
                        $city = $city[0];
                    }
                    else {
                        $city = $this->getDoctrine()
                            ->getRepository(City::class)
                            ->find(77);
                    }
                } else {
                    $city = new City();
                    $city->setCountry('RUS');
                    $city->setParentId(0);
                    $city->setTempId(0);
                }
                $this->get('session')->set('city', $city);
            } else {
                $city = $this->get('session')->get('city');
            }

            if (is_array($city)){
                $city = $city[0];
            }
            $in_city = $city->getUrl();

            $query = $em->createQuery('SELECT c FROM AppBundle:City c WHERE c.total > 0 ORDER BY c.total DESC, c.header ASC');
            $popular_city = $query->getResult();

            $stat_arr = [
                'url' => $request->getPathInfo(),
                'event_type' => 'visit',
                'page_type' => 'cabinet',
                'user_id' => $user->getId(),
            ];
            $stat->setStat($stat_arr);

            return $this->render('user/user_cards.html.twig', [
                'share' => true,
                'cards' => $cards,
                'city' => $city,
                'popular_city' => $popular_city,
                'in_city' => $in_city,
                'cityId' => $city->getId(),
            ]);
        } else return new Response("",404);
    }

    /**
     * @Route("/user/{id}", name="user_page")
     */
    public function userPageAction($id, MenuMarkModel $mm, EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find((int)$id);
        if(!$user->getIsBanned()) {
            $user_foto = false;
            foreach ($user->getInformation() as $info) {
                if ($info->getUiKey() == 'foto' and $info->getUiValue() != '') $user_foto = '/assets/images/users/t/' . $info->getUiValue() . '.jpg';
            }

            if(!$this->get('session')->has('city')){
                if($this->get('session')->has('geo')){
                    $geo = $this->get('session')->get('geo');
                    $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
                        ->where('c.header LIKE :geoname')
                        ->andWhere('c.parentId IS NOT NULL')
                        ->setParameter('geoname', '%'.$geo['city'].'%')
                        ->getQuery()
                        ->getResult();
                    if ($city) {
                        $city = $city[0];
                    }
                    else {
                        $city = $this->getDoctrine()
                            ->getRepository(City::class)
                            ->find(77);
                    }
                } else {
                    $city = new City();
                    $city->setCountry('RUS');
                    $city->setParentId(0);
                    $city->setTempId(0);
                }
                $this->get('session')->set('city', $city);
            } else {
                $city = $this->get('session')->get('city');
            }

            if (is_array($city)){
                $city = $city[0];
            }
            $in_city = $city->getUrl();

            $query = $em->createQuery('SELECT c FROM AppBundle:City c WHERE c.total > 0 ORDER BY c.total DESC, c.header ASC');
            $popular_city = $query->getResult();

            $mark_arr = $mm->getExistMarks('',1);
            $mark_arr_sorted = $mark_arr['sorted_marks'];
            $models_in_mark = $mark_arr['models_in_mark'];

            $query = $em->createQuery('SELECT g FROM AppBundle:GeneralType g WHERE g.total !=0 ORDER BY g.total DESC');
            $generalTypes = $query->getResult();


            $stat_arr = [
                'url' => $request->getPathInfo(),
                'event_type' => 'visit',
                'page_type' => 'profile',
                'user_id' => $user->getId(),
            ];
            $stat->setStat($stat_arr);

            return $this->render('user/user_page.html.twig', [
                'user' => $user,
                'user_foto' => $user_foto,
                'city' => $city,
                'popular_city' => $popular_city,
                'mark_arr_sorted' => $mark_arr_sorted,
                'models_in_mark' => $models_in_mark,
                'in_city' => $in_city,
                'cityId' => $city->getId(),
                'mark' => $mark_arr_sorted[1][0]['mark'],
                'model' => $mark_arr_sorted[1][0]['models'][0],
                'generalTypes' => $generalTypes,
            ]);
        } else return new Response("",404);
    }

    /**
     * @Route("/user", name="user_main")
     */
    public function indexAction(Password $password, EntityManagerInterface $em)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());

        if(!$this->get('session')->has('city')){
            if($this->get('session')->has('geo')){
                $geo = $this->get('session')->get('geo');
                $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
                    ->where('c.header LIKE :geoname')
                    ->andWhere('c.parentId IS NOT NULL')
                    ->setParameter('geoname', '%'.$geo['city'].'%')
                    ->getQuery()
                    ->getResult();
                if ($city) {
                    $city = $city[0];
                }
                else {
                    $city = $this->getDoctrine()
                        ->getRepository(City::class)
                        ->find(77);
                }
            } else {
                $city = new City();
                $city->setCountry('RUS');
                $city->setParentId(0);
                $city->setTempId(0);
            }
            $this->get('session')->set('city', $city);
        } else {
            $city = $this->get('session')->get('city');
        }

        if (is_array($city)){
            $city = $city[0];
        }
        $in_city = $city->getUrl();

        $query = $em->createQuery('SELECT c FROM AppBundle:City c WHERE c.total > 0 ORDER BY c.total DESC, c.header ASC');
        $popular_city = $query->getResult();

        if(!$user->getIsBanned()) return $this->render('user/profile_main.html.twig',[
            'user' => $user,
            'city' => $city,
            'popular_city' => $popular_city,
            'in_city' => $in_city,
            'cityId' => $city->getId(),
        ]);
        else return new Response("",404);
    }



    /**
     * @Route("/profile", name="user_profile")
     */
    public function editProfileAction(EntityManagerInterface $em, Request $request, ServiceStat $stat)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());




        if(!$user->getIsBanned()) {


            if(!$this->get('session')->has('city')){
                if($this->get('session')->has('geo')){
                    $geo = $this->get('session')->get('geo');
                    $city = $em->getRepository("AppBundle:City")->createQueryBuilder('c')
                        ->where('c.header LIKE :geoname')
                        ->andWhere('c.parentId IS NOT NULL')
                        ->setParameter('geoname', '%'.$geo['city'].'%')
                        ->getQuery()
                        ->getResult();
                    if ($city) {
                        $city = $city[0];
                    }
                    else {
                        $city = $this->getDoctrine()
                            ->getRepository(City::class)
                            ->find(77);
                    }
                } else {
                    $city = new City();
                    $city->setCountry('RUS');
                    $city->setParentId(0);
                    $city->setTempId(0);
                }
                $this->get('session')->set('city', $city);
            } else {
                $city = $this->get('session')->get('city');
            }

            if (is_array($city)){
                $city = $city[0];
            }
            $in_city = $city->getUrl();

            $query = $em->createQuery('SELECT c FROM AppBundle:City c WHERE c.total > 0 ORDER BY c.total DESC, c.header ASC');
            $popular_city = $query->getResult();

            $stat_arr = [
                'url' => $request->getPathInfo(),
                'event_type' => 'visit',
                'page_type' => 'cabinet',
                'user_id' => $user->getId(),
            ];
            $stat->setStat($stat_arr);


            return $this->render('user/user_profile.html.twig', [
                'user' => $user,
                'city' => $city,
                'popular_city' => $popular_city,
                'in_city' => $in_city,
                'cityId' => $city->getId(),
            ]);
        }
        else return new Response("",404);
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
        $query = $em->createQuery('DELETE UserBundle\Entity\UserInfo u WHERE u.userId = ?1 AND u.uiKey != ?2');
        $query->setParameter(1, $user->getId());
        $query->setParameter(2, 'foto');
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
            ->setFrom(['mail@multiprokat.com' => 'Робот Мультипрокат'])
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
                        'card' => $card,
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

//        $card_id = $post->get('card_id');
//
//        $card = $this->getDoctrine()
//            ->getRepository(Card::class)
//            ->find($card_id);
//
//        $user = $card->getUser();

        $message = (new \Swift_Message('Сообщение от пользователя'))
            ->setFrom(['mail@multiprokat.com' => 'Робот Мультипрокат'])
            ->setTo('mail@multiprokat.com')
            ->setCc('test.multiprokat@gmail.com')
            ->setBody(
                $this->renderView(
                    'email/request.html.twig',
                    array(
                        'header' => 'Админ',
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

        return $this->redirect('/');
    }

    /**
     * @Route("/profile/saveFoto")
     */
    public function saveFotoAction(Request $request, FotoUtils $fu)
    {
        $post = $request->request;
        $em = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($post->get('user_id'));


        $query = $em->createQuery('DELETE UserBundle\Entity\UserInfo u WHERE u.userId = ?1 AND u.uiKey = ?2');
        $query->setParameter(1, $post->get('user_id'));
        $query->setParameter(2, 'foto');
        $query->execute();
        if (is_file($_SERVER['DOCUMENT_ROOT'].'/assets/images/users/'.$post->get('user_id')))
            unlink ($_SERVER['DOCUMENT_ROOT'].'/assets/images/users/'.$post->get('user_id'));

        if($post->has('delete')) return $this->redirectToRoute('user_profile');

        $userInfo = new UserInfo();
        $userInfo->setUser($user);
        $userInfo->setUiKey('foto');
        $userInfo->setUiValue('user_'.$post->get('user_id'));
        $em->persist($userInfo);
        $em->flush();



        $fu->uploadImage(
            'foto',
            'user_'.$post->get('user_id'),
            $_SERVER['DOCUMENT_ROOT'].'/assets/images/users',
            $_SERVER['DOCUMENT_ROOT'].'/assets/images/users/t');

        return $this->redirectToRoute('user_profile');
    }
}