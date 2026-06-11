<?php

namespace App\MessageHandler;

use App\Message\TicketClosedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TicketClosedHandler
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(TicketClosedEvent $event)
    {
        // Simulate sending email
        $this->logger->info(sprintf('Sending email notification for closed ticket #%d', $event->getTicketId()));
    }
}
