<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\MediaType;
use ApiPlatform\OpenApi\Model\Response;
use App\Dto\AssignTechnicianDto;
use App\Filter\TicketPriorityFilter;
use App\Filter\TicketSerialNumberFilter;
use App\Filter\TicketStatusFilter;
use App\Repository\TicketRepository;
use App\State\TicketAssignProcessor;
use App\State\TicketCollectionProvider;
use App\State\TicketStateProcessor;
use ArrayObject;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['ticket:read']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['ticket:read-collection', 'ticket:read']],
            provider: TicketCollectionProvider::class,
            parameters: [
                'status' => new QueryParameter(filter: TicketStatusFilter::class),
                'priority' => new QueryParameter(filter: TicketPriorityFilter::class),
                'serialNumber' => new QueryParameter(filter: TicketSerialNumberFilter::class),
            ]
        ),
        new Post(denormalizationContext: ['groups' => ['ticket:create']]),
        new Put(
            denormalizationContext: ['groups' => ['ticket:write']],
            security: "is_granted('TICKET_EDIT', object)"
        ),
        new Patch(
            denormalizationContext: ['groups' => ['ticket:write']],
            security: "is_granted('TICKET_EDIT', object)"
        ),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
        new Patch(
            uriTemplate: '/tickets/{id}/assign',
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Technician assigned successfully',
                    ),
                ],
                summary: 'Assign a technician to a ticket',
                description: 'Assigns a technician to a ticket, sets status to ASSIGNED, and records history.',
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/AssignTechnicianDto',
                            ],
                        ],
                    ]),
                    required: true,
                ),
            ),
            normalizationContext: ['groups' => ['ticket:read']],
            security: "is_granted('ROLE_ADMIN')",
            input: AssignTechnicianDto::class,
            processor: TicketAssignProcessor::class
        ),
    ],
    normalizationContext: ['groups' => ['ticket:read']],
    denormalizationContext: ['groups' => ['ticket:write']],
    processor: TicketStateProcessor::class
)]
class Ticket
{
    public const string PRIORITY_LOW = 'LOW';
    public const string PRIORITY_MEDIUM = 'MEDIUM';
    public const string PRIORITY_HIGH = 'HIGH';
    public const string PRIORITY_CRITICAL = 'CRITICAL';

    public const string STATUS_NEW = 'NEW';
    public const string STATUS_ASSIGNED = 'ASSIGNED';
    public const string STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const string STATUS_DONE = 'DONE';
    public const string STATUS_CANCELLED = 'CANCELLED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ticket:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['ticket:read', 'ticket:write', 'ticket:create'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 50, max: 1000)]
    #[Groups(['ticket:read', 'ticket:write', 'ticket:create'])]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [
        self::PRIORITY_LOW,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_HIGH,
        self::PRIORITY_CRITICAL
    ])]
    #[Groups(['ticket:read', 'ticket:write', 'ticket:create'])]
    private ?string $priority = self::PRIORITY_MEDIUM;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [
        self::STATUS_NEW,
        self::STATUS_ASSIGNED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_DONE,
        self::STATUS_CANCELLED
    ])]
    #[Groups(['ticket:read', 'ticket:write'])]
    private ?string $status = self::STATUS_NEW;

    #[ORM\Column]
    #[Groups(['ticket:read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['ticket:read'])]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['ticket:read'])]
    private ?DateTimeImmutable $closedAt = null;

    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['ticket:read', 'ticket:write'])]
    private ?int $version = 1;

    #[ORM\ManyToOne]
    #[Groups(['ticket:read'])]
    private ?Technician $assignedTechnician = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['ticket:read', 'ticket:write', 'ticket:create'])]
    private ?Device $device = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getClosedAt(): ?DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function setClosedAt(?DateTimeImmutable $closedAt): static
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function getAssignedTechnician(): ?Technician
    {
        return $this->assignedTechnician;
    }

    public function setAssignedTechnician(?Technician $assignedTechnician): static
    {
        $this->assignedTechnician = $assignedTechnician;

        return $this;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): static
    {
        $this->device = $device;

        return $this;
    }
}
