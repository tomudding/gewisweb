<?php

declare(strict_types=1);

namespace Activity\Mapper\Factory;

use Activity\Mapper\Proposal as ProposalMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ProposalFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): ProposalMapper {
        return new ProposalMapper($container->get('doctrine.entitymanager.orm_default'));
    }
}
