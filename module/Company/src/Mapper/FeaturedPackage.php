<?php

namespace Company\Mapper;

use Doctrine\ORM\EntityRepository;

/**
 * Mappers for package.
 *
 * NOTE: Packages will be modified externally by a script. Modifications will be
 * overwritten.
 */
class FeaturedPackage extends Package
{
    /**
     * Returns a random featured package from the active featured packages,
     * and null when there is no featured package.
     */
    public function getFeaturedPackage()
    {
        $featuredPackages = $this->findVisiblePackages();

        if (!empty($featuredPackages)) {
            return $featuredPackages[array_rand($featuredPackages)];
        }

        return null;
    }

    /**
     * Get the repository for this mapper.
     *
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->em->getRepository('Company\Model\CompanyFeaturedPackage');
    }
}
