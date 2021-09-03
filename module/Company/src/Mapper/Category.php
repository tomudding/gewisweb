<?php

namespace Company\Mapper;

use Company\Model\JobCategory as JobCategoryModel;
use Doctrine\ORM\{
    EntityManager,
    EntityRepository,
    NonUniqueResultException,
    ORMException,
};

/**
 * Mappers for category.
 */
class Category
{
    /**
     * Doctrine entity manager.
     *
     * @var EntityManager
     */
    protected EntityManager $em;

    /**
     * Constructor.
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param JobCategoryModel $jobCategory
     *
     * @throws ORMException
     */
    public function persist(JobCategoryModel $jobCategory): void
    {
        $this->em->persist($jobCategory);
        $this->em->flush();
    }

    /**
     * @param int $id
     *
     * @return JobCategoryModel|null
     */
    public function find(int $id): ?JobCategoryModel
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Finds the category with the given id.
     *
     * @param int $categorySlug
     */
    public function findCategory($categorySlug)
    {
        return $this->getRepository()->findOneBy(['slug' => $categorySlug]);
    }

    /**
     * @return array
     */
    public function findVisibleCategories(): array
    {
        $objectRepository = $this->getRepository(); // From clause is integrated in this statement
        $qb = $objectRepository->createQueryBuilder('c')
            ->select('c')
            ->where('c.hidden = :hidden')
            ->setParameter('hidden', false);

        return $qb->getQuery()->getResult();
    }

    /**
     *
     * @param string $value
     *
     * @return JobCategoryModel|null
     * @throws NonUniqueResultException
     */
    public function findCategoryBySlug(string $value): ?JobCategoryModel
    {
        $qb = $this->getRepository()->createQueryBuilder('c')
            ->select('c')
            ->innerJoin('c.pluralName', 'l', 'WITH', 'l.valueEN = :value')
            ->setParameter(':value', $value);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Deletes the given category.
     *
     * @param JobCategoryModel $category
     *
     * @throws ORMException
     */
    public function delete(JobCategoryModel $category)
    {
        $this->em->remove($category);
        $this->em->flush();
    }

    /**
     * Find all Categories.
     *
     * @return array
     */
    public function findAll(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Get the repository for this mapper.
     *
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->em->getRepository('Company\Model\JobCategory');
    }
}
