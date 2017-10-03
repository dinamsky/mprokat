<?php

namespace UserBundle\Controller;

use MarkBundle\Entity\CarModel;
use MarkBundle\Entity\CarMark;
use UserBundle\Entity\UserOrder;
use AppBundle\Entity\CardFeature;
use AppBundle\Entity\CardPrice;
use AppBundle\Entity\Feature;
use AppBundle\Entity\Foto;
use AppBundle\Entity\Price;
use AppBundle\Entity\Tariff;
use AppBundle\Foto\FotoUtils;
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
use Symfony\Component\HttpFoundation\RedirectResponse;

class EditCardController extends Controller
{
    /**
     * @Route("/user/edit/card/{cardId}")
     */
    public function editCardAction($cardId, MenuGeneralType $mgt, MenuMarkModel $markmenu, MenuCity $mc, SubFieldUtils $sf)
    {
        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($cardId);

        if ( $this->get('session')->get('logged_user') !== null
            and $card->getUserId() != $this->get('session')->get('logged_user')->getId()
        ) return new Response("",404);

        if ( $this->get('session')->get('logged_user') === null
            and $this->get('session')->get('admin') === null
        ) return new Response("",404);

        if ( $this->get('session')->has('admin')
            and  $this->get('session')->get('admin')->getId() != $card->getAdminId()
            and  $this->get('session')->get('admin')->getRole() != 'superadmin'
        ) return new Response("",404);


        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->get('session')->get('logged_user')->getId());

        if($user->getIsBanned()) return new Response("",404);


        $conditions = $this->getDoctrine()
            ->getRepository(State::class)
            ->findAll();

        $colors = $this->getDoctrine()
            ->getRepository(Color::class)
            ->findAll();

        $generalType = $this->getDoctrine()
        ->getRepository(GeneralType::class)
        ->find($card->getGeneralTypeId());

        $model = $this->getDoctrine()
            ->getRepository(CarModel::class)
            ->find($card->getModelId());

        $marks = $markmenu->getMarks($model->getCarTypeId());

        $mark = $this->getDoctrine()
            ->getRepository(CarMark::class)
            ->find($model->getCarMarkId());

        $models = $this->getDoctrine()
            ->getRepository(CarModel::class)
            ->findBy(['carMarkId' => $model->getCarMarkId()]);

        $city = $this->getDoctrine()
            ->getRepository(City::class)
            ->find($card->getCityId());

        $features = $this->getDoctrine()
            ->getRepository(Feature::class)
            ->findBy(['parent'=>null]);

        $prices = $this->getDoctrine()
            ->getRepository(Price::class)
            ->findAll();

        $tariffs = $this->getDoctrine()
            ->getRepository(Tariff::class)
            ->findAll();

        $gt = $this->getDoctrine()
            ->getRepository(GeneralType::class)
            ->find($card->getGeneralTypeId());

        if ($card->getFieldIntegers()->isEmpty()) $subfields = false;
        else $subfields = $sf->getSubFieldsEdit($card);

        $gtid = $card->getGeneralTypeId();
        $pgtid = $card->getGeneralType()->getParentId();
        if($pgtid == null) $pgtid = $card->getGeneralTypeId();

