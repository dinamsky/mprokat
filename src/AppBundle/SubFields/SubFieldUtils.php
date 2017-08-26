<?php

namespace AppBundle\SubFields;

use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\SubField;
use AppBundle\Entity\Card;
use AppBundle\Entity\CardField;
use AppBundle\Entity\FieldInteger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Menu\MenuSubFieldAjax;

class SubFieldUtils extends Controller
{
    private $em;
    private $menu;

    public function __construct(em $em, MenuSubFieldAjax $menu)
    {
        $this->em = $em;
        $this->menu = $menu;
    }

    public function getSubFields($generalTypeId)
    {

        $query = $this->em->createQuery('SELECT f, t FROM AppBundle:CardField f JOIN f.fieldType t WHERE f.generalTypeId = ?1');
        $query->setParameter(1, $generalTypeId);
        $fields = $query->getResult();
        foreach ($fields as $field){
            if ($field->getfieldType()->getformElementType() == 'numberInput'){
                $result[] = array(
                    'template' => 'common/input.html.twig',
                    'value' => $field->getfieldId(),
                    'label' => $field->getfieldType()->getHeader()
                );
            }
            if ($field->getfieldType()->getformElementType() == 'ajaxMenu'){
                $result[] = array(
                    'template' => 'common/ajax_select.html.twig',
                    'value' => $this->menu->getSubField($field->getfieldId(),0),
                    'label' => $field->getfieldType()->getHeader()
                );
            }
        }
        return $result;
    }

    public function getCardSubFields(Card $card)
    {
        $generalTypeId = $card->getGeneralTypeId();

        $query = $this->em->createQuery('SELECT f, t FROM AppBundle:CardField f JOIN f.fieldType t WHERE f.generalTypeId = ?1');
        $query->setParameter(1, $generalTypeId);
        $fields = $query->getResult();


        /**
         * @var $field CardField
         */
        foreach ($fields as $field){
            $storage = 'AppBundle:'.$field->getfieldType()->getStorageType();
            $query = $this->em->createQuery('SELECT s FROM '.$storage.' s WHERE s.cardId = ?1 AND s.cardFieldId = ?2');
            $query->setParameter(1, $card->getId());
            $query->setParameter(2, $field->getFieldType()->getId());
            $value = $query->getSingleResult();

            $type = 'integer';

            if ($field->getfieldType()->getformElementType() == 'ajaxMenu'){
                $value = $this->em
                    ->getRepository(SubField::class)
                    ->find($value->getValue());
                $type = 'subfield';
            }

            $result[] = array(
                'value' => $value,
                'label' => $field->getfieldType()->getHeader(),
                'type' => $type
            );


        }
        return $result;
    }
}