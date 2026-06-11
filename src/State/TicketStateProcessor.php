<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Ticket;
use App\Entity\TicketHistory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use App\Message\TicketClosedEvent;
use Symfony\Component\Messenger\MessageBusInterface;

class TicketStateProcessor implements ProcessorInterface
{
    private ProcessorInterface $persistProcessor;
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $messageBus;
    private Security $security;

    public function __construct(
        ProcessorInterface $persistProcessor, 
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        Security $security
    ) {
        $this->persistProcessor = $persistProcessor;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->security = $security;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Ticket) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();
        $originalData = $unitOfWork->getOriginalEntityData($data);

        if (isset($originalData['status']) && $originalData['status'] !== $data->getStatus()) {
            $this->validateTransition($originalData['status'], $data->getStatus());
            
            $history = new TicketHistory();
            $history->setTicket($data);
            $history->setOldStatus($originalData['status']);
            $history->setNewStatus($data->getStatus());
            
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $history->setChangedBy($user);
            }
            
            $this->entityManager->persist($history);

            if ($data->getStatus() === Ticket::STATUS_DONE) {
                $data->setClosedAt(new \DateTimeImmutable());
                $this->messageBus->dispatch(new TicketClosedEvent($data->getId()));
            }
            
            $data->setUpdatedAt(new \DateTimeImmutable());
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function validateTransition(string $oldStatus, string $newStatus): void
    {
        $allowedTransitions = [
            Ticket::STATUS_NEW => [Ticket::STATUS_ASSIGNED, Ticket::STATUS_CANCELLED],
            Ticket::STATUS_ASSIGNED => [Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_CANCELLED],
            Ticket::STATUS_IN_PROGRESS => [Ticket::STATUS_DONE, Ticket::STATUS_CANCELLED],
            Ticket::STATUS_DONE => [],
            Ticket::STATUS_CANCELLED => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [], true)) {
            throw new BadRequestHttpException(sprintf('Transition from %s to %s is not allowed.', $oldStatus, $newStatus));
        }
    }
}
