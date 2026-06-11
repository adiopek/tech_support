<?php

namespace App\Tests\Serializer;

use App\Dto\TechnicianPerformanceDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

class TechnicianPerformanceNormalizerTest extends KernelTestCase
{
    public function testNormalizationOfTechnicianPerformanceDto(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $serializer = $container->get(SerializerInterface::class);

        $dto = new TechnicianPerformanceDto(
            1,
            'John Doe',
            5,
            12.345678
        );

        $json = $serializer->serialize($dto, 'json');
        $data = json_decode($json, true);

        // Before custom normalizer, it will likely be 12.345678
        // After our custom normalizer, we want it to be rounded to 2 decimal places, e.g., 12.35
        $this->assertEquals(12.35, $data['averageClosingTimeHours'], 'The average closing time should be rounded to 2 decimal places.');
    }
}
