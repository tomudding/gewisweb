<?php

declare(strict_types=1);

namespace Activity\Form\Factory;

use Activity\Form\Signup as SignupForm;
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
    ): SignupForm {
        return new SignupForm();
    }
}
