<?php

namespace App\DataFixtures;

use App\Entity\Device;
use App\Entity\Technician;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Users
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setApiKey('admin-token');
        $manager->persist($admin);

        $techUser = new User();
        $techUser->setEmail('tech@example.com');
        $techUser->setRoles(['ROLE_TECHNICIAN']);
        $techUser->setPassword($this->passwordHasher->hashPassword($techUser, 'tech123'));
        $techUser->setApiKey('tech-token');
        $manager->persist($techUser);

        // Technicians
        $technician1 = new Technician();
        $technician1->setFirstName('John');
        $technician1->setLastName('Doe');
        $technician1->setEmail('tech@example.com');
        $technician1->setActive(true);
        $manager->persist($technician1);

        $technician2 = new Technician();
        $technician2->setFirstName('Jane');
        $technician2->setLastName('Smith');
        $technician2->setEmail('jane@example.com');
        $technician2->setActive(false);
        $manager->persist($technician2);

        // Devices
        $device1 = new Device();
        $device1->setSerialNumber('SN12345');
        $device1->setModel('ThinkPad X1');
        $device1->setCustomerName('ACME Corp');
        $manager->persist($device1);

        $device2 = new Device();
        $device2->setSerialNumber('SN67890');
        $device2->setModel('MacBook Pro');
        $device2->setCustomerName('Globex');
        $manager->persist($device2);

        // Tickets
        $ticket1 = new Ticket();
        $ticket1->setTitle('Broken Screen');
        $ticket1->setDescription('The screen is flickering.');
        $ticket1->setPriority(Ticket::PRIORITY_HIGH);
        $ticket1->setStatus(Ticket::STATUS_NEW);
        $ticket1->setDevice($device1);
        $manager->persist($ticket1);

        $ticket2 = new Ticket();
        $ticket2->setTitle('Battery Issue');
        $ticket2->setDescription('Battery does not hold charge.');
        $ticket2->setPriority(Ticket::PRIORITY_MEDIUM);
        $ticket2->setStatus(Ticket::STATUS_DONE);
        $ticket2->setDevice($device2);
        $ticket2->setAssignedTechnician($technician1);
        $ticket2->setCreatedAt(new \DateTimeImmutable('-2 days'));
        $ticket2->setClosedAt(new \DateTimeImmutable('-1 day'));
        $manager->persist($ticket2);

        $manager->flush();
    }
}
