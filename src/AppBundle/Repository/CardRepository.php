<?php

namespace AppBundle\Repository;

/**
 * CardRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CardRepository extends \Doctrine\ORM\EntityRepository
{
    public function getLimitedSlider($gt)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT c.id FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = '.$gt.' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setMaxResults(7);
        foreach ($query->getScalarResult() as $cars_id) $cars_ids[] = $cars_id['id'];
        $dql = 'SELECT c,f,p FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.fotos f LEFT JOIN c.cardPrices p WHERE c.id IN ('.implode(",",$cars_ids).') ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
        $query = $em->createQuery($dql);
        return $query->getResult();
    }

    public function getLimitedSliders($gts,$cityId)
    {
        $cars_ids = [];
        $result = [];
        $em = $this->getEntityManager();
        foreach ($gts as $gt) {
            $query = $em->createQuery('SELECT c.id FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = ' . $gt . ' AND c.cityId= ?1 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
            $query->setParameter(1, $cityId);
            $query->setMaxResults(7);
            if(count($query->getScalarResult())<2) {
                $query = $em->createQuery('SELECT c.id FROM AppBundle:Card c JOIN c.tariff t WHERE c.generalTypeId = ' . $gt . ' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
                $query->setMaxResults(7);
            }

            foreach ($query->getScalarResult() as $cars_id) $cars_ids[] = $cars_id['id'];
        }



        $dql = 'SELECT c,f,p,g,m FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.fotos f LEFT JOIN c.cardPrices p LEFT JOIN c.city g LEFT JOIN c.markModel m WHERE c.id IN ('.implode(",",$cars_ids).') ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
        $query = $em->createQuery($dql);
        foreach($query->getResult() as $row){
            $result[$row->getGeneralTypeId()][] = $row;
        }
        return $result;
    }

    public function getTopSlider($cityId)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT c.id FROM AppBundle:Card c JOIN c.tariff t WHERE c.cityId=?1 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setParameter(1, $cityId);
        $query->setMaxResults(20);

        if(count($query->getScalarResult())<6) {
            $query = $em->createQuery('SELECT c.id FROM AppBundle:Card c JOIN c.tariff t ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
            $query->setMaxResults(20);
        }


        foreach ($query->getScalarResult() as $cars_id) $cars_ids[] = $cars_id['id'];
        $dql = 'SELECT c,f,p,g,m FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.fotos f LEFT JOIN c.cardPrices p LEFT JOIN c.city g LEFT JOIN c.markModel m WHERE c.id IN ('.implode(",",$cars_ids).') ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
        $query = $em->createQuery($dql);
        return $query->getResult();
    }

    public function getTop10Slider($cityId)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT c.id FROM AppBundle:Card c JOIN c.tariff t WHERE c.cityId=?1 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setParameter(1, $cityId);
        $query->setMaxResults(10);

        if(count($query->getScalarResult())<10) {
            $query = $em->createQuery('SELECT c.id FROM AppBundle:Card c JOIN c.tariff t ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
            $query->setMaxResults(10);
        }


        foreach ($query->getScalarResult() as $cars_id) $cars_ids[] = $cars_id['id'];
        $dql = 'SELECT c,f,p,g,m FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.fotos f LEFT JOIN c.cardPrices p LEFT JOIN c.city g LEFT JOIN c.markModel m WHERE c.id IN ('.implode(",",$cars_ids).') ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC';
        $query = $em->createQuery($dql);
        return $query->getResult();
    }

    public function getTopOne($gt)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT c,f,p FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.fotos f LEFT JOIN c.cardPrices p WHERE c.generalTypeId = '.$gt.' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.views DESC');
        $query->setMaxResults(1);
//        $q = $query->getResult();
//        $dql = 'SELECT c,f,p FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.fotos f LEFT JOIN c.cardPrices p WHERE c.id = '.$q[0]->getId();
//        $query = $em->createQuery($dql);
        $res = $query->getResult();
        return $res[0];
    }

    public function getTop13_10($cityId)
    {
        // $cityId = $this->sess->get('city')->getId();
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.cityId = ?1 AND c.isActive = 1 ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
        $query->setParameter(1, $cityId);
        $query->setMaxResults(10);
        $query->setFirstResult(10);
        $cars = $query->getResult();
        
        if(count($cars)<10) {
            $ccars_ids = [];
            $carInQ = '';
            if (count($cars) > 0) {
                foreach ($cars as $cars_id) $ccars_ids[] = $cars_id['cardId'];
                $carInQ = 'AND o.cardId NOT IN ('.implode(",",$ccars_ids).') ';
            }
            $query = $em->createQuery('SELECT c FROM AppBundle:Card c JOIN c.tariff t WHERE c.cityId < 1260 AND c.isActive = 1 '.$carInQ.' ORDER BY t.weight DESC, c.dateTariffStart DESC, c.dateUpdate DESC');
            $query->setMaxResults(10 - count($cars));
            $query->setFirstResult(10);
            // return $query->getResult();
            foreach ($query->getResult() as $car) {
                $cars[] = $car;
            }
        }
        return $cars;
    }

    public function getOwnerTop10Slider($cityId)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT DISTINCT o.cardId FROM UserBundle:FormOrder o JOIN AppBundle:Card c WITH c.id = o.cardId WHERE c.cityId = ?1 AND c.isActive = 1 AND o.ownerStatus NOT IN (\'rejected\', \'wait_for_accept\') AND o.isActiveOwner = 1 ORDER BY o.dateCreate DESC');
        $query->setParameter(1, $cityId);
        $query->setMaxResults(10);
        $cars_ids = [];
        foreach ($query->getResult() as $cars_id) $cars_ids[] = $cars_id['cardId'];
        if (count($cars_ids) < 1) {
            return array();
        }
        $dql = 'SELECT c,f,p,g,m FROM AppBundle:Card c JOIN c.tariff t LEFT JOIN c.fotos f LEFT JOIN c.cardPrices p LEFT JOIN c.city g LEFT JOIN c.markModel m WHERE c.id IN ('.implode(",",$cars_ids).') ORDER BY c.dateUpdate DESC';
        $query = $em->createQuery($dql);
        return $query->getResult();
    }
}
