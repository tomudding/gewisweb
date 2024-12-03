<?php

declare(strict_types=1);

namespace Activity\Form\Factory;

use Activity\Form\ActivityCategory as CategoryForm;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
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
    ): CategoryForm {
        return new CategoryForm($container->get(MvcTranslator::class));
    }
}
