<?php

namespace Company\Mapper;

use Doctrine\ORM\EntityRepository;

/**
 * Mappers for package.
 *
 * NOTE: Packages will be modified externally by a script. Modifications will be
 * overwritten.
 */
class BannerPackage extends Package
{
    /**
     * Returns a random banner from the active banners.
     */
    public function getBannerPackage()
    {
        $banners = $this->findVisiblePackages();

        if (!empty($banners)) {
            return $banners[array_rand($banners)];
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
        return $this->em->getRepository('Company\Model\CompanyBannerPackage');
    }
}
