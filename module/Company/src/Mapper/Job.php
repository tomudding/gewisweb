<?php

namespace Company\Mapper;

use Company\Model\Job as JobModel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

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
     * @param string $companySlug
     * @param string $jobSlug The slugName to be checked
     * @param int $jobCategory
     *
     * @return bool
     */
    public function isSlugNameUnique(string $companySlug, string $jobSlug, int $jobCategory): bool
    {
        // A slug in unique if there is no other slug of the same category and same language
        $objects = $this->findJob(
            [
                'companySlugName' => $companySlug,
                'jobSlug' => $jobSlug,
                'jobCategoryId' => $jobCategory,
            ]
        );

        return !(count($objects) > 0);
    }

    /**
     * Inserts a job into a given package.
     *
     * @param mixed $package
     */
    public function insertIntoPackage($package, $lang, $languageNeutralId)
    {
        $job = new JobModel();
        $job->setLanguage($lang);
        $job->setLanguageNeutralId($languageNeutralId);
        $job->setPackage($package);

        return $job;
    }

    /**
     * Find all jobs identified by $jobSlugName that are owned by a company
     * identified with $companySlugName.
     *
     * @param array $dict
     * @return int|mixed|string
     */
    public function findJob($dict)
    {
        $qb = $this->getRepository()->createQueryBuilder('j');
        $qb->select('j')->join('j.package', 'p')->join('p.company', 'c');

        if (
            array_key_exists('jobCategory', $dict)
            || array_key_exists('jobCategoryId', $dict)
        ) {
            $qb->join('j.category', 'cat');
        }

        if (array_key_exists('jobSlug', $dict)) {
            $jobSlugName = $dict['jobSlug'];
            $qb->andWhere('j.slugName=:jobId');
            $qb->setParameter('jobId', $jobSlugName);
        }

        if (array_key_exists('jobCategory', $dict)) {
            $category = $dict['jobCategory'];
            $qb->andWhere('cat.slug=:category');
            $qb->setParameter('category', $category);
        }

        if (array_key_exists('jobCategoryId', $dict)) {
            $category = $dict['jobCategoryId'];
            $qb->andWhere('cat.id=:category');
            $qb->setParameter('category', $category);
        }

        if (array_key_exists('companySlugName', $dict)) {
            $companySlugName = $dict['companySlugName'];
            $qb->andWhere('c.slugName=:companySlugName');
            $qb->setParameter('companySlugName', $companySlugName);
        }

        return $qb->getQuery()->getResult();
    }

    public function persist($job)
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
     * Deletes the jobs corresponding to the given language neutral id.
     */
    public function deleteByLanguageNeutralId($jobId)
    {
        $jobs = $this->getRepository()->findBy(['languageNeutralId' => $jobId]);
        foreach ($jobs as $job) {
            $this->em->remove($job);
        }

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

    public function createObjectSelectConfig($targetClass, $property, $label, $name, $locale)
    {
        return [
            'name' => $name,
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'options' => [
                'label' => $label,
                'object_manager' => $this->em,
                'target_class' => $targetClass,
                'property' => $property,
                'find_method' => [
                    'name' => 'findBy',
                    'params' => [
                        'criteria' => ['language' => $locale],
                        // Use key 'orderBy' if using ORM
                        //'orderBy'  => ['lastname' => 'ASC'],
                    ],
                ],
            ],
            //'attributes' => [
            //'class' => 'form-control input-sm'
            //]
        ];
    }
}
