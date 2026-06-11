<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;

class TechnicianPerformanceDto
{
    #[ApiProperty(identifier: true)]
    public int $technicianId;
    public string $name;
    public int $closedTickets;
    public float $averageClosingTimeHours;

    public function __construct(int $technicianId, string $name, int $closedTickets, float $averageClosingTimeHours)
    {
        $this->technicianId = $technicianId;
        $this->name = $name;
        $this->closedTickets = $closedTickets;
        $this->averageClosingTimeHours = $averageClosingTimeHours;
    }
}
