<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\TechnicianPerformanceDto;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class TechnicianPerformanceProvider implements ProviderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $sql = "SELECT
                    tech.id AS technician_id,
                    CONCAT_WS(' ', tech.first_name, tech.last_name) AS name,
                    COALESCE(stats.closed_tickets, 0) AS closed_tickets,
                    COALESCE(stats.avg_closing_time, 0) AS avg_closing_time
                FROM technician tech
                         LEFT JOIN (
                    SELECT
                        assigned_technician_id,
                        COUNT(*) AS closed_tickets,
                        AVG(EXTRACT(EPOCH FROM (closed_at - created_at))) / 3600 AS avg_closing_time
                    FROM ticket
                    WHERE status = 'DONE'
                      AND closed_at IS NOT NULL
                    GROUP BY assigned_technician_id
                ) stats
                   ON stats.assigned_technician_id = tech.id";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('technician_id', 'technicianId', 'integer');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('closed_tickets', 'closedTickets', 'integer');
        $rsm->addScalarResult('avg_closing_time', 'averageClosingTimeHours', 'float');

        $nativeQuery = $this->entityManager->createNativeQuery($sql, $rsm);
        $results = $nativeQuery->getResult();

        $dtos = [];
        foreach ($results as $result) {
            $dtos[] = new TechnicianPerformanceDto(
                $result['technicianId'],
                $result['name'],
                $result['closedTickets'],
                (float)$result['averageClosingTimeHours']
            );
        }

        return $dtos;
    }
}
