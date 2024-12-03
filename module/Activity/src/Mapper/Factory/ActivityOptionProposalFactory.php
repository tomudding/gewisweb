<?php

declare(strict_types=1);

namespace Activity\Mapper\Factory;

use Activity\Mapper\ActivityOptionProposal as ActivityOptionProposalMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ActivityOptionProposalFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): ActivityOptionProposalMapper {
        return new ActivityOptionProposalMapper($container->get('doctrine.entitymanager.orm_default'));
    }
}
