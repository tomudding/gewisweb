<?php

declare(strict_types=1);

namespace Activity\Mapper\Factory;

use Activity\Mapper\SignupList as SignupListMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SignupListFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): SignupListMapper {
        return new SignupListMapper($container->get('doctrine.entitymanager.orm_default'));
    }
}
