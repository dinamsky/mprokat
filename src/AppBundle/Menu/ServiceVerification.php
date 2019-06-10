<?php

namespace AppBundle\Menu;

use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface as si;

class ServiceVerification extends Controller
{
    private $em;

    public function __construct(em $em, si $session)
    {
        $this->em = $em;
        $this->sess = $session;

    }

    public function getFormatPhone(string $phone_in, string $country = 'RU', string $format = ''){
        $phone = $phone_in;
        $phone = preg_replace('/^(00)/','',$phone);
        // $phone = preg_replace('/^(8)/','7',$phone);
        $phone = preg_replace('~[^0-9]+~','',$phone);
        switch ($country) {
            case 'RU':
                if ( substr($phone,0,4) == '8800') {
                    break;
                }
                if ( substr($phone,0,1) == '8') {
                    $phone = preg_replace('/^(8)/','7',$phone);
                }
                if ( substr($phone,0,1) !== '7') {
                    $phone = '7'.$phone;
                }
                break;
            default:
                break;
        }
        return $phone;
    }

    public function isEmail(string $mail){
        if (strpos($mail, '@', 1) !== false) {
            return true;
        }
        return false;
    }

}