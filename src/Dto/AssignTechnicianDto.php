<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource]
class AssignTechnicianDto
{
    #[ApiProperty(required: true)]
    #[Groups(['ticket:write'])]
    #[Assert\NotBlank]
    public ?int $technicianId = null;
}
