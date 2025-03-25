<?php

declare(strict_types=1);

namespace Decision\Extensions\Doctrine;

class AttributeDriver extends \Doctrine\ORM\Mapping\Driver\AttributeDriver
{
    /**
     * The {@link \Doctrine\ORM\Mapping\Driver\AttributeDriver}, through the
     * {@link \Doctrine\Persistence\Mapping\Driver\ColocatedMappingDriver} allows for the configuration of excluded
     * paths for the models. Unfortunately, this functionality is not publicly available through the normal
     * configuration.
     *
     * As such, we create this _shadow_ `AttributeDriver` that manually adds the reference entity models from ReportDB
     * to the path exclusion list.
     */
    public function __construct(array $paths, bool $reportFieldsWhereDeclared = false)
    {
        parent::__construct($paths, $reportFieldsWhereDeclared);

        $this->addExcludePaths([
            __DIR__ . '/../../Model/Reference/',
        ]);
    }
}
