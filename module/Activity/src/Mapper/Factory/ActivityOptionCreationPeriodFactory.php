<?php

declare(strict_types=1);

namespace Activity\Mapper\Factory;

use Activity\Mapper\ActivityOptionCreationPeriod as ActivityOptionCreationPeriodMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ActivityOptionCreationPeriodFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): ActivityOptionCreationPeriodMapper {
        return new ActivityOptionCreationPeriodMapper($container->get('doctrine.entitymanager.orm_default'));
    }
}
