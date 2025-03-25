<?php

declare(strict_types=1);

namespace Decision\Model;

use Decision\Model\Reference\Address as AddressModelReference;
use Doctrine\ORM\Mapping\Entity;

/**
 * Address model.
 *
 * @psalm-type AddressGdprArrayType = array{
 *     type: string,
 *     street: string,
 *     number: string,
 *     postalCode: string,
 *     city: string,
 *     postalRegion: string,
 *     phone: string,
 *  }
 */
#[Entity]
class Address extends AddressModelReference
{
    /**
     * @return AddressGdprArrayType
     */
    public function toGdprArray(): array
    {
        return [
            'type' => $this->getType()->value,
            'street' => $this->getStreet(),
            'number' => $this->getNumber(),
            'postalCode' => $this->getPostalCode(),
            'city' => $this->getCity(),
            'postalRegion' => $this->getCountry(),
            'phone' => $this->getPhone(),
        ];
    }
}
