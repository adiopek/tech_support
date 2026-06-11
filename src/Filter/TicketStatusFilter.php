<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Ticket;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyInfo\Type;

final class TicketStatusFilter implements FilterInterface
{
    private const VALID_STATUSES = [
        Ticket::STATUS_NEW,
        Ticket::STATUS_ASSIGNED,
        Ticket::STATUS_IN_PROGRESS,
        Ticket::STATUS_DONE,
        Ticket::STATUS_CANCELLED,
    ];

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (!isset($context['filters']['status']) || $resourceClass !== Ticket::class) {
            return;
        }

        $value = $context['filters']['status'];

        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new BadRequestHttpException('Invalid status selected');
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $parameterName = $queryNameGenerator->generateParameterName('status');
        $queryBuilder
            ->andWhere(sprintf('%s.status = :%s', $alias, $parameterName))
            ->setParameter($parameterName, $value);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'status' => [
                'property' => 'status',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filter by ticket status',
            ],
        ];
    }
}
