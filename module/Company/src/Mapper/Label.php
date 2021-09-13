<?php

namespace Company\Mapper;

use Company\Model\JobLabel as JobLabeLModel;
use Doctrine\ORM\{
    EntityManager,
    EntityRepository,
    ORMException,
};

/**
 * Mappers for labels.
 */
class Label
{
    /**
     * Doctrine entity manager.
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor.
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param JobLabeLModel $label
     *
     * @throws ORMException
     */
    public function persist(JobLabeLModel $label)
    {
        $this->em->persist($label);
        $this->em->flush();
    }

    /**
     * Saves all labels.
     */
    public function save()
    {
        $this->em->flush();
    }

    /**
     * Finds the label with the given id.
     *
     * @param int $jobLabelId
     *
     * @return JobLabelModel|null
     */
    public function find(int $jobLabelId): ?JobLabelModel
    {
        return $this->getRepository()->find($jobLabelId);
    }

    /**
     * @return array
     */
    public function findVisibleLabels(): array
    {
        $objectRepository = $this->getRepository(); // From clause is integrated in this statement
        $qb = $objectRepository->createQueryBuilder('c')
            ->select('c')
            ->where('c.hidden = :hidden')
            ->setParameter('hidden', false);

        return $qb->getQuery()->getResult();
    }

    /**
     * Deletes the given label.
     *
     * @param JobLabeLModel $label
     *
     * @throws ORMException
     */
    public function delete(JobLabeLModel $label)
    {
        $this->em->remove($label);
        $this->em->flush();
    }

    /**
     * Find all Labels.
     *
     * @return array
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Get the repository for this mapper.
     *
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('Company\Model\JobLabel');
    }
}