        return $this->render('card/card_edit.html.twig',[
            'card' => $card,
            'conditions' => $conditions,
            'colors' => $colors,
            'generalTopLevel' => $mgt->getTopLevel(),
            'generalSecondLevel' => $mgt->getSecondLevel($generalType->getParentId()),
            'gt' => $gt,
            'gtid' => $gtid,
            'pgtid' => $pgtid,

            'countries' => $mc->getCountry(),
            'mark' => $mark,
            'model' => $model,
            'marks' => $marks,
            'models' => $models,
            'mark_groups' => $markmenu->getGroups(),
            'countryCode' =>$city->getCountry(),
            'regionId' => $city->getParent()->getId(),
            'regions' => $mc->getRegion($city->getCountry()),
            'cities' => $city->getParent()->getChildren(),
            'city' => $city,
            'subfields' => $subfields,
            'features' => $features,
            'prices' => $prices,
            'tariffs' => $tariffs
        ]);
    }

    /**
     * @Route("/card/update")
     */
    public function saveCardAction(Request $request, FotoUtils $fu)
    {

        $post = $request->request;

        $card = $this->getDoctrine()
            ->getRepository(Card::class)
            ->find($post->get('cardId'));

        $em = $this->getDoctrine()->getManager();

        if ($post->has('delete')){
            $fu->deleteAllFotos($card);
            $em->remove($card);
            $em->flush();
            if ($this->get('session')->get('admin') === null) return $this->redirectToRoute('user_cards');
            else return $this->redirectToRoute('search');
        }

        $card->setHeader($post->get('header'));
        $card->setContent($post->get('content'));
        $card->setAddress($post->get('address'));
        $card->setCoords($post->get('coords'));
        $card->setVideo($post->get('video'));
        $card->setStreetView($post->get('streetView'));

        $model = $this->getDoctrine()
            ->getRepository(CarModel::class)
            ->find($post->get('modelId'));
        $card->setMarkModel($model);

        if ($post->get('generalTypeId') == 0) $gt = $post->get('generalTypeTopLevelId');
        else $gt = $post->get('generalTypeId');

        $generalType = $this->getDoctrine()
            ->getRepository(GeneralType::class)
            ->find($gt);
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
            ->find($card->getUserId());
        $card->setUser($user);

        $color = $this->getDoctrine()
            ->getRepository(Color::class)
            ->find($post->get('colorId'));
        $card->setColor($color);

        $tariff = $this->getDoctrine()
            ->getRepository(Tariff::class)
            ->find($post->get('tariffId'));

        //$card->setTariff($tariff);

        $em->persist($card);


        if ($post->get('subField') !== null) foreach($post->get('subField') as $fieldId=>$value) if($value!=0 and $value!=''){
            $subfield = $this->getDoctrine()
                ->getRepository(FieldType::class)
                ->find($fieldId);

            $dql = 'SELECT s FROM AppBundle:'.$subfield->getStorageType().' s WHERE s.cardId = ?1 AND s.cardFieldId = ?2';
            $query = $em->createQuery($dql);
            $query->setParameter(1, $card->getId());
            $query->setParameter(2, $fieldId);
            try {
                $storage = $query->getSingleResult();
            }
            catch (\Doctrine\ORM\NoResultException $e){
                $storageTypeName = "\AppBundle\Entity\\".$subfield->getStorageType();
                $storage = new $storageTypeName();
                $storage->setCard($card);
                $storage->setCardFieldId($fieldId);
            }

            $storage->setValue($value);
            $em->persist($storage);

        }

        $query = $em->createQuery('DELETE AppBundle\Entity\CardFeature c WHERE c.cardId = ?1');
        $query->setParameter(1, $card->getId());
        $query->execute();

        if ($post->has('feature')) foreach($post->get('feature') as $fid=>$f) {
            $feature = $this->getDoctrine()
                ->getRepository(Feature::class)
                ->find($fid);
            $cardFeature = new CardFeature();
            $cardFeature->setCard($card);
            $cardFeature->setFeature($feature);
            $em->persist($cardFeature);
        };

        $query = $em->createQuery('DELETE AppBundle\Entity\CardPrice c WHERE c.cardId = ?1');
        $query->setParameter(1, $card->getId());
        $query->execute();

        if ($post->has('price')) foreach($post->get('price') as $priceId=>$priceValue) {
            if ($priceValue != '' and $priceValue !=0) {
                $price = $this->getDoctrine()
                    ->getRepository(Price::class)
                    ->find($priceId);
                $cardPrice = new cardPrice();
                $cardPrice->setCard($card);
                $cardPrice->setPrice($price);
                $cardPrice->setValue($priceValue);
                $em->persist($cardPrice);
            }
        };

        $em->flush();

        $fu->uploadImages($card);

        if ($post->has('change_tariff') and $tariff->getId() > 1){

            if ($this->get('session')->has('admin')){ // admin able to change tariff without payment
                $card->setTariff($tariff);
                $card->setDateTariffStart(new \DateTime());
                $em->persist($card);
                $em->flush();
                return $this->redirectToRoute('search');
            } else {
                $order = new UserOrder();
                $order->setUser($user);
                $order->setCard($card);
                $order->setTariff($tariff);
                $order->setPrice($tariff->getPrice());
                $order->setOrderType('tariff_' . $tariff->getId());
                $order->setStatus('new');
                $em->persist($order);
                $em->flush();

                $mrh_login = "multiprokat";
                $mrh_pass1 = "Wf1bYXSd5V8pKS3ULwb3";
                $inv_id = $order->getId();
                $inv_desc = "set_tariff";
                $out_summ = $tariff->getPrice();

                $crc = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");

                $url = "https://auth.robokassa.ru/Merchant/Index.aspx?MrchLogin=$mrh_login&" .
                    "OutSum=$out_summ&InvId=$inv_id&Desc=$inv_desc&SignatureValue=$crc";

                return new RedirectResponse($url);
            }

        } else {
            if($post->has('change_tariff') and $tariff->getId() == 1){
                $card->setTariff($tariff);
                $em->persist($card);
                $em->flush();
            }
            if ($this->get('session')->get('admin') === null) return $this->redirectToRoute('user_cards');
            else return $this->redirectToRoute('search');
        }
    }

}
