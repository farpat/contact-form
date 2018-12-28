<?php

namespace App\DataFixtures;

use App\Entity\Contact;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct (UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load (ObjectManager $manager)
    {
        for ($i = 1; $i < 5; $i++) {
            $user = new User;
            $user
                ->setUsername("user$i")
                ->setPassword($this->encoder->encodePassword($user, 'secret'));
            $manager->persist($user);
        }

        for ($i = 1; $i < 5; $i++) {
            $contact = new Contact;
            $contact
                ->setName("contact$i")
                ->setEmail("contact$i@local.dev")
                ->setQuestion("Question $i")
                ->setIpAdress("127.0.0.1");
            $manager->persist($contact);
        }


        $manager->flush();
    }
}
