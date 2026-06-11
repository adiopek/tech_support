<?php

namespace App\Message;

class TicketClosedEvent
{
    private int $ticketId;

    public function __construct(int $ticketId)
    {
        $this->ticketId = $ticketId;
    }

    public function getTicketId(): int
    {
        return $this->ticketId;
    }
}
