<?php

declare(strict_types=1);

namespace Decision\Model;

use DateTimeInterface;
use Decision\Model\Reference\Member as MemberModelReference;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Photo\Model\Tag as TagModel;
use User\Model\User as UserModel;

/**
 * Member model.
 *
 * @psalm-type MemberGdprArrayType = array{
 *     lidnr: int,
 *     email: ?string,
 *     fullName: string,
 *     lastName: string,
 *     middleName: string,
 *     initials: string,
 *     firstName: string,
 *     birth: string,
 *     generation: int,
 *     type: string,
 *     paid: int,
 *     changedOn: string,
 *     membershipEndsOn: ?string,
 *     expiration: string,
 *     supremum: ?string,
 *     hidden: bool,
 *     deleted: bool,
 * }
 */
#[Entity]
class Member extends MemberModelReference
{
    /**
     * The user.
     */
    #[Id]
    #[Column(type: 'integer')]
    #[OneToOne(targetEntity: UserModel::class)]
    #[JoinColumn(
        name: 'lidnr',
        referencedColumnName: 'lidnr',
    )]
    protected ?int $lidnr;

    /**
     * Member tags.
     *
     * @var Collection<array-key, TagModel>
     */
    #[OneToMany(
        targetEntity: TagModel::class,
        mappedBy: 'member',
        fetch: 'EXTRA_LAZY',
    )]
    protected Collection $tags;

    public function __construct()
    {
        parent::__construct();

        $this->tags = new ArrayCollection();
    }

    /**
     * Convert most relevant items to array.
     *
     * @return MemberGdprArrayType
     */
    public function toGdprArray(): array
    {
        return [
            'lidnr' => $this->getLidnr(),
            'email' => $this->getEmail(),
            'fullName' => $this->getFullName(),
            'lastName' => $this->getLastName(),
            'middleName' => $this->getMiddleName(),
            'initials' => $this->getInitials(),
            'firstName' => $this->getFirstName(),
            'birth' => $this->getBirth()->format(DateTimeInterface::ATOM),
            'generation' => $this->getGeneration(),
            'type' => $this->getType()->value,
            'paid' => $this->getPaid(),
            'changedOn' => $this->getChangedOn()->format(DateTimeInterface::ATOM),
            'membershipEndsOn' => $this->getMembershipEndsOn()?->format(DateTimeInterface::ATOM),
            'expiration' => $this->getExpiration()->format(DateTimeInterface::ATOM),
            'supremum' => $this->getSupremum(),
            'hidden' => $this->getHidden(),
            'deleted' => $this->getDeleted(),
        ];
    }
}
