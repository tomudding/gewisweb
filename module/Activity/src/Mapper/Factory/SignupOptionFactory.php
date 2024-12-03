<?php

declare(strict_types=1);

namespace Activity\Mapper\Factory;

use Activity\Mapper\SignupOption as SignupOptionMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SignupOptionFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): SignupOptionMapper {
        return new SignupOptionMapper($container->get('doctrine.entitymanager.orm_default'));
    }
}
