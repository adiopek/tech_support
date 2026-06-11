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

        $technician3 = new Technician();
        $technician3->setFirstName('Robert');
        $technician3->setLastName('Brown');
        $technician3->setEmail('robert@example.com');
        $technician3->setActive(true);
        $manager->persist($technician3);

        $technician4 = new Technician();
        $technician4->setFirstName('Sarah');
        $technician4->setLastName('Wilson');
        $technician4->setEmail('sarah@example.com');
        $technician4->setActive(true);
        $manager->persist($technician4);

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

        $device3 = new Device();
        $device3->setSerialNumber('SN11111');
        $device3->setModel('Dell XPS 15');
        $device3->setCustomerName('Initech');
        $manager->persist($device3);

        $device4 = new Device();
        $device4->setSerialNumber('SN22222');
        $device4->setModel('HP Spectre x360');
        $device4->setCustomerName('Umbrella Corp');
        $manager->persist($device4);

        $device5 = new Device();
        $device5->setSerialNumber('SN33333');
        $device5->setModel('Asus ZenBook');
        $device5->setCustomerName('Hooli');
        $manager->persist($device5);

        // Tickets
        $ticket1 = new Ticket();
        $ticket1->setTitle('Broken Screen');
        $ticket1->setDescription('The screen is flickering constantly and showing horizontal lines. It makes the laptop unusable for daily tasks.');
        $ticket1->setPriority(Ticket::PRIORITY_HIGH);
        $ticket1->setStatus(Ticket::STATUS_NEW);
        $ticket1->setDevice($device1);
        $manager->persist($ticket1);

        $ticket2 = new Ticket();
        $ticket2->setTitle('Battery Issue');
        $ticket2->setDescription('Battery does not hold charge for more than 10 minutes. The device shuts down abruptly without any warning.');
        $ticket2->setPriority(Ticket::PRIORITY_MEDIUM);
        $ticket2->setStatus(Ticket::STATUS_DONE);
        $ticket2->setDevice($device2);
        $ticket2->setAssignedTechnician($technician1);
        $ticket2->setCreatedAt(new \DateTimeImmutable('-2 days'));
        $ticket2->setClosedAt(new \DateTimeImmutable('-1 day'));
        $manager->persist($ticket2);

        $ticket3 = new Ticket();
        $ticket3->setTitle('Keyboard malfunction');
        $ticket3->setDescription('Several keys are not responding, especially the "E" and "R" keys. This started happening after a small coffee spill.');
        $ticket3->setPriority(Ticket::PRIORITY_MEDIUM);
        $ticket3->setStatus(Ticket::STATUS_ASSIGNED);
        $ticket3->setDevice($device3);
        $ticket3->setAssignedTechnician($technician3);
        $manager->persist($ticket3);

        $ticket4 = new Ticket();
        $ticket4->setTitle('Overheating problem');
        $ticket4->setDescription('The laptop gets extremely hot within minutes of starting any application. Fans are making a very loud grinding noise.');
        $ticket4->setPriority(Ticket::PRIORITY_CRITICAL);
        $ticket4->setStatus(Ticket::STATUS_IN_PROGRESS);
        $ticket4->setDevice($device4);
        $ticket4->setAssignedTechnician($technician4);
        $ticket4->setCreatedAt(new \DateTimeImmutable('-5 hours'));
        $manager->persist($ticket4);

        $ticket5 = new Ticket();
        $ticket5->setTitle('Software updates failing');
        $ticket5->setDescription('System updates consistently fail with error code 0x80070005. Tried rebooting multiple times but it does not help.');
        $ticket5->setPriority(Ticket::PRIORITY_LOW);
        $ticket5->setStatus(Ticket::STATUS_NEW);
        $ticket5->setDevice($device5);
        $manager->persist($ticket5);

        $ticket6 = new Ticket();
        $ticket6->setTitle('Blue Screen of Death');
        $ticket6->setDescription('Random BSOD occurs every few hours. The error message is "MEMORY_MANAGEMENT". Likely a faulty RAM module.');
        $ticket6->setPriority(Ticket::PRIORITY_HIGH);
        $ticket6->setStatus(Ticket::STATUS_DONE);
        $ticket6->setDevice($device1);
        $ticket6->setAssignedTechnician($technician1);
        $ticket6->setCreatedAt(new \DateTimeImmutable('-5 days'));
        $ticket6->setClosedAt(new \DateTimeImmutable('-3 days'));
        $manager->persist($ticket6);

        $ticket7 = new Ticket();
        $ticket7->setTitle('Wifi Connectivity Issues');
        $ticket7->setDescription('The laptop frequently disconnects from the Wi-Fi network even when sitting right next to the router. Other devices work fine.');
        $ticket7->setPriority(Ticket::PRIORITY_MEDIUM);
        $ticket7->setStatus(Ticket::STATUS_CANCELLED);
        $ticket7->setDevice($device2);
        $ticket7->setAssignedTechnician($technician3);
        $ticket7->setCreatedAt(new \DateTimeImmutable('-1 week'));
        $manager->persist($ticket7);

        $manager->flush();
    }
}
