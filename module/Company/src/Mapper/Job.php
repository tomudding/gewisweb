<?php

namespace Company\Mapper;

use Company\Model\Job as JobModel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Mappers for jobs.
 *
 * NOTE: Jobs will be modified externally by a script. Modifications will be
 * overwritten.
 */
class Job
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
     * Saves all modified entities that are marked persistant.
     */
    public function save()
    {
        $this->em->flush();
    }

    /**
     * @param int $jobId
     *
     * @return JobModel|null
     */
    public function find(int $jobId): ?JobModel
    {
        return $this->getRepository()->find($jobId);
    }

    /**
     * Checks if $slugName is only used by object identified with $cid.
     *
     * @param string $companySlugName
     * @param string $jobSlugName
     * @param int $jobCategoryId
     *
     * @return bool
     */
    public function isSlugNameUnique(string $companySlugName, string $jobSlugName, int $jobCategoryId): bool
    {
        // A slug in unique if there is no other slug of the same category and same company.
        $jobs = $this->findJob(
            jobCategoryId: $jobCategoryId,
            jobSlugName: $jobSlugName,
            companySlugName: $companySlugName,
        );

        return !(count($jobs) > 0);
    }

    /**
     * Find all jobs identified by $jobSlugName that are owned by a company
     * identified with $companySlugName.
     *
     * @param int|null $jobCategoryId
     * @param string|null $jobCategorySlug
     * @param int|null $jobLabelId
     * @param string|null $jobSlugName
     * @param string|null $companySlugName
     *
     * @return array
     */
    public function findJob(
        int $jobCategoryId = null,
        string $jobCategorySlug = null,
        int $jobLabelId = null,
        string $jobSlugName = null,
        string $companySlugName = null,
    ): array {
        $qb = $this->getRepository()->createQueryBuilder('j');
        $qb->select('j');

        if (null !== $jobCategoryId) {
            $qb->join('j.category', 'cat')
                ->andWhere('cat.id = :jobCategoryId')
                ->setParameter('jobCategoryId', $jobCategoryId);
        }

        if (null !== $jobCategorySlug) {
            $qb->innerJoin('j.category', 'cat')
                ->innerJoin(
                    'cat.slug',
                    'loc',
                    Join::WITH,
                    $qb->expr()->orX(
                        'LOWER(loc.valueEN) = :jobCategorySlug',
                        'LOWER(loc.valueNL) = :jobCategorySlug',
                    )
                )
                ->setParameter('jobCategorySlug', $jobCategorySlug);
        }

        if (null !== $jobLabelId) {
            $qb->join('j.labels', 'l')
                ->andWhere('l.id = :jobLabelId')
                ->setParameter('jobLabelId', $jobLabelId);
        }

        if (null !== $jobSlugName) {
            $qb->andWhere('j.slugName = :jobSlugName')
                ->setParameter('jobSlugName', $jobSlugName);
        }

        if (null !== $companySlugName) {
            $qb->join('j.package', 'p')
                ->join('p.company', 'c')
                ->andWhere('c.slugName=:companySlugName')
                ->setParameter('companySlugName', $companySlugName);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param JobModel $job
     *
     * @throws ORMException
     */
    public function persist(JobModel $job)
    {
        $this->em->persist($job);
        $this->em->flush();
    }

    /**
     * Flush.
     */
    public function flush()
    {
        $this->em->flush();
    }

    /**
     * Deletes a particular job.
     *
     * @param JobModel $job
     *
     * @throws ORMException
     */
    public function delete(JobModel $job): void
    {
        $this->em->remove($job);
        $this->em->flush();
    }

    /**
     * Get the repository for this mapper.
     *
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('Company\Model\Job');
    }
}
