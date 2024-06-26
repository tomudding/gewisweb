<?php

declare(strict_types=1);

namespace Application\Model\Traits;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

/**
 * A trait which can be used to keep track of when changes where made to an entity.
 *
 * Requires the usage of {@link HasLifecycleCallbacks} on the entity using this trait.
 */
trait TimestampableTrait
{
    /**
     * The date at which the entity was created.
     */
    #[Column(type: 'datetime')]
    protected DateTime $createdAt;

    /**
     * The date at which the entity was updated.
     */
    #[Column(type: 'datetime')]
    protected DateTime $updatedAt;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    private function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    private function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Automatically fill in the `DateTime`s before the initial call to `persist()`.
     */
    #[PrePersist]
    public function prePersist(): void
    {
        $now = new DateTime();

        $this->setCreatedAt($now);
        $this->setUpdatedAt($now);
    }

    /**
     * Automatically update the `updatedAt` `DateTime` when doing an update to the entity.
     */
    #[PreUpdate]
    public function preUpdate(): void
    {
        $this->setUpdatedAt(new DateTime());
    }
}
