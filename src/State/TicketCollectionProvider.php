<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\Extension\PaginationExtension;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class TicketCollectionProvider implements ProviderInterface
{
    private EntityManagerInterface $entityManager;
    private iterable $collectionExtensions;

    public function __construct(EntityManagerInterface $entityManager, iterable $collectionExtensions)
    {
        $this->entityManager = $entityManager;
        $this->collectionExtensions = $collectionExtensions;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|null|object
    {
        if (!$operation instanceof CollectionOperationInterface) {
            return null;
        }

        $queryBuilder = $this->entityManager->getRepository(Ticket::class)->createQueryBuilder('t')
            ->leftJoin('t.device', 'd')
            ->leftJoin('t.assignedTechnician', 'tech');

        $queryNameGenerator = new QueryNameGenerator();
        foreach ($this->collectionExtensions as $extension) {
            if ($extension instanceof QueryCollectionExtensionInterface) {
                $extension->applyToCollection($queryBuilder, $queryNameGenerator, $operation->getClass(), $operation, $context);
            }

            if (
                $extension instanceof QueryResultCollectionExtensionInterface &&
                $extension->supportsResult($operation->getClass(), $operation, $context)
            ) {
                return $extension->getResult($queryBuilder, $operation->getClass(), $operation, $context);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
