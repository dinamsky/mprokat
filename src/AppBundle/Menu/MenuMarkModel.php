<?php

namespace AppBundle\Menu;

use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MarkBundle\Entity\CarType;
use MarkBundle\Entity\CarMark;
use MarkBundle\Entity\CarModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MenuMarkModel extends Controller
{
    private $em;

    public function __construct(em $em)
    {
        $this->em = $em;
    }

    public function getGroups()
    {
        $query = $this->em->createQuery('SELECT g FROM MarkBundle:CarType g');
        return $query->getResult();
    }

    public function getMark($mark_id)
    {
        $query = $this->em->createQuery('SELECT m FROM MarkBundle:CarMark m WHERE m.id=?1');
        $query->setParameter(1, $mark_id);
        return $query->getSingleResult();
    }

    public function getMarks($groupId)
    {
        $query = $this->em->createQuery('SELECT m FROM MarkBundle:CarMark m WHERE m.carTypeId = ?1');
        $query->setParameter(1, $groupId);
        return $query->getResult();
    }

    public function getModels($markId)
    {
        $query = $this->em->createQuery('SELECT m FROM MarkBundle:CarModel m WHERE m.carMarkId = ?1');
        $query->setParameter(1, $markId);
        return $query->getResult();
    }

//    public function getLimitedArray($ids)
//    {
//        $mark = array();
//        $query = $this->em->createQuery('SELECT m FROM MarkBundle:CarMark m WHERE m.id IN ( :ids )');
//        $query->setParameter('ids', $ids);
//        foreach ($query->getResult() as $row){
//            $mark[$row->getParentId()][] = $row;
//        }
//        foreach ($mark as $id=>$models){
//            $result[] = array(
//                'object' => $this->getMark($id),
//                'childs' => $models
//            );
//        }
//        return $result;
//    }

    /**
     * @Route("/ajax/getMarks")
     */
    public function getMarksAction(Request $request)
    {
        $groupId = $request->request->get('groupId');
        return $this->render('common/ajax_options_url.html.twig', [
            'options' => $this->getMarks($groupId)
        ]);
    }

    /**
     * @Route("/ajax/getModels")
     */
    public function getModelsAction(Request $request)
    {
        $markId = $request->request->get('markId');
        return $this->render('common/ajax_options_url.html.twig', [
            'options' => $this->getModels($markId)
        ]);
    }
}