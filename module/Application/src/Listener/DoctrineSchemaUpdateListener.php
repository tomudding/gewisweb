<?php

namespace Application\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\{
    Event\SchemaColumnDefinitionEventArgs,
    Event\SchemaIndexDefinitionEventArgs,
    Events,
};

/**
 * The `Member.fullName` column and `Member.idx_fullName` index have been created by hand and not with Doctrine. This
 * listener prevents all Doctrine migrations (orm:schema-tool:update) from wanting to remove the column and index.
 */
class DoctrineSchemaUpdateListener implements EventSubscriber
{
    /**
     * @param SchemaColumnDefinitionEventArgs $eventArgs
     */
    public function onSchemaColumnDefinition(SchemaColumnDefinitionEventArgs $eventArgs)
    {
        if (
            'Member' === $eventArgs->getTable() &&
            'fullName' === $eventArgs->getTableColumn()['Field']
        ) {
            $eventArgs->preventDefault();
        }
    }

    /**
     * @param SchemaIndexDefinitionEventArgs $eventArgs
     */
    public function onSchemaIndexDefinition(SchemaIndexDefinitionEventArgs $eventArgs)
    {
        if (
            'Member' === $eventArgs->getTable()
            && 'idx_fullName' === $eventArgs->getTableIndex()['name']
        ) {
            $eventArgs->preventDefault();
        }
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onSchemaColumnDefinition,
            Events::onSchemaIndexDefinition,
        ];
    }
}
