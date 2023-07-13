<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Model\Movie;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MovieMinAgeRequiredVoter extends Voter
{
    public const VIEW_DETAILS = 'MOVIE_VIEW_DETAILS';

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return self::VIEW_DETAILS === $attribute && $subject instanceof Movie;
    }

    /**
     * @param Movie $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $user->isOlderThanOrEqual($subject->rated->minAgeRequired(), $this->clock->now());
    }
}
