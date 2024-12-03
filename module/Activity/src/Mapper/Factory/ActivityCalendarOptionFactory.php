<?php

declare(strict_types=1);

namespace Activity\Mapper\Factory;

use Activity\Mapper\ActivityCalendarOption as ActivityCalendarOptionMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ActivityCalendarOptionFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): ActivityCalendarOptionMapper {
        return new ActivityCalendarOptionMapper($container->get('doctrine.entitymanager.orm_default'));
    }
}
