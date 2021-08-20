<?php

namespace Frontpage\Mapper;

use Doctrine\ORM\{
    EntityManager,
    EntityRepository,
    ORMException,
};
use Frontpage\Model\Page as PageModel;

/**
 * Mappers for Pages.
 */
class Page
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
     * Returns a page.
     *
     * @param string $category
     * @param string|null $subCategory
     * @param string|null $name
     *
     * @return PageModel|null
     */
    public function findPage(string $category, ?string $subCategory = null, ?string $name = null): ?PageModel
    {
        return $this->getRepository()->findOneBy(
            [
                'category' => $category,
                'subCategory' => $subCategory,
                'name' => $name,
            ]
        );
    }

    /**
     * Returns a page based on its id.
     *
     * @param int $pageId
     *
     * @return PageModel|null
     */
    public function findPageById(int $pageId): ?PageModel
    {
        return $this->getRepository()->find($pageId);
    }

    /**
     * Returns all available pages.
     */
    public function getAllPages(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Removes a page.
     *
     * @param PageModel $page
     *
     * @throws ORMException
     */
    public function remove(PageModel $page)
    {
        $this->em->remove($page);
    }

    /**
     * Persist a page.
     *
     * @param PageModel $page
     *
     * @throws ORMException
     */
    public function persist(PageModel $page)
    {
        $this->em->persist($page);
    }

    /**
     * Flush.
     *
     * @throws ORMException
     */
    public function flush()
    {
        $this->em->flush();
    }

    /**
     * Get the repository for this mapper.
     *
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->em->getRepository(PageModel::class);
    }
}
