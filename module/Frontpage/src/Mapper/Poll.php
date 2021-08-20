<?php

namespace Frontpage\Mapper;

use Doctrine\ORM\{
    EntityManager,
    EntityRepository,
    ORMException,
};
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Frontpage\Model\{
    Poll as PollModel,
    PollComment as PollCommentModel,
    PollOption as PollOptionModel,
    PollVote as PollVoteModel,
};

/**
 * Mappers for Polls.
 */
class Poll
{
    /**
     * Doctrine entity manager.
     *
     * @var EntityManager
     */
    protected EntityManager $em;

    /**
     * Constructor.
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Returns a poll based on its id.
     *
     * @param int $pollId
     *
     * @return PollModel|null
     */
    public function findPollById(int $pollId): ?PollModel
    {
        return $this->getRepository()->find($pollId);
    }

    /**
     * Returns a poll based on its id.
     *
     * @param int $optionId
     *
     * @return PollOptionModel|null
     *
     * @throws ORMException
     */
    public function findPollOptionById(int $optionId): ?PollOptionModel
    {
        return $this->em->find(PollOptionModel::class, $optionId);
    }

    /**
     * Find the vote of a certain user on a poll.
     *
     * @param int $pollId
     * @param int $lidnr
     *
     * @return PollVoteModel|null
     */
    public function findVote(int $pollId, int $lidnr): ?PollVoteModel
    {
        return $this->em->getRepository(PollVoteModel::class)->findOneBy(
            [
                'poll' => $pollId,
                'respondent' => $lidnr,
            ]
        );
    }

    public function getUnapprovedPolls()
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('p')
            ->from(PollModel::class, 'p')
            ->where('p.approver IS NULL')
            ->orderBy('p.expiryDate', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the latest poll if one is available. Please note that this returns the poll which has its expiryDate
     * furthest into the future, and thus not necessarily the 'newest' poll.
     *
     * @return PollModel|null
     */
    public function getNewestPoll(): ?PollModel
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('p')
            ->from(PollModel::class, 'p')
            ->where('p.approver IS NOT NULL')
            ->andWhere('p.expiryDate > CURRENT_DATE()')
            ->setMaxResults(1)
            ->orderBy('p.expiryDate', 'DESC');

        $res = $qb->getQuery()->getResult();

        return empty($res) ? null : $res[0];
    }

    /**
     * Returns a paginator adapter for paging through all polls.
     *
     * @return DoctrineAdapter
     */
    public function getPaginatorAdapter(): DoctrineAdapter
    {
        $qb = $this->getRepository()->createQueryBuilder('poll');
        $qb->where('poll.approver IS NOT NULL');
        $qb->orderBy('poll.expiryDate', 'DESC');

        return new DoctrineAdapter(new Paginator($qb));
    }

    /**
     * Removes a poll.
     *
     * @param PollModel $poll
     *
     * @throws ORMException
     */
    public function remove(PollModel $poll): void
    {
        $this->em->remove($poll);
    }

    /**
     * Persist.
     *
     * @param PollCommentModel|PollModel|PollOptionModel|PollVoteModel $entity an entity to persist
     *
     * @throws ORMException
     */
    public function persist(PollCommentModel|PollModel|PollOptionModel|PollVoteModel $entity)
    {
        $this->em->persist($entity);
    }

    /**
     * Flush.
     *
     * @throws ORMException
     */
    public function flush()
    {
        $this->em->flush();
    }

    /**
     * Get the repository for this mapper.
     *
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->em->getRepository(PollModel::class);
    }
}
