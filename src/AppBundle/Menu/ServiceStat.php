<?php

namespace AppBundle\Menu;

use AppBundle\Entity\Stat;
use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Card;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface as si;

class ServiceStat extends Controller
{
    private $em;
    private $sess;

    public function __construct(em $em, si $session)
    {
        $this->em = $em;
        $this->sess = $session;
    }

    public function setStat($data)
    {

        $userAgent = isset($_SERVER['HTTP_USER_AGENT'])
            ? strtolower($_SERVER['HTTP_USER_AGENT'])
            : '';

        $is_bot = preg_match(
            "~(google|yahoo|rambler|bot|yandex|spider|snoopy|crawler|finder|mail|curl)~i",
            $userAgent
        );

        if(!$is_bot) {

            if (!isset($this->sess->get('stat_url')[$data['url']][$data['event_type']])) {
                $query = $this->em->createQuery('SELECT s FROM AppBundle:Stat s WHERE s.url=?1 AND s.eventType=?2');
                $query->setParameter(1, $data['url']);
                $query->setParameter(2, $data['event_type']);
                if (count($query->getResult()) > 0) {
                    $stat = $query->getResult()[0];
                    $stat->setQty($stat->getQty() + 1);
                } else {

                    if (!isset($data['card_id'])) $data['card_id'] = null;
                    if (!isset($data['user_id'])) $data['user_id'] = null;

                    $stat = new Stat();
                    $stat->setUrl($data['url']);
                    $stat->setCardId($data['card_id']);
                    $stat->setUserId($data['user_id']);
                    $stat->setEventType($data['event_type']);
                    $stat->setPageType($data['page_type']);
                    $stat->setQty(1);
                }

                if (isset($data['is_empty'])) $stat->setIsEmpty(true);

                if ($this->sess->has('logged_user')) {
                    $stat->setVisitorType('user');
                    $stat->setVisitorId($this->sess->get('logged_user')->getId());
                }
                if ($this->sess->has('admin')) {
                    $stat->setVisitorType('admin');
                    $stat->setVisitorId($this->sess->get('admin')->getId());
                }

                $this->em->persist($stat);
                $this->em->flush();

                $arr = $this->sess->get('stat_url');
                $arr[$data['url']][$data['event_type']] = 1;
                $this->sess->set('stat_url', $arr);
            }
        }
    }

}