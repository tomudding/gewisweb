<?php

declare(strict_types=1);

namespace Activity\Mapper\Factory;

use Activity\Mapper\Activity as ActivityMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ActivityFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): ActivityMapper {
        return new ActivityMapper($container->get('doctrine.entitymanager.orm_default'));
    }
}
