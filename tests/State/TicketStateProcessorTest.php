<?php

namespace App\Tests\State;

use ApiPlatform\Metadata\Operation;
use App\Entity\Ticket;
use App\Entity\User;
use App\State\TicketStateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use ApiPlatform\State\ProcessorInterface;

class TicketStateProcessorTest extends TestCase
{
    public function testProcessWorksInStatelessContext(): void
    {
        $persistProcessor = $this->createMock(ProcessorInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $security = $this->createMock(Security::class);
        $unitOfWork = $this->createMock(UnitOfWork::class);

        $ticket = new Ticket();
        $ticket->setStatus(Ticket::STATUS_ASSIGNED);

        $entityManager->method('getUnitOfWork')->willReturn($unitOfWork);
        $unitOfWork->method('getOriginalEntityData')->willReturn(['status' => Ticket::STATUS_NEW]);

        $user = new User();
        $security->method('getUser')->willReturn($user);

        $processor = new TicketStateProcessor($persistProcessor, $entityManager, $messageBus, $security);

        $persistProcessor->expects($this->once())
            ->method('process')
            ->willReturn($ticket);

        $result = $processor->process($ticket, $this->createMock(Operation::class));
        
        $this->assertSame($ticket, $result);
    }
}
