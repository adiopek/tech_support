<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class AssignTechnicianDto
{
    #[ApiProperty(required: true)]
    #[Groups(['ticket:write'])]
    #[Assert\NotBlank]
    public ?int $technicianId = null;
}
