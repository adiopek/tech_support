<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Ticket;
use App\Entity\Device;
use App\Entity\Technician;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TicketApiTest extends ApiTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testGetTickets(): void
    {
        $response = static::createClient()->request('GET', '/api/tickets', [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@context' => '/api/contexts/Ticket']);
    }

    public function testCreateTicket(): void
    {
        $device = $this->entityManager->getRepository(Device::class)->findOneBy([]);

        $response = static::createClient()->request('POST', '/api/tickets', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'title' => 'Test Ticket',
                'description' => 'This is a test ticket description with enough length to pass validation..............................',
                'priority' => Ticket::PRIORITY_HIGH,
                'device' => '/api/devices/' . $device->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'title' => 'Test Ticket',
            'priority' => Ticket::PRIORITY_HIGH,
            'status' => Ticket::STATUS_NEW,
        ]);
    }

    public function testUnauthorizedDelete(): void
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->findOneBy([]);

        static::createClient()->request('DELETE', '/api/tickets/' . $ticket->getId());

        $this->assertResponseStatusCodeSame(401);
    }

    public function testAssignTechnician(): void
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->findOneBy([]);
        $technician = $this->entityManager->getRepository(Technician::class)->findOneBy(['active' => true]);

        $response = static::createClient()->request('POST', '/api/tickets/' . $ticket->getId() . '/assign', [
            'headers' => [
                'x-api-key' => 'admin-token',
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'technicianId' => $technician->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@type' => 'Ticket',
            'status' => Ticket::STATUS_ASSIGNED,
            'assignedTechnician' => [
                'firstName' => $technician->getFirstName(),
                'lastName' => $technician->getLastName(),
            ],
        ]);
    }
}
