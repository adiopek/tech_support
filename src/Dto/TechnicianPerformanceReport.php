<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use App\State\TechnicianPerformanceProvider;

#[ApiResource(
    shortName: 'TechnicianPerformanceReport',
    operations: [
        new GetCollection(
            uriTemplate: '/reports/technicians-performance',
            openapi: new Operation(
                summary: 'Technicians performance report',
                description: 'Returns statistics about closed tickets and average closing time for each technician.'
            ),
            provider: TechnicianPerformanceProvider::class
        )
    ]
)]
class TechnicianPerformanceReport
{
    /**
     * @var TechnicianPerformanceDto[]
     */
    public array $items = [];
}
