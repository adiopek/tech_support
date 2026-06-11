<?php

namespace App\Tests\Filter;

use App\Filter\TicketPriorityFilter;
use App\Entity\Ticket;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TicketPriorityFilterTest extends TestCase
{
    private TicketPriorityFilter $filter;
    private QueryBuilder $queryBuilder;
    private QueryNameGeneratorInterface $queryNameGenerator;

    protected function setUp(): void
    {
        $this->filter = new TicketPriorityFilter();
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->queryNameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
    }

    public function testApplyWithInvalidPriorityThrowsBadRequest(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid priority selected');

        $context = [
            'filters' => [
                'priority' => 'INVALID_PRIORITY',
            ],
        ];

        $this->queryBuilder->expects($this->never())
            ->method('getRootAliases');

        $this->filter->apply($this->queryBuilder, $this->queryNameGenerator, Ticket::class, null, $context);
    }

    public function testApplyWithValidPriority(): void
    {
        $context = [
            'filters' => [
                'priority' => Ticket::PRIORITY_HIGH,
            ],
        ];

        $this->queryBuilder->expects($this->once())
            ->method('getRootAliases')
            ->willReturn(['t']);

        $this->queryNameGenerator->expects($this->once())
            ->method('generateParameterName')
            ->with('priority')
            ->willReturn('priority_p1');

        $this->queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with('t.priority = :priority_p1')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('priority_p1', Ticket::PRIORITY_HIGH)
            ->willReturnSelf();

        $this->filter->apply($this->queryBuilder, $this->queryNameGenerator, Ticket::class, null, $context);
    }
}
