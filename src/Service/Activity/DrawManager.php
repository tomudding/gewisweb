<?php

declare(strict_types=1);

namespace App\Service\Activity;

use App\Entity\Activity\Enums\AllocationMethod;
use App\Entity\Activity\ExternalSignup;
use App\Entity\Activity\Signup;
use App\Entity\Activity\SignupList;
use App\Entity\Decision\Member;
use App\Repository\Activity\ExternalSignupVerificationRepository;
use DateTime;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Random\Randomizer;

use function in_array;

/**
 * Performs the one-shot admission draw on a limited-capacity sign-up list: admit the (optionally shuffled) confirmed
 * sign-ups up to capacity, waitlist the rest, and lock the draw. Shared by the board's manual draw
 * ({@see \App\Twig\Components\Activity\Admin\SignupOverview}) and the automated deadline draw
 * ({@see \App\Command\Activity\RunDueDrawsCommand}) so the guard rules, the exclusion of unverified externals and the
 * locking transaction never diverge.
 *
 * Authorisation (board role, list ownership) stays with the callers; this service trusts what it is given.
 */
final readonly class DrawManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalSignupVerificationRepository $verificationRepository,
    ) {
    }

    /**
     * Board-run draw. Allowed once the list has closed, or -- as a fallback for a missed automated draw -- once the
     * list's own automated draw moment has passed; a board member can never pre-empt the announced moment. The
     * expected method guards against a stale button acting on a since-reconfigured list.
     *
     * Returns whether a draw was actually performed.
     */
    public function drawManually(
        SignupList $list,
        AllocationMethod $method,
        Member $drawnBy,
    ): bool {
        return $this->runDraw(
            $list,
            $method,
            $drawnBy,
            requireDue: false,
        );
    }

    /**
     * Scheduled draw at the list's own automated draw moment ({@see SignupList::getAutoDrawAt()}); the list need not
     * have closed, because {@see \App\Entity\Activity\Enums\DrawCutoffRule::IfFullBefore} and
     * {@see \App\Entity\Activity\Enums\DrawCutoffRule::AfterDurationOpen} legitimately fire while sign-up is still
     * open. A null drawnBy marks the draw as automated.
     *
     * Returns whether a draw was actually performed.
     */
    public function drawAutomatically(SignupList $list): bool
    {
        if ($list->getAllocationMethod()->isManual()) {
            return false;
        }

        return $this->runDraw(
            $list,
            $list->getAllocationMethod(),
            null,
            requireDue: true,
        );
    }

    private function runDraw(
        SignupList $list,
        AllocationMethod $method,
        ?Member $drawnBy,
        bool $requireDue,
    ): bool {
        $performed = false;

        // Serialise concurrent draws of the same list (a double-click, two tabs, or the manual draw racing the
        // automated one): a pessimistic write lock makes the second draw block until the first commits; refresh()
        // then re-reads the now-locked row so the canDraw() recheck sees the freshly set drawnAt and bails -- a
        // lottery is never re-run and its result never changes.
        $this->entityManager->wrapInTransaction(
            function () use ($list, $method, $drawnBy, $requireDue, &$performed): void {
                $this->entityManager->lock(
                    $list,
                    LockMode::PESSIMISTIC_WRITE,
                );
                $this->entityManager->refresh($list);

                if (
                    !$this->canDraw(
                        $list,
                        $method,
                        $requireDue,
                    )
                ) {
                    return;
                }

                // Only confirmed sign-ups take part in the draw: an external guest who has not verified their email
                // is not yet a real participant and must neither be admitted nor take up a capacity slot. A random
                // lottery shuffles; first-come-first-served keeps the creation order.
                $signups = $this->confirmedSignups($list);
                if (AllocationMethod::ConditionalDraw === $method) {
                    $signups = new Randomizer()->shuffleArray($signups);
                }

                $this->applyDraw(
                    $list,
                    $signups,
                    $drawnBy,
                );
                $performed = true;
            },
        );

        return $performed;
    }

    /**
     * Whether the given draw may be run on a list now: it is limited with a real capacity, uses that draw method, has
     * not been drawn yet, and we are within the admission window. An automated draw additionally requires the list's
     * own draw moment to have passed; a manual draw requires the list to have closed or that same moment to have
     * passed (the fallback for a missed automated draw). The capacity guard is essential: without it a capacity-less
     * limited list would admit zero and lock irreversibly.
     */
    private function canDraw(
        SignupList $list,
        AllocationMethod $method,
        bool $requireDue,
    ): bool {
        $allowed = $list->getLimitedCapacity()
            && null !== $list->getCapacity()
            && $list->getCapacity() >= 1
            && $list->getAllocationMethod() === $method
            && !$list->isDrawLocked()
            && SignupAdminWindow::canChangeAdmission($list->getActivity()->getEndTime());

        if (!$allowed) {
            return false;
        }

        return $requireDue
            ? $list->isAutoDrawDue()
            : ($list->isClosed() || $list->isAutoDrawDue());
    }

    /**
     * Admit the first capacity of the (pre-ordered) sign-ups, waitlist the rest (clearing their attendance), then
     * lock the draw with an audit stamp. The draw is a one-shot event and cannot be re-run; later adjustments are
     * manual ({@see \App\Twig\Components\Activity\Admin\SignupOverview::toggleAdmission()}).
     *
     * @param Signup[] $orderedSignups
     */
    private function applyDraw(
        SignupList $list,
        array $orderedSignups,
        ?Member $drawnBy,
    ): void {
        $capacity = $list->getCapacity() ?? 0;
        $position = 0;
        foreach ($orderedSignups as $signup) {
            $admitted = $position < $capacity;
            $signup->setDrawn($admitted);
            if (!$admitted) {
                $signup->setPresent(false);
            }

            ++$position;
        }

        $list->setDrawnAt(new DateTime());
        $list->setDrawnBy($drawnBy);

        $this->entityManager->flush();
    }

    /**
     * A list's sign-ups excluding externals still awaiting e-mail verification: an unconfirmed external is not a real
     * participant, so it must never be drawn nor take up a capacity slot. Queried inside the locking transaction, so
     * it always sees fresh data.
     *
     * @return Signup[]
     */
    private function confirmedSignups(SignupList $list): array
    {
        $pending = $this->verificationRepository->findPendingExternalSignupIdsForList($list);

        $signups = [];
        foreach ($list->getSignUps() as $signup) {
            if (
                $signup instanceof ExternalSignup
                && in_array(
                    $signup->getId(),
                    $pending,
                    true,
                )
            ) {
                continue;
            }

            $signups[] = $signup;
        }

        return $signups;
    }
}
