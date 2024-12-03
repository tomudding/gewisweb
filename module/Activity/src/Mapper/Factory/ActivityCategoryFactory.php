<?php

declare(strict_types=1);

namespace Activity\Mapper\Factory;

use Activity\Mapper\ActivityCategory as ActivityCategoryMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ActivityCategoryFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): ActivityCategoryMapper {
        return new ActivityCategoryMapper($container->get('doctrine.entitymanager.orm_default'));
    }
}
