<?php

namespace Education\Hydrator\Strategy;

use Education\Model\Enums\ExamTypes;
use Laminas\Hydrator\Strategy\StrategyInterface;

class ExamTypeHydratorStrategy implements StrategyInterface
{
    public function extract(
        $value,
        ?object $object = null,
    ): string {
        if ($value instanceof ExamTypes) {
            return $value->value;
        }

        return ExamTypes::from($value)->value;
    }

    public function hydrate(
        $value,
        ?array $data,
    ): ExamTypes {
        if ($value instanceof ExamTypes) {
            return $value;
        }

        return ExamTypes::from($value);
    }
}