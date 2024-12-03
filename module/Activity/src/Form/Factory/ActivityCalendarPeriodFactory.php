<?php

declare(strict_types=1);

namespace Activity\Form\Factory;

use Activity\Form\ActivityCalendarPeriod as ActivityCalendarPeriodForm;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ActivityCalendarPeriodFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): ActivityCalendarPeriodForm {
        return new ActivityCalendarPeriodForm($container->get(MvcTranslator::class));
    }
}
