<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Ticket;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

final class TicketSerialNumberFilter implements FilterInterface
{
    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void
    {
        if (!isset($context['filters']['serialNumber']) || $resourceClass !== Ticket::class) {
            return;
        }

        $value = $context['filters']['serialNumber'];

        $parameterName = $queryNameGenerator->generateParameterName('serialNumber');
        $queryBuilder
            ->andWhere(sprintf('d.serialNumber LIKE :%s', $parameterName))
            ->setParameter($parameterName, '%' . $value . '%');
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'serialNumber' => [
                'property' => 'serialNumber',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filter by device serial number',
            ],
        ];
    }
}
