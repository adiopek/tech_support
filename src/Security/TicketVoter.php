<?php

namespace App\Security;

use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

class TicketVoter extends Voter
{
    public function __construct(
        private Security $security
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Ticket;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var Ticket $ticket */
        $ticket = $subject;

        return $this->canEdit($ticket, $user);
    }

    private function canEdit(Ticket $ticket, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_TECHNICIAN')) {
            return false;
        }

        return $ticket->getAssignedTechnician() !== null &&
               $ticket->getAssignedTechnician()->getEmail() === $user->getEmail();
    }
}
