<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Model\Movie;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MovieVoter extends Voter
{
    public const VIEW_DETAILS = 'MOVIE_VIEW_DETAILS';

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::VIEW_DETAILS === $attribute && $subject instanceof Movie;
    }

    /**
     * @param Movie $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($subject->rated->minAgeRequired() === 0) {
            return true;
        }

        $user = $token->getUser();

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN') === true) {
            return true;
        }

        if (!$user instanceof User) {
            return false;
        }

        return $user->isOlderThanOrEqual($subject->rated->minAgeRequired(), $this->clock->now());
    }
}
