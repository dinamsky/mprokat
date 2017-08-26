<?php

namespace UserBundle\Controller;

use UserBundle\Entity\User;
use AppBundle\Entity\Card;
use AppBundle\Entity\City;
use AppBundle\Entity\Color;
use AppBundle\Entity\FieldInteger;
use AppBundle\Entity\FieldType;
use AppBundle\Entity\GeneralType;
use AppBundle\Entity\Mark;
use AppBundle\Entity\State;
use AppBundle\Entity\CardField;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuMarkModel;
use AppBundle\Menu\MenuSubFieldAjax;
use AppBundle\SubFields\SubFieldUtils;
use UserBundle\Security\Password;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends Controller
{
    /**
     * @Route("/card/new")
     */
    public function indexAction(MenuMarkModel $markmenu, MenuGeneralType $mgt, MenuCity $mc, Request $request)
    {
        if($this->get('session')->get('logged_user') === null) return new Response("",404);

        $card = new Card();

        $conditions = $this->getDoctrine()
            ->getRepository(State::class)
            ->findAll();

        $colors = $this->getDoctrine()
            ->getRepository(Color::class)
            ->findAll();

        if($request->isMethod('GET')) {
            $response = $this->render('card/card_new.html.twig', [
                'generalTopLevel' => $mgt->getTopLevel(),
                'countries' => $mc->getCountry(),
                'custom_fields' => '',
                'mark_groups' => $markmenu->getGroups(),
                'conditions' => $conditions,
                'colors' => $colors,
            ]);
        }

        if($request->isMethod('POST')){
            $em = $this->getDoctrine()->getManager();
            $post = $request->request;
            $card->setHeader($post->get('header'));

            $modelId = $this->getDoctrine()
                ->getRepository(Mark::class)
                ->find($post->get('modelId'));
            $card->setMarkModel($modelId);

            $generalType = $this->getDoctrine()
                ->getRepository(GeneralType::class)
                ->find($post->get('generalTypeId'));
            $card->setGeneralType($generalType);

            $city = $this->getDoctrine()
                ->getRepository(City::class)
                ->find($post->get('cityId'));
            $card->setCity($city);

            $card->setProdYear($post->get('prodYear'));

            $condition = $this->getDoctrine()
                ->getRepository(State::class)
                ->find($post->get('conditionId'));
            $card->setCondition($condition);

            $card->setServiceTypeId($post->get('serviceTypeId'));

            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($this->get('session')->get('logged_user')->getId());
            $card->setUser($user);

            $color = $this->getDoctrine()
                ->getRepository(Color::class)
                ->find($post->get('colorId'));
            $card->setColor($color);

            $em->persist($card);
            $em->flush();

            foreach($post->get('subField') as $fieldId=>$value){
                $subfield = $this->getDoctrine()
                    ->getRepository(FieldType::class)
                    ->find($fieldId);
                $storageTypeName = "\AppBundle\Entity\\".$subfield->getStorageType();
                $storage = new $storageTypeName();
                $storage->setCardId($card->getId());
                $storage->setCardFieldId($fieldId);
                $storage->setValue($value);
                //$storage->setSubField(new \AppBundle\Entity\SubField());
                //dump($storage);
                $em->persist($storage);

            }
            $em->flush();
            $response = $this->redirectToRoute('homepage');
        }

        return $response;
    }

    /**
     * @Route("/ajax/getAllSubFields")
     */
    public function getAllSubField(Request $request, SubFieldUtils $sf)
    {
        $generalTypeId = $request->request->get('generalTypeId');

        $result = $sf->getSubFields($generalTypeId);

        return $this->render('all_subfields.html.twig', [
            'result' => $result,
        ]);
    }

    /**
     * @Route("/ajax/getSubField")
     */
    public function getSubLevel(Request $request, MenuSubFieldAjax $menu)
    {
        $subId = $request->request->get('subId');
        $fieldId = $request->request->get('fieldId');


        $result = $menu->getSubField($fieldId, $subId);

        if(!empty($result)) {
            return $this->render('common/ajax_select.html.twig', [
                'options' => $result
            ]);
        }
        else{
            $response = new Response();
            $response->setStatusCode(204);
            return $response;
        }
    }

    /**
     * @Route("/userSignIn")
     */
    public function signInAction(Request $request, Password $password)
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(array(
                'email' => $request->request->get('email')
            ));

        foreach($users as $user){

            if ($password->CheckPassword($request->request->get('password'), $user->getPassword())){
                $this->get('session')->set('logged_user', $user);
                break;
            }
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/userLogout")
     */
    public function logoutAction(Request $request)
    {
        $this->get('session')->remove('logged_user');
        $this->addFlash(
            'notice',
            'You logged out from system!'
        );
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/testMail")
     */
    public function testMailAction(\Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('send@example.com')
            ->setTo('wqs-info@mail.ru')
            ->setBody(
                $this->renderView(
                    'email/registration.html.twig',
                    array('name' => 'NaMe')
                ),
                'text/html'
            );
        $mailer->send($message);
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/userSignUp")
     */
    public function signUpAction(Request $request, Password $password, \Swift_Mailer $mailer)
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(array(
                'email' => $request->request->get('email')
            ));

        foreach($users as $user){

            if ($password->CheckPassword($request->request->get('password'), $user->getPassword())){
                $this->addFlash(
                    'notice',
                    'User already exist!'
                );
                return $this->redirectToRoute('homepage');
                break;
            }
        }

        $code = md5(rand(0,99999999));
        $user = new User();
        $user->setEmail($request->request->get('email'));
        $user->setPassword($password->HashPassword($request->request->get('password')));
        $user->setHeader($request->request->get('header'));
        $user->setActivateString($code);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('robot@multiprokat.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'email/registration.html.twig',
                    array(
                        'header' => $user->getHeader(),
                        'code' => $code
                    )
                ),
                'text/html'
            );
        $mailer->send($message);

        $this->addFlash(
            'notice',
            'Check your mail, we send you link to activate!'
        );
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/activate_account/{code}", name="activate_account")
     */
    public function activateAccountAction($code)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array(
                'activateString' => $code,
                'isActivated' => 0
            ));

        if($user){
            $user->setIsActivated(true);
            $user->setActivateString('');
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->get('session')->set('logged_user', $user);
            $this->addFlash(
                'notice',
                'Your account is activated!'
            );
        } else {
            $this->addFlash(
                'notice',
                'Error! Try again!'
            );
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/user/edit/card/{cardId}")
     */
    public function editCardAction($cardId, MenuGeneralType $mgt, MenuMarkModel $markmenu, MenuCity $mc)
    {
        $conditions = $this->getDoctrine()
            ->getRepository(State::class)
            ->findAll();

        $colors = $this->getDoctrine()
            ->getRepository(Color::class)
            ->findAll();

        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($cardId);

        $generalType = $this->getDoctrine()
        ->getRepository(GeneralType::class)
        ->find($card->getGeneralTypeId());

        $model = $this->getDoctrine()
            ->getRepository(Mark::class)
            ->find($card->getModelId());

        $marks = $markmenu->getMarks($model->getGroupName());

        $mark = $model->getParent();

        $models = $mark->getChildren();

        $city = $this->getDoctrine()
            ->getRepository(City::class)
            ->find($card->getCityId());



        return $this->render('user/edit_card.html.twig',[
            'card' => $card,
            'conditions' => $conditions,
            'colors' => $colors,
            'generalTopLevel' => $mgt->getTopLevel(),
            'generalSecondLevel' => $mgt->getSecondLevel($generalType->getParentId()),
            'countries' => $mc->getCountry(),
            'mark' => $mark,
            'model' => $model,
            'marks' => $marks,
            'models' => $models,
            'mark_groups' => $markmenu->getGroups(),
            'countryCode' =>$city->getCountry(),
            'regionId' =>$city->getParent()->getId(),
            'cityId' =>$city->getId(),
        ]);
    }
}
