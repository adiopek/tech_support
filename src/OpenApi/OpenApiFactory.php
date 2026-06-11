<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Components;
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
            name: 'x-api-key',
            in: 'header',
            description: 'Use your API key to authenticate. Default keys: admin-token, tech-token'
        );

        $components = $components->withSecuritySchemes($securitySchemes);
        $openApi = $openApi->withComponents($components);

        $openApi = $openApi->withSecurity([['apiKey' => []]]);

        return $openApi;
    }
}
