<?php

namespace App\Serializer;

use App\Dto\TechnicianPerformanceDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TechnicianPerformanceNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer
    ) {
    }

    /**
     * @param TechnicianPerformanceDto $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if (isset($data['averageClosingTimeHours'])) {
            $data['averageClosingTimeHours'] = round($data['averageClosingTimeHours'], 2);
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TechnicianPerformanceDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            TechnicianPerformanceDto::class => true,
        ];
    }
}
