<?php

declare(strict_types=1);

namespace Decision\Model\Reference\SubDecision;

use Doctrine\ORM\Mapping\Entity;
use Decision\Model\Reference\SubDecision;

/**
 * Entity for undefined decisions.
 */
#[Entity]
class Other extends SubDecision
{
}
