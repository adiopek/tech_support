<?php

namespace App\Tests\State;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Ticket;
use App\State\TicketCollectionProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class TicketCollectionProviderTest extends TestCase
{
    public function testProvideAppliesSerialNumberFilter(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $entityManager->method('getRepository')->with(Ticket::class)->willReturn($repository);
        $repository->method('createQueryBuilder')->with('t')->willReturn($queryBuilder);
        
        $queryBuilder->method('leftJoin')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        $query->method('getResult')->willReturn([]);

        // The real problem is that TicketCollectionProvider might not be receiving all filters.
        // Or it's not applying them correctly.
        
        // Mock a QueryCollectionExtensionInterface
        $extension = $this->createMock(QueryCollectionExtensionInterface::class);
        $extension->expects($this->once())->method('applyToCollection');

        $provider = new TicketCollectionProvider($entityManager, [$extension]);

        $operation = new GetCollection(
            class: Ticket::class,
            parameters: [
                'serialNumber' => new \ApiPlatform\Metadata\QueryParameter(
                    filter: \ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter::class,
                    property: 'device.serialNumber',
                    key: 'serialNumber'
                ),
            ]
        );
        $context = ['filters' => ['serialNumber' => 'XYZ123']];

        // We expect it to NOT throw and to call methods on queryBuilder
        $queryBuilder->method('getRootAliases')->willReturn(['t']);
        $queryBuilder->method('andWhere')->willReturnSelf();

        $provider->provide($operation, [], $context);
    }
    public function testProvideAppliesTicketStatusFilter(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $entityManager->method('getRepository')->with(Ticket::class)->willReturn($repository);
        $repository->method('createQueryBuilder')->with('t')->willReturn($queryBuilder);
        
        $queryBuilder->method('leftJoin')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        $query->method('getResult')->willReturn([]);

        $extension = $this->createMock(QueryCollectionExtensionInterface::class);
        $extension->expects($this->once())->method('applyToCollection');

        $provider = new TicketCollectionProvider($entityManager, [$extension]);

        $operation = new GetCollection(
            class: Ticket::class,
            parameters: [
                'status' => new \ApiPlatform\Metadata\QueryParameter(
                    filter: \App\Filter\TicketStatusFilter::class,
                    key: 'status'
                ),
            ]
        );
        $context = ['filters' => ['status' => Ticket::STATUS_NEW]];

        $queryBuilder->method('getRootAliases')->willReturn(['t']);
        $queryBuilder->method('andWhere')->willReturnSelf();

        $provider->provide($operation, [], $context);
    }

    public function testProvideReturnsNullForNonCollectionOperation(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $provider = new TicketCollectionProvider($entityManager, []);

        $operation = new Get(class: Ticket::class);

        $result = $provider->provide($operation, [], []);

        $this->assertNull($result);
    }
}
