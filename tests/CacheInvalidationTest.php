<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CacheInvalidationTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    public function testTicketCacheHeaders(): void
    {
        $client = static::createClient();
        
        $response = $client->request('GET', '/api/tickets');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Cache-Control', 'max-age=60, public, s-maxage=60');
        $this->assertResponseHeaderHasAttributes($response, 'Cache-Tags');
    }

    public function testCollectionCacheHeaders(): void
    {
        $client = static::createClient();
        
        $response = $client->request('GET', '/api/tickets');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Cache-Control', 'max-age=60, public, s-maxage=60');
        $this->assertResponseHeaderHasAttributes($response, 'Cache-Tags');
        
        $tags = $response->getHeaders()['cache-tags'][0] ?? '';
        $this->assertStringContainsString('/api/tickets', $tags);
    }

    private function assertResponseHeaderHasAttributes(ResponseInterface $response, string $header): void
    {
        $headers = $response->getHeaders();
        $this->assertArrayHasKey(strtolower($header), $headers, "Header $header not found in response.");
        $this->assertNotEmpty($headers[strtolower($header)][0], "Header $header is empty.");
    }
}
