<?php
// AppBundle/SitemapController.php

namespace AppBundle\Controller;
use AppBundle\Entity\GeneralType;
use AppBundle\Menu\MenuMarkModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapIndex extends Controller
{
    /**
     * @Route("/sitemap/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function showAction(Request $request) {





    }

}