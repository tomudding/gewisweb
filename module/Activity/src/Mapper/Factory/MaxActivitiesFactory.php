<?php

declare(strict_types=1);

namespace Activity\Mapper\Factory;

use Activity\Mapper\MaxActivities as MaxActivitiesMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MaxActivitiesFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): MaxActivitiesMapper {
        return new MaxActivitiesMapper($container->get('doctrine.entitymanager.orm_default'));
    }
}
