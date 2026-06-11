<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Ticket;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyInfo\Type;

final class TicketPriorityFilter implements FilterInterface
{
    private const array VALID_PRIORITIES = [
        Ticket::PRIORITY_LOW,
        Ticket::PRIORITY_MEDIUM,
        Ticket::PRIORITY_HIGH,
        Ticket::PRIORITY_CRITICAL,
    ];

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (!isset($context['filters']['priority']) || $resourceClass !== Ticket::class) {
            return;
        }

        $value = $context['filters']['priority'];

        if (!in_array($value, self::VALID_PRIORITIES, true)) {
            throw new BadRequestHttpException('Invalid priority selected');
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $parameterName = $queryNameGenerator->generateParameterName('priority');
        $queryBuilder
            ->andWhere(sprintf('%s.priority = :%s', $alias, $parameterName))
            ->setParameter($parameterName, $value);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'priority' => [
                'property' => 'priority',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filter by ticket priority',
            ],
        ];
    }
}
