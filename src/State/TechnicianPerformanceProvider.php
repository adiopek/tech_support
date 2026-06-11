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
        $sql = "SELECT tech.id as technician_id, 
                       tech.first_name || ' ' || tech.last_name as name,
                       COUNT(t.id) as closed_tickets,
                       AVG(EXTRACT(EPOCH FROM (t.closed_at - t.created_at))) / 3600 as avg_closing_time
                FROM ticket t
                JOIN technician tech ON t.assigned_technician_id = tech.id
                WHERE t.status = 'DONE' AND t.closed_at IS NOT NULL
                GROUP BY tech.id, tech.first_name, tech.last_name";

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
