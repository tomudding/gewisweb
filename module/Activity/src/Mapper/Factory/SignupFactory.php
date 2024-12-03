<?php

declare(strict_types=1);

namespace Activity\Mapper\Factory;

use Activity\Mapper\Signup as SignupMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SignupFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): SignupMapper {
        return new SignupMapper($container->get('doctrine.entitymanager.orm_default'));
    }
}
