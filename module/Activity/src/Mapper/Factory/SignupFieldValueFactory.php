<?php

declare(strict_types=1);

namespace Activity\Mapper\Factory;

use Activity\Mapper\SignupFieldValue as SignupFieldValueMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SignupFieldValueFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): SignupFieldValueMapper {
        return new SignupFieldValueMapper($container->get('doctrine.entitymanager.orm_default'));
    }
}
