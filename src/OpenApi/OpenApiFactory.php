<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Schema;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    private OpenApiFactoryInterface $decorated;

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $components = $openApi->getComponents();

        $securitySchemes = $components->getSecuritySchemes() ?: new \ArrayObject();
        $securitySchemes['apiKey'] = new SecurityScheme(
            type: 'apiKey',
            description: 'Use your API key to authenticate. Default keys: admin-token, tech-token',
            name: 'x-api-key',
            in: 'header'
        );

        $components = $components->withSecuritySchemes($securitySchemes);
        $openApi = $openApi->withComponents($components);
        $openApi = $openApi->withSecurity([['apiKey' => []]]);

        $schemas = $openApi->getComponents()->getSchemas();

        $schema = new Schema();
        $schema['type'] = 'object';
        $schema['properties'] = [
            'technicianId' => [
                'type' => 'integer',
                'example' => 123,
            ],
        ];
        $schema['required'] = ['technicianId'];

        $schemas['AssignTechnicianDto'] = $schema;

        return $openApi->withComponents($components->withSchemas($schemas));
    }
}
