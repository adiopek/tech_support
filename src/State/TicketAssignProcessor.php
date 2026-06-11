<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\AssignTechnicianDto;
use App\Entity\Technician;
use App\Entity\Ticket;
use App\Entity\TicketHistory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketAssignProcessor implements ProcessorInterface
{
    private EntityManagerInterface $entityManager;
    private ProcessorInterface $persistProcessor;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, ProcessorInterface $persistProcessor, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->persistProcessor = $persistProcessor;
        $this->security = $security;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof AssignTechnicianDto) {
            return $data;
        }

        $ticketId = $uriVariables['id'] ?? null;
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($ticketId);

        if (!$ticket) {
            throw new NotFoundHttpException('Ticket not found');
        }

        $technician = $this->entityManager->getRepository(Technician::class)->find($data->technicianId);

        if (!$technician) {
            throw new BadRequestHttpException('Technician not found');
        }

        if (!$technician->isActive()) {
            throw new BadRequestHttpException('Technician is not active');
        }

        $oldStatus = $ticket->getStatus();
        
        // Custom logic for assignment
        $ticket->setAssignedTechnician($technician);
        $ticket->setStatus(Ticket::STATUS_ASSIGNED);
        $ticket->setUpdatedAt(new \DateTimeImmutable());

        $history = new TicketHistory();
        $history->setTicket($ticket);
        $history->setOldStatus($oldStatus);
        $history->setNewStatus(Ticket::STATUS_ASSIGNED);
        
        $user = $this->security->getUser();
        if ($user instanceof User) {
            $history->setChangedBy($user);
        }

        $this->entityManager->persist($history);
        
        return $this->persistProcessor->process($ticket, $operation, $uriVariables, $context);
    }
}
