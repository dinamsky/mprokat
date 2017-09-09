<?php

namespace AppBundle\Menu;

use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Mark;
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
        $array = array(
            ['id'=>'cars', 'header'=>'Легковые автомобили','url' => 'cars'],
            ['id'=>'trucks', 'header'=>'Грузовые автомобили','url' => 'trucks']
        );
        return $array;
    }

    public function getMark($mark_id)
    {
        $query = $this->em->createQuery('SELECT m FROM AppBundle:Mark m WHERE m.id=?1');
        $query->setParameter(1, $mark_id);
        return $query->getSingleResult();
    }

    public function getMarks($groupName)
    {
        $query = $this->em->createQuery('SELECT m FROM AppBundle:Mark m WHERE m.groupName = ?1 AND m.parentId IS NULL');
        $query->setParameter(1, $groupName);
        return $query->getResult();
    }

    public function getModels($parentId)
    {
        $query = $this->em->createQuery('SELECT m FROM AppBundle:Mark m WHERE m.parentId = ?1');
        $query->setParameter(1, $parentId);
        return $query->getResult();
    }

    public function getLimitedArray($ids)
    {
        $mark = array();
        $query = $this->em->createQuery('SELECT m FROM AppBundle:Mark m WHERE m.id IN ( :ids )');
        $query->setParameter('ids', $ids);
        foreach ($query->getResult() as $row){
            $mark[$row->getParentId()][] = $row;
        }
        foreach ($mark as $id=>$models){
            $result[] = array(
                'object' => $this->getMark($id),
                'childs' => $models
            );
        }
        return $result;
    }

    /**
     * @Route("/ajax/getMarks")
     */
    public function getMarksAction(Request $request)
    {
        $groupName = $request->request->get('groupName');
        return $this->render('common/ajax_options_url.html.twig', [
            'options' => $this->getMarks($groupName)
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