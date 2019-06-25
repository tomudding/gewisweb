<?php

namespace Activity\Mapper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Option\Model\MaxActivityOptions as MaxActivityOptionsModel;

class MaxActivities
{
    /**
     * Doctrine entity manager.
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Finds the MaxActivityOptions model with the given id.
     *
     * @param int $id
     * @return MaxActivityOptionsModel
     */
    public function getMaxActivityOptionsById($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Get the repository for this mapper.
     *
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('Activity\Model\MaxActivities');
    }

    /**
     * Finds the MaxActivityOptions model with the given organ and period
     *
     * @param int $organId
     * @param int $periodId
     * @return MaxActivityOptionsModel
     * @throws NonUniqueResultException
     */
    public function getMaxActivityOptionsByOrganPeriod($organId, $periodId)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('x')
            ->from('Activity\Model\MaxActivities', 'x')
            ->where('x.organ = :organ')
            ->where('x.period = :period')
            ->setParameter('organ', $organId)
            ->setParameter('period', $periodId)
            ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
}