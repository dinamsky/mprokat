<?php
// AppBundle/SitemapController.php

namespace AppBundle\Controller;
use AppBundle\Entity\Card;
use AppBundle\Entity\GeneralType;
use AppBundle\Menu\MenuMarkModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class YandexYmlController extends Controller
{
    /**
     * @Route("/yandex/transport.yml", name="yandex_yml", defaults={"_format"="yml"})
     */
    public function showAction(Request $request, MenuMarkModel $mm) {

        $em = $this->getDoctrine()->getManager();
        $products = array();
        $hostname = $request->getSchemeAndHttpHost();


        foreach ($em->getRepository('AppBundle:Card')->findAll() as $card) {
            $price = 0;
            foreach ($card->getCardPrices() as $cp) {
                if ($cp->getPriceId() == 1 and $cp->getValue() != 0) $hour = $cp->getValue();
                if ($cp->getPriceId() == 2 and $cp->getValue() != 0) $day = $cp->getValue();
            }
            $price = $hour ? $hour : $day;
            if ($price != 0) {
                $city = $card->getCity()->getHeader();
                $mainFoto = '';
                foreach ($card->getFotos() as $foto) {
                    if ($foto->getIsMain()) $mainFoto = $hostname . '/assets/images/cards/' . $foto->getFolder() . '/' . $foto->getId() . '.jpg';
                }
                $general = $card->getGeneralTypeId();
                $model = $mm->getModel($card->getModelId());
                $mark = $mm->getMark($model->getCarMarkId());
                $mark= $mark->getHeader();
                $model = $model->getHeader();
                $id = $card->getId();
                $url = $hostname . '/card/' . $id;
                $name = 'Прокат ' . $mark . ' ' . $model . ' в ' . $city;
                $products[] = array(
                    'id' => $id,
                    'name' => $name,
                    'url' => $url,
                    'price' => $price,
                    'category' => $general,
                    'image' => $mainFoto,
                    'desc' => 'Мультипрокат: аренда и прокат мотоциклов, автомобилей, катеров, яхт в 230 городах от владельцев. 4600 единиц транспорта в одном месте'
                );
            }

        }

        $response = new Response(
            $this->renderView('yandex/transport_yml.html.twig',['products'=>$products]),
            200
        );
        $response->headers->set('Content-Type', 'text/xml');

        return $response;

    }

}
